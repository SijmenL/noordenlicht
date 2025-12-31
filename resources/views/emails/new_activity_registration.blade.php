@extends('emails.layouts.mail')

@section('title')
    <h1 class="email-title">Er is een inschrijving op je activiteit binnen!</h1>
@endsection

@section('info')
    @php
        use Illuminate\Support\Str;
        use App\Models\ActivityFormResponses;
        use App\Models\Activity;

        // Fetch the latest form submission for the given activity ID
        $latestSubmissionGroup = ActivityFormResponses::where('activity_id', $data['relevant_id'])
            ->with('formElement') // Eager-load the related form elements
            ->orderBy('submitted_id', 'desc') // Order by the latest submission
            ->get()
            ->groupBy('submitted_id') // Group submissions by submitted_id
            ->first(); // Take the latest group

        $activity = Activity::findOrFail($data["relevant_id"])

    @endphp

    @if($latestSubmissionGroup)
        <div>
            <p>Beste {{ $data['reciever_name'] }}, er is een inschrijving binnen op {{ $activity->title }}.</p>
            <br>

            {{-- Display the latest submission --}}
            <table class="post">
                <tbody>
                {{-- Loop through the latest submission group --}}
                @php
                    $groupedEntries = $latestSubmissionGroup->groupBy('activity_form_element_id');
                @endphp

                @foreach($groupedEntries as $formElementId => $entries)
                    <tr>
                        <td style="width: 10vw; background: rgba(255,255,255,0.3)">
                            {{ $entries[0]->formElement->label }}
                        </td>
                        <td style="background: rgba(255,255,255,0.3)">
                            {{ $entries->pluck('response')->join(', ') }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <br>

            @php
                $routeParams = [
                                           'month'     => 0,
                                           'view'      => 'month',
                                           'startDate' => $activity->date_start->format('Y-m-d'),
                                           'id'        => $activity->id,
                                       ];
            @endphp
            <a class="action-button" href="{{ route('agenda.activity', $routeParams) }}">
                Klik hier om alle inschrijvingen te bekijken!
            </a>
        </div>
    @endif
@endsection

@section('main_footer')
    <td style="padding-top: 30px;">
        <p class="footer-bold">Waarom ontvang jij deze email?</p>
        <p class="footer-text">
            Deze email is automatisch gegenereerd omdat iemand zich heeft ingeschreven op je activiteit.
            Als je deze notificaties niet meer wilt ontvangen, wijzig dan je instellingen op de instellingen pagina.
        </p>
    </td>
@endsection

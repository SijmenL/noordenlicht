@extends('emails.layouts.mail')

@php
    use Illuminate\Support\Str;

    $ticket = \App\Models\Ticket::findOrFail($data['relevant_id']);
@endphp

@section('title')
    <h1 class="email-title">Ticket {{ $ticket->activity->title }}.</h1>
@endsection

@section('greeting')
    <div class="centered">
        Hier is jouw ticket voor <strong>{{ $ticket->activity->title }}</strong>
    </div>
@endsection

@section('info')
    <div class="box">
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 15px; color: #0e1e3b;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Activiteit:</td>
                <td style="padding: 8px 0; text-align: right; vertical-align: top;">{{ $ticket->activity->title }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Datum:</td>
                <td style="padding: 8px 0; text-align: right; vertical-align: top;">{{ \Carbon\Carbon::parse($ticket->start_date)->format('d-m-Y H:i') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Naam:</td>
                <td style="padding: 8px 0; text-align: right; vertical-align: top;">{{ $ticket->user->name ?? $ticket->order->first_name . ' ' . $ticket->order->last_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Ticket ID:</td>
                <td style="padding: 8px 0; text-align: right; font-family: monospace; vertical-align: top;">{{ $ticket->uuid }}</td>
            </tr>
        </table>
    </div>
@endsection

@section('action')
    <a href="{{ route('ticket.download', $ticket->uuid) }}" class="action-button">
        Download PDF
    </a>
@endsection

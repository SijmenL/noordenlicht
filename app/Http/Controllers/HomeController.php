<?php

namespace App\Http\Controllers;

use App\Models\Accommodatie;
use App\Models\Activity;
use App\Models\ActivityException;
use App\Models\News;
use App\Traits\AgendaPublicScheduleTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    use AgendaPublicScheduleTrait;

    public function index(Request $request)
    {
        $news = News::where('accepted', true)
            ->orderBy('date', 'desc')
            ->paginate(3);


        $locations = Accommodatie::orderBy('name')->paginate(25);

        $monthOffset = $request->query('month', 0);
        $limit = 3;

        $activities = $this->fetchAndProcessActivities($monthOffset, $limit);


        $agendaViewData = $this->getAgendaViewData($monthOffset, $limit);

        return view('home', ['news' => $news, 'locations' => $locations, 'activities' => $activities] + $agendaViewData);
    }
    public function eula() {
        return view('policies.eula');
    }

    public function cancellationPolicy() {
        return view('policies.cancelation');
    }

    public function privacyPolicy() {
        return view('policies.privacy');
    }

    public function rules() {
        return view('policies.rules');
    }

}

<?php

namespace App\Http\Controllers;

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

        $locations = [
            ['name' => 'De Hinde', 'image' => 'img/photo/compressed/Hinde4.webp'],
            ['name' => 'De Libelle', 'image' => 'img/photo/compressed/Libelle1.webp'],
            ['name' => 'De Otter', 'image' => 'img/photo/compressed/Otter1.webp'],
            ['name' => 'THuus', 'image' => 'img/photo/compressed/thuus1.webp'],
            ['name' => 'TWinkeltje', 'image' => 'img/photo/compressed/TWinkeltje1.webp'],
            ['name' => 'De VuurVlinder', 'image' => 'img/photo/compressed/vuurvlinder4.webp'],
            ['name' => 'De Witte Raaf', 'image' => 'img/photo/compressed/WitteRaaf3.webp'],
        ];

        $monthOffset = $request->query('month', 0);
        $limit = 3;

        $activities = $this->fetchAndProcessActivities($monthOffset, $limit);
        $agendaViewData = $this->getAgendaViewData($monthOffset, $limit);

        return view('home', ['news' => $news, 'locations' => $locations, 'activities' => $activities] + $agendaViewData);
    }
}

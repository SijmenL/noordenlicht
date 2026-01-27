<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\LapostaService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LapostaController extends Controller
{
    protected $laposta;

    public function __construct(LapostaService $laposta)
    {
        $this->laposta = $laposta;
    }

    /**
     * Handle Public Newsletter Subscription
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Attempt to get list ID from config or fetch the first one available
        $listId = config('services.laposta.list');

        if (!$listId) {
            return response()->json([
                'success' => false,
                'message' => 'Configuratiefout: Geen nieuwsbrief lijst gevonden.'
            ], 500);
        }

        $success = $this->laposta->subscribe($request->email, $listId);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Bedankt voor je inschrijving! Je ontvangt binnenkort een bevestiging.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Er is helaas iets misgegaan. Probeer het later opnieuw.'
        ], 500);
    }


    /**
     * Show Newsletters (Campaigns)
     */
    public function campaigns(Request $request)
    {
        // 1. Fetch campaigns and wrap in a collection
        $allCampaigns = collect($this->laposta->getCampaigns());

        // 2. Sort by date (newest first)
        $allCampaigns = $allCampaigns->sortByDesc(function ($item) {
            // Accessing the nested 'campaign' key based on your Blade file structure
            return $item['campaign']['delivery_requested'] ?? 0;
        });

        // 3. Apply Search Filter
        if ($request->filled('search')) {
            $search = strtolower($request->get('search'));
            $allCampaigns = $allCampaigns->filter(function ($item) use ($search) {
                $name = strtolower($item['campaign']['name'] ?? '');
                $subject = strtolower($item['campaign']['subject'] ?? '');
                return str_contains($name, $search) || str_contains($subject, $search);
            });
        }

        // 4. Manual Pagination Logic
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;

        // Slice the collection to get only the items for the current page
        $currentPageItems = $allCampaigns->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // Create the paginator
        $campaigns = new LengthAwarePaginator(
            $currentPageItems,
            $allCampaigns->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.news.newsletter.campaigns', [
            'campaigns' => $campaigns,
            'search' => $request->get('search'),
        ]);
    }

    /**
     * Refresh ALL Data (Lists + Campaigns + Latest Content)
     */
    public function refresh()
    {
        $this->laposta->refreshAll();
        return back()->with('success', 'Alle data is ververst en gecacht.');
    }

    public function publicList(Request $request)
    {
        // 1. Fetch campaigns
        $allCampaigns = collect($this->laposta->getCampaigns());

        // 2. Sort by date (newest first)
        $allCampaigns = $allCampaigns->sortByDesc(function ($item) {
            return $item['campaign']['delivery_requested'] ?? 0;
        });

        // 3. Search Filter
        if ($request->filled('search')) {
            $search = strtolower($request->get('search'));
            $allCampaigns = $allCampaigns->filter(function ($item) use ($search) {
                $name = strtolower($item['campaign']['name'] ?? '');
                $subject = strtolower($item['campaign']['subject'] ?? '');
                return str_contains($name, $search) || str_contains($subject, $search);
            });
        }

        // 4. Paginate (10 per page)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 9;
        $currentPageItems = $allCampaigns->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $campaigns = new LengthAwarePaginator(
            $currentPageItems,
            $allCampaigns->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('newsletter.list', [
            'campaigns' => $campaigns,
            'search' => $request->get('search'),
        ]);
    }
}

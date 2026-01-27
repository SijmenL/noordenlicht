<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LapostaService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.laposta.nl/v2';

    // Cache duration in seconds (60 minutes)
    protected $cacheDuration = 3600;

    public function __construct()
    {
        $this->apiKey = config('services.laposta.key');
    }


    public function getLists()
    {
        return Cache::remember('laposta_lists', $this->cacheDuration, function () {
            return $this->fetchFromApi('/list');
        });
    }


    public function getCampaigns()
    {
        return Cache::remember('laposta_campaigns', $this->cacheDuration, function () {
            return $this->fetchFromApi('/campaign');
        });
    }

    /**
     * Subscribe an email to a list
     */
    public function subscribe($email, $listId)
    {
        try {
            if (empty($this->apiKey)) {
                Log::error('Laposta API Key is missing in config/services.php');
                return false;
            }

            // Laposta API: POST /member
            // https://api.laposta.nl/doc/member
            $response = Http::withBasicAuth($this->apiKey, '')
                ->post("{$this->baseUrl}/member", [
                    'list_id' => $listId,
                    'email' => $email,
                    'ip' => request()->ip(),
                    'source_url' => request()->url(),
                    'suppress_email_notification' => true // Set to false if you want Admin notifications
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error("Laposta Subscribe Failed: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("Laposta Subscribe Exception: " . $e->getMessage());
            return false;
        }
    }


    public function refreshAll()
    {
        // 1. Clear old keys
        Cache::forget('laposta_lists');
        Cache::forget('laposta_campaigns');
        Cache::forget('laposta_latest_campaign_html');

        // 2. Fetch and Cache Lists
        $this->getLists();

        // 3. Fetch and Cache Campaigns
        $campaigns = $this->fetchFromApi('/campaign/'.config('services.laposta.list'));
        Cache::put('laposta_campaigns', $campaigns, $this->cacheDuration);

        // 4. Pre-fetch the latest campaign's HTML for the frontend
        //    This ensures the website user never waits for the API.
        if (!empty($campaigns)) {
            $latest = collect($campaigns)
                ->where('state', 'sent')
                ->sortByDesc('delivery_requested')
                ->first();

            if ($latest) {
                $content = $this->fetchFromApi("/campaign/{$latest['campaign_id']}/content");
                if (isset($content['html'])) {
                    Cache::put('laposta_latest_campaign_html', $content['html'], $this->cacheDuration);
                    // Also cache this specific ID while we are at it
                    Cache::put("laposta_campaign_{$latest['campaign_id']}", $content['html'], $this->cacheDuration);
                }
            }
        }

        return true;
    }

    /**
     * Generic API Fetcher
     */
    protected function fetchFromApi($endpoint)
    {
        try {
            if (empty($this->apiKey)) {
                Log::error('Laposta API Key is missing in config/services.php');
                return [];
            }

            $response = Http::withBasicAuth($this->apiKey, '')
                ->get("{$this->baseUrl}{$endpoint}");

            if ($response->successful()) {
                $json = $response->json();

                // Laposta usually wraps lists in 'data', but content might be direct.
                // We check if 'data' exists, otherwise return the whole response (for content).
                if (isset($json['data'])) {
                    return $json['data'];
                }

                return $json;
            }

            Log::error("Laposta API Error [{$endpoint}]: " . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error("Laposta Connection Error [{$endpoint}]: " . $e->getMessage());
            return [];
        }
    }
}

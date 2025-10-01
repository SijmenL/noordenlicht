<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Log;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class NewsController extends Controller
{
    public function viewNewsPage(Request $request)
    {
        $search = request('search');

        // Get the 'items' query parameter or default to 25 if not set
        $items = $request->query('items', 25);

        // Ensure 'items' is a positive integer
        if (!is_numeric($items) || $items <= 0) {
            $items = 25;
        }

        // Fetch the news items based on the 'items' parameter
        $news = News::where('accepted', true)
            ->orderBy('date', 'desc')
            ->when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%')
                        ->orWhere('category', 'like', '%' . $search . '%')
                        ->orWhere('date', 'like', '%' . $search . '%')
                        ->orWhere('category', 'like', '%' . $search . '%');
                });
            })
            ->paginate((int)$items);


        return view('news.list', ['news' => $news, 'search' => $search, 'items' => $items]);
    }


    public function viewNewsItem($id)
    {
        if ($id === '-1') {
            $news = null;
        } else {
            try {
                $news = News::find($id);
            } catch (ModelNotFoundException $exception) {
                $log = new Log();
                $log->createLog(auth()->user()->id, 1, 'View news items', 'news', 'News id: ' . $id, 'Nieuws bestaat niet');
                $news = null;
            }
            if ($news === null) {
                $log = new Log();
                $log->createLog(auth()->user()->id, 1, 'View news items', 'news', 'News id: ' . $id, 'Nieuws bestaat niet');
                $news = null;
            }
        }


        return view('news.item', ['news' => $news]);
    }
}

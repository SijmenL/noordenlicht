<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Log;
use App\Models\News;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use DOMDocument;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Exports\UsersExport;

class AdminController extends Controller
{
    public function admin()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $contact = Contact::where('done', false)->count();
        $orders = Order::where('status', 'paid')->count();
        $signup = User::where('allow_booking', false)->count();

        $totalNotifications = $contact + $orders + $signup;

        return view('admin.admin', ['user' => $user, 'roles' => $roles, 'totalNotifications' => $totalNotifications, 'contact' => $contact, 'orders' => $orders, 'signup' => $signup]);
    }

    public function notifications()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        return view('admin.notifications.send', ['user' => $user, 'roles' => $roles]);
    }

    public function notificationsSend(Request $request)
    {
        $request->validate([
            'users' => 'integer|required',
            'display_text' => 'string|required',
        ]);

        $user = User::findOrFail($request->users);

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'Send notification', 'Admin', $user->name . ' ' . $user->infix . ' ' . $user->last_name, $request->display_text);

        $notification = new Notification();
        $notification->sendNotification(null, [$user->id], $request->display_text, '', '', 'admin');


        return redirect()->route('admin.notifications')->with('success', 'Notificatie verzonden!');
    }

    public function debugMail()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        return view('admin.debug.mails.mails', ['user' => $user, 'roles' => $roles]);
    }

    public function mail($id)
    {
        $user = Auth::user();

        $data = [
            'reciever_name' => $user->name,
            'message' => 'John heeft je post geliked',
            'link' => '/dolfijnen/post/15',
            'relevant_id' => 12,
            'location' => 'les',
            'sender_full_name' => 'John Doe',
            'sender_dolfijnen_name' => 'Balder',
            'reciever_is_dolfijn' => false,
            'email' => 'lokerssijmen@gmail.com', // Using the user's email
        ];
        return view('emails.'.$id, ['data' => $data]);
    }

    // Logs

    public function logs()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $search = request('search');
        $search_user = request('user');

        $logs = Log::where(function ($query) use ($search) {
            $query->where('display_text', 'like', '%' . $search . '%')
                ->orWhere('type', 'like', '%' . $search . '%')
                ->orWhere('reference', 'like', '%' . $search . '%')
                ->orWhere('created_at', 'like', '%' . $search . '%')
                ->orWhere('location', 'like', '%' . $search . '%');
        })
            ->where(function ($query) use ($search_user) {
                if ($search_user) {
                    $query->where('user_id', $search_user);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25);


        return view('admin.logs.list', ['user' => $user, 'roles' => $roles, 'logs' => $logs, 'search' => $search, 'search_user' => $search_user]);
    }

// Contact
    public function contact()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $search = request('search');
        $seen = request('seen');

        if ($seen !== 'all' && $seen !== 'seen' && $seen !== 'unseen') {
            $seen = 'all';
        }

        if ($seen === 'all') {
            $contact_submissions = Contact::query()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('message', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->paginate(25);
        }
        if ($seen === 'seen') {
            $contact_submissions = Contact::query()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('message', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
                })
                ->where('done', true)
                ->orderBy('created_at', 'desc')
                ->paginate(25);
        }
        if ($seen === 'unseen') {
            $contact_submissions = Contact::query()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('message', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
                })
                ->where('done', false)
                ->orderBy('created_at', 'desc')
                ->paginate(25);
        }


        return view('admin.contact.list', ['user' => $user, 'roles' => $roles, 'contact_submissions' => $contact_submissions, 'search' => $search, 'seen' => $seen]);
    }

    public function contactDetails($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $contact = Contact::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Contact details', 'admin', 'Contact id: ' . $id, 'Contact bestaat niet');
            return redirect()->route('admin.contact')->with('error', 'Dit contact bestaat niet.');
        }
        if ($contact === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Contact details', 'admin', 'Contact id: ' . $id, 'Contact bestaat niet');
            return redirect()->route('admin.contact')->with('error', 'Dit contact bestaat niet.');
        }

        return view('admin.contact.details', ['user' => $user, 'roles' => $roles, 'contact' => $contact]);
    }

    public function contactSeen($id)
    {
        try {
            $contact = Contact::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Contact seen', 'admin', 'Contact id: ' . $id, 'Contact bestaat niet');
            return redirect()->route('admin.contact')->with('error', 'Dit contact bestaat niet.');
        }

        $contact->done = !$contact->done;

        $contact->save();

        return redirect()->route('admin.contact.details', [$contact->id]);
    }


    public function contactDelete($id)
    {
        try {
            $contact = Contact::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Delete contact', 'admin', 'Contact id: ' . $id, 'Contact bestaat niet');
            return redirect()->route('admin.contact')->with('error', 'Dit contact bestaat niet.');
        }
        if ($contact === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Delete contact', 'admin', 'Contact id: ' . $id, 'Contact bestaat niet');
            return redirect()->route('admin.contact')->with('error', 'Dit contact bestaat niet.');
        }

        if ($contact === null) {
            return redirect()->route('admin.contact')->with('error', 'Geen contact gevonden om te verwijderen');
        }

        $contact->delete();


        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'Delete contact', 'Admin', $contact->id, '');

        return redirect()->route('admin.contact')->with('success', 'Contact verwijderd');

    }


    // Nieuws
    public function news()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $search = request('search');
        $accepted = request('accepted');

        if ($accepted !== 'all' && $accepted !== 'accepted' && $accepted !== 'unaccepted') {
            $accepted = 'all';
        }

        if ($accepted === 'all') {
            $news = News::query()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%')
                            ->orWhere('content', 'like', '%' . $search . '%')
                            ->orWhere('category', 'like', '%' . $search . '%')
                            ->orWhere('date', 'like', '%' . $search . '%')
                            ->orWhere('category', 'like', '%' . $search . '%');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->paginate(25);
        }
        if ($accepted === 'accepted') {
            $news = News::query()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%')
                            ->orWhere('content', 'like', '%' . $search . '%')
                            ->orWhere('category', 'like', '%' . $search . '%')
                            ->orWhere('date', 'like', '%' . $search . '%')
                            ->orWhere('category', 'like', '%' . $search . '%');
                    });
                })
                ->where('accepted', true)
                ->orderBy('created_at', 'desc')
                ->paginate(25);
        }
        if ($accepted === 'unaccepted') {
            $news = News::query()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%')
                            ->orWhere('content', 'like', '%' . $search . '%')
                            ->orWhere('category', 'like', '%' . $search . '%')
                            ->orWhere('date', 'like', '%' . $search . '%')
                            ->orWhere('category', 'like', '%' . $search . '%');
                    });
                })
                ->where('accepted', false)
                ->orderBy('created_at', 'desc')
                ->paginate(25);
        }


        $all_roles = Role::orderBy('role')->get();

        $selected_role = '';

        return view('admin.news.list', ['user' => $user, 'accepted' => $accepted, 'roles' => $roles, 'news' => $news, 'search' => $search, 'all_roles' => $all_roles, 'selected_role' => $selected_role]);
    }

    public function newsDetails($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $news = News::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'News details', 'admin', 'News id: ' . $id, 'Nieuws bestaat niet');
            return redirect()->route('admin.news')->with('error', 'Dit nieuws bestaat niet.');
        }
        if ($news === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'News details', 'admin', 'News id: ' . $id, 'Nieuws bestaat niet');
            return redirect()->route('admin.news')->with('error', 'Dit nieuws bestaat niet.');
        }

        return view('admin.news.details', ['user' => $user, 'roles' => $roles, 'news' => $news]);
    }

    public function newsPublish($id)
    {
        try {
            $news = News::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'News publish', 'admin', 'News id: ' . $id, 'Nieuws bestaat niet');
            return redirect()->route('admin.news')->with('error', 'Dit nieuws bestaat niet.');
        }

        $news->accepted = !$news->accepted;

        $news->save();

        return redirect()->route('admin.news.details', [$news->id]);
    }

    public function newsEdit($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $news = News::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'News details', 'admin', 'News id: ' . $id, 'Nieuws bestaat niet');
            return redirect()->route('admin.news')->with('error', 'Dit nieuws bestaat niet.');
        }
        if ($news === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'News details', 'admin', 'News id: ' . $id, 'Nieuws bestaat niet');
            return redirect()->route('admin.news')->with('error', 'Dit nieuws bestaat niet.');
        }

        return view('admin.news.edit', ['user' => $user, 'roles' => $roles, 'news' => $news]);
    }

    public function newsEditSave(Request $request, $id)
    {
        $request->validate([
            'content' => 'string|max:65535|required',
            'description' => 'string|max:200|required',
            'date' => 'date|required',
            'category' => 'string|required',
            'title' => 'string|required',
            'image' => 'mimes:jpeg,png,jpg,gif,webp|max:6000',
        ]);

        try {
            $news = News::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'News edit', 'admin', 'News id: ' . $id, 'Nieuws bestaat niet');
            return redirect()->route('admin.news')->with('error', 'Dit nieuws bestaat niet.');
        }
        if ($news === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'News edit', 'admin', 'News id: ' . $id, 'Nieuws bestaat niet');
            return redirect()->route('admin.news')->with('error', 'Dit nieuws bestaat niet.');
        }

        try {
            if (isset($request->image)) {
                // Process image upload
                $newPictureName = time() . '.' . $request->image->extension();
                $destinationPath = 'files/news/news_images';
                $request->image->move(public_path($destinationPath), $newPictureName);

                $news->image = $newPictureName;
            }


            // Validate content for disallowed elements or styles
            if (ForumController::validatePostData($request->input('content'))) {

                $news->content = $request->input('content');
                $news->description = $request->input('description');
                $news->date = $request->input('date');
                $news->category = $request->input('category');
                $news->title = $request->input('title');

                $news->save();

                // Log the creation of the news item
                $log = new Log();
                $log->createLog(auth()->user()->id, 2, 'edit nieuws', 'admin', 'News id: ' . $news->id, '');

                return redirect()->route('admin.news.details', $id)->with('success', 'Je wijzigingen zijn opgelsagen!');
            } else {
                throw ValidationException::withMessages(['content' => 'Je wijzigingen kunnen niet opgeslagen worden']);
            }
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // General exception handling for unexpected errors
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het opslaan van je wijzigingen. Probeer het opnieuw.')->withInput();
        }
    }

    public function newsDelete($id)
    {
        try {
            $news = News::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'News delete', 'admin', 'News id: ' . $id, 'Nieuws bestaat niet');
            return redirect()->route('news.user')->with('error', 'Dit nieuws bestaat niet.');
        }
        if ($news === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'News delete', 'admin', 'News id: ' . $id, 'Nieuws bestaat niet');
            return redirect()->route('news.user')->with('error', 'Dit nieuws bestaat niet.');
        }

        $news->delete();


        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'News delete', 'admin', $news->id, '');

        return redirect()->route('admin.news')->with('success', 'Dit nieuwsitem is permanent verwijderd!');

    }

    public function newNews()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();


        return view('admin.news.new_news', ['user' => $user, 'roles' => $roles]);
    }

    public function newsCreate(Request $request)
    {
        // Validate the request inputs
        $request->validate([
            'content' => 'string|max:65535|required',
            'description' => 'string|max:200|required',
            'date' => 'date|required',
            'category' => 'string|required',
            'title' => 'string|required',
            'image' => 'mimes:jpeg,png,jpg,gif,webp|max:6000|required',
        ]);

        try {
            // Process image upload
            $newPictureName = time() . '.' . $request->image->extension();
            $destinationPath = 'files/news/news_images';
            $request->image->move(public_path($destinationPath), $newPictureName);

            // Validate content for disallowed elements or styles
            if (ForumController::validatePostData($request->input('content'))) {

                // Create the news item
                $news = News::create([
                    'content' => $request->input('content'),
                    'description' => $request->input('description'),
                    'date' => $request->input('date'),
                    'category' => $request->input('category'),
                    'title' => $request->input('title'),
                    'user_id' => Auth::id(),
                    'image' => $newPictureName,
                    'accepted' => true,
                ]);

                // Log the creation of the news item
                $log = new Log();
                $log->createLog(auth()->user()->id, 2, 'Create nieuws', 'nieuws', 'News id: ' . $news->id, '');

                return redirect()->route('admin.news.new')->with('success', 'Je nieuwsitem is opgeslagen!.');
            } else {
                throw ValidationException::withMessages(['content' => 'Je nieuwsitem kan niet opgeslagen worden.']);
            }
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // General exception handling for unexpected errors
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het opslaan van je nieuwsitem. Probeer het opnieuw.')->withInput();
        }
    }

    // Account management
    public function accountManagement()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $search = request('search', '');
        $selected_role = request('role', '');

        // If search or selected role is empty, handle it differently
        $usersQuery = User::query()
            ->with(['roles' => function ($query) {
                $query->orderBy('role', 'asc');
            }])
            ->orderBy('name');

        // Apply search filters only if search is not empty
        if (!empty($search)) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('sex', 'like', '%' . $search . '%')
                    ->orWhere('birth_date', 'like', '%' . $search . '%')
                    ->orWhere('street', 'like', '%' . $search . '%')
                    ->orWhere('postal_code', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        // Apply role filters if selected role is not empty
        if (!empty($selected_role) && $selected_role !== 'none') {
                $usersQuery->whereHas('roles', function ($query) use ($selected_role) {
                    $query->where('role', $selected_role);
                });
        }

        // Finally, paginate the users
        $users = $usersQuery->paginate(25);

        // Get all user IDs for the filtered users
        $user_ids = $usersQuery->pluck('id');

        $all_roles = Role::orderBy('role')->get();

        return view('admin.account_management.list', [
            'user' => $user,
            'user_ids' => $user_ids,
            'roles' => $roles,
            'users' => $users,
            'search' => $search,
            'all_roles' => $all_roles,
            'selected_role' => $selected_role
        ]);
    }


    public function exportData(Request $request)
    {
        // Retrieve the filtered user data from the request
        $users = json_decode($request->input('user_ids'));

        $type = 'administratie';

        // Export data to Excel
        $export = new UsersExport($users, $type);
        return $export->export();
    }

    public function accountDetails($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $account = User::with(['roles' => function ($query) {
                $query->orderBy('role', 'asc');
            }])->find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'View user', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }
        if ($account === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'View user', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'View account', 'Admin', $account->name . ' ' . $account->infix . ' ' . $account->last_name, '');

        return view('admin.account_management.details', ['user' => $user, 'roles' => $roles, 'account' => $account]);
    }

    public function editAccount($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $all_users = User::all();

        $all_roles = Role::all();

        try {
            $account = User::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Edit user', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }
        if ($account === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Edit user', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }

        if ($account !== null) {
            $selectedRoles = $account->roles->pluck('id')->toArray();
        } else {
            $selectedRoles = '';
        }


        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'View account edit', 'Admin', $account->name . ' ' . $account->infix . ' ' . $account->last_name, '');

        return view('admin.account_management.edit', ['user' => $user, 'roles' => $roles, 'all_roles' => $all_roles, 'account' => $account, 'selectedRoles' => $selectedRoles, 'all_users' => $all_users]);
    }

    public function storeAccount(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'sex' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'street' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'city' => 'nullable|string',
            'phone' => 'nullable|string',
            'profile_picture' => 'nullable|mimes:jpeg,png,jpg,gif,webp|max:6000',
        ]);

        try {
            $user = User::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Edit user', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }
        if ($user === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Edit user', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }

        if (!$user) {
            return redirect()->back()->with('error', 'Gebruiker niet gevonden');
        }



        // Handle profile picture
        if (isset($request->profile_picture)) {
            $newPictureName = time() . '-' . $request->name . '.' . $request->profile_picture->extension();
            $destinationPath = 'profile_pictures';
            $request->profile_picture->move($destinationPath, $newPictureName);
            $user->profile_picture = $newPictureName;
        }

        // Update user fields
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->sex = $request->input('sex');
        $user->birth_date = $request->input('birth_date');
        $user->street = $request->input('street');
        $user->postal_code = $request->input('postal_code');
        $user->city = $request->input('city');
        $user->phone = $request->input('phone');


        // Save user and sync roles
        $user->save();
        $user->roles()->sync($request->input('roles'));

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'Edit account', 'Admin', $user->name . ' ' . $user->infix . ' ' . $user->last_name, '');


        return redirect()->route('admin.account-management.details', ['id' => $user->id])->with('success', 'Account succesvol bijgewerkt');
    }


    public function deleteAccount($id)
    {
        try {
            $user = User::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Delete user', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }
        if ($user === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Delete user', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }

        if ($user === null) {
            return redirect()->route('admin.account-management')->with('error', 'Geen gebruiker gevonden om te verwijderen');
        }
        if ($id === (string)Auth::id()) {
            return redirect()->back()->with('error', 'Je kunt jezelf niet verwijderen.');
        } else {
            $user->delete();

            $log = new Log();
            $log->createLog(auth()->user()->id, 2, 'Delete account', 'Admin', $user->name . ' ' . $user->infix . ' ' . $user->last_name, '');

            return redirect()->route('admin.account-management')->with('success', 'Gebruiker verwijderd');
        }
    }

    public function createAccount()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $all_roles = Role::all();

        return view('admin.account_management.create_account', ['user' => $user, 'roles' => $roles, 'all_roles' => $all_roles]);
    }

    // Make account
    public function createAccountStore(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();


        $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255',
            'password' => 'string|min:8',
            'sex' => 'nullable|string',
            'infix' => 'nullable|string',
            'last_name' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'street' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'city' => 'nullable|string',
            'phone' => 'nullable|string',
            'avg' => 'nullable|bool',
            'member_date' => 'nullable|date',
            'profile_picture' => 'nullable|mimes:jpeg,png,jpg,gif,webp|max:6000',
            'dolfijnen_name' => 'nullable|string',
        ]);

        if (isset($request->profile_picture)) {
            // Process and save the uploaded image
            $newPictureName = time() . '-' . $request->name . '.' . $request->profile_picture->extension();
            $destinationPath = 'profile_pictures';
            $request->profile_picture->move($destinationPath, $newPictureName);
        }

        if (User::where('email', $request->email)->exists()) {
            return redirect()->back()->withErrors(['email' => 'Dit emailadres is al in gebruik.']);
        } else {
            $account = User::create([
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'sex' => $request->input('sex'),
                'name' => $request->input('name'),
                'infix' => $request->input('infix'),
                'last_name' => $request->input('last_name'),
                'birth_date' => $request->input('birth_date'),
                'street' => $request->input('street'),
                'postal_code' => $request->input('postal_code'),
                'city' => $request->input('city'),
                'phone' => $request->input('phone'),
                'member_date' => $request->input('member_date'),
                'dolfijnen_name' => $request->input('dolfijnen_name'),
            ]);

            if (isset($request->profile_picture)) {
                $account->profile_picture = $newPictureName;
            }

            $account->save();

            if (!empty($request->roles)) {
                $account->roles()->attach($request->roles);
            }

            $log = new Log();
            $log->createLog(auth()->user()->id, 2, 'Create account', 'Admin', $account->name . ' ' . $account->infix . ' ' . $account->last_name, '');

            return redirect()->route('admin.create-account', ['user' => $user, 'roles' => $roles])->with('success', 'Gebruiker succesvol aangemaakt');

        }
    }

    // Verander wachtwoord

    public function editAccountPassword($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $account = User::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Change user password', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }
        if ($account === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Change user password', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }

        return view('admin.account_management.change_password', ['user' => $user, 'roles' => $roles, 'account' => $account]);
    }

    public function editAccountPasswordStore(Request $request, $id)
    {
        try {
            $account = User::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Edit user password', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }
        if ($account === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Edit user password', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }

        $request->validate([
            'new_password' => 'required|confirmed|min:8',
        ]);

        User::whereId($id)->update([
            'password' => Hash::make($request->new_password)
        ]);


        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'Edit password', 'Admin', $account->name . ' ' . $account->infix . ' ' . $account->last_name, '');

        return redirect()->route('admin.account-management.edit', $id)->with('success', 'Wachtwoord succesvol bijgewerkt!');
    }

    // Rollen

    public function roleManagement()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $search = request('search');


        if (request('search')) {
            $all_roles = Role::where(function ($query) use ($search) {
                $query->where('role', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })->orderBy('role')->paginate(25);
        } else {
            $all_roles = Role::orderBy('role')->paginate(25);
        }


        return view('admin.role_management.list', ['user' => $user, 'roles' => $roles, 'all_roles' => $all_roles, 'search' => $search]);
    }

    public function editRole($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $role = Role::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Edit role', 'admin', 'Role id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.role-management')->with('error', 'Deze rol bestaat niet.');
        }
        if ($role === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Edit role', 'admin', 'Role id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.role-management')->with('error', 'Deze rol bestaat niet.');
        }

        return view('admin.role_management.edit', ['user' => $user, 'roles' => $roles, 'role' => $role]);
    }

    public function storeRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'string|max:255',
            'description' => 'string',
        ]);

        try {
            $role = Role::find($id);;
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Edit role', 'admin', 'Role id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.role-management')->with('error', 'Deze rol bestaat niet.');
        }
        if ($role === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Edit role', 'admin', 'Role id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.role-management')->with('error', 'Deze rol bestaat niet.');
        }

        if (!$role) {
            return redirect()->back()->with('error', 'Rol niet gevonden');
        }

        $role->role = $request->input('role');
        $role->description = $request->input('description');

        $role->save();


        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'Edit role', 'Admin', $role->role, '');

        return redirect()->route('admin.role-management')->with('success', 'Rol succesvol bijgewerkt');
    }

    public function deleteRole($id)
    {
        try {
            $role = Role::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Delete role', 'admin', 'Role id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.role-management')->with('error', 'Deze rol bestaat niet.');
        }
        if ($role === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Delete role', 'admin', 'Role id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.role-management')->with('error', 'Deze rol bestaat niet.');
        }

        if ($role === null) {
            return redirect()->route('admin.role-management')->with('error', 'Geen rol gevonden om te verwijderen');
        }

        $role->delete();


        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'Delete role', 'Admin', $role->roles, '');

        return redirect()->route('admin.role-management')->with('success', 'Rol verwijderd');

    }

    public function createRole()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();


        return view('admin.role_management.create_role', ['user' => $user, 'roles' => $roles]);
    }

    public function createRoleStore(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();


        $request->validate([
            'role' => 'string|max:255',
            'description' => 'string',
        ]);

        $role = Role::create([
            'role' => $request->input('role'),
            'description' => $request->input('description')
        ]);

        $role->save();

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'Create role', 'Admin', $role->role, '');

        return redirect()->route('admin.role-management', ['user' => $user, 'roles' => $roles])->with('success', 'Rol succesvol aangemaakt');

    }

    public function signup()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $search = '';

        $users = User::orderBy('created_at', 'desc')
            ->where('allow_booking', false)
            ->paginate(25);

        $user_ids = User::orderBy('created_at', 'desc')
            ->where('allow_booking', false)
            ->get()
            ->pluck('id');

        $all_roles = Role::orderBy('role')->get();

        $selected_role = '';

        return view('admin.signup.list', ['user' => $user, 'user_ids' => $user_ids, 'roles' => $roles, 'users' => $users, 'search' => $search, 'all_roles' => $all_roles, 'selected_role' => $selected_role]);
    }

    public function signupAccountDetails($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $account = User::with(['roles' => function ($query) {
                $query->orderBy('role', 'asc');
            }])->find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'View user', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }
        if ($account === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'View user', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.account-management')->with('error', 'Dit account bestaat niet.');
        }

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'View account', 'Admin', $account->name . ' ' . $account->infix . ' ' . $account->last_name, '');

        return view('admin.signup.details', ['user' => $user, 'roles' => $roles, 'account' => $account]);
    }

    public function signupAccept($id)
    {
        $account = User::find($id);

        $account->allow_booking = true;

        $account->save();

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'Accept signup', 'Admin', $account->name . ' ' . $account->infix . ' ' . $account->last_name, '');

        $notification = new Notification();
        $notification->sendNotification(null, [$id], 'Je aanmelding is goedgekeurd. Je kunt nu accomodaties boeken!', '', '', 'account_activated', $account->id);

        return redirect()->route('admin.signup')->with('success', 'Aanmelding geaccepteerd');
    }

    public function signupDelete($id)
    {
        try {
            $user = User::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Delete signup', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.signup')->with('error', 'Dit account bestaat niet.');
        }
        if ($user === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Delete signup', 'admin', 'Account id: ' . $id, 'Gebruiker bestaat niet');
            return redirect()->route('admin.signup')->with('error', 'Dit account bestaat niet.');
        }

        if ($user === null) {
            return redirect()->route('admin.signup')->with('error', 'Geen aanmelding gevonden om te verwijderen');
        }
        if ($id === (string)Auth::id()) {
            return redirect()->back()->with('error', 'Je kunt jezelf niet verwijderen.');
        } else {
            $user->delete();

            $log = new Log();
            $log->createLog(auth()->user()->id, 2, 'Delete signup', 'Admin', $user->name . ' ' . $user->infix . ' ' . $user->last_name, '');

            return redirect()->route('admin.signup')->with('success', 'Aanmelding verwijderd');
        }
    }

}

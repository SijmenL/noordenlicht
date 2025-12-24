<?php

namespace App\Http\Controllers;

use App\Exports\AgendaExport;
use App\Models\Activity;
use App\Models\ActivityFormElement;
use App\Models\ActivityFormResponses;
use App\Models\ActivityPrice;
use App\Models\Log;
use App\Models\Presence;
use App\Models\Price;
use App\Models\ProductPrice;
use App\Models\Role;
use App\Models\ActivityException;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\AgendaPublicScheduleTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Properties\TextProperty;


class AgendaController extends Controller
{
    use AgendaPublicScheduleTrait;

    public function editActivity(Request $request, $id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $all_roles = Role::all();

        $teacherRoles = ['Administratie', 'Bestuur', 'Ouderraad', 'Praktijkbegeleider'];


        $activity = Activity::with('formElements')->findOrFail($id);

        if (!$activity) {
            return redirect()->back()->with('error', 'Activiteit niet gevonden.');
        }


        // Ownership or teacher check
        if ($activity->user_id !== Auth::id() && !$user->roles->contains('role', 'Administratie')) {
                return redirect()->back()->with('error', 'Activiteit niet gevonden.');
        }

        $month = $request->query('month', '0');
        $wantViewAll = $request->query('all', '0');
        $view = $request->query('view', 'month');

        if (isset($activity) && $activity->recurrence_rule !== null) {
            $dateStart = $request->query('startDate');

            if (!$dateStart || !$activity->recurrence_rule) {
                return redirect()->route('agenda.month')->with('error', 'Activiteit niet gevonden.');
            }

            if (!self::isValidRepetitionDate($activity, $dateStart)) {
                return redirect()->route('agenda.month')->with('error', 'Deze herhaling van de activiteit bestaat niet.');
            }


// Update activity dates for the current occurrence
            // Update activity dates for the current occurrence
            $originalStart = \Carbon\Carbon::parse($activity->date_start);
            $originalEnd = \Carbon\Carbon::parse($activity->date_end);

            $requestedStart = \Carbon\Carbon::parse($dateStart)->setTimeFrom($originalStart);

            $duration = $originalEnd->diffInSeconds($originalStart);

            $activity->date_start = $requestedStart->toDateTimeString();
            $activity->date_end = $requestedStart->copy()->addSeconds($duration)->toDateTimeString();

            // If there's a “deadline” stored in presence, shift it too
            if ($activity->presence !== null && $activity->presence !== "1" && $activity->presence !== "0") {
                $originalDeadline = Carbon::parse($activity->presence);

                $deadlineOffset = $originalDeadline->getTimestamp() - $originalStart->getTimestamp();
                $newDeadline = $requestedStart->copy()->addSeconds($deadlineOffset);

                $activity->presence = $newDeadline->toDateTimeString();
            }
        }

        return view('agenda.edit', [
            'user' => $user,
            'roles' => $roles,
            'activity' => $activity,
            'all_roles' => $all_roles,
            'monthOffset' => $month,
            'wantViewAll' => $wantViewAll,
            'view' => $view
        ]);
    }

    public function editActivitySave(Request $request, $id)
    {
        $month = $request->input('month', '0');
        $wantViewAll = $request->input('all', '0');
        $view = $request->input('view', 'month');

        // 1) User’s choice + the occurrence date
        $editType = $request->input('edit_type', 'all');      // 'all', 'following', or 'single'
        $occurrenceDate = $request->input('occurrence_date');       // 'YYYY-MM-DD'

        // 2) Validate
        $validatedData = $request->validate([
            'title' => 'string|required',
            'content' => 'string|max:65535|nullable',
            'date_start' => 'date|required',
            'date_end' => 'date|required',
            'reoccurrence' => 'string|required',
            'max_tickets' => 'integer|nullable',
            'public' => 'boolean|required',
            'location' => 'string|nullable',
            'organisator' => 'string|nullable',
            'image' => 'mimes:jpeg,png,jpg,gif,webp|max:6000',

            'form_labels' => 'nullable|array',
            'form_types' => 'nullable|array',
            'form_options' => 'nullable|array',
            'is_required' => 'nullable|array',
        ]);

        try {
            $activity = Activity::findOrFail($id);

            // Image upload (unchanged)
            $newPictureName = $activity->image;
            if ($request->hasFile('image')) {
                $newPictureName = time() . '.' . $request->file('image')->extension();
                $destPath = 'files/agenda/agenda_images';
                $request->file('image')->move(public_path($destPath), $newPictureName);
            }

            // Roles & users implode
            $roles = $request->input('roles')
                ? implode(', ', $request->input('roles'))
                : null;
            $users = $request->input('users')
                ? implode(',', array_map('trim', array_filter(explode(',', $request->input('users')))))
                : null;

            // Content validation
            if (!ForumController::validatePostData($request->input('content'))) {
                throw ValidationException::withMessages([
                    'content' => 'Je agendapunt kan niet opgeslagen worden.'
                ]);
            }

            // Presence date logic
            $presence = $request->input('presence');
            if ($presence === "1" && $request->filled('presence-date')) {
                $presence = $request->input('presence-date');
            }

            // Shared data
            $data = [
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'date_start' => $request->input('date_start'),
                'date_end' => $request->input('date_end'),
                'recurrence_rule' => $request->input('reoccurrence'),
                'roles' => $roles,
                'users' => $users,
                'public' => $request->input('public'),
                'max_tickets' => $request->input('max_tickets'),
                'presence' => $presence,
                'price' => $request->input('price'), // Note: this looks like a flat price field, separate from the 'prices' relation below
                'location' => $request->input('location'),
                'organisator' => $request->input('organisator'),
                'image' => $newPictureName,
            ];

            // Compute original start time and duration
            $origStart = Carbon::parse($activity->date_start);
            $origEnd = Carbon::parse($activity->date_end);
            $durationSec = $origEnd->diffInSeconds($origStart);

            $log = new Log();

            // 3) Branch by editType
            if ($editType === 'all' || !$activity->recurrence_rule) {
                // Entire series
                $activity->update($data);

            } elseif ($editType === 'following') {

                // fetch *all* exceptions on the original activity
                $originalExceptions = ActivityException::where('activity_id', $activity->id)->get();

                // 3b) Clone new series starting at occurrenceDate + original time
                $new = $activity->replicate();
                $new->fill($data);

                // Preserve original time‐of‐day
                $newStart = Carbon::parse("{$occurrenceDate} " . $origStart->format('H:i:s'));
                $new->date_start = $newStart->toDateTimeString();
                $new->date_end = $newStart->copy()->addSeconds($durationSec)->toDateTimeString();

                $new->save();

                // --- CLONE PRICES ---
                foreach ($activity->prices as $price) {
                    $newPrice = $price->replicate();
                    $newPrice->activity_id = $new->id;
                    $newPrice->save();
                }

                // --- MOVE TICKETS ---
                // Move tickets that are for the occurrence date or later to the new activity
                Ticket::where('activity_id', $activity->id)
                    ->whereDate('start_date', '>=', $occurrenceDate)
                    ->update(['activity_id' => $new->id]);

                // Trim original up to the chosen date
                $activity->update([
                    'end_recurrence' => $occurrenceDate,
                ]);

                // re‐insert exceptions for the new activity
                foreach ($originalExceptions as $ex) {
                    ActivityException::create([
                        'activity_id' => $new->id,
                        'date' => $ex->date,
                    ]);
                }

                $activity = $new;

            } else { // single
                // 3c) Exception row for *this* date
                ActivityException::create([
                    'activity_id' => $activity->id,
                    'date' => $occurrenceDate,
                ]);

                // 3d) Clone one‐off event
                $new = $activity->replicate();
                $new->fill($data);
                $new->recurrence_rule = "never";

                $newStart = Carbon::parse("{$occurrenceDate} " . $origStart->format('H:i:s'));
                $new->date_start = $newStart->toDateTimeString();
                $new->date_end = $newStart->copy()->addSeconds($durationSec)->toDateTimeString();

                $new->save();

                // --- CLONE PRICES ---
                foreach ($activity->prices as $price) {
                    $newPrice = $price->replicate();
                    $newPrice->activity_id = $new->id;
                    $newPrice->save();
                }

                // --- MOVE TICKETS ---
                // Move tickets specifically for this date to the new one-off activity
                Ticket::where('activity_id', $activity->id)
                    ->whereDate('start_date', $occurrenceDate)
                    ->update(['activity_id' => $new->id]);

                $activity = $new;
            }


            // 4) Form elements (unchanged)
            ActivityFormElement::where('activity_id', $activity->id)->delete();
            if (isset($validatedData['form_labels'])) {
                foreach ($validatedData['form_labels'] as $i => $label) {
                    $type = $validatedData['form_types'][$i];
                    $isRequired = isset($validatedData['is_required'][$i]);
                    $opts = null;
                    if (in_array($type, ['select', 'radio', 'checkbox'])
                        && isset($validatedData['form_options'][$i])) {
                        $opts = implode(',', $validatedData['form_options'][$i]);
                    }

                    ActivityFormElement::create([
                        'activity_id' => $activity->id,
                        'label' => $label,
                        'type' => $type,
                        'option_value' => $opts,
                        'is_required' => $isRequired,
                    ]);
                }
                $log->createLog(
                    Auth::id(), 2,
                    'Update activity form',
                    'agenda',
                    'Activity id: ' . $activity->id,
                    'Inschrijfformulier aangepast'
                );
            }

            $log->createLog(
                Auth::id(), 2,
                'Update activity',
                'agenda',
                'Activity id: ' . $activity->id,
                ''
            );

            // 5) Redirect back
            return redirect()
                ->route(
                    'agenda.activity',
                    ['id' => $activity->id, 'startDate' => $occurrenceDate, 'month' => $month, 'all' => $wantViewAll, 'view' => $view])
                ->with('success', 'Je agendapunt is bijgewerkt!');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // It is often useful to log the error for debugging:
            // \Log::error($e->getMessage());
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden. Probeer opnieuw.')
                ->withInput();
        }
    }


    public function agendaSubmissionsActivity(Request $request, $id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        // Get search term from query parameters, default to empty string
        $search = $request->query('search', '');

        // Retrieve the activity by its ID
        $activity = Activity::find($id);

        if (!$activity) {
            return abort(404);
        }

        // Retrieve all form submissions for this activity
        $formSubmissions = ActivityFormResponses::where('activity_id', $id)
            ->with('formElement') // Eager-load the related form elements
            ->get()
            ->groupBy('submitted_id');

        // Apply search filter regardless of count
        if (!empty($search)) {
            $formSubmissions = $formSubmissions->filter(function ($group) use ($search) {
                return $group->contains(function ($entry) use ($search) {
                    return stripos($entry->response, $search) !== false;
                });
            });
        }

        // Return view with activity and grouped form submission data
        return view('agenda.submissions', [
            'activity' => $activity,
            'formSubmissions' => $formSubmissions,
            'user' => $user,
            'roles' => $roles,
            'search' => $search,
        ]);
    }


    public function agendaPresenceActivity(Request $request, $id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $selected_role = $request->query('role', 'none');
        $search = $request->query('search', '');
        $activity = Activity::find($id);

        if (!$activity || !$activity->presence) {
            return redirect()->route('agenda.presence')->with('error', 'Activiteit niet gevonden.');
        }


        $activityRoleIds = $activity->roles ? array_map('trim', explode(',', $activity->roles)) : [];
        $activityUserIds = $activity->users ? array_map('trim', explode(',', $activity->users)) : [];

        $all_roles = Role::whereIn('id', $activityRoleIds)->get();

        if (!in_array($selected_role, $all_roles->pluck('role')->toArray())) {
            $selected_role = 'none';
        }

        // Fetch users by roles if roles exist
        $usersWithRoles = User::whereHas('roles', function ($query) use ($activityRoleIds, $selected_role) {
            $query->whereIn('roles.id', $activityRoleIds);
            if ($selected_role !== 'none') {
                $query->where('role', $selected_role);
            }
        });

        // Fetch users by specific user IDs
        $mentionedUsers = User::whereIn('id', $activityUserIds);

        // If the user has picked a specific role, require it here too
        if ($selected_role !== 'none') {
            $mentionedUsers->whereHas('roles', function ($q) use ($selected_role) {
                $q->where('role', $selected_role);
            });
        }


        // If the activity has neither roles nor users, show all users
        if (empty($activityRoleIds) && empty($activityUserIds)) {
            $allUsersQuery = User::query();
            if (!empty($search)) {
                $allUsersQuery->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.infix', 'like', "%{$search}%")
                        ->orWhere('users.last_name', 'like', "%{$search}%");
                });
            }
            $users = $allUsersQuery->get();
        } else {
            // Combine role-based and mentioned users
            if (!empty($search)) {
                $usersWithRoles->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.infix', 'like', "%{$search}%")
                        ->orWhere('users.last_name', 'like', "%{$search}%");
                });
                $mentionedUsers->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.infix', 'like', "%{$search}%")
                        ->orWhere('users.last_name', 'like', "%{$search}%");
                });
            }

            $users = $usersWithRoles->get()
                ->merge($mentionedUsers->get())
                ->unique('id');
        }

        $dateOccurrence = null;

        // Fetch users who set presence but are not part of the above sets
        if ($activity->recurrence_rule !== null && $activity->recurrence_rule !== 'never') {
            $dateOccurrence = $request->query('startDate');
        }

        $presenceUserIds = Presence::where('activity_id', $activity->id)
            ->where('date_occurrence', $dateOccurrence)
            ->pluck('user_id')->toArray();


        $usersWithPresence = User::whereIn('id', $presenceUserIds)
            ->when(!empty($search), function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('infix', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->get();

        // Merge in presence-only users
        if (!isset($users)) {
            $users = collect();
        }
        $users = $users->merge($usersWithPresence)->unique('id');

        // Fetch presence data
        $userPresenceArray = Presence::where('activity_id', $activity->id)
            ->where('date_occurrence', $dateOccurrence)
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->mapWithKeys(function ($p) {
                return [$p->user_id => [
                    'status' => $p->presence ? 'present' : 'absent',
                    'date' => $p->updated_at->toDateTimeString(),
                ]];
            });

        // Assign presence and invited flag
        $users->each(function ($u) use ($userPresenceArray, $usersWithRoles, $mentionedUsers, $activity) {
            $u->presence = $userPresenceArray->get($u->id, ['status' => 'null', 'date' => null]);
            $u->not_invited = !$usersWithRoles->pluck('id')->contains($u->id)
                && !$mentionedUsers->pluck('id')->contains($u->id)
                && (!empty($activity->roles) || !empty($activity->users));
        });

        // Filter by selected role if needed
        if ($selected_role !== 'none') {
            $users = $users->filter(fn($u) => !$u->not_invited);
        }

        // Sort users by presence then last name
        $sortedUsers = $users->sort(function ($a, $b) {
            $order = ['present' => 1, 'absent' => 2, 'null' => 3];
            $ap = $order[$a->presence['status']] ?? 3;
            $bp = $order[$b->presence['status']] ?? 3;
            return $ap === $bp
                ? strcmp($a->last_name, $b->last_name)
                : $ap <=> $bp;
        })->values();

        // Preserve query params
        $month = $request->query('month', '0');
        $wantViewAll = $request->query('all', '0');
        $view = $request->query('view', 'month');

        return view('agenda.presence', [
            'user' => $user,
            'roles' => $roles,
            'activity' => $activity,
            'users' => $sortedUsers,
            'all_roles' => $all_roles,
            'selected_role' => $selected_role,
            'search' => $search,
            'monthOffset' => $month,
            'wantViewAll' => $wantViewAll,
            'view' => $view,
        ])->with('#presence');
    }


    public function exportPresenceData(Request $request)
    {
        // Retrieve the filtered user data from the request
        $usersData = json_decode($request->input('users'), true);
        $activityName = $request->input('activity_name');

        // Ensure presence data is correctly mapped and sanitized
        $usersData = collect($usersData)->map(function ($user) {
            return [
                'id' => $user['id'] ?? null,
                'name' => $user['name'] ?? 'Unknown',
                'infix' => $user['infix'] ?? '', // Ensure this exists
                'last_name' => $user['last_name'] ?? '', // Ensure this exists
                'email' => $user['email'] ?? '', // Ensure this exists
                'presence' => $user['presence'] ?? 'null',
                'date' => !empty($user['date'])
                    ? \Carbon\Carbon::parse($user['date'])->format('d-m-Y H:i')
                    : '-',
            ];
        });


        // Export data to Excel
        $export = new AgendaExport($usersData->toArray(), $activityName);
        return $export->export();
    }

    protected function expandRecurringActivity($activity, $rangeStart, $rangeEnd)
    {
        $occurrences = [];

        if (empty($activity->recurrence_rule) || !in_array($activity->recurrence_rule, ['daily', 'weekly', 'monthly'])) {
            return [$activity];
        }

        $originalStart = Carbon::parse($activity->date_start);
        $originalEnd = Carbon::parse($activity->date_end);
        $duration = $originalEnd->diffInSeconds($originalStart);

        $lastOccurrence = $activity->end_recurrence ? Carbon::parse($activity->end_recurrence)->endOfDay() : Carbon::maxValue();

        $currentOccurrenceStart = $originalStart->copy();

        while ($currentOccurrenceStart->lte($rangeEnd) && $currentOccurrenceStart->lte($lastOccurrence)) {
            $currentOccurrenceEnd = $currentOccurrenceStart->copy()->addSeconds($duration);

            if ($currentOccurrenceStart->between($rangeStart, $rangeEnd)) {
                $clone = clone $activity;
                $clone->date_start = $currentOccurrenceStart->copy();
                $clone->date_end = $currentOccurrenceEnd->copy();
                $occurrences[] = $clone;
            }

            switch ($activity->recurrence_rule) {
                case 'daily':
                    $currentOccurrenceStart->addDay();
                    break;
                case 'weekly':
                    $currentOccurrenceStart->addWeek();
                    break;
                case 'monthly':
                    $currentOccurrenceStart->addMonth();
                    break;
            }
        }

        return $occurrences;
    }


    /**
     * Agenda Month view (authenticated users)
     */
    public function agendaMonth(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();
        $rolesIDList = $roles->pluck('id')->toArray();
        $canViewAll = false;

        if (
            $user->roles->contains('role', 'Dolfijnen Leiding') ||
            $user->roles->contains('role', 'Zeeverkenners Leiding') ||
            $user->roles->contains('role', 'Loodsen Stamoudste') ||
            $user->roles->contains('role', 'Afterloodsen Organisator') ||
            $user->roles->contains('role', 'Administratie') ||
            $user->roles->contains('role', 'Bestuur') ||
            $user->roles->contains('role', 'Praktijkbegeleider') ||
            $user->roles->contains('role', 'Loodsen Mentor') ||
            $user->roles->contains('role', 'Ouderraad') ||
            $user->roles->contains('role', 'Loods') ||
            $user->roles->contains('role', 'Afterloods')
        ) {
            $canViewAll = true;
        }

        $wantViewAll = filter_var($request->query('all', false), FILTER_VALIDATE_BOOLEAN);
        if ($wantViewAll === false) {
            $canViewAll = false;
        }

        $monthOffset = $request->query('month', 0);
        $dayOffset = $request->query('day', 0);

        Carbon::setLocale('nl');
        $baseDate = Carbon::now();
        $calculatedDate = $baseDate->copy()->addMonthsNoOverflow($monthOffset);
        $calculatedDay = $calculatedDate->day;
        $calculatedMonth = $calculatedDate->month;
        $calculatedYear = $calculatedDate->year;

        $firstDayOfMonth = Carbon::create($calculatedYear, $calculatedMonth, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        $firstDayOfWeek = ($firstDayOfMonth->dayOfWeek + 6) % 7;

        $monthName = $calculatedDate->translatedFormat('F');

        // For the month view, set the display period for the whole month.
        $rangeStart = Carbon::create($calculatedYear, $calculatedMonth, 1)->startOfDay();
        $rangeEnd = Carbon::create($calculatedYear, $calculatedMonth, 1)->endOfMonth()->endOfDay();

        // Retrieve activities that might belong to this month.
        $fetchedActivities = Activity::where(function ($query) use ($calculatedYear, $calculatedMonth) {
                $query->where(function ($query) use ($calculatedYear, $calculatedMonth) {
                    $query->whereYear('date_start', $calculatedYear)
                        ->whereMonth('date_start', $calculatedMonth);
                })
                    ->orWhere(function ($query) use ($calculatedYear, $calculatedMonth) {
                        $query->whereYear('date_end', $calculatedYear)
                            ->whereMonth('date_end', $calculatedMonth);
                    })
                    ->orWhere(function ($query) {
                        $query->whereNotNull('recurrence_rule');
                    });
            })
            ->get();

        // Load exceptions for single-instance deletions
        $exceptionsByActivity = ActivityException::whereIn('activity_id', $fetchedActivities->pluck('id'))
            ->get()
            ->groupBy('activity_id')
            ->map(function ($group) {
                return $group->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();
            });


        // Expand recurring events—even if the original date is far in the past.
        $activities = collect();
        foreach ($fetchedActivities as $activity) {
            $occurrences = $this->expandRecurringActivity($activity, $rangeStart, $rangeEnd);

            // get exception-dates array for this activity (or empty array)
            $skipDates = $exceptionsByActivity[$activity->id] ?? [];

            foreach ($occurrences as $occurrence) {
                // skip if this occurrence date was “deleted”
                $occDate = \Carbon\Carbon::parse($occurrence->date_start)->toDateString();
                if (in_array($occDate, $skipDates, true)) {
                    continue;
                }
                $activities->push($occurrence);
            }
        }

        $activities = $activities->filter(function ($activity) use ($user, $rolesIDList, $canViewAll) {
                $activityRoleIds = !empty($activity->roles)
                    ? array_map('trim', explode(',', $activity->roles))
                    : [];
                $activityUserIds = !empty($activity->users)
                    ? array_map('trim', explode(',', $activity->users))
                    : [];
                if (empty($activityRoleIds) && empty($activityUserIds)) {
                    $activity->should_highlight = false;
                    return true;
                }

            $hasRoleAccess = !empty(array_intersect($rolesIDList, $activityRoleIds));
            $isUserListed = in_array($user->id, $activityUserIds);

            if ($canViewAll) {
                $activity->should_highlight = (!$hasRoleAccess && !$isUserListed);
                return true;
            } else {
                return $hasRoleAccess || $isUserListed;
            }
        });

        // After expanding and filtering events…
        $activities = $activities->sortBy('date_start')->values();


        $globalRowTracker = [];
        $activityPositions = [];
        foreach ($activities as $activity) {
            // Get the occurrence start and end (normalized to start/end of day)
            $startDate = Carbon::parse($activity->date_start)->startOfDay();
            $endDate = Carbon::parse($activity->date_end)->endOfDay();

            // Build a composite key using the event id and the occurrence date
            // Using the occurrence date (here formatted as Y-m-d) allows repeated events on different days
            $compositeKey = $activity->id . '-' . $startDate->format('Y-m-d');

            $position = 0;
            $conflictFound = true;
            while ($conflictFound) {
                // Instead of passing $activity->id, we pass the composite key.
                $conflictFound = !$this->trackEventPosition($compositeKey, $startDate, $endDate, $position, $globalRowTracker);
                if ($conflictFound) {
                    $position++;
                }
            }
            // Use the composite key for storing the calculated position
            $activityPositions[$compositeKey] = $position;
        }


        return view('agenda.month', [
            'user' => $user,
            'roles' => $roles,
            'day' => $calculatedDay,
            'month' => $calculatedMonth,
            'year' => $calculatedYear,
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'currentDay' => now()->day,
            'currentMonth' => now()->month,
            'currentYear' => now()->year,
            'monthOffset' => $monthOffset,
            'dayOffset' => $dayOffset,
            'monthName' => $monthName,
            'activities' => $activities,
            'wantViewAll' => $wantViewAll,
            'activityPositions' => $activityPositions,
        ]);
    }

    /**
     * Public month view.
     */
    public function agendaMonthPublic(Request $request)
    {
        $monthOffset = $request->query('month', 0);
        Carbon::setLocale('nl');
        $baseDate = Carbon::now();
        $calculatedDate = $baseDate->copy()->addMonthsNoOverflow($monthOffset);
        $calculatedDay = $calculatedDate->day;
        $calculatedMonth = $calculatedDate->month;
        $calculatedYear = $calculatedDate->year;

        $firstDayOfMonth = Carbon::create($calculatedYear, $calculatedMonth, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        $firstDayOfWeek = ($firstDayOfMonth->dayOfWeek + 6) % 7;
        $monthName = $calculatedDate->translatedFormat('F');

        // Set the display period for the month.
        $rangeStart = Carbon::create($calculatedYear, $calculatedMonth, 1)->startOfDay();
        $rangeEnd = Carbon::create($calculatedYear, $calculatedMonth, 1)->endOfMonth()->endOfDay();

        $fetchedActivities = Activity::where(function ($query) use ($calculatedYear, $calculatedMonth) {
            $query->whereYear('date_start', $calculatedYear)
                ->whereMonth('date_start', $calculatedMonth)
                ->orWhere(function ($query) use ($calculatedYear, $calculatedMonth) {
                    $query->whereYear('date_end', $calculatedYear)
                        ->whereMonth('date_end', $calculatedMonth);
                })
                ->orWhere(function ($query) {
                    $query->whereNotNull('recurrence_rule');
                });;
        })
            ->where('public', "0")
            ->get();

        // Load exceptions for single-instance deletions
        $exceptionsByActivity = ActivityException::whereIn('activity_id', $fetchedActivities->pluck('id'))
            ->get()
            ->groupBy('activity_id')
            ->map(function ($group) {
                return $group->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();
            });


        // Expand recurring events—even if the original date is far in the past.
        $activities = collect();
        foreach ($fetchedActivities as $activity) {
            $occurrences = $this->expandRecurringActivity($activity, $rangeStart, $rangeEnd);
            $skipDates = $exceptionsByActivity[$activity->id] ?? [];

            foreach ($occurrences as $occurrence) {
                $occDate = Carbon::parse($occurrence->date_start)->toDateString();

                // Skip single-instance exceptions
                if (in_array($occDate, $skipDates, true)) {
                    continue;
                }

                // Skip occurrences after end_recurrence if set
                if (!is_null($activity->end_recurrence)) {
                    $endRec = Carbon::parse($activity->end_recurrence)->endOfDay();
                    if (Carbon::parse($occurrence->date_start)->gt($endRec)) {
                        continue;
                    }
                }

                $activities->push($occurrence);
            }
        }

        $globalRowTracker = [];
        $activityPositions = [];
        foreach ($activities as $activity) {
            // Get the occurrence start and end (normalized to start/end of day)
            $startDate = Carbon::parse($activity->date_start)->startOfDay();
            $endDate = Carbon::parse($activity->date_end)->endOfDay();

            // Build a composite key using the event id and the occurrence date
            // Using the occurrence date (here formatted as Y-m-d) allows repeated events on different days
            $compositeKey = $activity->id . '-' . $startDate->format('Y-m-d');

            $position = 0;
            $conflictFound = true;
            while ($conflictFound) {
                // Instead of passing $activity->id, we pass the composite key.
                $conflictFound = !$this->trackEventPosition($compositeKey, $startDate, $endDate, $position, $globalRowTracker);
                if ($conflictFound) {
                    $position++;
                }
            }
            // Use the composite key for storing the calculated position
            $activityPositions[$compositeKey] = $position;
        }

        return view('agenda.public.month', [
            'day' => $calculatedDay,
            'month' => $calculatedMonth,
            'year' => $calculatedYear,
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'currentDay' => now()->day,
            'currentMonth' => now()->month,
            'currentYear' => now()->year,
            'monthOffset' => $monthOffset,
            'monthName' => $monthName,
            'activities' => $activities,
            'activityPositions' => $activityPositions,
        ]);
    }

    /**
     * Agenda Schedule view (authenticated users) with a 3-month period.
     */
    public function agendaSchedule(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();
        $rolesIDList = $roles->pluck('id')->toArray();
        $canViewAll = false;

        if (
            $user->roles->contains('role', 'Dolfijnen Leiding') ||
            $user->roles->contains('role', 'Zeeverkenners Leiding') ||
            $user->roles->contains('role', 'Loodsen Stamoudste') ||
            $user->roles->contains('role', 'Afterloodsen Organisator') ||
            $user->roles->contains('role', 'Administratie') ||
            $user->roles->contains('role', 'Bestuur') ||
            $user->roles->contains('role', 'Praktijkbegeleider') ||
            $user->roles->contains('role', 'Loodsen Mentor') ||
            $user->roles->contains('role', 'Loods') ||
            $user->roles->contains('role', 'Afterloods') ||
            $user->roles->contains('role', 'Ouderraad')
        ) {
            $canViewAll = true;
        }

        $wantViewAll = filter_var($request->query('all', false), FILTER_VALIDATE_BOOLEAN);
        if ($wantViewAll === false) {
            $canViewAll = false;
        }

        $monthOffset = $request->query('month', 0);
        $dayOffset = $request->query('day', 0);

        Carbon::setLocale('nl');
        $baseDate = Carbon::now();
        $calculatedDate = $baseDate->copy()->addMonths($monthOffset)->addDays($dayOffset);
        $monthName = $calculatedDate->translatedFormat('F');
        $calculatedYear = $calculatedDate->year;

        $rangeStart = $calculatedDate->copy()->startOfMonth()->startOfDay();
        $rangeEnd = $calculatedDate->copy()->addMonths(5)->endOfMonth()->endOfDay();


        $fetchedActivities = Activity::query()
            ->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('date_start', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($q) use ($rangeStart, $rangeEnd) {
                        $q->whereIn('recurrence_rule', ['daily', 'weekly', 'monthly'])
                            ->where(function ($q2) use ($rangeStart) {
                                $q2->whereNull('end_recurrence')
                                    ->orWhere('end_recurrence', '>=', $rangeStart);
                            })
                            ->where('date_start', '<=', $rangeEnd);
                    });
            })
            ->orderBy('date_start')
            ->get();


        // Load exceptions for single-instance deletions
        $exceptionsByActivity = ActivityException::whereIn('activity_id', $fetchedActivities->pluck('id'))
            ->get()
            ->groupBy('activity_id')
            ->map(function ($group) {
                return $group->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();
            });

        // Expand recurring events
        $activities = collect();
        foreach ($fetchedActivities as $activity) {
            $occurrences = $this->expandRecurringActivity($activity, $rangeStart, $rangeEnd);
            $skipDates = $exceptionsByActivity[$activity->id] ?? [];

            foreach ($occurrences as $occurrence) {
                $occDate = Carbon::parse($occurrence->date_start)->toDateString();

                // Skip single-instance exceptions
                if (in_array($occDate, $skipDates, true)) {
                    continue;
                }

                // Skip beyond end_recurrence
                if (!is_null($activity->end_recurrence)) {
                    $endRec = Carbon::parse($activity->end_recurrence)->endOfDay();
                    if (Carbon::parse($occurrence->date_start)->gt($endRec)) {
                        continue;
                    }
                }


                $activities->push($occurrence);
            }
        }

        $activities = $activities->filter(function ($activity) use ($user, $rolesIDList, $canViewAll,) {
            $activityRoleIds = !empty($activity->roles)
                ? array_map('trim', explode(',', $activity->roles))
                : [];
            $activityUserIds = !empty($activity->users)
                ? array_map('trim', explode(',', $activity->users))
                : [];

            if (empty($activityRoleIds) && empty($activityUserIds)) {
                $activity->should_highlight = false;
                return true;
            } else {
                $hasRoleAccess = !empty(array_intersect($rolesIDList, $activityRoleIds));
                $isUserListed = in_array($user->id, $activityUserIds);

                if ($canViewAll) {
                    $activity->should_highlight = !$hasRoleAccess && !$isUserListed;
                    return true;
                } else {
                    return $hasRoleAccess || $isUserListed;
                }
            }
        });

        $activities = $activities->sortBy('date_start')->values();

        return view('agenda.schedule', [
            'activities' => $activities,
            'roles' => $roles,
            'user' => $user,
            'monthOffset' => $monthOffset,
            'monthName' => $monthName,
            'year' => $calculatedYear,
            'dayOffset' => $dayOffset,
            'wantViewAll' => $wantViewAll,
        ]);
    }

    /**
     * Public schedule view with a 3-month period.
     */
    public function agendaSchedulePublic(Request $request)
    {
        $monthOffset = $request->query('month', 0);
        $limit = $request->query('limit', null);
        $activities = $this->fetchAndProcessActivities($monthOffset, $limit, $request->query('dateStart'));
        $agendaViewData = $this->getAgendaViewData($monthOffset, $limit);

        return view('agenda.public.schedule', ['activities' => $activities] + $agendaViewData);
    }

    // Function to track event positions and detect conflicts
    private function trackEventPosition($event, $startDate, $endDate, $position, &$globalRowTracker)
    {
        // Compute a unique identifier using the event's ID and its own date_start timestamp.
        // This works even if the event doesn't have a dedicated "occurrence_id" property.
        if (is_object($event)) {
            $identifier = $event->id . '-' . Carbon::parse($event->date_start)->timestamp;
        } else {
            $identifier = $event;
        }

        // Check each day in the event range to see if the position is free.
        for ($day = $startDate->copy(); $day->lte($endDate); $day->addDay()) {
            $formattedDay = $day->format('Y-m-d');
            if (!isset($globalRowTracker[$formattedDay])) {
                $globalRowTracker[$formattedDay] = [];
            }
            if (isset($globalRowTracker[$formattedDay][$position])) {
                // Conflict found on this day.
                return false;
            }
        }

        // If no conflict, mark the position as occupied for each day in the event range.
        for ($day = $startDate->copy(); $day->lte($endDate); $day->addDay()) {
            $formattedDay = $day->format('Y-m-d');
            $globalRowTracker[$formattedDay][$position] = $identifier;
        }

        return true;
    }


    public function agendaPresent(Request $request, $activityId, $userId)
    {
        // Retrieve the specified user
        $user = User::findOrFail($userId);
        $activity = Activity::findOrFail($activityId);

        $dateOccurrence = null;
        if ($activity->recurrence_rule !== null && $activity->recurrence_rule !== 'never') {
            $dateOccurrence = $request->query('startDate');
        }

        // Check if the user is either the authenticated user
        if ((int)$userId === Auth::id()) {
            $presence = Presence::where('user_id', $userId)
                ->where('activity_id', $activityId)
                ->where('date_occurrence', $dateOccurrence)
                ->first();

            if ($presence) {
                $presence->update(['presence' => 1]);
                $presence->update(['date_occurrence' => $dateOccurrence]);
            } else {
                Presence::create([
                    'user_id' => $userId,
                    'activity_id' => $activityId,
                    'date_occurrence' => $dateOccurrence,
                    'presence' => 1,
                ]);
            }

            // Append query parameters to the redirect URL
            $queryParams = request()->query();
            $redirectUrl = route('agenda.activity', $activityId) . ($queryParams ? '?' . http_build_query($queryParams) : '');

            if ($userId === Auth::id()) {
                return redirect($redirectUrl)->with('success', 'Je bent aanwezig gemeld!');
            } else {
                return redirect($redirectUrl)->with('success', $user->name . ' ' . $user->infix . ' ' . $user->last_name . ' is aanwezig gemeld!');
            }
        } else {
            return redirect()->route('agenda.activity', $activityId)->with('error', 'Dit account kan niet aanwezig gemeld worden');
        }
    }

    public function agendaAbsent(Request $request, $activityId, $userId)
    {
        // Retrieve the specified user
        $user = User::findOrFail($userId);
        $activity = Activity::findOrFail($activityId);

        $dateOccurrence = null;

        if ($activity->recurrence_rule !== null && $activity->recurrence_rule !== 'never') {
            $dateOccurrence = $request->query('startDate');
        }

        // Check if the user is either the authenticated user
        if ((int)$userId === Auth::id()) {
            $presence = Presence::where('user_id', $userId)
                ->where('activity_id', $activityId)
                ->where('date_occurrence', $dateOccurrence)
                ->first();

            if ($presence) {
                $presence->update(['presence' => 0]);
                $presence->update(['date_occurrence' => $dateOccurrence]);
            } else {
                Presence::create([
                    'user_id' => $userId,
                    'activity_id' => $activityId,
                    'date_occurrence' => $dateOccurrence,
                    'presence' => 0,
                ]);
            }

            // Append query parameters to the redirect URL
            $queryParams = request()->query();
            $redirectUrl = route('agenda.activity', $activityId) . ($queryParams ? '?' . http_build_query($queryParams) : '');

            if ($userId === Auth::id()) {
                return redirect($redirectUrl)->with('success', 'Je bent afwezig gemeld, jammer dat je er niet bij bent!');
            } else {
                return redirect($redirectUrl)->with('success', $user->name . ' ' . $user->infix . ' ' . $user->last_name . ' is afwezig gemeld!');
            }
        } else {
            return redirect()->route('agenda.activity', $activityId)->with('error', 'Dit account kan niet afwezig gemeld worden');
        }
    }

    private function isValidRepetitionDate(Activity $activity, string $requestedDate): bool
    {
        $startDate = \Carbon\Carbon::parse($activity->date_start)->startOfDay();
        $targetDate = \Carbon\Carbon::parse($requestedDate)->startOfDay();

        // Always allow the original start date
        if ($targetDate->equalTo($startDate)) {
            return true;
        }

        // If the target is before the original, it's invalid
        if ($targetDate->lessThan($startDate)) {
            return false;
        }

        switch ($activity->recurrence_rule) {
            case 'daily':
                return true; // Already filtered earlier if before original

            case 'weekly':
                return $startDate->diffInWeeks($targetDate) * 7 === $startDate->diffInDays($targetDate);

            case 'monthly':
                return $startDate->day === $targetDate->day &&
                    $startDate->lessThanOrEqualTo($targetDate);

            case null:
            case 'none':
            default:
                return false;
        }
    }


    public function agendaActivity(Request $request, $id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        // Determine if the user can view all activities
        $canViewAll = ($user->roles->contains('role', 'Dolfijnen Leiding') ||
            $user->roles->contains('role', 'Zeeverkenners Leiding') ||
            $user->roles->contains('role', 'Loodsen Stamoudste') ||
            $user->roles->contains('role', 'Afterloodsen Organisator') ||
            $user->roles->contains('role', 'Administratie') ||
            $user->roles->contains('role', 'Bestuur') ||
            $user->roles->contains('role', 'Praktijkbegeleider') ||
            $user->roles->contains('role', 'Loodsen Mentor') ||
            $user->roles->contains('role', 'Ouderraad') ||
            $user->roles->contains('role', 'Loods') ||
            $user->roles->contains('role', 'Afterloods'));

        $wantViewAll = filter_var($request->query('all', false), FILTER_VALIDATE_BOOLEAN);
        $month = $request->query('month', '0');
        $view = $request->query('view', 'month');

        $rolesIDList = $roles->pluck('id')->toArray();

        // Fetch the activity using the provided id
        $activity = Activity::find($id);

        if (isset($activity) && $activity->recurrence_rule !== null) {
            $dateStart = $request->query('startDate');

            if (!$dateStart || !$activity->recurrence_rule) {
                return redirect()->route('agenda.month')->with('error', 'Activiteit niet gevonden.');
            }

            if (!self::isValidRepetitionDate($activity, $dateStart)) {
                return redirect()->route('agenda.month')->with('error', 'Deze herhaling van de activiteit bestaat niet.');
            }


            // Update activity dates for the current occurrence
            $originalStart = \Carbon\Carbon::parse($activity->date_start);
            $originalEnd = \Carbon\Carbon::parse($activity->date_end);

            $requestedStart = \Carbon\Carbon::parse($dateStart)->setTimeFrom($originalStart);

            $duration = $originalEnd->diffInSeconds($originalStart);

            $activity->date_start = $requestedStart->toDateTimeString();
            $activity->date_end = $requestedStart->copy()->addSeconds($duration)->toDateTimeString();

            // If there's a “deadline” stored in presence, shift it too
            if ($activity->presence !== null && $activity->presence !== "1" && $activity->presence !== "0") {
                $originalDeadline = Carbon::parse($activity->presence);

                $deadlineOffset = $originalDeadline->getTimestamp() - $originalStart->getTimestamp();
                $newDeadline = $requestedStart->copy()->addSeconds($deadlineOffset);

                $activity->presence = $newDeadline->toDateTimeString();
            }
        }

        if (!$activity) {
            return redirect()->route('agenda.month')->with('error', 'Activiteit niet gevonden.');
        }

        $userPresence = null;

        if ($activity->recurrence_rule !== null && $activity->recurrence_rule !== 'never') {
            $userPresence = Presence::where('user_id', $user->id)
                ->where('activity_id', $activity->id)
                ->where('date_occurrence', date("Y-m-d", strtotime($activity->date_start)))
                ->first();
        } else {
            $userPresence = Presence::where('user_id', $user->id)
                ->where('activity_id', $activity->id)
                ->first();
        }


        // Fetch user's presence status for the activity
        $presenceStatus = $userPresence?->presence;

        // Fetch roles and users for the activity
        $activityRoleIds = !empty($activity->roles)
            ? array_map('trim', explode(',', $activity->roles))
            : [];
        $activityUserIds = !empty($activity->users)
            ? array_map('trim', explode(',', $activity->users))
            : [];

        $hasRoleAccess = !empty(array_intersect($rolesIDList, $activityRoleIds));
        $isUserListed = in_array($user->id, $activityUserIds);


        // Check if the user is directly included via user_id.
        $isDirectUserAccess = in_array($user->id, array_map('trim', explode(',', $activity->user_id)));
        if (empty($activityRoleIds) && empty($activityUserIds)) {
            $isDirectUserAccess = true;
        }

        $canAlwaysView = $hasRoleAccess || $isUserListed || $isDirectUserAccess || $canViewAll;
        if (!$canAlwaysView) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'View activity', 'agenda', 'Activity item id: ' . $id, 'Gebruiker had geen toegang tot Activiteit');
            return redirect()->route('agenda.month')->with('error', 'Je hebt geen toegang tot deze activiteit.');
        }

        // Pass the requested startDate along to the view. (For recurring events it represents the occurrence.)
        return view('agenda.activity', [
            'user' => $user,
            'roles' => $roles,
            'activity' => $activity,
            'presenceStatus' => $presenceStatus,
            'month' => $month,
            'wantViewAll' => $wantViewAll,
            'view' => $view,
            'canAlwaysView' => $canAlwaysView,
        ]);
    }


    public function agendaActivityPublic(Request $request, $id)
    {
        $month = $request->query('month', '0');
        $view = $request->query('view', 'month');

        // Fetch the public activity by id
        $activity = Activity::where('id', $id)
            ->where('public', "0")
            ->first();

        $dateStart = $request->query('startDate');

        if (!$dateStart || !$activity->recurrence_rule) {
            return redirect()->route('agenda.public.schedule')->with('error', 'Activiteit niet gevonden.');
        }

        if (!self::isValidRepetitionDate($activity, $dateStart)) {
            return redirect()->route('agenda.public.schedule')->with('error', 'Deze herhaling van de activiteit bestaat niet.');
        }

// Update activity dates for the current occurrence
        $originalStart = \Carbon\Carbon::parse($activity->date_start);
        $originalEnd = \Carbon\Carbon::parse($activity->date_end);

        $requestedStart = \Carbon\Carbon::parse($dateStart)->setTimeFrom($originalStart);

        $duration = $originalEnd->diffInSeconds($originalStart);

        $activity->date_start = $requestedStart->toDateTimeString();
        $activity->date_end = $requestedStart->copy()->addSeconds($duration)->toDateTimeString();

        // If there's a “deadline” stored in presence, shift it too
        if ($activity->presence !== null && $activity->presence !== "1" && $activity->presence !== "0") {
            $originalDeadline = Carbon::parse($activity->presence);

            $deadlineOffset = $originalDeadline->getTimestamp() - $originalStart->getTimestamp();
            $newDeadline = $requestedStart->copy()->addSeconds($deadlineOffset);

            $activity->presence = $newDeadline->toDateTimeString();
        }


        if (!$activity) {
            return view('agenda.public.event', [
                'activity' => null,
                'month' => $month,
                'view' => $view
            ]);
        }

        // Check for startDate parameter
        $requestedStartDate = $request->query('startDate');
        if (!empty($activity->recurrence_rule)) {
            // For recurring events, a valid startDate must be provided.
            if (!$requestedStartDate) {
                // If startDate isn’t provided, consider the occurrence non‑existent.
                $activity = null;
            } else {
                try {
                    $occurrenceStart = Carbon::parse($requestedStartDate)->startOfDay();
                } catch (\Exception $e) {
                    $activity = null;
                }
            }
        } else {
            // For non‑recurring events, if a startDate is provided, treat it as invalid.
            if ($requestedStartDate) {
                $activity = null;
            }
        }

        return view('agenda.public.event', [
            'activity' => $activity,
            'month' => $month,
            'view' => $view
        ]);
    }


    public function createAgenda(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $all_roles = Role::all();


        $month = $request->query('month', '0');
        $wantViewAll = $request->query('all', '0');
        $view = $request->query('view', 'month');

        return view('agenda.add', ['user' => $user, 'roles' => $roles, 'all_roles' => $all_roles, 'monthOffset' => $month, 'wantViewAll' => $wantViewAll, 'view' => $view]);
    }

    public function createAgendaSave(Request $request)
    {
        $month = $request->query('month', '0');
        $wantViewAll = $request->query('all', '0');
        $view = $request->query('view', 'month');

        // Validate the request inputs
        $validatedData = $request->validate([
            'title' => 'string|required',
            'content' => 'string|max:65535|nullable',
            'date_start' => ['date', 'required', 'before_or_equal:date_end'],
            'date_end' => ['date', 'required', 'after_or_equal:date_start'],
            'public' => 'boolean|required',
            'location' => 'string|nullable',
            'max_tickets' => 'integer|nullable',
            'organisator' => 'string|nullable',
            'image' => 'mimes:jpeg,png,jpg,gif,webp|max:6000',

            'reoccurrence' => 'string',

            'form_labels' => 'nullable|array',
            'form_types' => 'nullable|array',
            'form_options' => 'nullable|array',
            'is_required' => 'nullable|array',

            'prices_to_add' => 'nullable|string'
        ]);

        try {
            // Process image upload
            $newPictureName = null;
            if ($request->hasFile('image')) {
                $newPictureName = time() . '.' . $request->file('image')->extension();
                $destinationPath = 'files/agenda/agenda_images';
                $request->file('image')->move(public_path($destinationPath), $newPictureName);
            }

            // Validate content for disallowed elements or styles
            if (ForumController::validatePostData($request->input('content'))) {

                // Create the activity
                $activity = Activity::create([
                    'content' => $request->input('content'),
                    'organisator' => $request->input('organisator'),
                    'location' => $request->input('location'),
                    'max_tickets' => $request->input('max_tickets'),
                    'date_start' => $request->input('date_start'),
                    'date_end' => $request->input('date_end'),
                    'title' => $request->input('title'),
                    'user_id' => Auth::id(),
                    'image' => $newPictureName,
                    'public' => $request->input('public'),
                    'recurrence_rule' => $request->input('reoccurrence')
                ]);

                if (!empty($request->input('prices_to_add'))) {
                    $prices = json_decode($request->input('prices_to_add'), true);
                    if (is_array($prices)) {
                        foreach ($prices as $priceData) {
                            // Create the generic Price record
                            $price = Price::create([
                                'name' => $priceData['name'],
                                'amount' => $priceData['amount'],
                                'type' => $priceData['type'],
                            ]);

                            // Link it to the Product
                            ActivityPrice::create([
                                'activity_id' => $activity->id,
                                'price_id' => $price->id,
                            ]);
                        }
                    }
                }

                // Log the creation of the activity
                $log = new Log();
                $log->createLog(auth()->user()->id, 2, 'Create event', 'agenda', 'Activity id: ' . $activity->id, '');

                // Handle form elements (if provided)
                if (isset($validatedData['form_labels']) && $request->input('reoccurrence') === "never") {
                    foreach ($validatedData['form_labels'] as $index => $label) {
                        $type = $validatedData['form_types'][$index];
                        $isRequired = isset($validatedData['is_required'][$index]);

                        $optionsString = null;
                        // If the field type is select, radio, or checkbox, save options
                        if (in_array($type, ['select', 'radio', 'checkbox']) && isset($validatedData['form_options'][$index])) {
                            $optionsString = implode(',', $validatedData['form_options'][$index]);
                        }

                        // Create form element
                        ActivityFormElement::create([
                            'option_value' => $optionsString,
                            'activity_id' => $activity->id,
                            'label' => $label,
                            'type' => $type,
                            'is_required' => $isRequired,
                        ]);
                    }

                    // Log the creation of the form elements
                    $log->createLog(auth()->user()->id, 2, 'Create activity form', 'agenda', 'Activity id: ' . $activity->id, 'Er is een inschrijfformulier aangemaakt.');
                }

                    return redirect()->route('agenda.activity', ['id' => $activity->id, 'month' => $month, 'all' => $wantViewAll, 'view' => $view])->with('success', 'Je agendapunt is opgeslagen!');
            } else {
                throw ValidationException::withMessages(['content' => 'Je agendapunt kan niet opgeslagen worden.']);
            }
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het opslaan van je agendapunt. Probeer het opnieuw. '.$e)->withInput();
        }
    }

    public function deleteActivity(Request $request, $id)
    {
        $user = Auth::user();

        $type = $request->query('type', 'all');


        try {
            $activity = Activity::find($id);
        } catch (ModelNotFoundException $exception) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Delete activity', 'activity', 'Actvity id: ' . $id, 'Activiteit bestaat niet');
            return redirect()->route('agenda.edit.activity', $id)->with('error', 'Deze activiteit bestaat niet.');
        }
        if ($activity === null) {
            $log = new Log();
            $log->createLog(auth()->user()->id, 1, 'Delete activity', 'activity', 'Actvity id: ' . $id, 'Activiteit bestaat niet');
            return redirect()->route('agenda.edit.activity', $id)->with('error', 'Deze activiteit bestaat niet.');
        }

        Presence::where('activity_id', $activity->id)->delete();

        if (isset($type)) {
            switch ($type) {
                case 'all':
                    // Delete recurring rule entirely
                    $activity->delete();
                    break;

                case 'following':
                    $end = $request->query('end_date');
                    // Set the recurrence_rule end to this occurrence
                    $activity->end_recurrence = $end;
                    $activity->save();
                    break;

                case 'single':
                    $date = $request->query('date');
                    // Insert into exceptions table
                    ActivityException::create([
                        'activity_id' => $activity->id,
                        'date' => $date,
                    ]);
                    break;
            }
        } else {

            $activity->delete();
        }

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'Delete activity', 'activity', $activity->title, '');

        $month = $request->query('month', '0');
        $wantViewAll = $request->query('all', '0');
        $view = $request->query('view', 'month');

            if ($view === 'month') {
                return redirect()->route('agenda.month', ['month' => $month, 'all' => $wantViewAll])->with('success', 'Je activiteit is verwijderd');
            } else {
                return redirect()->route('agenda.schedule', ['month' => $month, 'all' => $wantViewAll])->with('success', 'Je activiteit is verwijderd');
            }
    }


    public function generateToken(Request $request)
    {
        $user = Auth::user();

        if (!$user->calendar_token) {
            $user->calendar_token = Str::uuid();;
            $user->save();
        }

        return response()->json([
            'token' => $user->calendar_token,
            'calendar_url' => route('agenda.feed', ['token' => $user->calendar_token]),
        ]);
    }

    public function exportFeed(Request $request, $token)
    {
        $user = User::where('calendar_token', $token)->firstOrFail();
        $roles = $user->roles()->orderBy('role', 'asc')->get();
        $rolesIDList = $roles->pluck('id')->toArray();
        $canViewAll = false;

        $monthOffset = 0;

        Carbon::setLocale('nl');
        $baseDate = Carbon::now();
        $calculatedDate = $baseDate->copy()->addMonths($monthOffset);

        $rangeStart = $calculatedDate->copy()->startOfMonth()->addMonths(-1)->startOfDay();
        $rangeEnd = $calculatedDate->copy()->addMonths(5)->endOfMonth()->endOfDay();

        $fetchedActivities = Activity::query()
            ->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('date_start', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($q) use ($rangeStart, $rangeEnd) {
                        $q->whereIn('recurrence_rule', ['daily', 'weekly', 'monthly'])
                            ->where(function ($q2) use ($rangeStart) {
                                $q2->whereNull('end_recurrence')
                                    ->orWhere('end_recurrence', '>=', $rangeStart);
                            })
                            ->where('date_start', '<=', $rangeEnd);
                    });
            })
            ->orderBy('date_start')
            ->get();


        // Load exceptions for single-instance deletions
        $exceptionsByActivity = ActivityException::whereIn('activity_id', $fetchedActivities->pluck('id'))
            ->get()
            ->groupBy('activity_id')
            ->map(function ($group) {
                return $group->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();
            });

        // Expand recurring events
        $activities = collect();
        foreach ($fetchedActivities as $activity) {
            $occurrences = $this->expandRecurringActivity($activity, $rangeStart, $rangeEnd);
            $skipDates = $exceptionsByActivity[$activity->id] ?? [];

            foreach ($occurrences as $occurrence) {
                $occDate = Carbon::parse($occurrence->date_start)->toDateString();

                // Skip single-instance exceptions
                if (in_array($occDate, $skipDates, true)) {
                    continue;
                }

                // Skip beyond end_recurrence
                if (!is_null($activity->end_recurrence)) {
                    $endRec = Carbon::parse($activity->end_recurrence)->endOfDay();
                    if (Carbon::parse($occurrence->date_start)->gt($endRec)) {
                        continue;
                    }
                }


                $activities->push($occurrence);
            }
        }

        $activities = $activities->filter(function ($activity) use ($user, $rolesIDList, $canViewAll) {
            $activityRoleIds = !empty($activity->roles)
                ? array_map('trim', explode(',', $activity->roles))
                : [];
            $activityUserIds = !empty($activity->users)
                ? array_map('trim', explode(',', $activity->users))
                : [];

            if (empty($activityRoleIds) && empty($activityUserIds)) {
                $activity->should_highlight = false;
                return true;
            } else {
                $hasRoleAccess = !empty(array_intersect($rolesIDList, $activityRoleIds));
                $isUserListed = in_array($user->id, $activityUserIds);

                if ($canViewAll) {
                    $activity->should_highlight = !$hasRoleAccess && !$isUserListed;
                    return true;
                } else {
                    return $hasRoleAccess || $isUserListed;
                }
            }
        });

        $activities = $activities->sortBy('date_start')->values();

        $calendar = Calendar::create()
            ->name("MHG Agenda van ".$user->name)
            ->description('Jouw persoonlijke MHG Agenda')
            ->appendProperty(TextProperty::create('CALSCALE', 'GREGORIAN'))
            ->appendProperty(TextProperty::create('METHOD', 'PUBLISH'))
            ->refreshInterval(30);


        foreach ($activities as $act) {

            $desc = strip_tags(html_entity_decode($act->content));
            $desc = preg_replace("/\r\n|\r|\n/", '\\n', $desc);
            $desc = preg_replace('/\s+/', ' ', $desc);


            $event = Event::create()
                ->name($act->title)
                ->description($desc)
                ->createdAt(Carbon::parse($act->created_at))
                ->startsAt(Carbon::parse($act->date_start))
                ->endsAt(Carbon::parse($act->date_end));

            $calendar->event($event);
        }


        return response($calendar->get(), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="mhg-calender.ics"',
        ]);
    }
}

<?php

namespace App\Models;

use App\Mail\accountActivated;
use App\Mail\accountChange;
use App\Mail\accountDeactivated;
use App\Mail\accountReactivated;
use App\Mail\addParent;
use App\Mail\admin;
use App\Mail\contactMessage;
use App\Mail\deleteChild;
use App\Mail\deleteParent;
use App\Mail\newAccount;
use App\Mail\newActivityRegistration;
use App\Mail\newComment;
use App\Mail\newPost;
use App\Mail\newReactionComment;
use App\Mail\newRegistration;
use App\Mail\newRegistrationAdminNotification;
use App\Mail\newsAccepted;
use App\Mail\passwordChange;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class Notification extends Model
{
    protected $fillable = [
        'display_text',
        'link',
        'seen',
        'sender_id',
        'reciever_id'
    ];

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function sendNotification($senderId, $recieverIds, $displayText, $link, $location, $notificationType, $relevant_id = null)
    {
        foreach ($recieverIds as $recieverId) {

            $user = User::find($recieverId);

            if ($user->getNotificationSetting('app_' . $notificationType)) {
                // Create a notification within portal
                $notification = new Notification();
                $notification->sender_id = $senderId;
                $notification->receiver_id = $recieverId;
                $notification->display_text = $displayText;
                $notification->link = $link;
                $notification->location = $location;
                $notification->seen = false;
                $notification->save();
            }

            if ($user->getNotificationSetting('mail_' . $notificationType) && config('app.mail') && $user->is_associate != 1) {
//            if ($user->getNotificationSetting('mail_' . $notificationType) && $user->is_associate != 1) {

                if (isset($senderId)) {
                    $sender = User::find($senderId);
                }

                $isDolfijn = false;

                if ($user->roles->contains('role', 'dolfijn')) {
                    $isDolfijn = true;
                }

                if ($user) {

                    // Gather the necessary data for the email
                    $data = [
                        'reciever_name' => $user->name,
                        'message' => $displayText,
                        'link' => $link,
                        'location' => $location,
                        'sender_dolfijnen_name' => $senderId ? $sender->dolfijnen_name : null,
                        'reciever_is_dolfijn' => $senderId && $isDolfijn ? $isDolfijn : null,
                        'sender_full_name' => $senderId ? $sender->name . " " . ($sender->infix ?? '') . " " . $sender->last_name : null,
                        'email' => $user->email,
                        'relevant_id' => $relevant_id
                    ];

                    switch ($notificationType) {
                        case 'admin':
                            Mail::to($data['email'])->send(new admin($data));
                            break;

                        case 'account_change':
                            Mail::to($data['email'])->send(new accountChange($data));
                            break;

                        case 'password_change':
                            Mail::to($data['email'])->send(new passwordChange($data));
                            break;

                        case 'new_post':
                            Mail::to($data['email'])->send(new newPost($data));
                            break;

                        case 'new_comment':
                            Mail::to($data['email'])->send(new newComment($data));
                            break;

                        case 'new_reaction_comment':
                            Mail::to($data['email'])->send(new newReactionComment($data));
                            break;

                        case 'news_accepted':
                            Mail::to($data['email'])->send(new newsAccepted($data));
                            break;

                        case 'new_activity_registration':
                            Mail::to($data['email'])->send(new newActivityRegistration($data));
                            break;

                        case 'contact_message':
                            Mail::to($data['email'])->send(new contactMessage($data));
                            break;

                        case 'new_registration':
                            Mail::to($data['email'])->send(new newRegistration($data));
                            break;

                        case 'new_account':
                            Mail::to($data['email'])->send(new newAccount($data));
                            break;
                        case 'new_registration_admin_notification':
                            Mail::to($data['email'])->send(new newRegistrationAdminNotification($data));
                            break;

                        case 'account_activated':
                            Mail::to($data['email'])->send(new accountActivated($data));
                            break;
                        case 'account_deactivated':
                            Mail::to($data['email'])->send(new accountDeactivated($data));
                            break;
                        case 'account_reactivated':
                            Mail::to($data['email'])->send(new accountReactivated($data));
                            break;

                        case 'add_parent':
                            Mail::to($data['email'])->send(new addParent($data));
                            break;

                        case 'delete_parent':
                            Mail::to($data['email'])->send(new deleteParent($data));
                            break;

                        case 'delete_child':
                            Mail::to($data['email'])->send(new deleteChild($data));
                            break;
                    }


                }
            }
        }
    }
}

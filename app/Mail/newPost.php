<?php

namespace App\Mail;

use App\Models\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class newPost extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $log = new Log();
        $log->createLog(null, 1, 'Send mail', $this->data["location"], '', 'new_post');

        return $this->subject('Er is een nieuwe '.$this->data["location"].' post geplaatst')
            ->markdown('emails.new_post')
            ->with(['data' => $this->data]);
    }
}

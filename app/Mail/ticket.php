<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class ticket extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

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
        // Generate PDF
        $pdf = Pdf::loadView('pdf.ticket', ['ticket' => $this->data]);

        return $this->subject('Je Ticket voor NoordenLicht')
            ->markdown('emails.ticket')
            ->with(['ticket' => $this->data])
            ->attachData($pdf->output(), 'ticket-' . $this->data->activity->title . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}

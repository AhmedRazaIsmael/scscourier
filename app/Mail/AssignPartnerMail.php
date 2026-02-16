<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssignPartnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        $subject = "Booking Number: " . $this->booking->bookNo;
        $body = "Dear Team,<br><br>"
              . "Please arrange this pickup against Booking Number: <b>{$this->booking->bookNo}</b>.<br><br>"
              . "Origin: {$this->booking->origin}<br>"
              . "Destination: {$this->booking->destination}<br>"
              . "Weight: {$this->booking->weight} KG<br><br>"
              . "Thank you.";

        return $this->subject($subject)
                    ->html($body)
                    ->attach(storage_path('app/public/bookings/' . $this->booking->bookNo . '.pdf'), [
                        'as' => $this->booking->bookNo . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}

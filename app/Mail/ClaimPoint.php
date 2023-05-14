<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaimPoint extends Mailable
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

    }

    /**
     * Build the message.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @return void
     */
    public function build(Mailer $mailer)
    {
        return $this->markdown('pointsAwarded')
                    ->subject('New Transaction')
                    ->to('group@example.com')
                    ->from(env('MAIL_FROM_ADDRESS'))
                    ->with(['customer' =>$this->data['customerName'],
                            'tel' => $this->data['customerPhone'],
                             'amount' => $this->data['amountPurchased'],
                            'points' => $this->data['awardedPoint'],
                            'center' => $this->data['center'],
                            'totalPoints' => $this->data['totalPoints']]);
    }
}

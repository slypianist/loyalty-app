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
        $this->data = $data;

    }

    /**
     * Build the message.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @return void
     */
    public function build(Mailer $mailer)
    {
        return $this->markdown('pointsClaimed')
                    ->subject('New Claims')
                    ->to('itsupport@capplc.com')
                    ->bcc('capplc104@gmail.com')
                    ->from(env('MAIL_FROM_ADDRESS','CAP PLC'))
                    ->with(['customer' =>$this->data['customerName'],
                            'tel' => $this->data['customerPhone'],
                            'amount' => $this->data['amount'],
                             'claim' => $this->data['claims'],
                            'points' => $this->data['points'],
                            'balance' => $this->data['balance']]);
    }
}

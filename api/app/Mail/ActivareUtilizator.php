<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\User;
use App\Setting;

class ActivareUtilizator extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->user = User::find($userId);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('tehnic@dsu-api.ro', 'Tehnic DSU API')
            ->replyTo('no-reply@dsu-api.ro', 'NO REPLY')
            ->subject('Activare utilizator')
            ->markdown('emails.users.activare', [

//            'body_message' => 'mail.body.reset_password',
            'level' => 'success',
            'actionUrl' => $this->user->nume,
            'actionText' => 'mailbuttonsactivate',
//            'salutation' => 'mail.greetings.salutation',
//            'signature' => 'mail.greetings.signature_tehnic',
//            'subcopy_content' => 'mail.subcopy.reset_password',
        ]);
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\User;
use App\Setting;

class WelcomeUtilizator extends Mailable
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
        return $this->from(Setting::where('key', 'site.api.email_support')->first()->value, Setting::where('key', 'site.api.support_signature')->first()->value)
            ->replyTo('no-reply@example.com', 'NO REPLY')
            ->subject('Cont de utilizator activat')
            ->markdown('emails.users.welcome', [
                'level' => 'success',
                'greeting' => 'Bună ' . (!empty($this->user->prenume) ? $this->user->prenume . ' ' . $this->user->nume : $this->user->nume) . ',',
                'message_1' => 'Contul tău de utilizator pentru aplicația de raportare integrată DSU a fost activat cu succes. Te sfătuim să modifici parola după logare.',
                'actionUrl' => url(route('login.utilizator')),
                'actionText' => 'Logare',
                'salutation' => 'Toate cele bune,',
                'signature' => 'Echipa tehnică',
                'subcopy_content' => sprintf('Dacă întâmpini probleme la clic pe butonul Activare, copiază și inserează adresa URL de mai jos în browser-ul tău:'),
        ]);
    }
}

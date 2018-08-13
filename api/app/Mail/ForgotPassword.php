<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\User;
use App\Setting;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

  protected $user;
  protected $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
  public function __construct($user, $token)
  {
      $this->user = $user;
      $this->token = $token;
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
          ->subject('Resetare parolă')
          ->markdown('emails.users.forgot-pass', [
              'level' => 'success',
              'greeting' => 'Bună ' . (!empty($this->user->prenume) ? $this->user->prenume . ' ' . $this->user->nume : $this->user->nume) . ',',
              'message_1' => 'Am primit de la tine o cerere de resetare a parolei.',
              'message_2' => 'Dacă nu ai inițiat această cerere te rugăm să ignori acest email.',
              'user_name' => $this->user->email,
              'actionUrl' => url(route('resetare.parola', ['token' => $this->token])),
              'actionText' => 'Schimbă parola',
              'salutation' => 'Toate cele bune,',
              'signature' => 'Echipa tehnică',
              'subcopy_content' => sprintf('Dacă întâmpini probleme la click pe butonul Schimbă parola, copiază și inserează adresa URL de mai jos în browser-ul tău:'),
          ]);
  }
}

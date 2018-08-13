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
  protected $parola;

    /**
     * Create a new message instance.
     *
     * @return void
     */
  public function __construct($userId, $parola)
  {
      $this->user = User::find($userId);
      $this->parola = $parola;
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
          ->subject('Activare utilizator')
          ->markdown('emails.users.activare', [
              'level' => 'success',
              'greeting' => 'Bună ' . (!empty($this->user->prenume) ? $this->user->prenume . ' ' . $this->user->nume : $this->user->nume) . ',',
              'message_1' => 'Contul tău de utilizator pentru aplicația de raportare integrată DSU a fost creat cu succes. '.
              .'Pentru conectare, contul tău trebuie activat. Datele tale de conectare sunt:',
              'message_2' => 'Păstreaza aceste date pentru conectare, necesare dupa activarea contului, apăsând butonul de mai jos.',
              'user_name' => $this->user->email,
              'password' => $this->parola,
              'actionUrl' => url(route('activare.utilizator', ['token' => $this->user->remember_token])),
              'actionText' => 'Activare',
              'salutation' => 'Toate cele bune,',
              'signature' => 'Echipa tehnică',
              'subcopy_content' => sprintf('Dacă întâmpini probleme la clic pe butonul Activare, copiază și inserează adresa URL de mai jos în browser-ul tău:'),
          ]);
  }
}

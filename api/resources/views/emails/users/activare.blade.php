@component('mail::message')

{{-- Greeting --}}
@if (! empty($greeting))
    {{ $greeting }}
@else
    @if ($level == 'error')
        Whoops!
    @else
        Buna!
    @endif
@endif

{{-- Body --}}
@if (! empty($message_1))
{{ $message_1 }}
@endif

@component('mail::panel')
Nume utilizator: {{$user_name}}<br />Parola: <strong>{{$password}}</strong>
@endcomponent

@if (! empty($message_2))
{{ $message_2 }}
@endif

{{-- Action Button --}}
@isset($actionText)
<?php
switch ($level) {
  case 'success':
      $color = 'green';
        break;
  case 'error':
      $color = 'red';
        break;
  default:
      $color = 'blue';
}
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}<br>{{ $signature }}
@else
Toate cele bune,<br>{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
@component('mail::subcopy')
{{ $subcopy_content }}<br>{{ $actionUrl }}
@endcomponent
@endisset
@endcomponent
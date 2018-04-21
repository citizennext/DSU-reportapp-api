@component('mail::message')

    {{-- Greeting --}}
    @if (!empty($greeting))
        {{ $greeting }}
    @endif

    {{-- Body --}}
    @if (!empty($body_message))
        {{ $body_message }}
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
    @if (!empty($salutation))
        {{ $salutation }}<br />{{ $signature }}
    @else
        Toate cele bune,<br />{{ config('app.name') }}
    @endif

@endcomponent
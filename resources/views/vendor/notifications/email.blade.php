<x-mail::message>
<table align="center">
    <tr>
        <td align="center">
            <img src="{{ asset('images/logo.png') }}" alt="Technograz logo" width="200">
        </td>
    </tr>
</table>

{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Hoppá!')
@else
# @lang('Üdvözöljük!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
Üdvözlettel,<br>
Technorgáz kft.
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang(
    "Ha nem tudja megnyitni a \":actionText\" gombot, másolja be az alábbi URL-t a böngészőjébe:",
    [
        'actionText' => $actionText,
    ]
) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>

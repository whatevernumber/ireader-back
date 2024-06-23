<x-mail::message>
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}
@endforeach

@foreach($books as $book)
    @if($book->image)
        @php($url = env('BACKEND_ADDRESS') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'covers' . DIRECTORY_SEPARATOR . $book->image->image)

![alt]({{ $message->embed($url) }})

    @endif
    {{ $book->title . ' - ' . $book->authors[0]->name . ', ' . $book->published_year}}
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
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang(
    "Если ссылка \":actionText\" не открывается, попробуйте\n".
    'скопировать ссылку и вставить её в адресную строку браузера:',
    [
        'actionText' => $actionText,
    ]
) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>

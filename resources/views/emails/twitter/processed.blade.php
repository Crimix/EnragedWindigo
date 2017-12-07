@component('mail::message')
# Processing done

Your request to get the political alignment of {{ $twitter_username }}
has been processed and the results are in.

@component('mail::button', ['url' => route('twitter.result', ['request_id' => $requestId])])
See result
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

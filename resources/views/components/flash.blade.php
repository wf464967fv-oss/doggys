{{--
    Session flash messages, rendered once by the layout under the header.
    Controllers: ->with('status', __('…')) for success, ->with('error', __('…')) for failures.
    Fortify status codes (e.g. "verification-link-sent") are translated through ui.status.*.
--}}
@php
    $status = session('status');
    $statusText = $status && \Illuminate\Support\Facades\Lang::has('ui.status.'.$status) ? __('ui.status.'.$status) : $status;
@endphp

@if ($statusText || session('error'))
    <div class="mx-auto mt-4 w-full max-w-6xl px-4 sm:px-6">
        @if ($statusText)
            <x-alert variant="success">{{ $statusText }}</x-alert>
        @endif
        @if (session('error'))
            <x-alert variant="error">{{ session('error') }}</x-alert>
        @endif
    </div>
@endif

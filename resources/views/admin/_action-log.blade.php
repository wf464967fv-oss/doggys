{{-- List of admin_actions rows. Expects $actions. --}}
<ul class="divide-y divide-line rounded-3xl bg-white ring-1 ring-line" role="list">
    @foreach ($actions as $action)
        <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm sm:px-5">
            <span class="min-w-0 break-words">
                <span class="font-bold text-brown">{{ \Illuminate\Support\Facades\Lang::has('admin.log.actions.'.$action->action) ? __('admin.log.actions.'.$action->action) : $action->action }}</span>
                <span class="text-muted">· {{ $action->subject_type }} #{{ $action->subject_id }}</span>
                @if ($action->details)
                    <span class="block text-brown-dark">{{ $action->details }}</span>
                @endif
            </span>
            <span class="shrink-0 text-muted">
                {{ $action->user?->name ?? __('admin.log.deleted_admin') }} ·
                <time datetime="{{ $action->created_at->toIso8601String() }}">{{ $action->created_at->locale(app()->getLocale())->isoFormat('lll') }}</time>
            </span>
        </li>
    @endforeach
</ul>

<x-admin-layout :title="__('admin.contact.title')" :subtitle="__('admin.contact.subtitle')">
    @include('admin._status-tabs', ['route' => 'admin.contact.index', 'current' => $status, 'options' => \Illuminate\Support\Arr::only(__('admin.report_statuses'), ['pending', 'handled'])])

    @if ($messages->isEmpty())
        <x-empty-state class="mt-6" icon="mail" :title="__('admin.contact.empty')" />
    @else
        <ul class="mt-6 space-y-3" role="list">
            @foreach ($messages as $message)
                <li>
                    <x-card as="article" padding="sm" class="sm:px-5">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-bold break-words text-brown">{{ $message->subject ?? '—' }}</p>
                                <p class="text-sm break-all text-muted">{{ $message->name }} · <a href="mailto:{{ $message->email }}" class="link">{{ $message->email }}</a></p>
                            </div>
                            <p class="text-sm text-muted"><span class="uppercase">{{ $message->locale }}</span> ·
                                <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->locale(app()->getLocale())->isoFormat('LLL') }}</time></p>
                        </div>
                        <p class="mt-3 rounded-2xl bg-cream-light p-3 text-sm break-words whitespace-pre-line text-brown-dark">{{ $message->message }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <x-button size="sm" variant="secondary" icon="mail" :href="'mailto:'.$message->email.'?subject='.rawurlencode('Re: '.$message->subject)">{{ __('admin.contact.reply') }}</x-button>
                            @if ($message->status === \App\Enums\ReportStatus::Pending)
                                <form method="POST" action="{{ route('admin.contact.handle', $message) }}">
                                    @csrf
                                    <x-button size="sm" variant="soft" icon="check">{{ __('admin.contact.mark_handled') }}</x-button>
                                </form>
                            @endif
                        </div>
                    </x-card>
                </li>
            @endforeach
        </ul>
        @if ($messages->hasPages())
            <div class="mt-6">{{ $messages->onEachSide(1)->links() }}</div>
        @endif
    @endif
</x-admin-layout>

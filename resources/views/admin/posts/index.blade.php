<x-admin-layout :title="__('admin.posts.title')" :subtitle="__('admin.posts.subtitle')">
    @if ($posts->isEmpty())
        <x-empty-state icon="book" :title="__('admin.posts.empty')" />
    @else
        <ul class="space-y-3" role="list">
            @foreach ($posts as $post)
                @php($isPublished = $post->published_at && $post->published_at->isPast())
                <li>
                    <x-card padding="sm" class="flex flex-wrap items-center justify-between gap-3 sm:px-5">
                        <div class="min-w-0">
                            <p class="font-bold break-words text-brown"><span class="mr-1 rounded-md bg-cream px-1.5 text-xs uppercase">{{ $post->locale }}</span>{{ $post->title }}</p>
                            <p class="text-sm break-all text-muted">{{ $post->translation_key }} · /{{ $post->slug }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-badge :variant="$isPublished ? 'verified' : 'neutral'">{{ $isPublished ? __('admin.posts.published') : __('admin.posts.draft') }}</x-badge>
                            @if ($isPublished)
                                <form method="POST" action="{{ route('admin.posts.unpublish', $post) }}">
                                    @csrf
                                    <x-button size="sm" variant="ghost" icon="lock">{{ __('admin.posts.unpublish') }}</x-button>
                                </form>
                            @endif
                        </div>
                    </x-card>
                </li>
            @endforeach
        </ul>
        @if ($posts->hasPages())
            <div class="mt-6">{{ $posts->onEachSide(1)->links() }}</div>
        @endif
    @endif
</x-admin-layout>

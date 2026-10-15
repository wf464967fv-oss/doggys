<x-admin-layout :title="__('admin.log.title')" :subtitle="__('admin.log.subtitle')">
    @if ($actions->isEmpty())
        <x-empty-state icon="clock" :title="__('admin.log.empty')" />
    @else
        @include('admin._action-log')
        @if ($actions->hasPages())
            <div class="mt-6">{{ $actions->onEachSide(1)->links() }}</div>
        @endif
    @endif
</x-admin-layout>

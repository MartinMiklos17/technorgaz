<x-filament::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}
        <div class="mt-4">
            {!! $this->getFormActions()[0] !!}
        </div>
    </form>
</x-filament::page>

<x-filament::page>
    <form wire:submit.prevent="send" class="space-y-6">
        {{ $this->form }}

        <x-filament::button
            type="submit"
            color="primary"
            icon="heroicon-m-paper-airplane"
            wire:loading.attr="disabled"
            wire:target="send"
        >
            <span wire:loading.remove wire:target="send">E-mailek küldése</span>
            <span wire:loading wire:target="send" class="inline-flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Küldés folyamatban...
            </span>
        </x-filament::button>
    </form>

    <div class="mt-6">
        {{ $this->table }}
    </div>
</x-filament::page>

<x-filament::page>
    <form wire:submit.prevent="invite" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" color="primary">
            Meghívó küldése
        </x-filament::button>
    </form>

    @if (session()->has('success'))
        <div class="mt-4 text-green-600 font-semibold">
            {{ session('success') }}
        </div>
    @endif
</x-filament::page>

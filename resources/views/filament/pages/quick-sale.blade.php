<x-filament-panels::page>
    <form wire:submit="submit" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" size="lg" color="success" icon="heroicon-m-printer">
            Process Sale & Print Receipt
        </x-filament::button>
    </form>
</x-filament-panels::page>

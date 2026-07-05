<x-filament-panels::page>
    <div>
        {{ $this->form }}

        <div class="mt-6 flex flex-wrap items-center gap-4">
            <x-filament::button wire:click="processOnly" size="lg" color="primary" icon="heroicon-m-check-circle">
                Process Sale
            </x-filament::button>

            <x-filament::button wire:click="processAndPrint" size="lg" color="gray" icon="heroicon-m-printer">
                Process & Print Receipt
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>

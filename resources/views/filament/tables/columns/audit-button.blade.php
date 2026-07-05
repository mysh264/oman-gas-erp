@php
    $record = $column->getRecord();
    $changes = $record?->attribute_changes?->toArray() ?? [];
@endphp

<div class="px-2">
    <x-filament::button
        x-data="{}"
        x-on:click="$dispatch('open-modal', { id: 'audit-details-{{ $record->id }}' })"
        size="sm"
        color="gray"
    >
        View Details
    </x-filament::button>

    <x-filament::modal id="audit-details-{{ $record->id }}" width="3xl">
        <x-slot name="heading">Change Details: {{ class_basename($record->subject_type) }} #{{ $record->subject_id }}</x-slot>

        <div class="p-4 space-y-4">
            @foreach(($changes['attributes'] ?? []) as $key => $value)
                <div class="grid grid-cols-2 gap-4 border-b pb-2">
                    <span class="font-bold text-gray-500">{{ ucfirst($key) }}</span>
                    <div>
                        <span class="text-red-500 line-through">{{ $changes['old'][$key] ?? 'N/A' }}</span>
                        <span class="text-green-500 font-bold"> -> {{ $value }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::modal>
</div>

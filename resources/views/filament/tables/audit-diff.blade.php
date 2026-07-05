<div class="p-4 space-y-4">
    @foreach($data['attributes'] ?? [] as $key => $value)
        <div class="grid grid-cols-2 gap-4 border-b pb-2">
            <span class="font-bold text-gray-500">{{ ucfirst($key) }}</span>
            <div>
                <span class="text-red-500 line-through">{{ $data['old'][$key] ?? 'N/A' }}</span>
                <span class="text-green-500"> -> {{ $value }}</span>
            </div>
        </div>
    @endforeach
</div>

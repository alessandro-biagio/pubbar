@extends('layouts.staff')

@section('content')
<div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Default per ora (per categoria) -->
    <div class="bg-white shadow-sm rounded-xl p-6">
        <h3 class="text-lg font-semibold mb-4">Default per ora (per categoria)</h3>

        <form method="POST" action="{{ route('staff.capacity.updateDefaults') }}" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf

            @foreach ($categories as $cat)
                <label class="block">
                    <span class="text-sm text-gray-700">{{ $cat->name }}</span>
                    <input type="number" name="defaults[{{ $cat->slug }}]" min="1" max="1000"
                           value="{{ $defaults[$cat->slug] ?? 30 }}"
                           class="mt-1 block w-full rounded border-gray-300">
                </label>
            @endforeach

            <div class="sm:col-span-2 lg:col-span-3">
                <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Salva default
                </button>
            </div>
        </form>
    </div>

    <!-- Override prossime ore -->
    <div class="bg-white shadow-sm rounded-xl p-6">
        <h3 class="text-lg font-semibold mb-4">Override prossime ore</h3>

        <form method="POST" action="{{ route('staff.capacity.saveOverrides') }}" class="space-y-4">
            @csrf

            @foreach ($hours as $h)
                @php
                    $key = $h->toDateTimeString();
                    $row = $overrides[$key] ?? collect(); // keyBy('group') già fatto nel controller
                @endphp

                <div class="border rounded p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500">Ora</div>
                            <div class="font-medium">{{ $h->isoFormat('ddd DD/MM HH:mm') }}</div>
                        </div>
                        <input type="hidden" name="hours[]" value="{{ $key }}">
                    </div>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($categories as $cat)
                            @php $curr = optional($row->get($cat->slug))->capacity; @endphp
                            <label class="block">
                                <span class="text-sm text-gray-700">
                                    {{ $cat->name }} (vuoto = default)
                                </span>
                                <input type="number"
                                       name="overrides[{{ $key }}][{{ $cat->slug }}]"
                                       min="1" max="1000"
                                       value="{{ $curr }}"
                                       class="mt-1 block w-full rounded border-gray-300">
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Salva override
            </button>
        </form>
    </div>
</div>
@endsection

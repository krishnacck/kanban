@extends('layouts.app')

@section('title', 'Manage Statuses')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Statuses</h1>
        <button @click="$dispatch('open-modal', 'create-status')"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">
            + Add Status
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-700 text-sm">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Color</th>
                    <th class="px-4 py-3 text-left">Order</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($statuses as $status)
                <tr x-data="{ editing: false }">
                    <td class="px-4 py-3">
                        <span x-show="!editing" class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full inline-block" style="background: {{ $status->color }}"></span>
                            {{ $status->name }}
                        </span>
                        <form x-show="editing" method="POST" action="{{ route('statuses.update', $status) }}">
                            @csrf @method('PUT')
                            <div class="flex gap-2 flex-wrap">
                                <input type="text" name="name" value="{{ $status->name }}" class="border rounded px-2 py-1 text-sm w-32" required>
                                <input type="color" name="color" value="{{ $status->color }}" class="border rounded h-8 w-10">
                                <input type="number" name="order" value="{{ $status->order }}" class="border rounded px-2 py-1 text-sm w-16" required>
                                <button type="submit" class="bg-indigo-600 text-white px-2 py-1 rounded text-xs">Save</button>
                                <button type="button" @click="editing = false" class="text-gray-500 px-2 py-1 rounded text-xs">Cancel</button>
                            </div>
                        </form>
                    </td>
                    <td class="px-4 py-3" x-show="!editing">
                        <span class="font-mono text-xs text-gray-500">{{ $status->color }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500" x-show="!editing">{{ $status->order }}</td>
                    <td class="px-4 py-3 text-right" x-show="!editing">
                        <button @click="editing = true" class="text-indigo-600 hover:underline text-xs mr-3">Edit</button>
                        <form method="POST" action="{{ route('statuses.destroy', $status) }}" class="inline"
                            onsubmit="return confirm('Delete this status?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No statuses yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create modal --}}
    <div x-data="{ open: false }" @open-modal.window="open = ($event.detail === 'create-status')"
        x-show="open" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" x-cloak>
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md" @click.outside="open = false">
            <h2 class="text-lg font-semibold mb-4">Add Status</h2>
            <form method="POST" action="{{ route('statuses.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" name="name" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color *</label>
                    <input type="color" name="color" value="#6366f1" class="border rounded h-10 w-full">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Order *</label>
                    <input type="number" name="order" value="0" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Manage Categories')

@section('content')
<div x-data="{ showAdd: false, addName: '', addCode: '' }" class="max-w-3xl mx-auto py-8 px-4">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
            <p class="text-sm text-gray-500 mt-0.5">Vertical lanes on your Kanban board</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/board" class="text-sm text-gray-500 hover:text-indigo-600 flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to board
            </a>
            <button @click="showAdd = !showAdd"
                class="flex items-center gap-1.5 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Category
            </button>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    {{-- Add form (inline) --}}
    <div x-show="showAdd" x-cloak
        class="mb-4 bg-white rounded-xl border border-indigo-200 shadow-sm p-4">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">New Category</h2>
        <form method="POST" action="{{ route('countries.store') }}" class="flex items-end gap-3 flex-wrap"
            x-data="{ catName: '', suggestions: [], selectedIdx: -1 }"
            @submit.prevent="$el.submit()">
            @csrf
            <div class="flex-1 min-w-[160px] relative">
                <label class="block text-xs font-medium text-gray-500 mb-1">Name *</label>
                <input type="text" name="name" placeholder="e.g. Engineering, Marketing…"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    required autofocus
                    x-model="catName"
                    @input.debounce.300ms="if(catName.length >= 2){ fetch('/categories/suggest?q='+encodeURIComponent(catName), {headers:{'Accept':'application/json'}}).then(r=>r.json()).then(d=>{suggestions=d; selectedIdx=-1;}) } else { suggestions=[] }"
                    @keydown.down.prevent="selectedIdx = Math.min(selectedIdx + 1, suggestions.length - 1)"
                    @keydown.up.prevent="selectedIdx = Math.max(selectedIdx - 1, 0)"
                    @keydown.enter="if(selectedIdx >= 0 && suggestions[selectedIdx]){ catName = suggestions[selectedIdx]; suggestions = []; $event.preventDefault(); }"
                    @click.outside="suggestions = []">

                {{-- Suggestions dropdown --}}
                <div x-show="suggestions.length > 0" x-cloak
                    class="absolute left-0 right-0 top-full z-50 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-40 overflow-y-auto">
                    <div class="px-3 py-1.5 text-xs text-gray-500 font-medium border-b border-gray-100">
                        Similar names already in use:
                    </div>
                    <template x-for="(s, idx) in suggestions" :key="s">
                        <button type="button" @click="catName = s; suggestions = []"
                            :class="idx === selectedIdx ? 'bg-indigo-50' : ''"
                            class="w-full text-left px-3 py-2 text-sm text-gray-800 hover:bg-indigo-50 cursor-pointer"
                            x-text="s"></button>
                    </template>
                </div>
            </div>
            <div class="w-24">
                <label class="block text-xs font-medium text-gray-500 mb-1">Icon / Code</label>
                <input type="text" name="code" maxlength="3" placeholder="US, 🚀…"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <input type="hidden" name="order" value="{{ $countries->count() + 1 }}">
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 font-medium">
                    Create
                </button>
                <button type="button" @click="showAdd = false"
                    class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    {{-- Categories list --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @forelse ($countries as $country)
        <div x-data="{ editing: false }" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 group">

            {{-- Drag handle / order indicator --}}
            <span class="text-xs text-gray-300 font-mono w-5 text-center shrink-0">{{ $country->order }}</span>

            {{-- Icon --}}
            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0 text-base">
                @if($country->code && strlen($country->code) <= 2 && ctype_alpha($country->code))
                    {{ countryFlag($country->code) }}
                @elseif($country->code)
                    <span class="text-sm">{{ $country->code }}</span>
                @else
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                @endif
            </div>

            {{-- Name (view / edit) --}}
            <div class="flex-1 min-w-0">
                <span x-show="!editing" class="text-sm font-medium text-gray-800">{{ $country->name }}</span>

                <form x-show="editing" method="POST" action="{{ route('countries.update', $country) }}" class="flex items-center gap-2 flex-wrap">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $country->name }}"
                        class="border border-indigo-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-40"
                        required>
                    <input type="text" name="code" value="{{ $country->code }}" maxlength="3" placeholder="Code/icon"
                        class="border border-gray-200 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-24">
                    <input type="number" name="order" value="{{ $country->order }}"
                        class="border border-gray-200 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-16">
                    <button type="submit" class="px-3 py-1 bg-indigo-600 text-white text-xs rounded-lg hover:bg-indigo-700">Save</button>
                    <button type="button" @click="editing = false" class="px-3 py-1 text-xs text-gray-500 hover:bg-gray-100 rounded-lg">Cancel</button>
                </form>
            </div>

            {{-- Task count --}}
            <span class="text-xs text-gray-400 shrink-0">{{ $country->tasks()->count() }} tasks</span>

            {{-- Actions --}}
            <div x-show="!editing" class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="editing = true"
                    class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                    title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <form method="POST" action="{{ route('countries.destroy', $country) }}"
                    onsubmit="return confirm('Delete \'{{ addslashes($country->name) }}\'? This cannot be undone if it has tasks.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-4 py-12 text-center">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
            <p class="text-sm text-gray-500">No categories yet.</p>
            <button @click="showAdd = true" class="mt-2 text-sm text-indigo-600 hover:underline">Add your first category</button>
        </div>
        @endforelse
    </div>
</div>
@endsection

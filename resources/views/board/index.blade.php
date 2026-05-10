@extends('layouts.app')

@section('title', 'Kanban Board')

@section('sidebar-countries')
<div style="padding:0 0.75rem 0.75rem; border-top:1px solid #E7E0EC; margin-top:0.5rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 0.25rem 0.375rem;">
        <span style="font-size:0.6875rem; font-weight:600; color:#79747E; letter-spacing:0.05em; text-transform:uppercase;">Statuses</span>
    </div>

    @foreach ($statuses as $status)
    <div class="sidebar-link" style="font-size:0.8125rem; display:flex; align-items:center; gap:0.5rem; cursor:default;">
        <span style="width:10px; height:10px; border-radius:50%; background:{{ $status->color }}; flex-shrink:0;"></span>
        <span style="flex:1; text-align:left; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $status->name }}</span>
        @if($status->is_completed)
            <span style="font-size:0.6rem; background:#D1FAE5; color:#065F46; border-radius:100px; padding:0.0625rem 0.375rem; font-weight:600;">Done</span>
        @endif
    </div>
    @endforeach

    @if($statuses->isEmpty())
    <div style="padding:0.75rem 0.5rem; text-align:center; color:#79747E; font-size:0.8125rem;">
        No statuses yet. Add one below.
    </div>
    @endif

    {{-- Quick add status --}}
    <div x-data="{ adding: false, name: '' }" style="margin-top:0.25rem;">
        <button x-show="!adding" @click="adding = true; $nextTick(() => $refs.statusInput.focus())"
            class="sidebar-link" style="color:#79747E; font-size:0.8125rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add status
        </button>
        <div x-show="adding" x-cloak style="margin-top:0.25rem; background:#F7F2FA; border-radius:12px; padding:0.5rem;">
            <input x-ref="statusInput" x-model="name" type="text" placeholder="Status name…"
                style="width:100%; background:transparent; border:none; outline:none; font-size:0.8125rem; color:#1C1B1F; padding:0.25rem 0.25rem 0.375rem; border-bottom:2px solid #6750A4;"
                @keydown.enter="if(name.trim()){ $dispatch('add-status', name.trim()); name=''; adding=false; }"
                @keydown.escape="adding=false; name=''">
            <div style="display:flex; gap:0.375rem; margin-top:0.5rem;">
                <button @click="if(name.trim()){ $dispatch('add-status', name.trim()); name=''; adding=false; }"
                    style="flex:1; font-size:0.75rem; background:#6750A4; color:#fff; border:none; border-radius:100px; padding:0.375rem; cursor:pointer; font-weight:500;">Add</button>
                <button @click="adding=false; name=''"
                    style="flex:1; font-size:0.75rem; background:transparent; color:#49454F; border:1px solid #CAC4D0; border-radius:100px; padding:0.375rem; cursor:pointer;">Cancel</button>
            </div>
        </div>
    </div>

    <a href="{{ route('statuses.index') }}" class="sidebar-link" style="color:#79747E; font-size:0.8125rem; margin-top:0.125rem; text-decoration:none;">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Manage statuses
    </a>
</div>

<div style="padding:0 0.75rem 0.75rem; border-top:1px solid #E7E0EC; margin-top:0.5rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 0.25rem 0.375rem;">
        <span style="font-size:0.6875rem; font-weight:600; color:#79747E; letter-spacing:0.05em; text-transform:uppercase;">Categories</span>
    </div>

    {{-- All --}}
    <button @click="filterByCountry('')"
        :style="filters.country_id === '' ? 'background:#E8DEF8; color:#21005D;' : ''"
        class="sidebar-link" style="font-weight:500; font-size:0.8125rem;">
        <span style="font-size:1rem;">📋</span>
        <span style="flex:1; text-align:left;">All Categories</span>
        <span style="font-size:0.6875rem; background:#E7E0EC; color:#49454F; border-radius:100px; padding:0.125rem 0.5rem; font-weight:600;">{{ $taskCounts['total'] }}</span>
    </button>

    @foreach ($countries as $country)
    <button @click="filterByCountry('{{ $country->id }}')"
        :style="filters.country_id == '{{ $country->id }}' ? 'background:#E8DEF8; color:#21005D;' : ''"
        class="sidebar-link" style="font-size:0.8125rem;">
        <span style="font-size:1rem; flex-shrink:0;">
            @if($country->code){{ countryFlag($country->code) }}@else🏷️@endif
        </span>
        <span style="flex:1; text-align:left; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $country->name }}</span>
        <span style="font-size:0.6875rem; background:#E7E0EC; color:#49454F; border-radius:100px; padding:0.125rem 0.5rem; font-weight:600; flex-shrink:0;">{{ $taskCounts[$country->id] ?? 0 }}</span>
    </button>
    @endforeach

    {{-- Quick add --}}
    <div x-data="{ adding: false, name: '' }" style="margin-top:0.25rem;">
        <button x-show="!adding" @click="adding = true; $nextTick(() => $refs.catInput.focus())"
            class="sidebar-link" style="color:#79747E; font-size:0.8125rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add category
        </button>
        <div x-show="adding" x-cloak style="margin-top:0.25rem; background:#F7F2FA; border-radius:12px; padding:0.5rem;">
            <input x-ref="catInput" x-model="name" type="text" placeholder="Category name…"
                style="width:100%; background:transparent; border:none; outline:none; font-size:0.8125rem; color:#1C1B1F; padding:0.25rem 0.25rem 0.375rem; border-bottom:2px solid #6750A4;"
                @keydown.enter="if(name.trim()){ $dispatch('add-category', name.trim()); name=''; adding=false; }"
                @keydown.escape="adding=false; name=''">
            <div style="display:flex; gap:0.375rem; margin-top:0.5rem;">
                <button @click="if(name.trim()){ $dispatch('add-category', name.trim()); name=''; adding=false; }"
                    style="flex:1; font-size:0.75rem; background:#6750A4; color:#fff; border:none; border-radius:100px; padding:0.375rem; cursor:pointer; font-weight:500;">Add</button>
                <button @click="adding=false; name=''"
                    style="flex:1; font-size:0.75rem; background:transparent; color:#49454F; border:1px solid #CAC4D0; border-radius:100px; padding:0.375rem; cursor:pointer;">Cancel</button>
            </div>
        </div>
    </div>

    <a href="{{ route('countries.index') }}" class="sidebar-link" style="color:#79747E; font-size:0.8125rem; margin-top:0.125rem; text-decoration:none;">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Manage categories
    </a>
</div>
@endsection

@section('content')
<div x-data="boardApp()" x-init="init()" style="display:flex; flex-direction:column; height:100vh; overflow:hidden;"
    @add-category.window="addCategory($event.detail)"
    @add-status.window="addStatus($event.detail)">
    {{-- MD3 Top App Bar --}}
    <div style="background:#FFFBFE; border-bottom:1px solid #E7E0EC; padding:0.75rem 1.5rem; display:flex; align-items:center; gap:1rem; position:sticky; top:0; z-index:20; box-shadow:0 1px 2px rgba(0,0,0,.08);">
        <h1 style="font-size:1.375rem; font-weight:600; color:#1C1B1F; letter-spacing:-0.01em; margin-right:0.5rem; white-space:nowrap;">Kanban Board</h1>

        {{-- Search (MD3 search bar) --}}
        <div style="position:relative; flex:1; max-width:320px;">
            <svg style="position:absolute; left:0.875rem; top:50%; transform:translateY(-50%); color:#49454F;" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="filters.search" @input.debounce.400ms="applyFilters()"
                placeholder="Search tasks…"
                style="width:100%; padding:0.625rem 1rem 0.625rem 2.75rem; background:#F4EFF4; border:none; border-radius:100px; font-size:0.875rem; color:#1C1B1F; outline:none; transition:background 0.2s;"
                onfocus="this.style.background='#EDE7F6'; this.style.boxShadow='0 0 0 2px #6750A4'"
                onblur="this.style.background='#F4EFF4'; this.style.boxShadow='none'">
        </div>

        {{-- Filter chips --}}
        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:nowrap;">
            <select x-model="filters.priority" @change="applyFilters()"
                style="padding:0.5rem 0.875rem; background:#F4EFF4; border:1px solid #CAC4D0; border-radius:8px; font-size:0.8125rem; color:#49454F; outline:none; cursor:pointer; transition:border-color 0.15s;"
                onfocus="this.style.borderColor='#6750A4'" onblur="this.style.borderColor='#CAC4D0'">
                <option value="">All Priorities</option>
                <option value="high">🔴 High</option>
                <option value="medium">🟡 Medium</option>
                <option value="low">🟢 Low</option>
            </select>

            <select x-model="filters.assigned_to" @change="applyFilters()"
                style="padding:0.5rem 0.875rem; background:#F4EFF4; border:1px solid #CAC4D0; border-radius:8px; font-size:0.8125rem; color:#49454F; outline:none; cursor:pointer; transition:border-color 0.15s;"
                onfocus="this.style.borderColor='#6750A4'" onblur="this.style.borderColor='#CAC4D0'">
                <option value="">All Assignees</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>

            <button @click="clearFilters()"
                x-show="filters.search || filters.priority || filters.country_id || filters.assigned_to"
                style="padding:0.5rem 0.875rem; background:#F9DEDC; color:#B3261E; border:none; border-radius:8px; font-size:0.8125rem; font-weight:500; cursor:pointer; transition:background 0.15s;"
                onmouseover="this.style.background='#F2B8B5'" onmouseout="this.style.background='#F9DEDC'">
                Clear
            </button>
        </div>

        <div style="margin-left:auto; display:flex; align-items:center; gap:0.75rem;">

            {{-- Group By controls --}}
            <div style="display:flex;align-items:center;gap:0.375rem;background:#F4EFF4;border-radius:100px;padding:0.25rem 0.75rem;border:1px solid #CAC4D0;">
                <span style="font-size:0.75rem;color:#79747E;font-weight:500;white-space:nowrap;">Rows:</span>
                <select x-model="groupRow" @change="renderGrouped()"
                    style="background:transparent;border:none;font-size:0.8125rem;color:#1C1B1F;outline:none;cursor:pointer;font-weight:500;">
                    <option value="category">Category</option>
                    <option value="priority">Priority</option>
                    <option value="assignee">Assignee</option>
                    <option value="month">Due Month</option>
                    <option value="none">None</option>
                </select>
            </div>

            <div style="display:flex;align-items:center;gap:0.375rem;background:#F4EFF4;border-radius:100px;padding:0.25rem 0.75rem;border:1px solid #CAC4D0;">
                <span style="font-size:0.75rem;color:#79747E;font-weight:500;white-space:nowrap;">Cols:</span>
                <select x-model="groupCol" @change="renderGrouped()"
                    style="background:transparent;border:none;font-size:0.8125rem;color:#1C1B1F;outline:none;cursor:pointer;font-weight:500;">
                    <option value="status">Status</option>
                    <option value="priority">Priority</option>
                    <option value="month">Due Month</option>
                </select>
            </div>

            <button @click="openCreateModal()" class="md-btn-filled md-ripple">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add Task
            </button>
        </div>
    </div>

    {{-- Board grid: fills remaining height, scrolls both axes --}}
    <div id="board-container" class="flex-1 overflow-auto min-h-0">
        @include('board._grid_dynamic')
    </div>

    {{-- Trash drop zone — floats bottom-right, appears while dragging --}}
    <div id="trash-zone" class="fixed bottom-6 right-6 z-50" data-trash>
        <div id="trash-inner" class="flex flex-col items-center justify-center w-16 h-16 rounded-2xl shadow-lg">
            <svg id="trash-icon" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            <span class="text-xs font-medium mt-0.5" style="color:#f87171">Delete</span>
        </div>
    </div>

    {{-- Task modal --}}
    @include('tasks._form')

    {{-- Right-click context menu --}}
    <div id="ctx-menu" x-show="ctxMenu.open" x-cloak
        class="fixed z-[100] bg-white rounded-xl shadow-2xl border border-gray-100 py-1.5 min-w-[180px] text-sm"
        :style="`top:${ctxMenu.y}px; left:${ctxMenu.x}px`"
        @click.outside="ctxMenu.open = false"
        @keydown.escape.window="ctxMenu.open = false">

        {{-- Task menu --}}
        <template x-if="ctxMenu.type === 'task'">
            <div>
                <div class="px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100 mb-1 truncate"
                    x-text="ctxMenu.data.title"></div>
                <button @click="ctxEditTask()" class="ctx-item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit task
                </button>
                <button @click="ctxRenameTask()" class="ctx-item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Rename
                </button>
                <div class="border-t border-gray-100 my-1"></div>
                <button @click="ctxDeleteTask()" class="ctx-item text-red-600 hover:bg-red-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete task
                </button>
            </div>
        </template>

        {{-- Category menu --}}
        <template x-if="ctxMenu.type === 'country'">
            <div>
                <div class="px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100 mb-1 truncate"
                    x-text="ctxMenu.data.name"></div>
                <button @click="ctxRenameCountry()" class="ctx-item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Rename category
                </button>
                <button @click="moveCountry(ctxMenu.data.id, 'up'); ctxMenu.open = false" class="ctx-item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                    Move up
                </button>
                <button @click="moveCountry(ctxMenu.data.id, 'down'); ctxMenu.open = false" class="ctx-item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    Move down
                </button>
                <button @click="openCreateModalForCell('', ctxMenu.data.id); ctxMenu.open = false" class="ctx-item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add task here
                </button>
                <div class="border-t border-gray-100 my-1"></div>
                <a :href="window.__COUNTRIES_ADMIN_URL__" class="ctx-item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Manage categories
                </a>
            </div>
        </template>

        {{-- Status menu --}}
        <template x-if="ctxMenu.type === 'status'">
            <div>
                <div class="px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100 mb-1 truncate"
                    x-text="ctxMenu.data.name"></div>
                <button @click="ctxRenameStatus()" class="ctx-item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Rename status
                </button>
                <button @click="openCreateModalForCell(ctxMenu.data.id, '')" class="ctx-item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add task here
                </button>
                <div class="border-t border-gray-100 my-1"></div>
                <a :href="window.__STATUSES_ADMIN_URL__" class="ctx-item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Manage statuses
                </a>
            </div>
        </template>
    </div>

    {{-- Inline rename popover --}}
    <div id="rename-popover" x-show="renamePopover.open" x-cloak
        class="fixed z-[101] bg-white rounded-xl shadow-2xl border border-gray-100 p-3 w-64"
        :style="`top:${renamePopover.y}px; left:${renamePopover.x}px`"
        @keydown.escape.window="renamePopover.open = false">
        <p class="text-xs font-semibold text-gray-500 mb-2" x-text="renamePopover.label"></p>
        <input x-ref="renameInput" x-model="renamePopover.value" type="text"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 mb-2"
            @keydown.enter="submitRename()"
            @keydown.escape="renamePopover.open = false">
        <div class="flex gap-2 justify-end">
            <button @click="renamePopover.open = false"
                class="px-3 py-1.5 text-xs text-gray-500 hover:bg-gray-100 rounded-lg">Cancel</button>
            <button @click="submitRename()"
                class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">Save</button>
        </div>
    </div>
</div>

<script>
    window.__CSRF_TOKEN__ = '{{ csrf_token() }}';
    window.__STATUSES__ = @json($statuses->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'color' => $s->color]));
    window.__COUNTRIES__ = @json($countries->map(fn($c) => ['id' => $c->id, 'name' => $c->name]));
    window.__USERS__ = @json($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'avatar' => $u->avatar]));
    window.__BOARD_URL__ = '/board';
    window.__COUNTRIES_ADMIN_URL__ = '{{ route("countries.index") }}';
    window.__STATUSES_ADMIN_URL__ = '{{ route("statuses.index") }}';
</script>
@endsection

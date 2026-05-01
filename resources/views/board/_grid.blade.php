@php
    // Pre-compute per-column task counts for headers
    $columnCounts = [];
    foreach ($statuses as $status) {
        $count = 0;
        foreach ($countries as $country) {
            $count += count($tasks[$country->id][$status->id] ?? []);
        }
        $columnCounts[$status->id] = $count;
    }
@endphp

<div class="min-w-max">
    {{-- Sticky column headers --}}
    <div class="flex sticky top-0 z-20" style="background:#F4EFF4;border-bottom:1px solid #E7E0EC;">
        <div style="width:160px;flex-shrink:0;border-right:1px solid #E7E0EC;display:flex;align-items:center;justify-content:center;padding:0.75rem;">
            <span style="font-size:0.6875rem;font-weight:600;color:#79747E;letter-spacing:0.05em;text-transform:uppercase;">Category</span>
        </div>

        @foreach ($statuses as $status)
        <div style="width:260px;flex-shrink:0;padding:0.875rem 1rem;border-right:1px solid #E7E0EC;background:{{ $status->is_completed ? '#F7F2FA' : 'transparent' }};"
            data-ctx="status" data-id="{{ $status->id }}" data-name="{{ $status->name }}" data-color="{{ $status->color }}"
            @contextmenu.prevent="openContextMenu($event, 'status', { id: '{{ $status->id }}', name: '{{ addslashes($status->name) }}', color: '{{ $status->color }}' })">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $status->color }};flex-shrink:0;"></span>
                    <span style="font-size:0.875rem;font-weight:600;color:#1C1B1F;">{{ $status->name }}</span>
                    <span style="font-size:0.6875rem;font-weight:600;background:#E8DEF8;color:#6750A4;border-radius:100px;padding:0.125rem 0.5rem;">{{ $columnCounts[$status->id] }}</span>
                </div>
                <button @click="openCreateModalForCell('{{ $status->id }}', '')"
                    style="width:28px;height:28px;border-radius:50%;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#79747E;transition:background 0.15s;"
                    onmouseover="this.style.background='#E8DEF8';this.style.color='#6750A4'" onmouseout="this.style.background='transparent';this.style.color='#79747E'"
                    title="Add task">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Category rows --}}
    @foreach ($countries as $country)
    <div style="display:flex;border-bottom:1px solid #E7E0EC;">
        {{-- Category label --}}
        <div style="width:160px;flex-shrink:0;border-right:1px solid #E7E0EC;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:0.875rem 0.5rem 0.75rem;background:#FFFBFE;cursor:context-menu;position:relative;"
            class="group/country"
            data-ctx="country" data-id="{{ $country->id }}" data-name="{{ $country->name }}"
            @contextmenu.prevent="openContextMenu($event, 'country', { id: '{{ $country->id }}', name: '{{ addslashes($country->name) }}', code: '{{ $country->code }}' })">

            {{-- Reorder arrows --}}
            <div style="display:flex;gap:0.25rem;margin-bottom:0.375rem;opacity:0;transition:opacity 0.15s;" class="group-hover/country:opacity-100">
                <button @click.stop="moveCountry('{{ $country->id }}', 'up')"
                    style="width:22px;height:22px;border-radius:50%;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#79747E;transition:background 0.15s;"
                    onmouseover="this.style.background='#E8DEF8';this.style.color='#6750A4'" onmouseout="this.style.background='transparent';this.style.color='#79747E'">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                </button>
                <button @click.stop="moveCountry('{{ $country->id }}', 'down')"
                    style="width:22px;height:22px;border-radius:50%;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#79747E;transition:background 0.15s;"
                    onmouseover="this.style.background='#E8DEF8';this.style.color='#6750A4'" onmouseout="this.style.background='transparent';this.style.color='#79747E'">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            @if($country->code && strlen($country->code) === 2)
                <div style="font-size:1.75rem;margin-bottom:0.25rem;">{{ countryFlag($country->code) }}</div>
            @else
                <div style="width:36px;height:36px;border-radius:12px;background:#E8DEF8;display:flex;align-items:center;justify-content:center;margin-bottom:0.375rem;">
                    <svg width="18" height="18" fill="none" stroke="#6750A4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            @endif
            <span style="font-size:0.75rem;font-weight:600;color:#1C1B1F;text-align:center;line-height:1.3;padding:0 0.25rem;">{{ $country->name }}</span>
        </div>

        {{-- Status cells --}}
        @foreach ($statuses as $status)
        <div style="width:260px;flex-shrink:0;padding:0.5rem;border-right:1px solid #E7E0EC;min-height:140px;background:{{ $status->is_completed ? '#F7F2FA' : '#FAFAFA' }};"
            data-cell data-status-id="{{ $status->id }}" data-country-id="{{ $country->id }}">

            @foreach ($tasks[$country->id][$status->id] ?? [] as $task)
                @include('board._task_card', ['task' => $task, 'isCompleted' => $status->is_completed])
            @endforeach

            {{-- Quick-add --}}
            <div x-data="{ open: false, title: '' }" style="margin-top:0.25rem;">
                <button x-show="!open"
                    @click.stop="open = true; $nextTick(() => $refs.quickInput.focus())"
                    style="width:100%;display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.625rem;border:none;background:transparent;cursor:pointer;border-radius:8px;font-size:0.8125rem;color:#79747E;transition:background 0.15s;"
                    onmouseover="this.style.background='#E8DEF8';this.style.color='#6750A4'" onmouseout="this.style.background='transparent';this.style.color='#79747E'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Add task
                </button>

                <div x-show="open" x-cloak style="background:#FFFBFE;border-radius:12px;border:2px solid #6750A4;padding:0.625rem;box-shadow:0 1px 2px rgba(0,0,0,.12),0 2px 6px 2px rgba(0,0,0,.08);">
                    <input x-ref="quickInput" x-model="title" type="text" placeholder="Task name…"
                        style="width:100%;background:transparent;border:none;border-bottom:1px solid #CAC4D0;outline:none;font-size:0.875rem;color:#1C1B1F;padding:0.25rem 0 0.375rem;font-family:inherit;"
                        @keydown.enter.prevent="if(title.trim()){ quickAddTask('{{ $status->id }}', '{{ $country->id }}', title); title=''; open=false; }"
                        @keydown.escape="open=false; title=''" @click.stop>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:0.5rem;">
                        <div style="display:flex;gap:0.375rem;">
                            <button type="button"
                                @click.stop="if(title.trim()){ quickAddTask('{{ $status->id }}', '{{ $country->id }}', title); title=''; open=false; }"
                                style="padding:0.375rem 0.875rem;background:#6750A4;color:#fff;border:none;border-radius:100px;font-size:0.75rem;font-weight:500;cursor:pointer;">Add</button>
                            <button type="button" @click.stop="open=false; title=''"
                                style="padding:0.375rem 0.875rem;background:transparent;color:#49454F;border:1px solid #CAC4D0;border-radius:100px;font-size:0.75rem;cursor:pointer;">Cancel</button>
                        </div>
                        <button type="button"
                            @click.stop="open=false; openCreateModalForCell('{{ $status->id }}', '{{ $country->id }}')"
                            style="font-size:0.75rem;color:#79747E;background:transparent;border:none;cursor:pointer;display:flex;align-items:center;gap:0.25rem;"
                            onmouseover="this.style.color='#6750A4'" onmouseout="this.style.color='#79747E'">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>

@php
    // Column task counts
    $colCounts = [];
    foreach ($colGroups as $cKey => $cMeta) {
        $count = 0;
        foreach ($rowGroups as $rKey => $rMeta) {
            $count += count($matrix[$rKey][$cKey] ?? []);
        }
        $colCounts[$cKey] = $count;
    }
@endphp

<div style="min-width:max-content;">

    {{-- Sticky column headers --}}
    <div style="display:flex;position:sticky;top:0;z-index:20;background:#F4EFF4;border-bottom:1px solid #E7E0EC;">

        {{-- Row label header --}}
        <div style="width:160px;flex-shrink:0;border-right:1px solid #E7E0EC;display:flex;align-items:center;justify-content:center;padding:0.75rem;">
            <span style="font-size:0.6875rem;font-weight:600;color:#79747E;letter-spacing:0.05em;text-transform:uppercase;">
                {{ $rowLabel ?: 'Group' }}
            </span>
        </div>

        {{-- Column headers --}}
        @foreach ($colGroups as $cKey => $cMeta)
        <div style="width:260px;flex-shrink:0;padding:0.875rem 1rem;border-right:1px solid #E7E0EC;background:{{ $cMeta['is_completed'] ? '#F7F2FA' : 'transparent' }};"
            @if($groupCol === 'status' && isset($cMeta['meta']))
            data-ctx="status"
            data-id="{{ $cMeta['meta']->id }}"
            data-name="{{ $cMeta['meta']->name }}"
            data-color="{{ $cMeta['meta']->color }}"
            @contextmenu.prevent="openContextMenu($event, 'status', { id: '{{ $cMeta['meta']->id }}', name: '{{ addslashes($cMeta['meta']->name) }}', color: '{{ $cMeta['meta']->color }}' })"
            @endif>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    @if($cMeta['icon'])
                        <span style="font-size:0.875rem;">{{ $cMeta['icon'] }}</span>
                    @else
                        <span style="width:10px;height:10px;border-radius:50%;background:{{ $cMeta['color'] }};flex-shrink:0;"></span>
                    @endif
                    <span style="font-size:0.875rem;font-weight:600;color:#1C1B1F;">{{ $cMeta['label'] }}</span>
                    <span style="font-size:0.6875rem;font-weight:600;background:#E8DEF8;color:#6750A4;border-radius:100px;padding:0.125rem 0.5rem;">{{ $colCounts[$cKey] }}</span>
                </div>
                @if($groupCol === 'status' && isset($cMeta['meta']))
                <button @click="openCreateModalForCell('{{ $cMeta['meta']->id }}', '')"
                    style="width:28px;height:28px;border-radius:50%;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#79747E;transition:background 0.15s;"
                    onmouseover="this.style.background='#E8DEF8';this.style.color='#6750A4'" onmouseout="this.style.background='transparent';this.style.color='#79747E'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Row groups --}}
    @foreach ($rowGroups as $rKey => $rMeta)
    <div style="display:flex;border-bottom:1px solid #E7E0EC;">

        {{-- Row label --}}
        <div style="width:160px;flex-shrink:0;border-right:1px solid #E7E0EC;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:0.875rem 0.5rem 0.75rem;background:#FFFBFE;cursor:context-menu;position:relative;"
            class="group/row"
            @if($groupRow === 'category' && isset($rMeta['meta']))
            data-ctx="country"
            data-id="{{ $rMeta['meta']->id }}"
            data-name="{{ $rMeta['meta']->name }}"
            @contextmenu.prevent="openContextMenu($event, 'country', { id: '{{ $rMeta['meta']->id }}', name: '{{ addslashes($rMeta['meta']->name) }}', code: '{{ $rMeta['meta']->code ?? '' }}' })"
            @endif>

            {{-- Reorder arrows for category rows --}}
            @if($groupRow === 'category' && isset($rMeta['meta']))
            <div style="display:flex;gap:0.25rem;margin-bottom:0.375rem;opacity:0;transition:opacity 0.15s;" class="group-hover/row:opacity-100">
                <button @click.stop="moveCountry('{{ $rMeta['meta']->id }}', 'up')"
                    style="width:22px;height:22px;border-radius:50%;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#79747E;transition:background 0.15s;"
                    onmouseover="this.style.background='#E8DEF8';this.style.color='#6750A4'" onmouseout="this.style.background='transparent';this.style.color='#79747E'">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                </button>
                <button @click.stop="moveCountry('{{ $rMeta['meta']->id }}', 'down')"
                    style="width:22px;height:22px;border-radius:50%;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#79747E;transition:background 0.15s;"
                    onmouseover="this.style.background='#E8DEF8';this.style.color='#6750A4'" onmouseout="this.style.background='transparent';this.style.color='#79747E'">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
            @endif

            {{-- Icon --}}
            @if($rMeta['icon'])
                <div style="font-size:1.5rem;margin-bottom:0.25rem;">{{ $rMeta['icon'] }}</div>
            @elseif(isset($rMeta['avatar']) && $rMeta['avatar'])
                <img src="{{ $rMeta['avatar'] }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;margin-bottom:0.375rem;">
            @else
                <div style="width:32px;height:32px;border-radius:50%;background:#E8DEF8;color:#6750A4;font-size:0.875rem;font-weight:700;display:flex;align-items:center;justify-content:center;margin-bottom:0.375rem;">
                    {{ $rMeta['icon'] ?? strtoupper(substr($rMeta['label'], 0, 1)) }}
                </div>
            @endif

            <span style="font-size:0.75rem;font-weight:600;color:#1C1B1F;text-align:center;line-height:1.3;padding:0 0.25rem;">{{ $rMeta['label'] }}</span>
        </div>

        {{-- Cells --}}
        @foreach ($colGroups as $cKey => $cMeta)
        @php
            $cellTasks = $matrix[$rKey][$cKey] ?? collect();
            $isCompleted = $cMeta['is_completed'] ?? false;
            // For drag-drop we need status_id and country_id
            $dndStatusId  = ($groupCol === 'status' && isset($cMeta['meta'])) ? $cMeta['meta']->id : null;
            $dndCountryId = ($groupRow === 'category' && isset($rMeta['meta'])) ? $rMeta['meta']->id : null;
        @endphp
        <div style="width:260px;flex-shrink:0;padding:0.5rem;border-right:1px solid #E7E0EC;min-height:140px;background:{{ $isCompleted ? '#F7F2FA' : '#FAFAFA' }};"
            @if($dndStatusId && $dndCountryId)
            data-cell
            data-status-id="{{ $dndStatusId }}"
            data-country-id="{{ $dndCountryId }}"
            @endif>

            @foreach ($cellTasks as $task)
                @include('board._task_card', ['task' => $task, 'isCompleted' => $isCompleted])
            @endforeach

            {{-- Quick-add (only when grouping allows it) --}}
            @if($dndStatusId && $dndCountryId)
            <div x-data="{ open: false, title: '' }" style="margin-top:0.25rem;">
                <button x-show="!open"
                    @click.stop="open = true; $nextTick(() => $refs.quickInput.focus())"
                    style="width:100%;display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.625rem;border:none;background:transparent;cursor:pointer;border-radius:8px;font-size:0.8125rem;color:#79747E;transition:background 0.15s;"
                    onmouseover="this.style.background='#E8DEF8';this.style.color='#6750A4'" onmouseout="this.style.background='transparent';this.style.color='#79747E'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Add task
                </button>
                <div x-show="open" x-cloak style="background:#FFFBFE;border-radius:12px;border:2px solid #6750A4;padding:0.625rem;">
                    <input x-ref="quickInput" x-model="title" type="text" placeholder="Task name…"
                        style="width:100%;background:transparent;border:none;border-bottom:1px solid #CAC4D0;outline:none;font-size:0.875rem;color:#1C1B1F;padding:0.25rem 0 0.375rem;font-family:inherit;"
                        @keydown.enter.prevent="if(title.trim()){ quickAddTask('{{ $dndStatusId }}', '{{ $dndCountryId }}', title); title=''; open=false; }"
                        @keydown.escape="open=false; title=''" @click.stop>
                    <div style="display:flex;gap:0.375rem;margin-top:0.5rem;">
                        <button type="button"
                            @click.stop="if(title.trim()){ quickAddTask('{{ $dndStatusId }}', '{{ $dndCountryId }}', title); title=''; open=false; }"
                            style="padding:0.375rem 0.875rem;background:#6750A4;color:#fff;border:none;border-radius:100px;font-size:0.75rem;font-weight:500;cursor:pointer;">Add</button>
                        <button type="button" @click.stop="open=false; title=''"
                            style="padding:0.375rem 0.875rem;background:transparent;color:#49454F;border:1px solid #CAC4D0;border-radius:100px;font-size:0.75rem;cursor:pointer;">Cancel</button>
                        <button type="button"
                            @click.stop="open=false; openCreateModalForCell('{{ $dndStatusId }}', '{{ $dndCountryId }}')"
                            style="margin-left:auto;font-size:0.75rem;color:#79747E;background:transparent;border:none;cursor:pointer;"
                            onmouseover="this.style.color='#6750A4'" onmouseout="this.style.color='#79747E'">Details</button>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endforeach
</div>

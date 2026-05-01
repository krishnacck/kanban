@php
    $priorities = ['low', 'medium', 'high'];
    $priorityIdx = array_search($task->priority, $priorities);
    $canGoUp   = $priorityIdx < 2;
    $canGoDown = $priorityIdx > 0;

    $pc = match($task->priority) {
        'high'   => ['bg' => '#FEE2E2', 'color' => '#DC2626', 'dot' => '#DC2626', 'label' => 'High'],
        'medium' => ['bg' => '#FEF9C3', 'color' => '#CA8A04', 'dot' => '#CA8A04', 'label' => 'Medium'],
        default  => ['bg' => '#DCFCE7', 'color' => '#16A34A', 'dot' => '#16A34A', 'label' => 'Low'],
    };

    $done = $isCompleted ?? false;

    $taskJson = json_encode([
        'title'       => $task->title,
        'description' => $task->description,
        'priority'    => $task->priority,
        'status_id'   => $task->status_id,
        'country_id'  => $task->country_id,
        'assigned_to' => $task->assigned_to,
        'due_date'    => $task->due_date?->format('Y-m-d'),
    ]);
    $ctxJson = json_encode([
        'id'          => $task->id,
        'title'       => $task->title,
        'description' => $task->description,
        'priority'    => $task->priority,
        'status_id'   => $task->status_id,
        'country_id'  => $task->country_id,
        'assigned_to' => $task->assigned_to,
        'due_date'    => $task->due_date?->format('Y-m-d'),
    ]);
@endphp

<div class="task-card group/card"
    data-task-id="{{ $task->id }}"
    data-status-id="{{ $task->status_id }}"
    data-country-id="{{ $task->country_id }}"
    data-priority="{{ $task->priority }}"
    @contextmenu.prevent="openContextMenu($event,'task',{{ $ctxJson }})"
    @click.stop="openEditModal({{ $task->id }},{{ $taskJson }})"
    style="
        background: {{ $done ? '#F9FAFB' : '#FFFFFF' }};
        border: 1px solid {{ $done ? '#E5E7EB' : '#E5E7EB' }};
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 6px;
        cursor: grab;
        opacity: {{ $done ? '0.6' : '1' }};
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        transition: box-shadow 0.15s, border-color 0.15s;
    "
    onmouseover="{{ $done ? '' : "this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';this.style.borderColor='#C4B5FD';" }}"
    onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,0.06)';this.style.borderColor='#E5E7EB';">

    {{-- Row 1: complete circle + title --}}
    <div style="display:flex;align-items:flex-start;gap:8px;margin-bottom:8px;">

        {{-- Complete button --}}
        <button @click.stop="completeTask({{ $task->id }})"
            title="{{ $done ? 'Mark incomplete' : 'Mark complete' }}"
            style="
                flex-shrink:0;
                margin-top:2px;
                width:16px; height:16px;
                border-radius:50%;
                border: 2px solid {{ $done ? '#16A34A' : '#9CA3AF' }};
                background: {{ $done ? '#16A34A' : 'transparent' }};
                display:flex; align-items:center; justify-content:center;
                cursor:pointer; padding:0;
                transition: border-color 0.15s, background 0.15s;
            "
            onmouseover="{{ $done ? '' : "this.style.borderColor='#16A34A';this.style.background='#DCFCE7';" }}"
            onmouseout="{{ $done ? '' : "this.style.borderColor='#9CA3AF';this.style.background='transparent';" }}">
            <svg width="9" height="9" fill="none" stroke="{{ $done ? '#fff' : 'transparent' }}" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/>
            </svg>
        </button>

        {{-- Title --}}
        <p style="
            flex:1; min-width:0;
            font-size:13px; font-weight:500; line-height:1.4;
            color: {{ $done ? '#9CA3AF' : '#111827' }};
            text-decoration: {{ $done ? 'line-through' : 'none' }};
            display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
        ">{{ $task->title }}</p>
    </div>

    {{-- Row 2: priority arrows + chip | meta --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;">

        @if(!$done)
        {{-- Priority control group --}}
        <div style="display:flex;align-items:center;gap:4px;">

            {{-- ↑ Increase priority --}}
            <button @click.stop="changePriority({{ $task->id }},'up')"
                title="{{ $canGoUp ? 'Increase priority' : 'Already highest priority' }}"
                {{ !$canGoUp ? 'disabled' : '' }}
                style="
                    width:22px; height:22px;
                    border-radius:6px;
                    border: 1px solid {{ $canGoUp ? '#A78BFA' : '#E5E7EB' }};
                    background: {{ $canGoUp ? '#EDE9FE' : '#F9FAFB' }};
                    color: {{ $canGoUp ? '#7C3AED' : '#D1D5DB' }};
                    display:flex; align-items:center; justify-content:center;
                    cursor: {{ $canGoUp ? 'pointer' : 'not-allowed' }};
                    padding:0; flex-shrink:0;
                    transition: background 0.1s, border-color 0.1s;
                "
                onmouseover="{{ $canGoUp ? "this.style.background='#DDD6FE';this.style.borderColor='#7C3AED';" : '' }}"
                onmouseout="{{ $canGoUp ? "this.style.background='#EDE9FE';this.style.borderColor='#A78BFA';" : '' }}">
                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/>
                </svg>
            </button>

            {{-- Priority chip --}}
            <span style="
                display:inline-flex; align-items:center; gap:4px;
                font-size:11px; font-weight:600;
                background: {{ $pc['bg'] }};
                color: {{ $pc['color'] }};
                border-radius:100px;
                padding:2px 8px;
                white-space:nowrap;
                user-select:none;
            ">
                <span style="width:6px;height:6px;border-radius:50%;background:{{ $pc['dot'] }};flex-shrink:0;display:inline-block;"></span>
                {{ $pc['label'] }}
            </span>

            {{-- ↓ Decrease priority --}}
            <button @click.stop="changePriority({{ $task->id }},'down')"
                title="{{ $canGoDown ? 'Decrease priority' : 'Already lowest priority' }}"
                {{ !$canGoDown ? 'disabled' : '' }}
                style="
                    width:22px; height:22px;
                    border-radius:6px;
                    border: 1px solid {{ $canGoDown ? '#A78BFA' : '#E5E7EB' }};
                    background: {{ $canGoDown ? '#EDE9FE' : '#F9FAFB' }};
                    color: {{ $canGoDown ? '#7C3AED' : '#D1D5DB' }};
                    display:flex; align-items:center; justify-content:center;
                    cursor: {{ $canGoDown ? 'pointer' : 'not-allowed' }};
                    padding:0; flex-shrink:0;
                    transition: background 0.1s, border-color 0.1s;
                "
                onmouseover="{{ $canGoDown ? "this.style.background='#DDD6FE';this.style.borderColor='#7C3AED';" : '' }}"
                onmouseout="{{ $canGoDown ? "this.style.background='#EDE9FE';this.style.borderColor='#A78BFA';" : '' }}">
                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
        @else
        <span style="font-size:11px;color:#9CA3AF;display:flex;align-items:center;gap:3px;">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            Done
        </span>
        @endif

        {{-- Due date + assignee --}}
        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
            @if($task->due_date)
            <span style="font-size:11px;color:#6B7280;">{{ $task->due_date->format('M j') }}</span>
            @endif
            @if($task->assignee)
                @if($task->assignee->avatar)
                <img src="{{ $task->assignee->avatar }}" alt="{{ $task->assignee->name }}"
                    style="width:20px;height:20px;border-radius:50%;object-fit:cover;{{ $done ? 'filter:grayscale(1);opacity:0.5;' : '' }}"
                    title="{{ $task->assignee->name }}">
                @else
                <div style="width:20px;height:20px;border-radius:50%;background:{{ $done ? '#E5E7EB' : '#EDE9FE' }};color:{{ $done ? '#9CA3AF' : '#7C3AED' }};font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;"
                    title="{{ $task->assignee->name }}">
                    {{ strtoupper(substr($task->assignee->name,0,1)) }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

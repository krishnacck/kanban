<!DOCTYPE html>
<html lang="en" x-data>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KanbanPro')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#F4EFF4; height:100vh; display:flex; overflow:hidden; font-family:'Google Sans',Roboto,sans-serif;">

    {{-- MD3 Navigation Drawer (sidebar) --}}
    <aside style="width:260px; flex-shrink:0; background:#FFFBFE; display:flex; flex-direction:column; height:100vh; overflow-y:auto; border-right:1px solid #E7E0EC; position:sticky; top:0; z-index:30;">

        {{-- App logo / headline --}}
        <div style="padding:1.25rem 1.25rem 1rem; border-bottom:1px solid #E7E0EC;">
            <a href="/board" style="display:flex; align-items:center; gap:0.75rem; text-decoration:none;">
                <div style="width:40px; height:40px; background:#6750A4; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="22" height="22" fill="none" stroke="#fff" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:1rem; font-weight:700; color:#1C1B1F; letter-spacing:-0.01em;">KanbanPro</div>
                    <div style="font-size:0.7rem; color:#79747E; margin-top:1px;">Task Management</div>
                </div>
            </a>
        </div>

        {{-- Navigation items --}}
        <nav style="padding:0.75rem 0.75rem 0; flex:1;">
            <div style="font-size:0.6875rem; font-weight:600; color:#79747E; letter-spacing:0.05em; text-transform:uppercase; padding:0.5rem 1rem 0.25rem;">Workspace</div>

            <a href="/board" class="sidebar-link {{ request()->is('board*') ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Board
            </a>

            @auth
            <a href="{{ route('countries.index') }}" class="sidebar-link {{ request()->is('countries*') ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Categories
            </a>

            @if(auth()->user()->isAdmin())
            <div style="font-size:0.6875rem; font-weight:600; color:#79747E; letter-spacing:0.05em; text-transform:uppercase; padding:1rem 1rem 0.25rem; margin-top:0.5rem;">Admin</div>
            @endif

            <a href="{{ route('statuses.index') }}" class="sidebar-link {{ request()->is('statuses*') ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Statuses
            </a>
            @endauth
        </nav>

        {{-- Sidebar slot (categories list) --}}
        @yield('sidebar-countries')

        {{-- User profile --}}
        @auth
        <div style="padding:0.75rem; border-top:1px solid #E7E0EC; margin-top:auto;">
            <div style="display:flex; align-items:center; gap:0.75rem; padding:0.5rem 0.75rem; border-radius:12px; background:#F7F2FA;">
                @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" style="width:36px; height:36px; border-radius:50%; object-fit:cover; flex-shrink:0;">
                @else
                    <div style="width:36px; height:36px; border-radius:50%; background:#6750A4; color:#fff; font-size:0.875rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div style="flex:1; min-width:0;">
                    <div style="font-size:0.875rem; font-weight:600; color:#1C1B1F; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                    <div style="font-size:0.7rem; color:#79747E; text-transform:capitalize;">{{ auth()->user()->role }}</div>
                </div>
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" title="Sign out" style="width:32px; height:32px; border-radius:50%; border:none; background:transparent; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#79747E; transition:background 0.15s;" onmouseover="this.style.background='#F9DEDC'; this.style.color='#B3261E'" onmouseout="this.style.background='transparent'; this.style.color='#79747E'">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </aside>

    {{-- Main content --}}
    <div style="flex:1; display:flex; flex-direction:column; min-width:0; height:100vh; overflow:hidden;">
        @yield('content')
    </div>

</body>
</html>

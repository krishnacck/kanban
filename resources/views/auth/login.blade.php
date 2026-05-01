<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — KanbanPro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="min-height:100vh;background:linear-gradient(135deg,#F4EFF4 0%,#EDE7F6 100%);display:flex;align-items:center;justify-content:center;padding:1rem;font-family:'Google Sans',Roboto,sans-serif;">

    <div style="width:100%;max-width:400px;">

        {{-- Logo --}}
        <div style="text-align:center;margin-bottom:2rem;">
            <div style="width:56px;height:56px;background:#6750A4;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 0.875rem;">
                <svg width="30" height="30" fill="none" stroke="#fff" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
            </div>
            <h1 style="font-size:1.75rem;font-weight:700;color:#1C1B1F;letter-spacing:-0.02em;">KanbanPro</h1>
            <p style="font-size:0.875rem;color:#49454F;margin-top:0.25rem;">Sign in to your workspace</p>
        </div>

        {{-- Card --}}
        <div style="background:#FFFBFE;border-radius:28px;padding:2rem;box-shadow:0 4px 8px 3px rgba(0,0,0,.10),0 1px 3px rgba(0,0,0,.12);">

            @if ($errors->any())
            <div style="background:#F9DEDC;border-radius:12px;padding:0.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:0.5rem;">
                <svg width="18" height="18" fill="none" stroke="#B3261E" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    @foreach ($errors->all() as $error)
                    <p style="font-size:0.875rem;color:#B3261E;">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            <form method="POST" action="/login">
                @csrf
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#49454F;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.375rem;">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        style="width:100%;background:#F4EFF4;border:none;border-bottom:2px solid #79747E;border-radius:4px 4px 0 0;padding:0.875rem 1rem;font-size:1rem;color:#1C1B1F;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                        onfocus="this.style.borderBottomColor='#6750A4'" onblur="this.style.borderBottomColor='#79747E'">
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#49454F;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.375rem;">Password</label>
                    <input type="password" name="password" required autocomplete="current-password"
                        style="width:100%;background:#F4EFF4;border:none;border-bottom:2px solid #79747E;border-radius:4px 4px 0 0;padding:0.875rem 1rem;font-size:1rem;color:#1C1B1F;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                        onfocus="this.style.borderBottomColor='#6750A4'" onblur="this.style.borderBottomColor='#79747E'">
                </div>
                <button type="submit" class="md-btn-filled md-ripple" style="width:100%;justify-content:center;padding:0.875rem;font-size:1rem;">
                    Sign In
                </button>
            </form>

            <div style="display:flex;align-items:center;gap:0.75rem;margin:1.25rem 0;">
                <div style="flex:1;height:1px;background:#E7E0EC;"></div>
                <span style="font-size:0.8125rem;color:#79747E;">or</span>
                <div style="flex:1;height:1px;background:#E7E0EC;"></div>
            </div>

            <a href="/auth/google"
                style="display:flex;align-items:center;justify-content:center;gap:0.75rem;width:100%;padding:0.875rem;border:1px solid #79747E;border-radius:100px;font-size:0.9375rem;font-weight:500;color:#1C1B1F;text-decoration:none;transition:background 0.15s,border-color 0.15s;box-sizing:border-box;"
                onmouseover="this.style.background='#F4EFF4';this.style.borderColor='#6750A4'" onmouseout="this.style.background='transparent';this.style.borderColor='#79747E'">
                <svg width="20" height="20" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continue with Google
            </a>

            <p style="text-align:center;font-size:0.875rem;color:#49454F;margin-top:1.25rem;">
                Don't have an account?
                <a href="/register" style="color:#6750A4;font-weight:600;text-decoration:none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Register</a>
            </p>
        </div>
    </div>
</body>
</html>

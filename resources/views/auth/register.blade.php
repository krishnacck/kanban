<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — KanbanPro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="min-height:100vh;background:linear-gradient(135deg,#F4EFF4 0%,#EDE7F6 100%);display:flex;align-items:center;justify-content:center;padding:1rem;font-family:'Google Sans',Roboto,sans-serif;">

    <div style="width:100%;max-width:400px;">
        <div style="text-align:center;margin-bottom:2rem;">
            <div style="width:56px;height:56px;background:#6750A4;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 0.875rem;">
                <svg width="30" height="30" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
            </div>
            <h1 style="font-size:1.75rem;font-weight:700;color:#1C1B1F;letter-spacing:-0.02em;">Create Account</h1>
            <p style="font-size:0.875rem;color:#49454F;margin-top:0.25rem;">Join your team on KanbanPro</p>
        </div>

        <div style="background:#FFFBFE;border-radius:28px;padding:2rem;box-shadow:0 4px 8px 3px rgba(0,0,0,.10),0 1px 3px rgba(0,0,0,.12);">
            @if ($errors->any())
            <div style="background:#F9DEDC;border-radius:12px;padding:0.75rem 1rem;margin-bottom:1.25rem;">
                @foreach ($errors->all() as $error)
                <p style="font-size:0.875rem;color:#B3261E;">{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="/register">
                @csrf
                @foreach([['name','Name','text'],['email','Email','email'],['password','Password','password'],['password_confirmation','Confirm Password','password']] as [$field,$label,$type])
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#49454F;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.375rem;">{{ $label }}</label>
                    <input type="{{ $type }}" name="{{ $field }}" {{ $type !== 'password' ? 'value="'.old($field).'"' : '' }} required
                        style="width:100%;background:#F4EFF4;border:none;border-bottom:2px solid #79747E;border-radius:4px 4px 0 0;padding:0.875rem 1rem;font-size:1rem;color:#1C1B1F;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                        onfocus="this.style.borderBottomColor='#6750A4'" onblur="this.style.borderBottomColor='#79747E'">
                </div>
                @endforeach
                <button type="submit" class="md-btn-filled md-ripple" style="width:100%;justify-content:center;padding:0.875rem;font-size:1rem;margin-top:0.25rem;">
                    Create Account
                </button>
            </form>

            <p style="text-align:center;font-size:0.875rem;color:#49454F;margin-top:1.25rem;">
                Already have an account?
                <a href="/login" style="color:#6750A4;font-weight:600;text-decoration:none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>

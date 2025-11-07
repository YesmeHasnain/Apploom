<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Create account • AppLoom</title>
<style>
/* same inline theme as login */
.app-auth{
  --bg:#f7fbff; --surface:#ffffff; --text:#101826; --muted:#667389; --border:#e6edf5;
  --ring:rgba(11,208,250,.35); --brand:#0bd0fa; --brand-2:#08c8ef; --shadow:0 18px 50px rgba(15,30,45,.12);
  font-family: Inter, ui-sans-serif, -apple-system, "Segoe UI", Roboto, Arial;
}
.app-auth body{ margin:0; background:var(--bg); color:var(--text) }
.app-auth .wrap{ min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px }
.app-auth .card{ width:min(420px,100%); background:var(--surface); border:1px solid var(--border);
  border-radius:18px; box-shadow:var(--shadow); padding:22px 22px 18px; }

.app-auth .top{ display:flex; align-items:center; gap:.55rem; margin-bottom:4px }
.app-auth .badge{ width:24px;height:24px;border-radius:7px;display:grid;place-items:center;
  background:linear-gradient(135deg,#e7fbff,#d2f6ff); border:1px solid #def2fa }
.app-auth .title{ font-weight:600; font-size:18px; letter-spacing:.1px }
.app-auth .sub{ color:var(--muted); font-size:.92rem; margin:2px 0 14px }

.app-auth .group{ margin-bottom:10px }
.app-auth label{ display:block; font-size:.9rem; font-weight:550; margin:0 0 6px }
.app-auth .input{ position:relative; display:flex; align-items:center; height:46px; border-radius:12px;
  border:1px solid var(--border); background:#fbfeff; overflow:hidden; }
.app-auth .input input{ flex:1; height:100%; border:none; outline:none; background:transparent; padding:0 12px 0 40px; font-size:.98rem }
.app-auth .input .ico{ position:absolute; left:12px; width:18px; height:18px; opacity:.6 }
.app-auth .input:focus-within{ box-shadow:0 0 0 4px var(--ring); border-color:var(--brand) }

/* checkbox */
.app-auth .check{ display:flex; align-items:center; gap:.55rem; font-weight:520; color:#2b394a; user-select:none }
.app-auth .check input{ appearance:none; width:18px; height:18px; margin:0; border:1.5px solid var(--border);
  border-radius:6px; display:grid; place-items:center; outline:none; }
.app-auth .check input:checked{ border-color:var(--brand); background:var(--brand) }
.app-auth .check input:checked::after{ content:""; width:10px; height:10px; background:#fff; border-radius:3px }

/* buttons */
.app-auth .btn{ width:100%; height:46px; border-radius:12px; border:1px solid var(--border); background:#fff;
  display:flex; align-items:center; justify-content:center; gap:.6rem; color:#0f172a; text-decoration:none; cursor:pointer; font-weight:550 }
.app-auth .btn:hover{ background:#f7fbff }
.app-auth .primary{ border:none; color:#fff; background:linear-gradient(90deg,var(--brand),var(--brand-2));
  box-shadow:0 10px 22px rgba(11,208,250,.24); font-weight:600 }
.app-auth .ico-btn{ width:18px;height:18px }

.app-auth .hr{ display:flex; align-items:center; gap:.75rem; color:#8da0b0; margin:12px 0 10px; font-weight:600; font-size:.9rem }
.app-auth .hr:before,.app-auth .hr:after{ content:""; height:1px; background:var(--border); flex:1 }
.app-auth .muted{ color:#7d8da1; font-size:.92rem }
.app-auth .err{ color:#e11d48; font-size:.84rem; margin-top:.3rem }
</style>
</head>
<body class="app-auth">
  <div class="wrap">
    <div class="card">
      <div class="top">
        <div class="badge">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none"><path d="M12 3l7 4v5c0 4.7-3.3 7.6-7 9.8C8.3 19.6 5 16.7 5 12V7l7-4Z" stroke="#0bd0fa" stroke-width="1.5" fill="#fff"/></svg>
        </div>
        <div class="title">Create account</div>
      </div>
      <p class="sub">Sign up with email, or continue with a provider below.</p>

      {{-- Registration form --}}
      <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="group">
          <label>Name</label>
          <div class="input">
            <svg class="ico" viewBox="0 0 24 24" fill="none"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="#6b7a8a" stroke-width="1.5"/><path d="M4 21a8 8 0 0 1 16 0" stroke="#6b7a8a" stroke-width="1.5" stroke-linecap="round"/></svg>
            <input type="text" name="name" placeholder="Your name" value="{{ old('name') }}" required>
          </div>
          @error('name') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="group">
          <label>Email</label>
          <div class="input">
            <svg class="ico" viewBox="0 0 24 24" fill="none"><path d="M4 7l8 6 8-6" stroke="#6b7a8a" stroke-width="1.5" stroke-linecap="round"/><rect x="4" y="5" width="16" height="14" rx="3" stroke="#6b7a8a" stroke-width="1.5"/></svg>
            <input type="email" name="email" placeholder="you@company.com" value="{{ old('email') }}" required>
          </div>
          @error('email') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="group">
          <label>Password</label>
          <div class="input">
            <svg class="ico" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="3" stroke="#6b7a8a" stroke-width="1.5"/><path d="M8 10V8a4 4 0 1 1 8 0v2" stroke="#6b7a8a" stroke-width="1.5"/></svg>
            <input type="password" name="password" placeholder="Create a strong password" required>
          </div>
          @error('password') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="group">
          <label>Confirm Password</label>
          <div class="input">
            <svg class="ico" viewBox="0 0 24 24" fill="none"><path d="M12 3l7 4v5c0 4.7-3.3 7.6-7 9.8C8.3 19.6 5 16.7 5 12V7l7-4Z" stroke="#6b7a8a" stroke-width="1.5"/><path d="M9 15l2 2 4-4" stroke="#6b7a8a" stroke-width="1.5" stroke-linecap="round"/></svg>
            <input type="password" name="password_confirmation" placeholder="Repeat password" required>
          </div>
        </div>

        <label class="check" style="margin:8px 0 12px">
          <input type="checkbox" required> I agree to the Terms & Privacy
        </label>

        <button type="submit" class="btn primary">Create account</button>
      </form>

      <div class="hr">or continue with</div>

      {{-- Social providers --}}
      <div style="display:grid; gap:10px">
        <a class="btn" href="{{ route('oauth.redirect','google') }}">
          <img class="ico-btn" src="https://www.svgrepo.com/show/475656/google-color.svg" alt=""> Sign up with Google
        </a>
        <a class="btn" href="{{ route('oauth.redirect','facebook') }}">
          <img class="ico-btn" src="https://www.svgrepo.com/show/448224/facebook.svg" alt=""> Sign up with Facebook
        </a>
        <a class="btn" href="{{ route('oauth.redirect','github') }}">
          <img class="ico-btn" src="https://www.svgrepo.com/show/512317/github-142.svg" alt=""> Sign up with GitHub
        </a>
      </div>

      <p class="muted" style="margin-top:12px;text-align:center">
        Already have an account? <a class="link" href="{{ route('login') }}" style="color:#0bd0fa;font-weight:600">Sign in</a>
      </p>
    </div>
  </div>
</body>
</html>

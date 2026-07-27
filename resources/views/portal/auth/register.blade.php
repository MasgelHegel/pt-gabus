<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun — PT Gabus Gas Trusss</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{
  font-family:'Inter',sans-serif;
  background:linear-gradient(135deg,#1e3a8a 0%,#1D4ED8 50%,#3B82F6 100%);
  min-height:100vh;display:flex;flex-direction:column;
  align-items:center;justify-content:center;padding:1.5rem 1rem;
}
body::before{
  content:'';position:fixed;inset:0;
  background:radial-gradient(ellipse 800px 600px at 70% 30%,rgba(255,255,255,.07) 0%,transparent 60%);
  pointer-events:none;
}
.wrap{position:relative;z-index:1;width:100%;max-width:460px}
.brand{display:flex;align-items:center;justify-content:center;gap:.75rem;margin-bottom:1.5rem}
.brand-logo{width:44px;height:44px;border-radius:12px;overflow:hidden;border:2px solid rgba(255,255,255,.3);box-shadow:0 4px 16px rgba(0,0,0,.25);flex-shrink:0}
.brand-logo img{width:100%;height:100%;object-fit:cover;display:block}
.brand-text .name{font-size:1.15rem;font-weight:800;color:#fff;letter-spacing:-.02em}
.brand-text .name span{color:#93C5FD}
.brand-text .sub{font-size:.65rem;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.07em;margin-top:1px}
.card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.card-header{background:linear-gradient(135deg,#1D4ED8,#3B82F6);padding:1.25rem 1.5rem}
.card-header h1{font-size:1.1rem;font-weight:800;color:#fff}
.card-header p{font-size:.75rem;color:rgba(255,255,255,.78);margin-top:.2rem}
.card-body{padding:1.5rem}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:.85rem}
@media(max-width:400px){.row2{grid-template-columns:1fr}}
.field{margin-bottom:.9rem}
label{display:block;font-size:.73rem;font-weight:700;color:#374151;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.04em}
label .req{color:#DC2626;margin-left:2px}
.input-wrap{position:relative}
.input-wrap .ico{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#9CA3AF;pointer-events:none}
.input-wrap .ico svg{width:15px;height:15px}
input[type=text],input[type=email],input[type=tel],input[type=password]{
  width:100%;border:1.5px solid #E2E8F0;border-radius:10px;
  background:#F8FAFC;color:#0F172A;font-family:inherit;
  font-size:.875rem;font-weight:500;
  padding:.65rem .9rem .65rem 2.5rem;
  outline:none;transition:all .15s;-webkit-appearance:none;
}
input:focus{background:#fff;border-color:#2563EB;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
input.err{border-color:#DC2626}
input::placeholder{color:#CBD5E1;font-weight:400}
.eye-btn{position:absolute;right:.8rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9CA3AF;display:flex;align-items:center;padding:.15rem}
.eye-btn:hover{color:#6B7280}
.eye-btn svg{width:15px;height:15px}
.strength{margin-top:.35rem;display:flex;gap:.25rem;align-items:center}
.strength-bar{height:4px;flex:1;border-radius:999px;background:#E2E8F0;transition:background .25s}
.strength-label{font-size:.65rem;font-weight:600;color:#6B7280;white-space:nowrap;min-width:40px;text-align:right}
.alert-err{background:#FFF5F5;border:1.5px solid #FECACA;border-radius:10px;padding:.75rem .9rem;font-size:.8rem;font-weight:600;color:#DC2626;display:flex;gap:.5rem;margin-bottom:1rem}
.alert-err svg{flex-shrink:0;width:15px;height:15px;margin-top:1px}
.btn-submit{
  width:100%;padding:.8rem;border-radius:12px;border:none;
  background:linear-gradient(135deg,#1D4ED8,#3B82F6);
  color:#fff;font-family:inherit;font-size:.95rem;font-weight:800;
  cursor:pointer;box-shadow:0 4px 16px rgba(37,99,235,.35);
  transition:opacity .15s,transform .1s;
  display:flex;align-items:center;justify-content:center;gap:.5rem;
}
.btn-submit svg{width:17px;height:17px}
.btn-submit:hover{opacity:.92}
.btn-submit:active{transform:scale(.97)}
.divider{display:flex;align-items:center;gap:.75rem;margin:.9rem 0}
.divider hr{flex:1;border:none;border-top:1px solid #E2E8F0}
.divider span{font-size:.72rem;color:#94A3B8;font-weight:500}
.btn-login{
  width:100%;padding:.75rem;border-radius:12px;border:1.5px solid #E2E8F0;
  background:#fff;color:#374151;font-family:inherit;font-size:.875rem;font-weight:700;
  cursor:pointer;transition:all .15s;text-align:center;text-decoration:none;display:block;
}
.btn-login:hover{background:#F8FAFC;border-color:#CBD5E1}
.terms{font-size:.7rem;color:#94A3B8;text-align:center;margin-top:1rem;line-height:1.6}
.terms a{color:#2563EB;text-decoration:none;font-weight:600}
.back{text-align:center;margin-top:1.1rem}
.back a{font-size:.75rem;color:rgba(255,255,255,.7);text-decoration:none;font-weight:500}
.back a:hover{color:#fff}
</style>
</head>
<body>
<div class="wrap">

  <div class="brand">
    <div class="brand-logo"><img src="{{ asset('image/logo.jpg') }}" alt="Logo"></div>
    <div class="brand-text">
      <div class="name">PT Gabus <span>Gas Trusss</span></div>
      <div class="sub">Portal Customer</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h1>Buat Akun Customer</h1>
      <p>Daftar gratis — pantau pesanan & invoice dari mana saja</p>
    </div>
    <div class="card-body">

      @if($errors->any())
      <div class="alert-err">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
        </svg>
        <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
      </div>
      @endif

      <form method="POST" action="{{ route('register.store') }}" id="reg-form">
        @csrf

        <div class="row2">
          <div class="field">
            <label>Nama Lengkap <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="ico"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/></svg></span>
              <input type="text" name="name" value="{{ old('name', request('name')) }}"
                     placeholder="Nama SPPG / Dapur" required autofocus
                     class="{{ $errors->has('name') ? 'err' : '' }}">
            </div>
          </div>
          <div class="field">
            <label>No. HP / WA <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="ico"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg></span>
              <input type="tel" name="phone" value="{{ old('phone', request('phone')) }}"
                     placeholder="08123456789" required
                     class="{{ $errors->has('phone') ? 'err' : '' }}">
            </div>
          </div>
        </div>

        <div class="field">
          <label>Email <span class="req">*</span></label>
          <div class="input-wrap">
            <span class="ico"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg></span>
            <input type="email" name="email" value="{{ old('email') }}"
                   placeholder="email@sppg.com" required
                   class="{{ $errors->has('email') ? 'err' : '' }}">
          </div>
        </div>

        <div class="row2">
          <div class="field">
            <label>Password <span class="req">*</span></label>
            <div class="input-wrap" x-data="{show:false}">
              <span class="ico"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg></span>
              <input :type="show?'text':'password'" name="password" id="pw"
                     placeholder="Min. 8 karakter" required
                     class="{{ $errors->has('password') ? 'err' : '' }}"
                     oninput="checkStrength(this.value)">
              <button type="button" class="eye-btn" @click="show=!show">
                <svg x-show="!show" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                <svg x-show="show" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
              </button>
            </div>
            <div class="strength">
              <div class="strength-bar" id="s1"></div>
              <div class="strength-bar" id="s2"></div>
              <div class="strength-bar" id="s3"></div>
              <div class="strength-bar" id="s4"></div>
              <div class="strength-label" id="slbl"></div>
            </div>
          </div>
          <div class="field">
            <label>Konfirmasi Password <span class="req">*</span></label>
            <div class="input-wrap" x-data="{show:false}">
              <span class="ico"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg></span>
              <input :type="show?'text':'password'" name="password_confirmation"
                     placeholder="Ulangi password" required>
              <button type="button" class="eye-btn" @click="show=!show">
                <svg x-show="!show" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                <svg x-show="show" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
              </button>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-submit">
          <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
          Daftar & Masuk Sekarang
        </button>
      </form>

      <div class="divider"><hr><span>atau</span><hr></div>

      <a href="{{ route('portal.login') }}" class="btn-login">Sudah punya akun? Login di sini</a>

      <p class="terms">
        Dengan mendaftar, Anda menyetujui syarat & ketentuan<br>
        <a href="{{ route('order.create') }}">← Kembali ke halaman pesan gas</a>
      </p>

    </div>
  </div>

  <div class="back">
    <a href="{{ route('home') }}">← Halaman Utama</a>
  </div>

</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function checkStrength(val) {
    var bars = [document.getElementById('s1'),document.getElementById('s2'),document.getElementById('s3'),document.getElementById('s4')];
    var lbl  = document.getElementById('slbl');
    var score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    var colors = ['','#EF4444','#F97316','#EAB308','#22C55E'];
    var labels = ['','Lemah','Cukup','Kuat','Sangat Kuat'];
    bars.forEach(function(b,i){ b.style.background = i < score ? colors[score] : '#E2E8F0'; });
    lbl.textContent = val.length ? labels[score] : '';
    lbl.style.color = colors[score];
}
</script>
</body>
</html>

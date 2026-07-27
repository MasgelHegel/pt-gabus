<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pesanan Berhasil — PT Gabus Gas Trusss</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{
  font-family:'Inter',sans-serif;
  background:linear-gradient(135deg,#1e3a8a 0%,#1D4ED8 50%,#3B82F6 100%);
  min-height:100vh;display:flex;flex-direction:column;
  align-items:center;justify-content:center;padding:1.5rem 1rem;
}
.wrap{width:100%;max-width:480px}
.card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3)}

/* SUCCESS HEADER */
.card-ok{background:linear-gradient(135deg,#16A34A,#22C55E);padding:1.75rem 1.5rem;text-align:center}
.check-circle{
  width:64px;height:64px;border-radius:50%;
  background:rgba(255,255,255,.2);border:3px solid rgba(255,255,255,.5);
  display:flex;align-items:center;justify-content:center;
  margin:0 auto .85rem;
}
.check-circle svg{width:32px;height:32px;color:#fff}
.card-ok h1{font-size:1.2rem;font-weight:800;color:#fff}
.card-ok p{font-size:.8rem;color:rgba(255,255,255,.85);margin-top:.25rem}

/* BODY */
.card-body{padding:1.5rem}

/* ORDER INFO */
.info-box{border:1.5px solid #E2E8F0;border-radius:12px;overflow:hidden;margin-bottom:1.25rem}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:.6rem .9rem;font-size:.82rem;border-bottom:1px solid #F1F5F9}
.info-row:last-child{border-bottom:none}
.info-row .lbl{color:#64748B;font-weight:500}
.info-row .val{font-weight:700;color:#0F172A}
.info-row.highlight{background:#F0FDF4}
.info-row.highlight .val{color:#16A34A}

/* STEPS */
.steps{margin-bottom:1.25rem}
.step{display:flex;align-items:flex-start;gap:.75rem;padding:.55rem 0;border-bottom:1px dashed #E2E8F0;font-size:.82rem}
.step:last-child{border-bottom:none}
.step-num{
  width:26px;height:26px;border-radius:50%;flex-shrink:0;
  background:#DBEAFE;color:#1D4ED8;
  font-size:.7rem;font-weight:800;
  display:flex;align-items:center;justify-content:center;
}
.step-text{color:#374151;font-weight:500;padding-top:.1rem}

/* REGISTER CTA */
.register-cta{
  background:linear-gradient(135deg,#1e3a8a,#1D4ED8);
  border-radius:14px;padding:1.1rem 1.25rem;
  margin-bottom:1.25rem;
}
.register-cta h3{font-size:.9rem;font-weight:800;color:#fff;margin-bottom:.3rem}
.register-cta p{font-size:.75rem;color:rgba(255,255,255,.8);line-height:1.55;margin-bottom:.85rem}
.register-cta .benefits{display:flex;flex-direction:column;gap:.35rem;margin-bottom:.9rem}
.register-cta .benefit{display:flex;align-items:center;gap:.5rem;font-size:.75rem;color:rgba(255,255,255,.9)}
.register-cta .benefit svg{width:14px;height:14px;color:#4ADE80;flex-shrink:0}
.btn-register{
  display:flex;align-items:center;justify-content:center;gap:.5rem;
  width:100%;padding:.75rem;border-radius:10px;border:none;
  background:#fff;color:#1D4ED8;
  font-family:inherit;font-size:.9rem;font-weight:800;
  cursor:pointer;text-decoration:none;
  transition:opacity .15s,transform .1s;
}
.btn-register:hover{opacity:.92}
.btn-register:active{transform:scale(.97)}
.btn-register svg{width:17px;height:17px}

/* BUTTONS */
.btn-outline{
  display:flex;align-items:center;justify-content:center;gap:.5rem;
  width:100%;padding:.72rem;border-radius:10px;
  border:1.5px solid #E2E8F0;background:#fff;
  color:#374151;font-family:inherit;font-size:.875rem;font-weight:700;
  cursor:pointer;text-decoration:none;margin-top:.65rem;
  transition:background .15s;
}
.btn-outline:hover{background:#F8FAFC}
.btn-login-link{
  text-align:center;font-size:.75rem;color:#94A3B8;
  margin-top:.85rem;
}
.btn-login-link a{color:#2563EB;text-decoration:none;font-weight:600}

/* BRAND */
.brand-footer{
  display:flex;align-items:center;justify-content:center;
  gap:.5rem;margin-top:1.25rem;
}
.brand-footer img{width:28px;height:28px;border-radius:7px;border:2px solid rgba(255,255,255,.3);object-fit:cover}
.brand-footer span{font-size:.75rem;font-weight:700;color:rgba(255,255,255,.7)}

.whatsapp-cta {
  background:#E8F5E9; border:1.5px solid #A5D6A7; border-radius:14px; padding:1.1rem 1.25rem; margin-bottom:1.25rem; text-align:center;
  transition: transform .2s, box-shadow .2s;
}
.whatsapp-cta:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(37,211,102,0.15); }
.whatsapp-cta h3 { font-size:.9rem; font-weight:800; color:#1B5E20; margin-bottom:.3rem; display:flex; align-items:center; justify-content:center; gap:0.5rem }
.whatsapp-cta p { font-size:.75rem; color:#2E7D32; line-height:1.55; margin-bottom:.85rem; font-weight:500; }
.btn-whatsapp {
  display:flex; align-items:center; justify-content:center; gap:.5rem; width:100%; padding:.75rem; border-radius:10px; border:none;
  background:#25D366; color:#fff; font-family:inherit; font-size:.9rem; font-weight:800; cursor:pointer; text-decoration:none;
  transition: opacity .15s, transform .1s; box-shadow: 0 4px 6px rgba(37, 211, 102, 0.2);
}
.btn-whatsapp:hover { opacity: .92; }
.btn-whatsapp:active { transform: scale(.98); }
</style>
</head>
<body>
<div class="wrap">

  <div class="card">

    <div class="card-ok">
      <div class="check-circle">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
        </svg>
      </div>
      <h1>Pesanan Berhasil Dikirim!</h1>
      <p>Tim Sales akan segera menghubungi Anda via WhatsApp</p>
    </div>

    <div class="card-body">

      @if(session('order_number') || session('nama_dapur'))
      <div class="info-box">
        @if(session('order_number'))
        <div class="info-row">
          <span class="lbl">No. Order</span>
          <span class="val">{{ session('order_number') }}</span>
        </div>
        @endif
        @if(session('nama_dapur'))
        <div class="info-row">
          <span class="lbl">Nama SPPG</span>
          <span class="val">{{ session('nama_dapur') }}</span>
        </div>
        @endif
        @if(session('no_hp'))
        <div class="info-row">
          <span class="lbl">No. HP</span>
          <span class="val">{{ session('no_hp') }}</span>
        </div>
        @endif
        <div class="info-row highlight">
          <span class="lbl">Status</span>
          <span class="val">✓ Menunggu Konfirmasi</span>
        </div>
      </div>
      @endif

      <div class="steps">
        <div class="step">
          <div class="step-num">1</div>
          <div class="step-text">Pesanan masuk ke sistem admin & sales kami</div>
        </div>
        <div class="step">
          <div class="step-num">2</div>
          <div class="step-text">Tim Sales menghubungi via WhatsApp untuk konfirmasi jadwal kirim</div>
        </div>
        <div class="step">
          <div class="step-num">3</div>
          <div class="step-text">Gas LPG dikirim ke alamat Anda</div>
        </div>
      </div>

      @if($order)
      <div class="whatsapp-cta">
        <h3>
          <svg style="width:20px; height:20px; fill:#2e7d32" viewBox="0 0 24 24">
            <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 0 0 1.37 5.054L2 22l5.132-1.347a9.936 9.936 0 0 0 4.88 1.28c5.508 0 9.99-4.478 9.99-9.984A10.003 10.003 0 0 0 12.012 2zm6.09 13.925c-.249.704-1.242 1.3-1.696 1.386-.407.078-.934.14-2.735-.606-2.304-.954-3.791-3.3-3.906-3.453-.115-.152-.936-1.247-.936-2.378 0-1.13.583-1.685.832-1.942.249-.257.54-.321.72-.321.18 0 .36.002.518.01.164.009.387-.063.606.463.224.54.767 1.865.832 1.996.065.13.109.283.022.456-.088.173-.131.283-.262.436-.131.152-.277.34-.395.456-.13.13-.267.272-.115.534.152.261.678 1.116 1.455 1.81.998.89 1.84 1.167 2.102 1.297.262.13.414.109.568-.065.152-.174.656-.763.832-1.02.176-.257.35-.217.59-.13.24.088 1.523.719 1.785.85.262.13.436.196.501.304.065.109.065.63-.184 1.334z"/>
          </svg>
          Kirim Rincian ke WhatsApp Admin
        </h3>
        <p>
          Kirim detail pesanan Anda ke WhatsApp Admin agar pesanan dapat langsung dikonfirmasi dan diproses lebih cepat!
        </p>
        <a href="{{ $order->getWhatsAppUrl() }}" target="_blank" class="btn-whatsapp">
          Kirim WhatsApp Sekarang
        </a>
      </div>
      @endif

      {{-- CTA DAFTAR AKUN (hanya untuk tamu) --}}
      @guest
      <div class="register-cta">
        <h3>🎯 Buat Akun — Pantau Pesanan Lebih Mudah</h3>
        <p>Dengan akun customer, Anda bisa memantau status pesanan, melihat invoice, dan melakukan pembayaran kapan saja.</p>
        <div class="benefits">
          <div class="benefit">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            Lihat status pesanan realtime
          </div>
          <div class="benefit">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            Terima & bayar invoice online
          </div>
          <div class="benefit">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            Riwayat lengkap semua transaksi
          </div>
        </div>
        <a href="{{ route('register') }}?name={{ urlencode(session('nama_dapur','')) }}&phone={{ urlencode(session('no_hp','')) }}" class="btn-register">
          <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
          Daftar Gratis Sekarang
        </a>
      </div>
      @endguest

      <a href="{{ route('order.create') }}" class="btn-outline">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Buat Pesanan Lagi
      </a>

      @guest
      <div class="btn-login-link">
        Sudah punya akun? <a href="{{ route('portal.login') }}">Login di sini</a>
      </div>
      @endguest

      @auth
      <a href="{{ route('portal.orders.index') }}" class="btn-outline" style="color:#1D4ED8;border-color:#BFDBFE;background:#EFF6FF">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
        Lihat Semua Pesanan Saya
      </a>
      @endauth

    </div>
  </div>

  <div class="brand-footer">
    <img src="{{ asset('image/logo.jpg') }}" alt="Logo">
    <span>PT Gabus Gas Trusss</span>
  </div>

</div>
</body>
</html>

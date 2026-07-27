<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Pesan Gas LPG — PT Gabus Gas Trusss</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --blue:#1D4ED8;--blue2:#2563EB;--blue3:#3B82F6;--blue4:#DBEAFE;--blue5:#EFF6FF;
  --dark:#0F172A;--text:#1E293B;--muted:#64748B;--border:#E2E8F0;
  --white:#FFFFFF;--green:#16A34A;--red:#DC2626;
}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:var(--blue5);color:var(--text);min-height:100vh}

/* NAV */
nav{
  background:var(--white);border-bottom:1px solid var(--border);
  padding:.9rem 1.5rem;display:flex;align-items:center;
  justify-content:space-between;position:sticky;top:0;z-index:100;
  box-shadow:0 1px 3px rgba(0,0,0,.08);
}
.nav-brand{display:flex;align-items:center;gap:.6rem}
.nav-logo{width:36px;height:36px;border-radius:10px;overflow:hidden;border:2px solid var(--border);flex-shrink:0}
.nav-logo img{width:100%;height:100%;object-fit:cover;display:block}
.nav-name{font-size:.95rem;font-weight:800;color:var(--dark);letter-spacing:-.02em}
.nav-name span{color:var(--blue2)}
.nav-badge{
  background:var(--blue2);color:var(--white);
  font-size:.72rem;font-weight:700;padding:.35rem .85rem;
  border-radius:999px;letter-spacing:.02em;
  box-shadow:0 2px 8px rgba(37,99,235,.35);
}

/* HERO */
.hero{
  background:linear-gradient(135deg,#1e3a8a 0%,#1D4ED8 45%,#2563EB 75%,#3B82F6 100%);
  position:relative;overflow:hidden;
  padding:3rem 1.5rem 0;
  min-height:420px;
  display:flex;align-items:flex-end;
}
.hero::before{
  content:'';position:absolute;inset:0;
  background:
    radial-gradient(ellipse 600px 400px at 80% 50%,rgba(255,255,255,.07) 0%,transparent 70%),
    radial-gradient(ellipse 300px 300px at 10% 80%,rgba(255,255,255,.05) 0%,transparent 60%);
}
/* decorative circles */
.hero::after{
  content:'';position:absolute;
  width:500px;height:500px;border-radius:50%;
  border:80px solid rgba(255,255,255,.05);
  top:-120px;right:-120px;
}
.hero-inner{
  max-width:1100px;margin:0 auto;width:100%;
  display:flex;align-items:flex-end;justify-content:space-between;
  gap:2rem;position:relative;z-index:2;
}
.hero-text{flex:1;padding-bottom:3rem;max-width:520px}
.hero-pill{
  display:inline-flex;align-items:center;gap:.45rem;
  background:rgba(255,255,255,.18);backdrop-filter:blur(8px);
  color:#fff;font-size:.72rem;font-weight:700;
  padding:.35rem .9rem;border-radius:999px;
  border:1px solid rgba(255,255,255,.3);
  margin-bottom:1.25rem;letter-spacing:.04em;
}
.hero-pill::before{content:'✦';font-size:.6rem}
.hero-h1{
  font-size:clamp(2rem,5vw,3rem);font-weight:900;
  color:#fff;line-height:1.1;letter-spacing:-.03em;
  margin-bottom:.75rem;
}
.hero-h1 span{color:#93C5FD}
.hero-sub{
  font-size:clamp(.9rem,2vw,1.05rem);color:rgba(255,255,255,.82);
  line-height:1.65;margin-bottom:1.75rem;font-weight:400;
}
.hero-stats{display:flex;gap:2rem;margin-bottom:2rem}
.hero-stat .num{
  font-size:1.4rem;font-weight:900;color:#fff;
  line-height:1;
}
.hero-stat .num span{color:#93C5FD}
.hero-stat .lbl{font-size:.65rem;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.08em;margin-top:.15rem}
.hero-cta{
  display:inline-flex;align-items:center;gap:.5rem;
  background:#fff;color:var(--blue2);
  font-size:.95rem;font-weight:800;
  padding:.8rem 1.75rem;border-radius:12px;
  border:none;cursor:pointer;
  box-shadow:0 4px 20px rgba(0,0,0,.25);
  text-decoration:none;
  transition:transform .15s,box-shadow .15s;
}
.hero-cta:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,0,0,.3)}
.hero-cta svg{width:18px;height:18px}
.hero-visual{
  flex-shrink:0;display:flex;align-items:flex-end;
  gap:1.25rem;padding-bottom:0;
}
.hero-img-card{
  background:rgba(255,255,255,.12);backdrop-filter:blur(12px);
  border:1px solid rgba(255,255,255,.25);border-radius:20px 20px 0 0;
  padding:1.25rem 1rem 0;
  display:flex;flex-direction:column;align-items:center;
  min-width:140px;cursor:pointer;
  transition:background .2s,transform .2s;
  position:relative;overflow:hidden;
}
.hero-img-card:hover{background:rgba(255,255,255,.22);transform:translateY(-6px)}
.hero-img-card img{width:120px;height:120px;object-fit:contain;display:block;filter:drop-shadow(0 8px 16px rgba(0,0,0,.3))}
.hero-img-card .card-label{
  color:#fff;font-size:.8rem;font-weight:800;
  padding:.5rem 0 1rem;text-align:center;
  letter-spacing:-.01em;
}
.hero-img-card .card-price{
  font-size:.72rem;font-weight:600;
  color:rgba(255,255,255,.75);
  margin-top:-.25rem;padding-bottom:1rem;
}

@media(max-width:768px){
  .hero{min-height:auto;padding:2rem 1.25rem 0}
  .hero-inner{flex-direction:column;align-items:flex-start}
  .hero-text{padding-bottom:1.5rem}
  .hero-visual{
    width:100%;
    justify-content:flex-start;
    overflow-x:auto;
    padding-bottom:1rem;
    -webkit-overflow-scrolling:touch;
    scrollbar-width:none;
  }
  .hero-visual::-webkit-scrollbar {
    display: none;
  }
  .hero-img-card{min-width:130px;flex-shrink:0}
  .hero-img-card img{width:100px;height:100px}
  .hero-stats{gap:1.25rem}
}
</style>
</head>
<body x-data="gasOrder()" x-init="init()">

{{-- NAV --}}
<nav>
  <div class="nav-brand">
    <div class="nav-logo"><img src="{{ asset('image/logo.jpg') }}" alt="Logo"></div>
    <div class="nav-name">PT Gabus <span>Gas Trusss</span></div>
  </div>
  <div class="nav-badge">🔥 Pesan Sekarang</div>
</nav>

{{-- HERO --}}
<div class="hero">
  <div class="hero-inner">
    <div class="hero-text">
      <div class="hero-pill">Distributor LPG Resmi Terpercaya</div>
      <h1 class="hero-h1">Gas LPG<br><span>12 Kg & 50 Kg</span><br>Harga Terbaik</h1>
      <p class="hero-sub">Pesan langsung dari distributor resmi. Pengiriman cepat ke seluruh wilayah layanan. Konfirmasi via WhatsApp.</p>
      <div class="hero-stats">
        <div class="hero-stat"><div class="num">500<span>+</span></div><div class="lbl">Pelanggan</div></div>
        <div class="hero-stat"><div class="num">2<span>x</span></div><div class="lbl">Ukuran Tabung</div></div>
        <div class="hero-stat"><div class="num">100<span>%</span></div><div class="lbl">Resmi</div></div>
      </div>
      <a href="#order-section" class="hero-cta">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/></svg>
        Pesan Gas Sekarang
      </a>
    </div>
    <div class="hero-visual">
      @foreach($products as $product)
      <div class="hero-img-card" @click="selectProduct('{{ $product->id }}'); $nextTick(()=>document.getElementById('order-section').scrollIntoView({behavior:'smooth'}))">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" onerror="this.src='https://via.placeholder.com/300?text=No+Image'">
        <div class="card-label">{{ $product->name }}</div>
        <div class="card-price">Rp {{ number_format($product->sell_price,0,',','.') }}</div>
      </div>
      @endforeach
    </div>
  </div>
</div>

<style>
/* ORDER SECTION */
#order-section{padding:2.5rem 1.5rem 4rem;max-width:1100px;margin:0 auto}
.section-head{text-align:center;margin-bottom:2rem}
.section-tag{
  display:inline-flex;align-items:center;gap:.4rem;
  background:var(--blue4);color:var(--blue2);
  font-size:.72rem;font-weight:700;padding:.35rem .9rem;
  border-radius:999px;border:1px solid #BFDBFE;
  margin-bottom:.75rem;letter-spacing:.04em;
}
.section-head h2{font-size:clamp(1.4rem,3vw,1.9rem);font-weight:800;color:var(--dark);letter-spacing:-.02em}
.section-head p{font-size:.9rem;color:var(--muted);margin-top:.4rem}

/* STEP PILLS */
.steps-row{display:flex;justify-content:center;gap:.5rem;margin-bottom:2.5rem;flex-wrap:wrap}
.step-pill{
  display:flex;align-items:center;gap:.45rem;
  background:var(--white);border:1.5px solid var(--border);
  border-radius:999px;padding:.4rem 1rem;
  font-size:.75rem;font-weight:600;color:var(--muted);
  box-shadow:0 1px 3px rgba(0,0,0,.05);
}
.step-pill.active{background:var(--blue2);color:#fff;border-color:var(--blue2)}
.step-pill .num{
  width:20px;height:20px;border-radius:50%;
  background:var(--blue4);color:var(--blue2);
  font-size:.68rem;font-weight:800;
  display:flex;align-items:center;justify-content:center;
}
.step-pill.active .num{background:rgba(255,255,255,.25);color:#fff}

/* ORDER GRID */
.order-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem}
@media(max-width:900px){.order-grid{grid-template-columns:1fr}}

/* PRODUCT CARDS */
.prod-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.prod-card{
  background:var(--white);border:2px solid var(--border);border-radius:16px;
  cursor:pointer;overflow:hidden;
  box-shadow:0 2px 8px rgba(0,0,0,.05);
  transition:border-color .15s,box-shadow .15s,transform .12s;
  -webkit-tap-highlight-color:transparent;
}
.prod-card:hover{border-color:var(--blue3);box-shadow:0 4px 20px rgba(37,99,235,.15);transform:translateY(-2px)}
.prod-card.active{border-color:var(--blue2);box-shadow:0 0 0 4px rgba(37,99,235,.12);background:#F0F7FF}
.prod-img{background:#F1F5F9;aspect-ratio:1;display:flex;align-items:center;justify-content:center;padding:1rem;position:relative}
.prod-card.active .prod-img{background:#DBEAFE}
.prod-img img{width:100%;height:100%;object-fit:contain;transition:transform .2s}
.prod-card:hover .prod-img img{transform:scale(1.06)}
.prod-check{
  position:absolute;top:.6rem;right:.6rem;
  width:24px;height:24px;border-radius:50%;
  background:var(--white);border:2px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  transition:all .15s;
}
.prod-card.active .prod-check{background:var(--blue2);border-color:var(--blue2)}
.prod-check svg{width:12px;height:12px;color:#fff;opacity:0;transition:opacity .15s}
.prod-card.active .prod-check svg{opacity:1}
.prod-body{padding:.85rem .9rem .9rem}
.prod-name{font-size:1rem;font-weight:800;color:var(--dark)}
.prod-sub{font-size:.7rem;color:var(--muted);margin-top:.15rem}
.prod-price{font-size:.9rem;font-weight:800;color:var(--blue2);margin-top:.5rem}

/* QTY BOX */
.qty-box{
  background:var(--white);border:1.5px solid var(--border);border-radius:16px;
  overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05);
  margin-top:1rem;display:none;
}
.qty-box.show{display:block}
.qty-head{
  background:linear-gradient(135deg,var(--blue),var(--blue3));
  padding:.75rem 1rem;display:flex;justify-content:space-between;align-items:center;
}
.qty-head-name{font-size:.85rem;font-weight:700;color:#fff}
.qty-head-price{font-size:.8rem;font-weight:600;color:rgba(255,255,255,.85)}
.qty-body{padding:1rem;display:flex;align-items:center;gap:1rem}
.qty-btn{
  width:42px;height:42px;border-radius:10px;
  border:1.5px solid var(--border);background:#F8FAFC;
  font-size:1.3rem;font-weight:700;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  color:var(--dark);transition:all .12s;flex-shrink:0;
}
.qty-btn:hover{border-color:var(--blue2);background:#EFF6FF;color:var(--blue2)}
.qty-btn:active{transform:scale(.92)}
.qty-btn:disabled{opacity:.3;cursor:not-allowed}
.qty-num{flex:1;text-align:center}
.qty-num .n{font-size:2rem;font-weight:900;color:var(--dark);line-height:1}
.qty-num .u{font-size:.68rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
.subtotal-bar{
  margin:.25rem 1rem .75rem;padding:.6rem .85rem;
  background:var(--blue4);border:1.5px solid #BFDBFE;border-radius:10px;
  display:flex;justify-content:space-between;align-items:center;
  font-size:.82rem;font-weight:700;color:var(--blue2);
}

/* FORM BOX */
.form-box{
  background:var(--white);border:1.5px solid var(--border);border-radius:16px;
  overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05);
  align-self:start;
}
.form-head{
  background:var(--blue2);padding:1rem 1.25rem;
}
.form-head h3{font-size:1rem;font-weight:800;color:#fff}
.form-head p{font-size:.75rem;color:rgba(255,255,255,.8);margin-top:.2rem}
.form-body{padding:1.25rem}
.field{margin-bottom:.9rem}
label{display:block;font-size:.73rem;font-weight:700;color:#374151;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.04em}
label .req{color:var(--red);margin-left:2px}
input[type=text],input[type=tel],textarea{
  width:100%;border:1.5px solid var(--border);border-radius:10px;
  background:#F8FAFC;color:var(--dark);font-family:inherit;
  font-size:.9rem;font-weight:500;padding:.65rem .9rem;
  outline:none;transition:all .15s;-webkit-appearance:none;
}
input:focus,textarea:focus{background:#fff;border-color:var(--blue2);box-shadow:0 0 0 3px rgba(37,99,235,.12)}
input.err,textarea.err{border-color:var(--red)}
input::placeholder,textarea::placeholder{color:#CBD5E1;font-weight:400}
textarea{resize:vertical;min-height:68px}
.empty-notice{
  text-align:center;padding:1.5rem 1rem;
  border:2px dashed #CBD5E1;border-radius:12px;
  color:var(--muted);font-size:.85rem;font-weight:500;
}
.empty-notice svg{width:2.5rem;height:2.5rem;color:#CBD5E1;margin:0 auto .5rem;display:block}

/* SUMMARY */
.summary{border:1.5px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:1rem}
.summary-row{display:flex;justify-content:space-between;align-items:center;padding:.55rem .85rem;font-size:.82rem;font-weight:600;border-bottom:1px solid var(--border)}
.summary-row:last-child{border-bottom:none;font-weight:800;font-size:.95rem;background:var(--blue4);color:var(--blue2)}

/* ALERT */
.alert-err{
  background:#FFF5F5;border:1.5px solid #FECACA;border-radius:10px;
  padding:.75rem .9rem;font-size:.8rem;font-weight:600;color:var(--red);
  display:flex;gap:.5rem;align-items:flex-start;margin-bottom:1rem;
}
.alert-err svg{width:16px;height:16px;flex-shrink:0;margin-top:1px}

/* SUBMIT */
.btn-submit{
  width:100%;padding:.85rem;border-radius:12px;border:none;
  background:linear-gradient(135deg,var(--blue),var(--blue3));
  color:#fff;font-family:inherit;font-size:.95rem;font-weight:800;
  cursor:pointer;box-shadow:0 4px 16px rgba(37,99,235,.35);
  transition:opacity .15s,transform .12s,box-shadow .15s;
  display:flex;align-items:center;justify-content:center;gap:.5rem;
  letter-spacing:.01em;
}
.btn-submit svg{width:18px;height:18px}
.btn-submit:hover{opacity:.92;box-shadow:0 6px 24px rgba(37,99,235,.45)}
.btn-submit:active{transform:scale(.97)}
.btn-submit:disabled{background:#94A3B8;cursor:not-allowed;box-shadow:none}

/* FEATURES */
.features{background:var(--white);border-top:1px solid var(--border);padding:2rem 1.5rem}
.features-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
@media(max-width:640px){.features-inner{grid-template-columns:1fr}}
.feat-card{background:var(--blue5);border:1px solid #BFDBFE;border-radius:14px;padding:1.25rem 1rem;text-align:center}
.feat-icon{width:42px;height:42px;border-radius:12px;background:var(--blue4);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem}
.feat-icon svg{width:22px;height:22px;color:var(--blue2)}
.feat-title{font-size:.88rem;font-weight:800;color:var(--dark);margin-bottom:.3rem}
.feat-desc{font-size:.75rem;color:var(--muted);line-height:1.55}

/* FOOTER */
footer{background:var(--dark);color:rgba(255,255,255,.55);text-align:center;padding:1.25rem;font-size:.75rem}
footer strong{color:#fff}

@media(max-width:640px){
  #order-section{padding:1.75rem 1rem 3rem}
  .prod-grid{grid-template-columns:1fr 1fr}
}
[x-cloak]{display:none!important}
</style>

{{-- AUTH BANNER (muncul jika belum login) --}}
@guest
<div style="background:#FFF7ED;border-top:3px solid #F59E0B;border-bottom:3px solid #F59E0B;padding:.85rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem">
    <div style="display:flex;align-items:center;gap:.65rem">
        <svg style="width:20px;height:20px;color:#D97706;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
        </svg>
        <div>
            <div style="font-size:.82rem;font-weight:700;color:#92400E">Pesan dengan akun agar bisa pantau status & invoice</div>
            <div style="font-size:.72rem;color:#B45309;margin-top:.1rem">Tanpa akun pesanan tetap diterima, tapi tidak bisa dilacak</div>
        </div>
    </div>
    <div style="display:flex;gap:.5rem;flex-shrink:0">
        <a href="{{ route('register') }}" style="background:#D97706;color:#fff;font-size:.78rem;font-weight:700;padding:.45rem 1rem;border-radius:8px;text-decoration:none;white-space:nowrap">Daftar Gratis</a>
        <a href="{{ route('portal.login') }}" style="background:#fff;color:#D97706;border:1.5px solid #D97706;font-size:.78rem;font-weight:700;padding:.45rem 1rem;border-radius:8px;text-decoration:none;white-space:nowrap">Login</a>
    </div>
</div>
@endguest

@auth
@if(auth()->user()->isCustomer())
<div style="background:#F0FDF4;border-top:3px solid #16A34A;border-bottom:3px solid #16A34A;padding:.75rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
    <div style="display:flex;align-items:center;gap:.6rem">
        <svg style="width:18px;height:18px;color:#16A34A;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
        <span style="font-size:.82rem;font-weight:700;color:#15803D">Login sebagai <strong>{{ auth()->user()->name }}</strong> — pesanan akan tersimpan di akun Anda</span>
    </div>
    <a href="{{ route('portal.orders.index') }}" style="font-size:.75rem;font-weight:700;color:#16A34A;text-decoration:none">Lihat Pesanan →</a>
</div>
@endif
@endauth

{{-- ORDER SECTION --}}
<div id="order-section">
  <div class="section-head">
    <div class="section-tag">✦ Form Pemesanan</div>
    <h2>Pesan Gas LPG Sekarang</h2>
    <p>Pilih jenis gas, tentukan jumlah, lalu isi data pengiriman</p>
  </div>

  <div class="steps-row">
    <div class="step-pill" :class="{'active': cart.length > 0}">
      <div class="num">1</div> Pilih Gas
    </div>
    <div class="step-pill" :class="{'active': cart.length > 0}">
      <div class="num">2</div> Jumlah
    </div>
    <div class="step-pill" :class="{'active': cart.length > 0}">
      <div class="num">3</div> Isi Data
    </div>
  </div>

  <div class="order-grid">

    {{-- KIRI: pilih produk + qty --}}
    <div>
      <div class="prod-grid">

        @foreach($products as $product)
        <div class="prod-card" :class="{'active': isSelected('{{ $product->id }}')}" @click="toggleProduct('{{ $product->id }}')">
          <div class="prod-img">
            <div class="prod-check">
              <svg fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
              </svg>
            </div>
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" onerror="this.src='https://via.placeholder.com/300?text=No+Image'">
          </div>
          <div class="prod-body">
            <div class="prod-name">{{ $product->name }}</div>
            <div class="prod-sub">{{ $product->description ?? 'Tabung LPG' }}</div>
            <div class="prod-price">Rp {{ number_format($product->sell_price,0,',','.') }}</div>
          </div>
        </div>
        @endforeach

      </div>

      {{-- CART ITEMS --}}
      <template x-for="item in cart" :key="item.key">
        <div class="qty-box show">
          <div class="qty-head">
            <div class="qty-head-name" x-text="item.name"></div>
            <div class="qty-head-price" x-text="'Rp '+formatRp(item.price)+' / tabung'"></div>
          </div>
          <div class="qty-body">
            <button type="button" class="qty-btn" @click="changeQty(item.key, -1)" :disabled="item.qty<=0">−</button>
            <div class="qty-num">
              <div class="n" x-text="item.qty"></div>
              <div class="u">Tabung</div>
            </div>
            <button type="button" class="qty-btn" @click="changeQty(item.key, 1)">+</button>
          </div>
          <div class="subtotal-bar">
            <span>Subtotal</span>
            <span x-text="'Rp '+formatRp(item.qty*item.price)"></span>
          </div>
        </div>
      </template>
    </div>

    {{-- KANAN: form --}}
    <div class="form-box">
      <div class="form-head">
        <h3>Detail Pengiriman</h3>
        <p>Isi nama SPPG dan nomor HP untuk konfirmasi</p>
      </div>
      <div class="form-body">

        <div x-show="cart.length===0" class="empty-notice">
          <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
          </svg>
          Pilih jenis gas dan jumlah tabung terlebih dahulu
        </div>

        <div x-show="cart.length>0" x-cloak>

          @if($errors->any())
          <div class="alert-err">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
            </svg>
            <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
          </div>
          @endif

          <form method="POST" action="{{ route('order.store') }}" @submit.prevent="submitForm">
            @csrf
            <div id="cart-inputs"></div>

            <div class="field">
              <label>Nama SPPG / Dapur <span class="req">*</span></label>
              <input type="text" name="nama_dapur" value="{{ old('nama_dapur') }}"
                     placeholder="Contoh: SPPG Jidor Jatimekar" required
                     class="{{ $errors->has('nama_dapur') ? 'err' : '' }}">
            </div>

            <div class="field">
              <label>No. HP / WhatsApp <span class="req">*</span></label>
              <input type="tel" name="no_hp" value="{{ old('no_hp') }}"
                     placeholder="08123456789" required
                     class="{{ $errors->has('no_hp') ? 'err' : '' }}">
            </div>

            <div class="field">
              <label>Alamat Pengiriman</label>
              <textarea name="alamat" placeholder="Alamat lengkap (opsional)">{{ old('alamat') }}</textarea>
            </div>

            <div class="field">
              <label>Catatan</label>
              <textarea name="catatan" placeholder="Catatan untuk tim kami (opsional)">{{ old('catatan') }}</textarea>
            </div>

            <div class="summary">
              <template x-for="item in cart" :key="item.key">
                <div class="summary-row">
                  <span x-text="item.name+' × '+item.qty+' tabung'"></span>
                  <span x-text="'Rp '+formatRp(item.qty*item.price)"></span>
                </div>
              </template>
              <div class="summary-row">
                <span>Total Pesanan</span>
                <span x-text="'Rp '+formatRp(cartTotal)"></span>
              </div>
            </div>

            <button type="submit" class="btn-submit" :disabled="cart.length===0">
              <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
              </svg>
              Kirim Pesanan Sekarang
            </button>
          </form>
        </div>

      </div>
    </div>

  </div>
</div>

{{-- FEATURES --}}
<div class="features">
  <div class="features-inner">
    <div class="feat-card">
      <div class="feat-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
        </svg>
      </div>
      <div class="feat-title">Distributor Resmi</div>
      <div class="feat-desc">Produk gas LPG langsung dari distributor resmi bersertifikat</div>
    </div>
    <div class="feat-card">
      <div class="feat-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/>
        </svg>
      </div>
      <div class="feat-title">Konfirmasi WhatsApp</div>
      <div class="feat-desc">Tim sales menghubungi via WhatsApp setelah pesanan masuk</div>
    </div>
    <div class="feat-card">
      <div class="feat-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
        </svg>
      </div>
      <div class="feat-title">Pengiriman Cepat</div>
      <div class="feat-desc">Jadwal pengiriman fleksibel sesuai kebutuhan Anda</div>
    </div>
  </div>
</div>

{{-- FOOTER --}}
<footer>
  <strong>PT Gabus Gas Trusss</strong> &mdash; Distributor LPG Resmi &copy; {{ date('Y') }}
</footer>

{{-- Alpine.js --}}
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function gasOrder() {
    return {
        cart: [],
        products: {
            @foreach($products as $product)
            '{{ $product->id }}': {
                id:    {{ $product->id }},
                name:  '{{ addslashes($product->name) }}',
                price: {{ (int) $product->sell_price }}
            },
            @endforeach
        },
        init() {
            @if(old('jenis_tabung'))
                @foreach(old('jenis_tabung') as $item)
                    @if($item['qty'] > 0)
                        if (this.products['{{ $item['product_id'] }}']) {
                            this.cart.push({...this.products['{{ $item['product_id'] }}'], key: '{{ $item['product_id'] }}', qty: {{ $item['qty'] }} });
                        }
                    @endif
                @endforeach
            @endif

            @if($errors->any())
                this.$nextTick(() => {
                    const el = document.getElementById('order-section');
                    if (el) el.scrollIntoView({ behavior: 'smooth' });
                });
            @endif
        },
        getQty(key) {
            const item = this.cart.find(i => i.key === key);
            return item ? item.qty : 0;
        },
        isSelected(key) {
            return this.cart.some(i => i.key === key);
        },
        selectProduct(key) {
            const idx = this.cart.findIndex(i => i.key === key);
            if (idx < 0) {
                const p = this.products[key];
                if (p) this.cart.push({ ...p, key, qty: 1 });
            }
        },
        toggleProduct(key) {
            const idx = this.cart.findIndex(i => i.key === key);
            if (idx >= 0) {
                this.cart.splice(idx, 1);
            } else {
                const p = this.products[key];
                if (p) this.cart.push({ ...p, key, qty: 1 });
            }
        },
        changeQty(key, delta) {
            const idx = this.cart.findIndex(i => i.key === key);
            if (idx < 0) return;
            const newQty = this.cart[idx].qty + delta;
            if (newQty <= 0) {
                this.cart.splice(idx, 1);
            } else {
                this.cart[idx].qty = newQty;
            }
        },
        get cartCount() { return this.cart.reduce((s, i) => s + i.qty, 0); },
        get cartTotal() { return this.cart.reduce((s, i) => s + i.price * i.qty, 0); },
        submitForm() {
            if (this.cart.length === 0) return;
            const container = document.getElementById('cart-inputs');
            container.innerHTML = '';
            this.cart.forEach((item, idx) => {
                const p = document.createElement('input');
                p.type = 'hidden'; p.name = 'jenis_tabung['+idx+'][product_id]'; p.value = item.id;
                container.appendChild(p);
                const q = document.createElement('input');
                q.type = 'hidden'; q.name = 'jenis_tabung['+idx+'][qty]'; q.value = item.qty;
                container.appendChild(q);
            });
            this.$el.querySelector('form').submit();
        },
        formatRp(v) {
            return new Intl.NumberFormat('id-ID').format(v || 0);
        }
    };
}
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Barion workbench')</title>
    <x-laravel-barion::pixel />
    <style>
        :root { --bg:#f5f6f8; --card:#fff; --text:#1d2330; --muted:#667085; --line:#e3e6ec; --accent:#0a7cff; --ok:#12805c; --bad:#c4321c; --warn:#a15c00; --code:#f0f2f5; }
        @media (prefers-color-scheme: dark) { :root { --bg:#12151b; --card:#1b1f27; --text:#e6e8ec; --muted:#98a1b2; --line:#2c3240; --accent:#4c9dff; --ok:#3ccf91; --bad:#ff7a66; --warn:#f0b35a; --code:#232834; } }
        * { box-sizing: border-box; }
        body { margin:0; background:var(--bg); color:var(--text); font:14px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif; }
        header { background:var(--card); border-bottom:1px solid var(--line); padding:12px 16px; display:flex; gap:16px; align-items:center; flex-wrap:wrap; }
        header a { color:var(--text); text-decoration:none; font-weight:600; }
        main { max-width:1100px; margin:0 auto; padding:16px; display:grid; gap:16px; }
        .grid { display:grid; gap:16px; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); }
        .card { background:var(--card); border:1px solid var(--line); border-radius:10px; padding:16px; min-width:0; }
        h1 { font-size:20px; margin:0; } h2 { font-size:16px; margin:0 0 12px; }
        label { display:block; font-size:12px; color:var(--muted); margin:8px 0 4px; }
        input, select, textarea { width:100%; padding:7px 9px; border:1px solid var(--line); border-radius:6px; background:var(--bg); color:var(--text); font:inherit; }
        select[multiple] { min-height:110px; }
        .row { display:grid; gap:8px; grid-template-columns:repeat(auto-fit, minmax(120px, 1fr)); }
        .check { display:flex; gap:6px; align-items:center; margin-top:10px; } .check input { width:auto; }
        button, .btn { display:inline-block; margin-top:12px; padding:8px 14px; border:0; border-radius:6px; background:var(--accent); color:#fff; font:inherit; font-weight:600; cursor:pointer; text-decoration:none; }
        button.secondary { background:var(--muted); } button.danger { background:var(--bad); }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        th, td { text-align:left; padding:6px 8px; border-bottom:1px solid var(--line); vertical-align:top; }
        th { color:var(--muted); font-weight:600; }
        .scroll { overflow-x:auto; }
        code, pre { font-family:ui-monospace, SFMono-Regular, Menlo, monospace; font-size:12px; }
        pre { background:var(--code); padding:12px; border-radius:6px; overflow:auto; max-height:480px; margin:0; white-space:pre-wrap; word-break:break-word; }
        dl { display:grid; grid-template-columns:max-content 1fr; gap:4px 12px; margin:0; } dt { color:var(--muted); } dd { margin:0; word-break:break-all; }
        .badge { display:inline-block; padding:1px 8px; border-radius:99px; font-size:12px; font-weight:600; background:var(--code); }
        .ok { color:var(--ok); } .bad { color:var(--bad); } .warn { color:var(--warn); } .muted { color:var(--muted); }
        .flash { border-left:4px solid var(--accent); } .flash.error { border-color:var(--bad); }
        .hint { font-size:12px; color:var(--muted); margin:8px 0 0; }
    </style>
</head>
<body>
<header>
    <a href="{{ route('workbench.barion.index') }}">Barion workbench</a>
    <span class="muted">szabrob/laravel-barion</span>
    <form method="post" action="{{ route('workbench.barion.lookup') }}" style="display:flex; gap:6px; margin-left:auto">
        @csrf
        <input name="payment_id" placeholder="paymentId lekérdezése" style="width:280px" required>
        <button style="margin:0">Lekérdez</button>
    </form>
</header>
<main>
    @if (session('info'))
        <div class="card flash">{{ session('info') }}</div>
    @endif
    @if (session('error'))
        <div class="card flash error"><strong class="bad">Hiba</strong><pre style="margin-top:8px">{{ session('error') }}</pre></div>
    @endif
    @if ($errors->any())
        <div class="card flash error"><strong class="bad">Érvénytelen adatok</strong>
            <ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
    @endif
    @if (session('result'))
        <div class="card flash"><h2>{{ session('result.title') }}</h2>
            <pre>{{ json_encode(session('result.data'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>

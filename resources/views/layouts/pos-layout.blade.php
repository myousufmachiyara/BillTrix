<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>POS — {{ config('app.name') }}</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
html, body { height: 100%; margin: 0; background: #f0f2f5; }
.pos-navbar {
    background: #1a1a2e;
    color: #fff;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.pos-navbar .brand { font-weight: 700; font-size: 1.1rem; color: #e0e0ff; }
.pos-navbar .branch-badge { background: #16213e; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; }
.pos-navbar .user-info { font-size: 0.85rem; color: #aaa; }
.pos-navbar a { color: #aaa; text-decoration: none; }
.pos-navbar a:hover { color: #fff; }
</style>
@stack('styles')
</head>
<body>
<nav class="pos-navbar">
    <span class="brand">{{ config('app.name') }} — POS</span>
    <span class="branch-badge">📍 {{ auth()->user()->branch->name ?? 'All Branches' }}</span>
    <div class="d-flex gap-3 align-items-center">
        <a href="{{ route('pos.zreport') }}">Z-Report</a>
        <a href="{{ route('dashboard') }}">← Dashboard</a>
        <span class="user-info">{{ auth()->user()->name }}</span>
    </div>
</nav>

@yield('content')

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
</script>
@stack('scripts')
</body>
</html>

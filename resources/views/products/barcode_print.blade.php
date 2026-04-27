<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Barcode Labels — {{ config('app.name') }}</title>
<style>
/* ── Reset & Base ─────────────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 12px;
    background: #f0f2f5;
    color: #1a1a2e;
}

/* ── Control Bar ─────────────────────────────────────────────────────────── */
.control-bar {
    background: #1a1a2e;
    color: #fff;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 8px rgba(0,0,0,.3);
}
.control-bar .brand {
    font-weight: 700;
    font-size: 15px;
    color: #e94560;
    margin-right: 8px;
}
.control-bar label {
    font-size: 12px;
    color: #adb5bd;
    white-space: nowrap;
}
.control-bar input[type="number"],
.control-bar select {
    background: #16213e;
    border: 1px solid #0f3460;
    color: #fff;
    border-radius: 5px;
    padding: 4px 8px;
    font-size: 12px;
    width: 70px;
}
.control-bar select { width: auto; }
.btn-ctrl {
    padding: 6px 16px;
    border-radius: 6px;
    border: none;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .15s;
}
.btn-ctrl:hover { opacity: .85; }
.btn-print { background: #e94560; color: #fff; }
.btn-close  { background: #495057; color: #fff; }
.btn-select-all { background: #0f3460; color: #fff; }
.separator { width: 1px; height: 24px; background: #2d3748; }
.label-count { font-size: 12px; color: #adb5bd; }

/* ── Sheet Area ──────────────────────────────────────────────────────────── */
.sheet-wrapper {
    padding: 20px;
    min-height: calc(100vh - 56px);
}
.sheet-title {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sheet-title strong { color: #1a1a2e; }

.label-sheet {
    display: flex;
    flex-wrap: wrap;
    gap: var(--gap, 6px);
    padding: 16px;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
}

/* ── Individual Label ────────────────────────────────────────────────────── */
.barcode-label {
    width: var(--lw, 180px);
    height: var(--lh, 90px);
    border: 1.5px solid #dee2e6;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 5px 6px;
    background: #fff;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
    position: relative;
    overflow: hidden;
    page-break-inside: avoid;
}
.barcode-label:hover  { border-color: #e94560; box-shadow: 0 2px 8px rgba(233,69,96,.15); }
.barcode-label.selected { border-color: #e94560; background: #fff8f9; }
.barcode-label.selected::after {
    content: '✓';
    position: absolute;
    top: 3px; right: 5px;
    font-size: 10px;
    color: #e94560;
    font-weight: 700;
}

.label-barcode {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
}
.label-barcode svg,
.label-barcode img {
    max-width: 100%;
    height: 42px;
    width: auto;
}
.label-info {
    width: 100%;
    text-align: center;
    margin-top: 3px;
    border-top: 1px dashed #e9ecef;
    padding-top: 3px;
}
.label-name  { font-size: 9px;  font-weight: 600; color: #1a1a2e; line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
.label-price { font-size: 11px; font-weight: 700; color: #e94560; letter-spacing: .3px; }
.label-sku   { font-size: 8px;  color: #6c757d;  margin-top: 1px; }

/* ── Copies Badge ─────────────────────────────────────────────────────────── */
.copies-input {
    position: absolute;
    bottom: 3px; right: 4px;
    width: 30px;
    background: #f0f2f5;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    font-size: 9px;
    text-align: center;
    padding: 1px 2px;
    color: #495057;
}

/* ── Empty State ─────────────────────────────────────────────────────────── */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}
.empty-state svg { width: 60px; height: 60px; opacity: .3; margin-bottom: 12px; }
.empty-state h5 { font-size: 15px; font-weight: 600; margin-bottom: 6px; }

/* ── Print Styles ─────────────────────────────────────────────────────────── */
@media print {
    .control-bar, .sheet-title { display: none !important; }
    body { background: #fff; }
    .sheet-wrapper { padding: 0; }
    .label-sheet {
        border: none;
        box-shadow: none;
        padding: 4px;
        gap: 4px;
    }
    .barcode-label {
        border: 1px solid #ccc;
        box-shadow: none;
        border-radius: 3px;
    }
    .barcode-label::after,
    .barcode-label.selected::after { display: none; }
    .copies-input { display: none; }
}
</style>
</head>
<body>

{{-- ── Control Bar ── --}}
<div class="control-bar no-print">
    <span class="brand">🧾 {{ config('app.name') }}</span>
    <span class="separator"></span>

    <label>Width (px)</label>
    <input type="number" id="lwInput" value="180" min="100" max="400" step="10" oninput="updateSize()">

    <label>Height (px)</label>
    <input type="number" id="lhInput" value="90"  min="60"  max="300" step="10" oninput="updateSize()">

    <label>Gap (px)</label>
    <input type="number" id="gapInput" value="6" min="0" max="30" step="2" oninput="updateSize()">

    <label>Per Row</label>
    <select id="perRow" onchange="updateSize()">
        <option value="auto" selected>Auto</option>
        @foreach([3,4,5,6,8,10] as $n)
        <option value="{{ $n }}">{{ $n }}</option>
        @endforeach
    </select>

    <span class="separator"></span>
    <button class="btn-ctrl btn-select-all" onclick="selectAll()">Select All</button>
    <span class="label-count" id="labelCount">{{ count($labels) }} labels</span>
    <span class="separator"></span>
    <button class="btn-ctrl btn-print" onclick="printSelected()">🖨 Print</button>
    <button class="btn-ctrl btn-close"  onclick="window.close()">✕ Close</button>
</div>

{{-- ── Sheet ── --}}
<div class="sheet-wrapper">
    <div class="sheet-title no-print">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M2 2h2v2H2zm0 4h2v2H2zm0 4h2v2H2zm4-8h2v2H6zm0 4h2v2H6zm0 4h2v2H6zm4-8h2v2h-2zm0 4h2v2h-2zm0 4h2v2h-2z"/>
        </svg>
        <span>Click labels to select · <strong id="selectedCount">0</strong> selected · Adjust size with controls above</span>
    </div>

    @if(count($labels) > 0)
    <div class="label-sheet" id="labelSheet">
        @foreach($labels as $idx => $label)
        @php $copies = $label['copies'] ?? 1; @endphp
        @for($i = 0; $i < $copies; $i++)
        <div class="barcode-label selected" data-idx="{{ $idx }}" onclick="toggleLabel(this)">
            <div class="label-barcode">
                {!! $label['barcode_svg'] ?? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 40"><rect x="5" y="2" width="3" height="36" fill="#000"/><rect x="10" y="2" width="2" height="36" fill="#000"/><rect x="14" y="2" width="4" height="36" fill="#000"/><rect x="20" y="2" width="2" height="36" fill="#000"/><rect x="24" y="2" width="3" height="36" fill="#000"/><rect x="29" y="2" width="2" height="36" fill="#000"/><rect x="33" y="2" width="4" height="36" fill="#000"/><rect x="39" y="2" width="2" height="36" fill="#000"/><rect x="43" y="2" width="3" height="36" fill="#000"/><rect x="48" y="2" width="2" height="36" fill="#000"/><rect x="52" y="2" width="4" height="36" fill="#000"/><rect x="58" y="2" width="2" height="36" fill="#000"/><rect x="62" y="2" width="3" height="36" fill="#000"/><rect x="67" y="2" width="4" height="36" fill="#000"/><rect x="73" y="2" width="2" height="36" fill="#000"/><rect x="77" y="2" width="3" height="36" fill="#000"/><rect x="82" y="2" width="2" height="36" fill="#000"/><rect x="86" y="2" width="4" height="36" fill="#000"/><rect x="92" y="2" width="3" height="36" fill="#000"/></svg>' !!}
            </div>
            <div class="label-info">
                <div class="label-name" title="{{ $label['name'] }}">{{ $label['name'] }}</div>
                <div class="label-price">PKR {{ number_format($label['price'], 2) }}</div>
                <div class="label-sku">{{ $label['sku'] }}</div>
            </div>
        </div>
        @endfor
        @endforeach
    </div>
    @else
    <div class="empty-state no-print">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M6 2H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2h-2"/>
            <path d="M6 2v4h12V2"/>
        </svg>
        <h5>No Labels to Print</h5>
        <p>Go to Products → select items → click Print Barcodes</p>
    </div>
    @endif
</div>

<script>
// ── Size controls ─────────────────────────────────────────────────────────
function updateSize() {
    const lw  = document.getElementById('lwInput').value  + 'px';
    const lh  = document.getElementById('lhInput').value  + 'px';
    const gap = document.getElementById('gapInput').value + 'px';
    const pr  = document.getElementById('perRow').value;
    const sheet = document.getElementById('labelSheet');
    if (!sheet) return;
    sheet.style.setProperty('--lw', lw);
    sheet.style.setProperty('--lh', lh);
    sheet.style.setProperty('--gap', gap);
    if (pr === 'auto') {
        sheet.style.justifyContent = '';
    } else {
        const w = parseInt(document.getElementById('lwInput').value);
        const g = parseInt(document.getElementById('gapInput').value);
        sheet.style.setProperty('--lw', w + 'px');
    }
    document.querySelectorAll('.barcode-label').forEach(el => {
        el.style.width  = lw;
        el.style.height = lh;
    });
}

// ── Select / deselect ─────────────────────────────────────────────────────
function toggleLabel(el) {
    el.classList.toggle('selected');
    updateSelectedCount();
}

function selectAll() {
    const labels = document.querySelectorAll('.barcode-label');
    const allSelected = [...labels].every(l => l.classList.contains('selected'));
    labels.forEach(l => allSelected ? l.classList.remove('selected') : l.classList.add('selected'));
    updateSelectedCount();
    document.querySelector('.btn-select-all').textContent = allSelected ? 'Select All' : 'Deselect All';
}

function updateSelectedCount() {
    const n = document.querySelectorAll('.barcode-label.selected').length;
    document.getElementById('selectedCount').textContent = n;
}

// ── Print only selected ───────────────────────────────────────────────────
function printSelected() {
    // Hide non-selected labels before printing
    document.querySelectorAll('.barcode-label').forEach(el => {
        if (!el.classList.contains('selected')) {
            el.style.display = 'none';
        }
    });
    window.print();
    // Restore after print
    setTimeout(() => {
        document.querySelectorAll('.barcode-label').forEach(el => {
            el.style.display = '';
        });
    }, 500);
}

// ── Init ──────────────────────────────────────────────────────────────────
updateSelectedCount();
</script>
</body>
</html>
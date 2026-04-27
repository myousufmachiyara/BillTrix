<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>POS Terminal — {{ $branch->name }}</title>
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/all.min.css') }}">
<style>
* { box-sizing: border-box; }
body { background: #f1f5f9; color: #1e293b; font-family: 'Segoe UI', sans-serif; margin: 0; height: 100vh; overflow: hidden; }

/* ── Top Bar ── */
.pos-topbar { background: #fff; padding: 8px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #e2e8f0; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.pos-topbar .branch-name { font-size: 18px; font-weight: 700; color: #dc2626; }
.pos-topbar .clock { font-size: 16px; color: #64748b; font-weight: 500; }
.pos-topbar .user { font-size: 13px; color: #64748b; }
.pos-topbar a { color: #94a3b8; text-decoration: none; }
.pos-topbar a:hover { color: #dc2626; }

/* ── Layout ── */
.pos-layout { display: flex; height: calc(100vh - 52px); }
.pos-left  { flex: 1 1 62%; display: flex; flex-direction: column; padding: 12px; overflow: hidden; background: #f1f5f9; }
.pos-right { flex: 0 0 38%; background: #fff; display: flex; flex-direction: column; border-left: 2px solid #e2e8f0; box-shadow: -2px 0 8px rgba(0,0,0,.04); }

/* ── Search ── */
.search-bar { position: relative; margin-bottom: 10px; }
.search-bar input { background: #fff; border: 2px solid #e2e8f0; color: #1e293b; border-radius: 8px; padding: 11px 16px 11px 44px; font-size: 16px; width: 100%; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
.search-bar input:focus { border-color: #dc2626; outline: none; box-shadow: 0 0 0 3px rgba(220,38,38,.1); }
.search-bar .scan-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #dc2626; font-size: 18px; }
.search-bar input::placeholder { color: #94a3b8; }

/* ── Category Tabs ── */
.cat-tabs { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 6px; margin-bottom: 10px; scrollbar-width: none; }
.cat-tab { flex-shrink: 0; padding: 6px 16px; border-radius: 20px; background: #fff; color: #64748b; border: 1.5px solid #e2e8f0; cursor: pointer; font-size: 13px; font-weight: 500; transition: all .2s; white-space: nowrap; }
.cat-tab.active, .cat-tab:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

/* ── Product Grid ── */
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; overflow-y: auto; flex: 1; padding-right: 4px; }
.product-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 10px; text-align: center; cursor: pointer; transition: all .2s; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.product-card:hover { border-color: #dc2626; box-shadow: 0 4px 12px rgba(220,38,38,.12); transform: translateY(-2px); }
.product-card.out-of-stock { opacity: .45; cursor: not-allowed; }
.product-card .p-name { font-size: 12px; font-weight: 600; color: #1e293b; margin-bottom: 4px; line-height: 1.3; }
.product-card .p-var  { font-size: 11px; color: #94a3b8; margin-bottom: 6px; }
.product-card .p-price { font-size: 15px; font-weight: 700; color: #dc2626; }
.product-card .p-stock { font-size: 10px; color: #94a3b8; margin-top: 3px; }
.product-card .p-icon { font-size: 26px; margin-bottom: 8px; color: #cbd5e1; }

/* ── Right Panel ── */
.cart-header { padding: 14px 16px; background: #fff; border-bottom: 1.5px solid #e2e8f0; }
.cart-header h5 { margin: 0; font-size: 16px; color: #1e293b; font-weight: 700; }

.customer-select { padding: 10px 14px; border-bottom: 1.5px solid #f1f5f9; background: #f8fafc; }
.customer-select select { background: #fff; border: 1.5px solid #e2e8f0; color: #1e293b; padding: 7px 10px; border-radius: 6px; width: 100%; font-size: 13px; }
.customer-select select:focus { border-color: #dc2626; outline: none; }

.cart-table-wrap { flex: 1; overflow-y: auto; }
.cart-table { width: 100%; font-size: 13px; }
.cart-table thead th { background: #f8fafc; padding: 8px 10px; color: #64748b; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; position: sticky; top: 0; border-bottom: 1.5px solid #e2e8f0; }
.cart-table tbody td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.cart-table tbody tr:hover { background: #fef2f2; }
.qty-btn { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; width: 26px; height: 26px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 700; transition: background .15s; }
.qty-btn:hover { background: #dc2626; color: #fff; border-color: #dc2626; }
.qty-input { background: #fff; border: 1px solid #e2e8f0; color: #1e293b; width: 50px; text-align: center; border-radius: 4px; padding: 2px; }
.remove-btn { background: none; border: none; color: #fca5a5; cursor: pointer; font-size: 14px; transition: color .15s; }
.remove-btn:hover { color: #dc2626; }

.cart-totals { padding: 12px 16px; border-top: 2px solid #f1f5f9; background: #f8fafc; }
.total-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 14px; color: #64748b; }
.total-row.grand { font-size: 22px; font-weight: 700; color: #1e293b; border-top: 2px solid #e2e8f0; padding-top: 10px; margin-top: 6px; }
.total-row.grand span:last-child { color: #dc2626; }
#discountInput { background: #fff; border: 1.5px solid #e2e8f0; color: #1e293b; border-radius: 4px; padding: 2px 6px; text-align: right; width: 70px; }

.payment-btn { width: 100%; padding: 16px; font-size: 18px; font-weight: 700; background: linear-gradient(135deg,#dc2626,#b91c1c); color: #fff; border: none; cursor: pointer; transition: opacity .2s; letter-spacing: .5px; }
.payment-btn:hover { opacity: .92; }
.shortcuts-bar { display: flex; gap: 6px; padding: 8px 14px; border-top: 1.5px solid #e2e8f0; background: #f8fafc; }
.shortcut-btn { flex: 1; padding: 6px 4px; font-size: 11px; background: #fff; color: #64748b; border: 1.5px solid #e2e8f0; border-radius: 6px; cursor: pointer; text-align: center; transition: all .15s; font-weight: 500; }
.shortcut-btn:hover { background: #f1f5f9; color: #1e293b; border-color: #cbd5e1; }

/* Empty cart state */
#emptyCart td { color: #94a3b8; padding: 40px 0 !important; }

/* ── Payment Modal ── */
.payment-modal { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
.payment-modal.show { display: flex; }
.payment-box { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px; width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
.payment-box h4 { color: #1e293b; margin-bottom: 20px; font-weight: 700; }
.payment-method-btn { padding: 10px; border: 2px solid #e2e8f0; background: #f8fafc; color: #64748b; border-radius: 8px; cursor: pointer; text-align: center; transition: all .2s; font-weight: 500; }
.payment-method-btn.active { border-color: #dc2626; color: #dc2626; background: #fef2f2; }
.cash-input { background: #f8fafc; border: 2px solid #e2e8f0; color: #1e293b; font-size: 24px; padding: 10px; border-radius: 8px; width: 100%; margin: 12px 0; text-align: right; font-weight: 700; }
.cash-input:focus { border-color: #dc2626; outline: none; }
.change-display { background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 12px; text-align: center; margin-bottom: 16px; }
.change-amount { font-size: 28px; font-weight: 700; color: #16a34a; }
</style>
</head>
<body>

{{-- Top Bar --}}
<div class="pos-topbar">
    <div class="branch-name"><i class="fas fa-store me-2"></i>{{ $branch->name }} — POS</div>
    <div class="clock" id="posTime"></div>
    <div class="user"><i class="fas fa-user me-1"></i>{{ auth()->user()->name }} &nbsp; <a href="{{ route('dashboard') }}" class="text-muted" style="font-size:12px;"><i class="fas fa-arrow-left"></i> Back</a></div>
</div>

<div class="pos-layout">
    {{-- Left: Product Panel --}}
    <div class="pos-left">
        <div class="search-bar">
            <i class="fas fa-barcode scan-icon"></i>
            <input type="text" id="posScanner" placeholder="Scan barcode or type to search... (F1 to focus)" autocomplete="off" autofocus>
        </div>
        <div class="cat-tabs">
            <div class="cat-tab active" data-cat="all">All</div>
            @foreach($categories as $cat)
            <div class="cat-tab" data-cat="{{ $cat->id }}">{{ $cat->name }}</div>
            @endforeach
        </div>
        <div class="product-grid" id="productGrid">
            {{-- Populated by JS --}}
        </div>
    </div>

    {{-- Right: Cart Panel --}}
    <div class="pos-right d-flex flex-column">
        <div class="cart-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-shopping-cart me-2"></i>Cart <span class="badge bg-danger ms-1" id="cartCount">0</span></h5>
            <button class="btn btn-sm btn-outline-danger" onclick="clearCart()"><i class="fas fa-trash"></i></button>
        </div>

        <div class="customer-select">
            <select id="customerSelect">
                <option value="{{ $cashAccount->id ?? 0 }}">Walk-in Customer</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="cart-table-wrap">
            <table class="cart-table">
                <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr></thead>
                <tbody id="cartBody">
                    <tr id="emptyCart"><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-shopping-cart fa-2x mb-2"></i><br>Cart is empty</td></tr>
                </tbody>
            </table>
        </div>

        <div class="cart-totals">
            <div class="total-row"><span>Subtotal</span><span id="subtotalDisplay">0.00</span></div>
            <div class="total-row">
                <span>Discount</span>
                <span><input type="number" id="discountInput" value="0" min="0" style="width:70px;background:#0d1b2a;border:1px solid #0f3460;color:#fff;border-radius:4px;padding:2px 6px;text-align:right;" oninput="recalculate()"> PKR</span>
            </div>
            <div class="total-row"><span>Tax</span><span id="taxDisplay">0.00</span></div>
            <div class="total-row grand"><span>TOTAL</span><span id="grandTotalDisplay">PKR 0.00</span></div>
        </div>

        <div class="shortcuts-bar">
            <div class="shortcut-btn" onclick="holdCart()"><i class="fas fa-pause"></i><br>Hold [F3]</div>
            <div class="shortcut-btn" onclick="recallCart()"><i class="fas fa-play"></i><br>Recall</div>
            <div class="shortcut-btn" onclick="window.open('{{ route('pos.zreport') }}','_blank')"><i class="fas fa-chart-bar"></i><br>Z-Report</div>
            <div class="shortcut-btn" onclick="clearCart()"><i class="fas fa-times"></i><br>Cancel</div>
        </div>

        <button class="payment-btn" onclick="openPayment()" id="payBtn"><i class="fas fa-credit-card me-2"></i>PAYMENT [F2]</button>
    </div>
</div>

{{-- Payment Modal --}}
<div class="payment-modal" id="paymentModal">
    <div class="payment-box">
        <h4><i class="fas fa-money-bill-wave me-2 text-success"></i>Process Payment</h4>
        <div class="d-flex gap-2 mb-3">
            @foreach(['cash'=>'Cash','card'=>'Card','credit'=>'Credit'] as $k=>$v)
            <div class="payment-method-btn {{ $k=='cash'?'active':'' }}" data-method="{{ $k }}" onclick="selectMethod('{{ $k }}')">
                <i class="fas fa-{{ $k=='cash'?'money-bill':'credit-card' }} d-block mb-1"></i>{{ $v }}
            </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-between mb-2">
            <span style="color:#a0a0b0;">Grand Total:</span>
            <strong id="modalTotal" style="color:#e94560;font-size:20px;">0.00</strong>
        </div>
        <input type="number" class="cash-input" id="cashTendered" placeholder="Amount Received" oninput="calcChange()">
        <div class="change-display">
            <div style="color:#a0a0b0;font-size:12px;margin-bottom:4px;">Change Due</div>
            <div class="change-amount" id="changeDisplay">0.00</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success flex-fill py-3 fw-bold" id="confirmPayBtn" onclick="confirmPayment()" style="font-size:16px;"><i class="fas fa-check me-2"></i>CONFIRM</button>
            <button class="btn btn-outline-secondary flex-fill" onclick="closePayment()">Cancel</button>
        </div>
    </div>
</div>

<script src="{{ asset('assets/vendor/jquery/jquery.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ── State ──────────────────────────────────────────────────────────────────────
let cart       = [];
let allProducts= [];
let currentCat = 'all';
let holdSlots  = {};
let payMethod  = 'cash';
const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
const BRANCH_ID = {{ $branch->id ?? 0 }};

// ── Clock ─────────────────────────────────────────────────────────────────────
function tick() { document.getElementById('posTime').textContent = new Date().toLocaleTimeString('en-PK',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); }
setInterval(tick,1000); tick();

// ── Keyboard Shortcuts ────────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'F1') { e.preventDefault(); document.getElementById('posScanner').focus(); }
    if (e.key === 'F2') { e.preventDefault(); openPayment(); }
    if (e.key === 'F3') { e.preventDefault(); holdCart(); }
    if (e.key === 'Escape') closePayment();
});

// ── Load Products ─────────────────────────────────────────────────────────────
async function loadProducts(q='') {
    const url = `/pos/search?q=${encodeURIComponent(q)}`;
    const res  = await fetch(url, { headers: { 'X-CSRF-TOKEN': CSRF } });
    allProducts = await res.json();
    renderProducts();
}

function renderProducts() {
    const grid = document.getElementById('productGrid');
    const filtered = currentCat === 'all' ? allProducts : allProducts.filter(p=>p.category_id==currentCat);
    if (!filtered.length) { grid.innerHTML = '<div class="text-center text-muted p-4 col-span-all" style="grid-column:1/-1">No products found</div>'; return; }
    grid.innerHTML = filtered.map(p => `
        <div class="product-card ${p.stock <= 0 ? 'out-of-stock' : ''}" onclick="${p.stock > 0 ? `addToCart(${JSON.stringify(p).replace(/"/g,"'")})` : ''}">
            <div class="p-icon"><i class="fas fa-box"></i></div>
            <div class="p-name">${p.product_name}</div>
            <div class="p-var">${p.variation_name||p.sku}</div>
            <div class="p-price">PKR ${parseFloat(p.sale_price).toFixed(0)}</div>
            <div class="p-stock">Stock: ${parseFloat(p.stock).toFixed(0)}</div>
        </div>`).join('');
}

// ── Category Filter ────────────────────────────────────────────────────────────
document.querySelectorAll('.cat-tab').forEach(t => t.addEventListener('click', function(){
    document.querySelectorAll('.cat-tab').forEach(x=>x.classList.remove('active'));
    this.classList.add('active');
    currentCat = this.dataset.cat;
    renderProducts();
}));

// ── Scanner ────────────────────────────────────────────────────────────────────
let scanBuffer = '';
let scanTimer;
const scannerEl = document.getElementById('posScanner');
scannerEl.addEventListener('keydown', async function(e) {
    if (e.key === 'Enter') {
        const code = this.value.trim();
        if (code) {
            const res  = await fetch(`/api/barcode/${encodeURIComponent(code)}`);
            const data = await res.json();
            if (data.success) addToCart(data.variation);
            else { loadProducts(code); }
        }
        this.value = '';
        return;
    }
    clearTimeout(scanTimer);
    scanTimer = setTimeout(() => loadProducts(this.value), 300);
});

// ── Cart ────────────────────────────────────────────────────────────────────────
function addToCart(p) {
    const existing = cart.find(i => i.variation_id == p.id);
    if (existing) { existing.qty++; }
    else { cart.push({ variation_id: p.id, item_id: p.product_id, product_name: p.product_name, variation_name: p.variation_name||p.sku, price: parseFloat(p.sale_price), qty: 1, cost_price: parseFloat(p.cost_price||0), stock: parseFloat(p.stock) }); }
    renderCart();
}

function renderCart() {
    const tbody  = document.getElementById('cartBody');
    const empty  = document.getElementById('emptyCart');
    if (!cart.length) { tbody.innerHTML = ''; tbody.appendChild(empty); document.getElementById('cartCount').textContent='0'; recalculate(); return; }

    tbody.innerHTML = cart.map((item,i) => `
        <tr>
            <td><div style="font-size:12px;font-weight:600;">${item.product_name}</div><div style="font-size:11px;color:#6b7280;">${item.variation_name}</div></td>
            <td>
                <div style="display:flex;align-items:center;gap:4px;">
                    <button class="qty-btn" onclick="changeQty(${i},-1)">−</button>
                    <input class="qty-input" type="number" value="${item.qty}" onchange="setQty(${i},this.value)" min="1">
                    <button class="qty-btn" onclick="changeQty(${i},1)">+</button>
                </div>
            </td>
            <td style="color:#e94560;">${item.price.toFixed(2)}</td>
            <td style="font-weight:600;">${(item.qty*item.price).toFixed(2)}</td>
            <td><button class="remove-btn" onclick="removeItem(${i})"><i class="fas fa-times"></i></button></td>
        </tr>`).join('');

    document.getElementById('cartCount').textContent = cart.reduce((s,i)=>s+i.qty,0);
    recalculate();
}

function changeQty(i, delta) { cart[i].qty = Math.max(1, cart[i].qty + delta); renderCart(); }
function setQty(i, v) { cart[i].qty = Math.max(1, parseInt(v)||1); renderCart(); }
function removeItem(i) { cart.splice(i,1); renderCart(); }
function clearCart() { cart=[]; renderCart(); }

function recalculate() {
    const subtotal  = cart.reduce((s,i)=>s+i.qty*i.price,0);
    const discount  = parseFloat(document.getElementById('discountInput').value)||0;
    const tax       = 0;
    const grand     = Math.max(0, subtotal - discount + tax);
    document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
    document.getElementById('taxDisplay').textContent      = tax.toFixed(2);
    document.getElementById('grandTotalDisplay').textContent = 'PKR ' + grand.toFixed(2);
    document.getElementById('modalTotal').textContent = grand.toFixed(2);
    document.getElementById('cashTendered').placeholder = grand.toFixed(2);
}

// ── Hold / Recall ──────────────────────────────────────────────────────────────
function holdCart() {
    if (!cart.length) return;
    const id = Date.now();
    holdSlots[id] = [...cart];
    cart = [];
    renderCart();
    alert(`Cart held (ID: ${id}). Use Recall to restore.`);
}
function recallCart() {
    const ids = Object.keys(holdSlots);
    if (!ids.length) { alert('No held carts.'); return; }
    const id = ids[ids.length-1];
    cart = holdSlots[id];
    delete holdSlots[id];
    renderCart();
}

// ── Payment ────────────────────────────────────────────────────────────────────
function openPayment() {
    if (!cart.length) { alert('Cart is empty.'); return; }
    recalculate();
    document.getElementById('paymentModal').classList.add('show');
    setTimeout(()=>document.getElementById('cashTendered').focus(),100);
}
function closePayment() { document.getElementById('paymentModal').classList.remove('show'); }
function selectMethod(m) {
    payMethod = m;
    document.querySelectorAll('.payment-method-btn').forEach(b=>b.classList.remove('active'));
    document.querySelector(`[data-method="${m}"]`).classList.add('active');
}
function calcChange() {
    const grand   = parseFloat(document.getElementById('modalTotal').textContent)||0;
    const tendered= parseFloat(document.getElementById('cashTendered').value)||0;
    document.getElementById('changeDisplay').textContent = Math.max(0, tendered - grand).toFixed(2);
}

async function confirmPayment() {
    const grand     = parseFloat(document.getElementById('modalTotal').textContent)||0;
    const tendered  = parseFloat(document.getElementById('cashTendered').value)||grand;
    const customerId= document.getElementById('customerSelect').value;
    const discount  = parseFloat(document.getElementById('discountInput').value)||0;
    const btn       = document.getElementById('confirmPayBtn');

    if (tendered < grand && payMethod === 'cash') { alert('Insufficient cash amount.'); return; }

    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

    const payload = {
        customer_id    : customerId,
        branch_id      : BRANCH_ID,
        payment_method : payMethod,
        amount_paid    : tendered,
        net_amount     : grand,
        discount_amount: discount,
        items          : cart.map(i=>({ item_id: i.item_id, variation_id: i.variation_id, quantity: i.qty, price: i.price, cost_price: i.cost_price }))
    };

    try {
        const res  = await fetch('/pos/payment', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body:JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) {
            closePayment();
            window.open(`/pos/receipt/${data.invoice_id}`,'_blank','width=400,height=600');
            clearCart();
            document.getElementById('discountInput').value = 0;
        } else {
            alert('Error: ' + (data.message || 'Payment failed'));
        }
    } catch(e) {
        alert('Network error. Please try again.');
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-check me-2"></i>CONFIRM';
    }
}

// ── Init ────────────────────────────────────────────────────────────────────────
loadProducts();
</script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BillTrix POS Terminal</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/css/select2.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; height: 100vh; overflow: hidden; }

        /* Layout */
        .pos-wrap {
            display: grid;
            grid-template-columns: 1fr 390px;
            grid-template-rows: 52px 1fr;
            height: 100vh;
        }

        /* Header */
        .pos-header {
            grid-column: 1 / -1;
            background: #1e3a5f;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            font-size: .875rem;
        }
        .pos-header .pos-logo { font-weight: 700; font-size: 1rem; letter-spacing: .5px; }
        .pos-header .pos-info { opacity: .7; font-size: .78rem; }
        .offline-pill {
            background: #dc3545; color: #fff; font-size: .72rem;
            padding: .2rem .55rem; border-radius: 12px; display: none;
        }
        .offline-pill.show { display: inline-block; }

        /* Left – Products */
        .pos-left {
            display: flex;
            flex-direction: column;
            background: #fff;
            border-right: 1px solid #dee2e6;
            overflow: hidden;
        }
        .pos-search { padding: .6rem; border-bottom: 1px solid #eee; }
        .pos-search input { font-size: .95rem; }
        .cat-bar {
            display: flex;
            gap: .35rem;
            padding: .5rem .6rem;
            border-bottom: 1px solid #eee;
            overflow-x: auto;
            white-space: nowrap;
        }
        .cat-bar::-webkit-scrollbar { height: 4px; }
        .cat-bar button { font-size: .75rem; padding: .2rem .6rem; white-space: nowrap; }

        .product-grid {
            flex: 1;
            overflow-y: auto;
            padding: .6rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: .45rem;
            align-content: start;
        }
        .product-tile {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: .55rem .4rem;
            cursor: pointer;
            text-align: center;
            transition: border-color .15s, box-shadow .15s, transform .1s;
        }
        .product-tile:hover {
            border-color: #0d6efd;
            box-shadow: 0 2px 8px rgba(13,110,253,.15);
            transform: translateY(-1px);
        }
        .product-tile .pt-name { font-size: .75rem; font-weight: 600; line-height: 1.3; margin-bottom: .2rem; }
        .product-tile .pt-price { font-size: .82rem; color: #0d6efd; font-weight: 700; }
        .product-tile .pt-sku { font-size: .65rem; color: #aaa; }

        /* Right – Cart */
        .pos-right {
            display: flex;
            flex-direction: column;
            background: #fff;
        }
        .cart-header {
            padding: .6rem .75rem;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }
        .cart-items { flex: 1; overflow-y: auto; }
        .cart-item {
            display: flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem .75rem;
            border-bottom: 1px solid #f5f5f5;
        }
        .cart-item:hover { background: #fafafa; }
        .cart-item .ci-info { flex: 1; min-width: 0; }
        .cart-item .ci-name { font-size: .8rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cart-item .ci-price { font-size: .78rem; color: #0d6efd; }
        .cart-item .ci-qty {
            display: flex; align-items: center; gap: .25rem;
        }
        .cart-item .ci-qty button {
            width: 26px; height: 26px; border: 1px solid #dee2e6;
            background: #fff; border-radius: 4px; cursor: pointer; font-weight: bold;
            font-size: .8rem; line-height: 1;
        }
        .cart-item .ci-qty button:hover { background: #f0f0f0; }
        .cart-item .ci-qty input {
            width: 46px; text-align: center;
            border: 1px solid #dee2e6; border-radius: 4px;
            padding: .18rem .2rem; font-size: .8rem;
        }

        /* Cart Footer */
        .cart-footer {
            padding: .65rem .75rem;
            border-top: 1px solid #eee;
            background: #f8f9fa;
        }
        .totals-table { width: 100%; font-size: .85rem; margin-bottom: .6rem; }
        .totals-table td { padding: .15rem 0; }
        .totals-table .total-row { font-size: 1rem; font-weight: 700; color: #0d6efd; border-top: 1px solid #dee2e6; }
        .pay-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .35rem;
            margin-bottom: .55rem;
        }
        .pay-btn {
            padding: .35rem; border: 1px solid #dee2e6;
            border-radius: 5px; background: #fff; cursor: pointer;
            font-size: .75rem; text-align: center; transition: all .15s;
        }
        .pay-btn.selected { border-color: #0d6efd; background: #e7f1ff; color: #0d6efd; font-weight: 700; }

        @media (max-width: 768px) {
            .pos-wrap { grid-template-columns: 1fr; }
            .pos-left { display: none; }
        }
    </style>
</head>
<body>

<div class="pos-wrap">

    {{-- Header --}}
    <div class="pos-header">
        <div>
            <span class="pos-logo">BillTrix POS</span>
            <span class="pos-info ms-3">{{ session('branch_name','Main Branch') }}</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="offline-pill" id="offlinePill"><i class="fas fa-wifi-slash me-1"></i>Offline</span>
            <span class="pos-info" id="posClockDisplay"></span>
            <span class="pos-info">{{ auth()->user()->name ?? '' }}</span>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-light">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    {{-- Products --}}
    <div class="pos-left">
        <div class="pos-search">
            <input type="text" id="productSearch" class="form-control"
                   placeholder="Search name, SKU, or scan barcode…" autofocus>
        </div>
        <div class="cat-bar">
            <button class="btn btn-primary btn-sm cat-btn" data-cat="">All</button>
            @foreach($categories ?? [] as $cat)
            <button class="btn btn-outline-secondary btn-sm cat-btn" data-cat="{{ $cat->id }}">{{ $cat->name }}</button>
            @endforeach
        </div>
        <div class="product-grid" id="productGrid">
            @foreach($products ?? [] as $prod)
            <div class="product-tile"
                 data-id="{{ $prod->id }}"
                 data-name="{{ $prod->name }}"
                 data-price="{{ $prod->sale_price }}"
                 data-sku="{{ $prod->sku }}"
                 data-cat="{{ $prod->category_id }}"
                 data-tax="{{ $prod->tax_rate }}"
                 onclick="addToCart(this)">
                <div class="pt-name">{{ Str::limit($prod->name, 28) }}</div>
                <div class="pt-price">{{ number_format($prod->sale_price, 2) }}</div>
                <div class="pt-sku">{{ $prod->sku }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Cart --}}
    <div class="pos-right">
        <div class="cart-header">
            <strong class="small"><i class="fas fa-shopping-cart me-1"></i>Cart</strong>
            <select class="form-select form-select-sm" id="cartCustomer" style="max-width:160px">
                <option value="">Walk-in Customer</option>
                @foreach($customers ?? [] as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-danger" onclick="clearCart()" title="Clear cart">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="text-center text-muted py-5" id="emptyCartMsg">
                <i class="fas fa-shopping-basket fa-2x mb-2 d-block"></i>Cart is empty
            </div>
        </div>

        <div class="cart-footer">
            {{-- Promo --}}
            <div class="input-group input-group-sm mb-2">
                <input type="text" id="promoInput" class="form-control" placeholder="Promo code…">
                <button class="btn btn-outline-secondary" onclick="applyPromo()">Apply</button>
            </div>

            <table class="totals-table">
                <tr><td class="text-muted">Subtotal</td><td class="text-end" id="dispSubtotal">0.00</td></tr>
                <tr><td class="text-muted">Discount</td><td class="text-end text-danger" id="dispDiscount">-0.00</td></tr>
                <tr><td class="text-muted">Tax</td><td class="text-end" id="dispTax">0.00</td></tr>
                <tr class="total-row"><td>Total</td><td class="text-end" id="dispTotal">0.00</td></tr>
            </table>

            <div class="pay-grid">
                @foreach(['cash'=>'<i class="fas fa-money-bill-wave"></i> Cash','card'=>'<i class="fas fa-credit-card"></i> Card','bank_transfer'=>'<i class="fas fa-university"></i> Bank','cheque'=>'<i class="fas fa-check"></i> Cheque'] as $pm => $pml)
                <button class="pay-btn {{ $pm==='cash'?'selected':'' }}" data-method="{{ $pm }}"
                        onclick="selectPayMethod(this)">{!! $pml !!}</button>
                @endforeach
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary flex-fill btn-sm" onclick="holdTransaction()">
                    <i class="fas fa-pause me-1"></i>Hold
                </button>
                <button class="btn btn-primary fw-bold flex-fill btn-sm" id="checkoutBtn" onclick="processCheckout()">
                    <i class="fas fa-check me-1"></i>Checkout
                </button>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });

let cart = [];
let selectedPayMethod = 'cash';
let activePromo = null;

// Clock
setInterval(() => {
    document.getElementById('posClockDisplay').textContent = new Date().toLocaleTimeString();
}, 1000);

// Connectivity
window.addEventListener('offline', () => document.getElementById('offlinePill').classList.add('show'));
window.addEventListener('online',  () => {
    document.getElementById('offlinePill').classList.remove('show');
    syncOffline();
});
if (!navigator.onLine) document.getElementById('offlinePill').classList.add('show');

// Category filter
document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.cat-btn').forEach(b => {
            b.classList.remove('active','btn-primary');
            b.classList.add('btn-outline-secondary');
        });
        this.classList.add('active','btn-primary');
        this.classList.remove('btn-outline-secondary');
        const cat = this.dataset.cat;
        document.querySelectorAll('.product-tile').forEach(t => {
            t.style.display = (!cat || t.dataset.cat === cat) ? '' : 'none';
        });
    });
});

// Product search
$('#productSearch').on('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.product-tile').forEach(t => {
        t.style.display = (t.dataset.name.toLowerCase().includes(q) || t.dataset.sku.toLowerCase().includes(q)) ? '' : 'none';
    });
});

// Add to cart
function addToCart(tile) {
    const id    = tile.dataset.id;
    const name  = tile.dataset.name;
    const price = parseFloat(tile.dataset.price);
    const tax   = parseFloat(tile.dataset.tax) || 0;
    const existing = cart.find(i => i.id === id);
    if (existing) { existing.qty++; }
    else { cart.push({ id, name, price, tax, qty: 1 }); }
    renderCart();
}

function renderCart() {
    const $items = $('#cartItems');
    if (!cart.length) {
        $items.html('<div class="text-center text-muted py-5" id="emptyCartMsg"><i class="fas fa-shopping-basket fa-2x mb-2 d-block"></i>Cart is empty</div>');
        updateTotals(); return;
    }
    let html = '';
    cart.forEach((item, i) => {
        const lineTotal = (item.qty * item.price).toFixed(2);
        html += `<div class="cart-item">
            <div class="ci-info">
                <div class="ci-name">${item.name}</div>
                <div class="ci-price">${lineTotal}</div>
            </div>
            <div class="ci-qty">
                <button onclick="changeQty(${i},-1)">−</button>
                <input type="number" min="0.01" step="any" value="${item.qty}" onchange="setQty(${i},this.value)">
                <button onclick="changeQty(${i},1)">+</button>
                <button onclick="removeItem(${i})" style="color:#dc3545">✕</button>
            </div>
        </div>`;
    });
    $items.html(html);
    updateTotals();
}

function changeQty(i, d) { cart[i].qty = Math.max(0.01, parseFloat(cart[i].qty) + d); renderCart(); }
function setQty(i, v)    { cart[i].qty = Math.max(0.01, parseFloat(v) || 1); renderCart(); }
function removeItem(i)   { cart.splice(i, 1); renderCart(); }
function clearCart()     { cart = []; activePromo = null; renderCart(); }

function updateTotals() {
    let sub = 0, tax = 0;
    cart.forEach(item => { sub += item.qty * item.price; tax += item.qty * item.price * item.tax / 100; });
    const disc  = activePromo ? (activePromo.type === 'percentage' ? sub * activePromo.value / 100 : activePromo.value) : 0;
    const total = sub - disc + tax;
    $('#dispSubtotal').text(sub.toFixed(2));
    $('#dispDiscount').text('-' + disc.toFixed(2));
    $('#dispTax').text(tax.toFixed(2));
    $('#dispTotal').text(total.toFixed(2));
}

function selectPayMethod(btn) {
    document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    selectedPayMethod = btn.dataset.method;
}

function applyPromo() {
    const code = $('#promoInput').val().trim();
    if (!code) return;
    $.get('/pos/promo/' + encodeURIComponent(code), res => {
        if (res.success) { activePromo = res.promo; updateTotals(); alert('Promo applied!'); }
        else alert(res.message || 'Invalid promo.');
    });
}

function holdTransaction() {
    if (!cart.length) { alert('Cart is empty.'); return; }
    const held = JSON.parse(localStorage.getItem('billtrix_held') || '[]');
    held.push({ cart: [...cart], ts: new Date().toISOString() });
    localStorage.setItem('billtrix_held', JSON.stringify(held));
    clearCart();
    alert('Transaction held.');
}

function processCheckout() {
    if (!cart.length) { alert('Cart is empty.'); return; }
    const total      = parseFloat($('#dispTotal').text());
    const customerId = $('#cartCustomer').val();
    const payload = {
        customer_id:    customerId || null,
        payment_method: selectedPayMethod,
        promo_code:     activePromo?.code || null,
        total_amount:   total,
        items: cart.map(i => ({
            product_id: i.id, qty: i.qty, unit_price: i.price,
            discount_pct: 0, tax_rate: i.tax
        }))
    };

    if (!navigator.onLine) {
        const offline = JSON.parse(localStorage.getItem('billtrix_offline_tx') || '[]');
        offline.push({ ...payload, is_offline: true, created_at: new Date().toISOString() });
        localStorage.setItem('billtrix_offline_tx', JSON.stringify(offline));
        clearCart();
        alert('Saved offline. Will sync when online.');
        return;
    }

    $('#checkoutBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing…');
    $.post('{{ route("pos.checkout") }}', payload)
        .done(res => {
            if (res.success) {
                clearCart();
                if (res.receipt_url) window.open(res.receipt_url, '_blank');
                alert('Sale complete!');
            } else { alert(res.message || 'Error.'); }
        })
        .fail(() => alert('Server error. Please retry.'))
        .always(() => {
            $('#checkoutBtn').prop('disabled', false).html('<i class="fas fa-check me-1"></i>Checkout');
        });
}

function syncOffline() {
    const offline = JSON.parse(localStorage.getItem('billtrix_offline_tx') || '[]');
    if (!offline.length) return;
    $.post('{{ route("pos.sync_offline") }}', { transactions: offline })
        .done(res => {
            if (res.success) {
                localStorage.removeItem('billtrix_offline_tx');
                alert(offline.length + ' offline transaction(s) synced.');
            }
        });
}
</script>
</body>
</html>

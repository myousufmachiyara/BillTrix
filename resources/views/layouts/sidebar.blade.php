<aside id="sidebar-left" class="sidebar-left">
    <div class="sidebar-header">
        <div class="sidebar-title" style="display:flex; justify-content:space-between;">
            <a href="{{ route('dashboard') }}" class="logo">
                <img src="{{ asset('assets/img/billtrix-logo-1.png') }}" class="sidebar-logo" alt="BillTrix Logo">
            </a>
            <div class="d-md-none toggle-sidebar-left col-1"
                 data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
                <i class="fas fa-times" aria-label="Toggle sidebar"></i>
            </div>
        </div>
        <div class="sidebar-toggle d-none d-md-block"
             data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
        </div>
    </div>

    <div class="nano">
        <div class="nano-content">
            <nav id="menu" class="nav-main" role="navigation">
                <ul class="nav nav-main">

                    {{-- Dashboard --}}
                    <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="fa fa-tachometer-alt" aria-hidden="true"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    {{-- Users & Roles --}}
                    @if(auth()->user()->can('user_roles.index') || auth()->user()->can('users.index') || auth()->user()->can('branches.index'))
                    <li class="nav-parent {{ request()->routeIs('users.*','roles.*','branches.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-user-shield" aria-hidden="true"></i>
                            <span>Users</span>
                        </a>
                        <ul class="nav nav-children">
                            @can('user_roles.index')
                            <li class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('roles.index') }}">Roles &amp; Permissions</a>
                            </li>
                            @endcan
                            @can('users.index')
                            <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('users.index') }}">All Users</a>
                            </li>
                            @endcan
                            @can('branches.index')
                            <li class="{{ request()->routeIs('branches.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('branches.index') }}">Branches</a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif

                    {{-- Chart of Accounts --}}
                    @if(auth()->user()->can('coa.index') || auth()->user()->can('shoa.index'))
                    <li class="nav-parent {{ request()->routeIs('account_heads.*','shoa.*','coa.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-book" aria-hidden="true"></i>
                            <span>Accounts</span>
                        </a>
                        <ul class="nav nav-children">
                            @can('coa.index')
                            <li class="{{ request()->routeIs('account_heads.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('account_heads.index') }}">Account Heads</a>
                            </li>
                            @endcan
                            @can('shoa.index')
                            <li class="{{ request()->routeIs('shoa.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('shoa.index') }}">Sub Heads</a>
                            </li>
                            @endcan
                            @can('coa.index')
                            <li class="{{ request()->routeIs('coa.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('coa.index') }}">Chart of Accounts</a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif

                    {{-- Products --}}
                    @if(auth()->user()->can('product_categories.index') || auth()->user()->can('attributes.index') || auth()->user()->can('products.index'))
                    <li class="nav-parent {{ request()->routeIs('products.*','product_categories.*','product_subcategories.*','attributes.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-layer-group" aria-hidden="true"></i>
                            <span>Products</span>
                        </a>
                        <ul class="nav nav-children">
                            @can('product_categories.index')
                            <li class="{{ request()->routeIs('product_categories.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('product_categories.index') }}">Categories</a>
                            </li>
                            @endcan
                            @can('product_subcategories.index')
                            <li class="{{ request()->routeIs('product_subcategories.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('product_subcategories.index') }}">Sub Categories</a>
                            </li>
                            @endcan
                            @can('attributes.index')
                            <li class="{{ request()->routeIs('attributes.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('attributes.index') }}">Attributes</a>
                            </li>
                            @endcan
                            @can('products.index')
                            <li class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('products.index') }}">All Products</a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif

                    {{-- Stock Management --}}
                    @if(auth()->user()->can('locations.index') || auth()->user()->can('stock_transfer.index') || auth()->user()->can('stock.index'))
                    <li class="nav-parent {{ request()->routeIs('locations.*','stock.*','stock_transfer.*','stock_adjustments.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-cubes" aria-hidden="true"></i>
                            <span>Stock Management</span>
                        </a>
                        <ul class="nav nav-children">
                            @can('locations.index')
                            <li class="{{ request()->routeIs('locations.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('locations.index') }}">Locations</a>
                            </li>
                            @endcan
                            @can('stock.index')
                            <li class="{{ request()->routeIs('stock.balances') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('stock.balances') }}">Stock Balances</a>
                            </li>
                            @endcan
                            @can('stock_transfer.index')
                            <li class="{{ request()->routeIs('stock_transfer.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('stock_transfer.index') }}">Transfers</a>
                            </li>
                            @endcan
                            @can('stock_adjustments.index')
                            <li class="{{ request()->routeIs('stock_adjustments.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('stock_adjustments.index') }}">Adjustments</a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif

                    {{-- Purchase --}}
                    @if(auth()->user()->can('vendors.index') || auth()->user()->can('purchase_orders.index') || auth()->user()->can('purchase_invoices.index'))
                    <li class="nav-parent {{ request()->routeIs('vendors.*','purchase_orders.*','grn.*','purchase_invoices.*','purchase_returns.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                            <span>Purchase</span>
                        </a>
                        <ul class="nav nav-children">
                            @can('vendors.index')
                            <li class="{{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('vendors.index') }}">Vendors</a>
                            </li>
                            @endcan
                            @can('purchase_orders.index')
                            <li class="{{ request()->routeIs('purchase_orders.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('purchase_orders.index') }}">Purchase Orders</a>
                            </li>
                            @endcan
                            @can('grn.index')
                            <li class="{{ request()->routeIs('grn.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('grn.index') }}">GRN</a>
                            </li>
                            @endcan
                            @can('purchase_invoices.index')
                            <li class="{{ request()->routeIs('purchase_invoices.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('purchase_invoices.index') }}">Purchase Invoices</a>
                            </li>
                            @endcan
                            @can('purchase_returns.index')
                            <li class="{{ request()->routeIs('purchase_returns.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('purchase_returns.index') }}">Purchase Returns</a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif

                    {{-- Sales --}}
                    @if(auth()->user()->can('customers.index') || auth()->user()->can('quotations.index') || auth()->user()->can('sales_invoices.index'))
                    <li class="nav-parent {{ request()->routeIs('customers.*','quotations.*','sales_invoices.*','credit_notes.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-file-invoice-dollar" aria-hidden="true"></i>
                            <span>Sales</span>
                        </a>
                        <ul class="nav nav-children">
                            @can('customers.index')
                            <li class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('customers.index') }}">Customers</a>
                            </li>
                            @endcan
                            @can('quotations.index')
                            <li class="{{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('quotations.index') }}">Quotations</a>
                            </li>
                            @endcan
                            @can('sales_invoices.index')
                            <li class="{{ request()->routeIs('sales_invoices.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('sales_invoices.index') }}">Sales Invoices</a>
                            </li>
                            @endcan
                            @can('credit_notes.index')
                            <li class="{{ request()->routeIs('credit_notes.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('credit_notes.index') }}">Sales Returns</a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif

                    {{-- Production --}}
                    @if(auth()->user()->can('production.index'))
                    <li class="nav-parent {{ request()->routeIs('production.*','production_receiving.*','production_return.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-industry" aria-hidden="true"></i>
                            <span>Production</span>
                        </a>
                        <ul class="nav nav-children">
                            <li class="{{ request()->routeIs('production.index','production.create','production.edit','production.show') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('production.index') }}">Orders</a>
                            </li>
                            <li class="{{ request()->routeIs('production_receiving.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('production_receiving.index') }}">Receiving</a>
                            </li>
                            <li class="{{ request()->routeIs('production_return.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('production_return.index') }}">Return</a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    {{-- Vouchers --}}
                    @if(auth()->user()->can('vouchers.index'))
                    <li class="nav-parent {{ request()->routeIs('vouchers.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-money-check-alt" aria-hidden="true"></i>
                            <span>Vouchers</span>
                        </a>
                        <ul class="nav nav-children">
                            <li class="{{ request()->is('vouchers/journal*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('vouchers.index', 'journal') }}">Journal Vouchers</a>
                            </li>
                            <li class="{{ request()->is('vouchers/payment*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('vouchers.index', 'payment') }}">Payment Vouchers</a>
                            </li>
                            <li class="{{ request()->is('vouchers/receipt*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('vouchers.index', 'receipt') }}">Receipt Vouchers</a>
                            </li>
                            <li class="{{ request()->is('vouchers/purchase*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('vouchers.index', 'purchase') }}">Purchase Vouchers</a>
                            </li>
                            <li class="{{ request()->is('vouchers/sale*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('vouchers.index', 'sale') }}">Sale Vouchers</a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    {{-- Payments & PDC --}}
                    @if(auth()->user()->can('payments.index') || auth()->user()->can('pdc.index'))
                    <li class="nav-parent {{ request()->routeIs('payments.*','pdc.*','payment_allocations.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-coins" aria-hidden="true"></i>
                            <span>Payments</span>
                        </a>
                        <ul class="nav nav-children">
                            @can('payments.index')
                            <li class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('payments.index') }}">All Payments</a>
                            </li>
                            @endcan
                            @can('payments.index')
                            <li class="{{ request()->routeIs('payment_allocations.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('payment_allocations.index') }}">Ageing / Allocation</a>
                            </li>
                            @endcan
                            @can('pdc.index')
                            <li class="{{ request()->routeIs('pdc.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('pdc.index') }}">Post-Dated Cheques</a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif

                    {{-- Projects --}}
                    @if(auth()->user()->can('projects.index'))
                    <li class="nav-parent {{ request()->routeIs('projects.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-project-diagram" aria-hidden="true"></i>
                            <span>Projects</span>
                        </a>
                        <ul class="nav nav-children">
                            <li class="{{ request()->routeIs('projects.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('projects.index') }}">All Projects</a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    {{-- Tasks --}}
                    @if(auth()->user()->can('tasks.index'))
                    <li class="nav-parent {{ request()->routeIs('tasks.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-tasks" aria-hidden="true"></i>
                            <span>Tasks</span>
                        </a>
                        <ul class="nav nav-children">
                            <li class="{{ request()->routeIs('tasks.index') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tasks.index') }}">All Tasks</a>
                            </li>
                            <li class="{{ request()->routeIs('tasks.my') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('tasks.my') }}">My Tasks</a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    {{-- POS --}}
                    @if(auth()->user()->can('pos.index'))
                    <li class="nav-parent {{ request()->routeIs('pos.*','promo_codes.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-cash-register" aria-hidden="true"></i>
                            <span>POS</span>
                        </a>
                        <ul class="nav nav-children">
                            <li>
                                <a class="nav-link" href="{{ route('pos.open') }}" target="_blank">Open POS Terminal</a>
                            </li>
                            <li class="{{ request()->routeIs('pos.sessions.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('pos.sessions.index') }}">Sessions</a>
                            </li>
                            <li class="{{ request()->routeIs('pos.transactions.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('pos.transactions.index') }}">Transactions</a>
                            </li>
                            <li class="{{ request()->routeIs('promo_codes.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('promo_codes.index') }}">Promo Codes</a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    {{-- Shopify Sync --}}
                    @if(auth()->user()->can('shopify_stores.index'))
                    <li class="nav-parent {{ request()->routeIs('shopify.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fab fa-shopify" aria-hidden="true"></i>
                            <span>Shopify Sync</span>
                        </a>
                        <ul class="nav nav-children">
                            <li class="{{ request()->routeIs('shopify.settings') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('shopify.settings') }}">Store Settings</a>
                            </li>
                            <li class="{{ request()->routeIs('shopify.sync_log') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('shopify.sync_log') }}">Sync Log</a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    {{-- Reports --}}
                    @if(auth()->user()->can('reports.inventory') || auth()->user()->can('reports.purchase') || auth()->user()->can('reports.production') || auth()->user()->can('reports.sales') || auth()->user()->can('reports.accounts'))
                    <li class="nav-parent {{ request()->routeIs('reports.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa fa-chart-bar" aria-hidden="true"></i>
                            <span>Reports</span>
                        </a>
                        <ul class="nav nav-children">
                            @can('reports.inventory')
                            <li class="{{ request()->routeIs('reports.inventory') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('reports.inventory') }}">Inventory</a>
                            </li>
                            @endcan
                            @can('reports.purchase')
                            <li class="{{ request()->routeIs('reports.purchase') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('reports.purchase') }}">Purchase</a>
                            </li>
                            @endcan
                            @can('reports.production')
                            <li class="{{ request()->routeIs('reports.production') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('reports.production') }}">Production</a>
                            </li>
                            @endcan
                            @can('reports.sales')
                            <li class="{{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('reports.sales') }}">Sales</a>
                            </li>
                            @endcan
                            @can('reports.accounts')
                            <li class="{{ request()->routeIs('reports.accounts') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('reports.accounts') }}">Accounts</a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif

                    {{-- Settings --}}
                    @if(auth()->user()->can('settings.index'))
                    <li class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('settings.index') }}">
                            <i class="fa fa-cog" aria-hidden="true"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    @endif

                </ul>
            </nav>
        </div>

        <script>
            if (typeof localStorage !== 'undefined') {
                if (localStorage.getItem('sidebar-left-position') !== null) {
                    var sidebarLeft = document.querySelector('#sidebar-left .nano-content');
                    if (sidebarLeft) sidebarLeft.scrollTop = localStorage.getItem('sidebar-left-position');
                }
            }
        </script>
    </div>
</aside>

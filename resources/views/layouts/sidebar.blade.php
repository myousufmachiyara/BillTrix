<aside id="sidebar-left" class="sidebar-left">
  <div class="sidebar-header">
    <div class="sidebar-title" style="display:flex;justify-content:space-between;">
      <a href="{{ route('dashboard') }}" class="logo">
        <img src="{{ asset('assets/img/billtrix-logo-1.png') }}" class="sidebar-logo" alt="BillTrix Logo" />
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
              <i class="fa fa-home" aria-hidden="true"></i>
              <span>Dashboard</span>
            </a>
          </li>

          {{-- POS --}}
          @can('access pos')
          <li class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('pos.index') }}">
              <i class="fa fa-cash-register" aria-hidden="true"></i>
              <span>Point of Sale</span>
            </a>
          </li>
          @endcan

          {{-- Purchase --}}
          @canany(['view purchases', 'manage purchases'])
          <li class="nav-parent {{ request()->routeIs('purchases.*','purchase-orders.*','purchase-returns.*') ? 'nav-expanded active' : '' }}">
            <a class="nav-link" href="#">
              <i class="fa fa-shopping-cart"></i>
              <span>Purchase</span>
            </a>
            <ul class="nav nav-children">
              <li class="{{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('purchase-orders.index') }}">Orders</a>
              </li>
              <li class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('purchases.index') }}">Invoices</a>
              </li>
              <li class="{{ request()->routeIs('purchase-returns.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('purchase-returns.index') }}">Returns</a>
              </li>
            </ul>
          </li>
          @endcanany

          {{-- Sale --}}
          @canany(['view sales', 'manage sales'])
          <li class="nav-parent {{ request()->routeIs('quotations.*','sale-orders.*','sale-invoices.*','sale-returns.*') ? 'nav-expanded active' : '' }}">
            <a class="nav-link" href="#">
              <i class="fa fa-file-invoice-dollar"></i>
              <span>Sale</span>
            </a>
            <ul class="nav nav-children">
              <li class="{{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('quotations.index') }}">Quotations</a>
              </li>
              <li class="{{ request()->routeIs('sale-orders.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('sale-orders.index') }}">Orders</a>
              </li>
              <li class="{{ request()->routeIs('sale-invoices.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('sale-invoices.index') }}">Invoices</a>
              </li>
              <li class="{{ request()->routeIs('sale-returns.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('sale-returns.index') }}">Returns</a>
              </li>
            </ul>
          </li>
          @endcanany

          {{-- Inventory --}}
          @canany(['view products', 'manage products', 'view stock', 'manage stock'])
          <li class="nav-parent {{ request()->routeIs('products.*','stock.*') ? 'nav-expanded active' : '' }}">
            <a class="nav-link" href="#">
              <i class="fa fa-cubes"></i>
              <span>Inventory</span>
            </a>
            <ul class="nav nav-children">
              @canany(['view products','manage products'])
              <li class="{{ request()->routeIs('products.index','products.create','products.edit') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('products.index') }}">All Products</a>
              </li>
              <li>
                <a class="nav-link" href="{{ route('products.barcodePrint') }}">Barcode Print</a>
              </li>
              @endcanany
              @canany(['view stock','manage stock'])
              <li class="{{ request()->routeIs('stock.transfer*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('stock.transfer') }}">Stock Transfer</a>
              </li>
              <li class="{{ request()->routeIs('stock.transfers') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('stock.transfers') }}">Transfer History</a>
              </li>
              @endcanany
            </ul>
          </li>
          @endcanany

          {{-- Accounts --}}
          @canany(['view accounts', 'manage accounts'])
          <li class="nav-parent {{ request()->routeIs('coa.*','vouchers.*','cheques.*') ? 'nav-expanded active' : '' }}">
            <a class="nav-link" href="#">
              <i class="fa fa-book"></i>
              <span>Accounts</span>
            </a>
            <ul class="nav nav-children">
              <li class="{{ request()->routeIs('coa.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('coa.index') }}">Chart of Accounts</a>
              </li>
              <li class="{{ request()->routeIs('vouchers.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('vouchers.index') }}">Vouchers</a>
              </li>
              <li class="{{ request()->routeIs('cheques.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('cheques.index') }}">Post-Dated Cheques</a>
              </li>
            </ul>
          </li>
          @endcanany

          {{-- Production --}}
          @canany(['view production', 'manage production'])
          <li class="nav-parent {{ request()->routeIs('production.*') ? 'nav-expanded active' : '' }}">
            <a class="nav-link" href="#">
              <i class="fa fa-industry"></i>
              <span>Production</span>
            </a>
            <ul class="nav nav-children">
              <li class="{{ request()->routeIs('production.orders.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('production.orders.index') }}">Production Orders</a>
              </li>
            </ul>
          </li>
          @endcanany

          {{-- Reports --}}
          @can('view reports')
          <li class="nav-parent {{ request()->routeIs('reports.*') ? 'nav-expanded active' : '' }}">
            <a class="nav-link" href="#">
              <i class="fa fa-chart-bar"></i>
              <span>Reports</span>
            </a>
            <ul class="nav nav-children">
              <li class="{{ request()->is('reports/inventory*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reports.inventory') }}">Inventory</a>
              </li>
              <li class="{{ request()->is('reports/purchases*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reports.purchases') }}">Purchase</a>
              </li>
              <li class="{{ request()->is('reports/sales*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reports.sales') }}">Sales</a>
              </li>
              <li class="{{ request()->is('reports/accounts*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reports.accounts') }}">Accounts</a>
              </li>
              <li class="{{ request()->is('reports/production*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reports.production') }}">Production</a>
              </li>
            </ul>
          </li>
          @endcan

          {{-- Administration --}}
          @canany(['manage branches', 'manage users'])
          <li class="nav-parent {{ request()->routeIs('branches.*','users.*') ? 'nav-expanded active' : '' }}">
            <a class="nav-link" href="#">
              <i class="fa fa-user-shield"></i>
              <span>Administration</span>
            </a>
            <ul class="nav nav-children">
              @can('manage branches')
              <li class="{{ request()->routeIs('branches.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('branches.index') }}">Branches</a>
              </li>
              @endcan
              @can('manage users')
              <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('users.index') }}">Users</a>
              </li>
              @endcan
            </ul>
          </li>
          @endcanany

        </ul>
      </nav>
    </div>

    <script>
      if (typeof localStorage !== 'undefined') {
        if (localStorage.getItem('sidebar-left-position') !== null) {
          var sidebarLeft = document.querySelector('#sidebar-left .nano-content');
          sidebarLeft.scrollTop = localStorage.getItem('sidebar-left-position');
        }
      }
    </script>
  </div>
</aside>
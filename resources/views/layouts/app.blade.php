<!DOCTYPE html>
<html lang="en" class="fixed js flexbox flexboxlegacy no-touch csstransforms csstransforms3d no-overflowscrolling webkit chrome win js no-mobile-device custom-scroll sidebar-left-collapsed">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'BillTrix')</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Web Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800|Shadows+Into+Light" rel="stylesheet">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/animate/animate.compat.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/magnific-popup/magnific-popup.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/media/css/dataTables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/css/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2-bootstrap-theme/select2-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-multiselect/css/bootstrap-multiselect.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/dropzone/basic.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/dropzone/dropzone.css') }}">

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/skins/default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    <!-- jQuery early (needed by theme) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <style>
        #loader {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255,255,255,.8);
            display: flex; justify-content: center; align-items: center;
            z-index: 9999;
        }
        #loader.hidden { display: none; }

        .cust-pad { padding-top: 0; }

        @media (min-width: 768px) {
            .cust-pad       { padding: 60px 10px 0 20px; }
            .home-cust-pad  { padding: 60px 15px 0 15px; }
            .sidebar-logo   { width: 60%; height: auto; padding-top: 5px; }
        }
        @media (max-width: 767px) {
            .sidebar-logo { height: 40%; }
        }

        .icon-container {
            background-size: auto;
            background-repeat: no-repeat;
            background-position: right bottom;
        }

        /* Status badges */
        .badge-draft     { background: #6c757d; color:#fff; }
        .badge-posted    { background: #0d6efd; color:#fff; }
        .badge-paid      { background: #198754; color:#fff; }
        .badge-partial   { background: #fd7e14; color:#fff; }
        .badge-cancelled { background: #dc3545; color:#fff; }
        .badge-pending   { background: #ffc107; color:#000; }
        .badge-approved  { background: #198754; color:#fff; }
        .badge-active    { background: #198754; color:#fff; }
        .badge-inactive  { background: #6c757d; color:#fff; }
        .badge-overdue   { background: #dc3545; color:#fff; }

        .btn-action { padding: .15rem .4rem; font-size: .8rem; }
        .table-scroll { overflow-x: auto; }

        /* Overdue row highlight */
        tr.row-overdue { background-color: #fff5f5 !important; }
        tr.row-warning { background-color: #fffbeb !important; }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Page Loader -->
    <div id="loader">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- Change Password Modal (Magnific Popup inline) -->
    <div id="changePassword" class="zoom-anim-dialog modal-block modal-block-danger mfp-hide">
        <form id="changePasswordForm" method="POST" action="{{ route('password.change') }}"
              style="width:75%" enctype="multipart/form-data" onkeydown="return event.key != 'Enter';">
            @csrf
            <header class="card-header">
                <h2 class="card-title">Change Password</h2>
            </header>
            <div class="card-body">
                <div class="row form-group">
                    <div class="col-12 mb-2">
                        <label>Current Password</label>
                        <input type="password" class="form-control" id="current_password"
                               name="current_password" placeholder="Current Password" required>
                    </div>
                    <div class="col-12 mb-2">
                        <label>New Password</label>
                        <input type="password" class="form-control" id="new_password"
                               name="new_password" placeholder="New Password (min 8 chars)" minlength="8" required>
                    </div>
                    <div class="col-12 mb-2">
                        <label>Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_new_password"
                               placeholder="Confirm New Password" minlength="8" required>
                    </div>
                </div>
            </div>
            <footer class="card-footer">
                <div class="row">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                        <button type="button" class="btn btn-default modal-dismiss">Cancel</button>
                    </div>
                </div>
            </footer>
        </form>
    </div>

    <!-- Page Header -->
    <header class="page-header">

        <!-- Desktop header -->
        <div class="logo-container d-none d-md-block">
            <div id="userbox" class="userbox" style="float:right !important;">
                <a href="#" data-bs-toggle="dropdown" style="margin-right:20px;">
                    <div class="profile-info">
                        <span class="name">{{ session('user_name') }}</span>
                        <span class="role">{{ session('role_name') }}</span>
                    </div>
                    <i class="fa custom-caret"></i>
                </a>
                <div class="dropdown-menu">
                    <ul class="list-unstyled">
                        <li>
                            <a role="menuitem" tabindex="-1" href="#changePassword"
                               class="mb-1 mt-1 me-1 modal-with-zoom-anim ws-normal">
                                <i class="bx bx-lock"></i> Change Password
                            </a>
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button style="background:transparent;border:none;font-size:14px;"
                                        type="submit" role="menuitem" tabindex="-1">
                                    <i class="bx bx-power-off"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Mobile header -->
        <div class="logo-container d-md-none">
            <a href="{{ route('dashboard') }}" class="logo">
                <img class="pt-2" src="{{ asset('assets/img/billtrix-logo-black.png') }}"
                     width="35%" alt="BillTrix Logo">
            </a>
            <div id="userbox" class="userbox" style="float:right !important;">
                <a href="#" data-bs-toggle="dropdown" style="margin-right:20px;">
                    <div class="profile-info">
                        <span class="name">{{ session('user_name') }}</span>
                        <span class="role">{{ session('role_name') }}</span>
                    </div>
                    <i class="fa custom-caret"></i>
                </a>
                <div class="dropdown-menu">
                    <ul class="list-unstyled">
                        <li>
                            <a role="menuitem" tabindex="-1" href="#changePassword"
                               class="mb-1 mt-1 me-1 modal-with-zoom-anim ws-normal">
                                <i class="bx bx-lock"></i> Change Password
                            </a>
                        </li>
                    </ul>
                </div>
                <i class="fas fa-bars toggle-sidebar-left"
                   data-toggle-class="sidebar-left-opened"
                   data-target="html"
                   data-fire-event="sidebar-left-opened"
                   aria-label="Toggle sidebar"></i>
            </div>
        </div>

    </header>

    <!-- Body -->
    <section class="body">
        <div class="inner-wrapper cust-pad">
            @include('layouts.sidebar')
            <section role="main" class="content-body">

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')

            </section>
        </div>
    </section>

    <footer>
        @include('layouts.footer')
        <div class="text-end">
            <div>Powered By <a target="_blank" href="https://syitrix.com/">SyiTrix</a></div>
        </div>
    </footer>

    <!-- ============================================================
         Footer Scripts – exact order from your spec
    ============================================================ -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nanoscroller/0.8.7/jquery.nanoscroller.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-placeholder/2.3.1/jquery.placeholder.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.appear/0.4.1/jquery.appear.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="{{ asset('assets/vendor/bootstrapv5-multiselect/js/bootstrap-multiselect.js') }}"></script>
    <script src="{{ asset('assets/vendor/dropzone/dropzone.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/select2/js/select2.js') }}"></script>
    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="{{ asset('assets/js/examples/examples.header.menu.js') }}"></script>
    <script src="{{ asset('assets/js/examples/examples.dashboard.js') }}"></script>
    <script src="{{ asset('assets/js/examples/examples.datatables.default.js') }}"></script>
    <script src="{{ asset('assets/js/examples/examples.modals.js') }}"></script>
    <script src="{{ asset('assets/js/theme.init.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-nestable/jquery.nestable.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fingerprintjs/fingerprintjs@3/dist/fp.min.js"></script>

    <script>
    $(function () {
        // Hide loader
        $('#loader').addClass('hidden');

        // Global Select2
        if ($.fn.select2) {
            $('.select2').select2({ width: '100%' });
            $('.select2-js').select2({ width: '100%', dropdownAutoWidth: true });
        }

        // Global DataTable (only if specific id present)
        if ($.fn.DataTable && $('#cust-datatable-default').length) {
            $('#cust-datatable-default').DataTable({ pageLength: 25, responsive: true });
        }

        // Datepicker
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({ format: 'yyyy-mm-dd', autoclose: true, todayHighlight: true });
        }

        // Magnific Popup – inline modals
        if ($.fn.magnificPopup) {
            $('.modal-with-zoom-anim').magnificPopup({
                type: 'inline', fixedContentPos: false, fixedBgPos: true,
                overflowY: 'auto', closeBtnInside: true, preloader: false,
                midClick: true, removalDelay: 300, mainClass: 'my-mfp-zoom-in'
            });
            $('.modal-with-form').magnificPopup({
                type: 'inline', fixedContentPos: true,
                mainClass: 'mfp-with-zoom',
                zoom: { enabled: true, duration: 300 }
            });
        }

        // Form double-submit protection
        $('form').on('submit', function () {
            var $form = $(this);
            if ($form.hasClass('submitting')) return false;
            $form.addClass('submitting');
            $form.find('button[type="submit"]').prop('disabled', true)
                 .prepend('<span class="spinner-border spinner-border-sm me-1"></span>');
        });
    });

    // Global CSRF for AJAX
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Global number formatter
    window.formatNum = function(x) {
        return parseFloat(x || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };
    </script>

    @stack('scripts')
</body>
</html>

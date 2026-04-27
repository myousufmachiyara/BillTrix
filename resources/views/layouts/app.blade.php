<!DOCTYPE html>
<html lang="en" class="fixed js flexbox flexboxlegacy no-touch csstransforms csstransforms3d no-overflowscrolling webkit chrome win js no-mobile-device custom-scroll sidebar-left-collapsed">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>@yield('title', 'BillTrix ERP') | {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/animate/animate.compat.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/all.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/magnific-popup/magnific-popup.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/media/css/dataTables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/css/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2-bootstrap-theme/select2-bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-multiselect/css/bootstrap-multiselect.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/dropzone/basic.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/dropzone/dropzone.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/skins/default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <style>
        #loader {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255,255,255,0.8);
            display: flex; justify-content: center; align-items: center;
            z-index: 9999;
        }
        #loader.hidden { display: none; }
        .cust-pad { padding-top: 0; }
        @media (min-width: 768px) {
            .cust-pad { padding: 60px 10px 0px 20px; }
            .home-cust-pad { padding: 60px 15px 0px 15px; }
            .sidebar-logo { width: 60%; height: auto; padding-top: 5px; }
        }
        @media (max-width: 767px) {
            .sidebar-logo { height: 40%; }
        }
        .pw-wrap { position: relative; }
        .pw-wrap .form-control { padding-right: 2.5rem; }
        .pw-toggle {
            position: absolute; top: 50%; right: 10px;
            transform: translateY(-50%);
            background: none; border: none;
            padding: 0; cursor: pointer;
            color: #999; font-size: 14px; line-height: 1; z-index: 5;
        }
        .pw-toggle:hover { color: #444; }
    </style>

    @stack('styles')
</head>
<body>

    {{-- Page Loader --}}
    <div id="loader">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    {{-- Change Password Modal --}}
    <div id="changePassword" class="zoom-anim-dialog modal-block modal-block-danger mfp-hide">
        <section class="card">
            <form id="changePasswordForm" autocomplete="off" onkeydown="return event.key != 'Enter';">
                @csrf
                <header class="card-header">
                    <h2 class="card-title">Change Password</h2>
                </header>
                <div class="card-body">
                    <div id="cp-alert" class="alert d-none mb-3"></div>
                    <div class="row form-group">
                        <div class="col-12 mb-3">
                            <label>Current Password</label>
                            <div class="pw-wrap">
                                <input type="password" class="form-control" name="current_password"
                                       id="cp_current" placeholder="Current Password"
                                       autocomplete="current-password" required>
                                <button type="button" class="pw-toggle" tabindex="-1"
                                        onclick="togglePw('cp_current', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label>New Password</label>
                            <div class="pw-wrap">
                                <input type="password" class="form-control" name="new_password"
                                       id="cp_new" placeholder="New Password (min 8 chars)"
                                       minlength="8" autocomplete="new-password" required>
                                <button type="button" class="pw-toggle" tabindex="-1"
                                        onclick="togglePw('cp_new', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12 mb-2">
                            <label>Confirm New Password</label>
                            <div class="pw-wrap">
                                <input type="password" class="form-control" name="new_password_confirmation"
                                       id="cp_confirm" placeholder="Confirm New Password"
                                       minlength="8" autocomplete="new-password" required>
                                <button type="button" class="pw-toggle" tabindex="-1"
                                        onclick="togglePw('cp_confirm', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="col-md-12 text-end">
                        <button type="submit" id="cp-submit-btn" class="btn btn-primary">Change Password</button>
                        <button type="button" class="btn btn-default modal-dismiss">Cancel</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

    {{-- Header --}}
    <header class="page-header">

        {{-- Desktop header --}}
        <div class="logo-container d-none d-md-block">
            <div id="userbox" class="userbox" style="float:right !important;">
                <a href="#" data-bs-toggle="dropdown" style="margin-right:20px;">
                    <div class="profile-info">
                        <span class="name">{{ auth()->user()->name }}</span>
                        <span class="role">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</span>
                    </div>
                    <i class="fa custom-caret"></i>
                </a>
                <div class="dropdown-menu">
                    <ul class="list-unstyled">
                        <li>
                            <a role="menuitem" tabindex="-1"
                               href="#changePassword"
                               class="mb-1 mt-1 me-1 modal-with-zoom-anim ws-normal">
                                <i class="bx bx-lock"></i> Change Password
                            </a>
                        </li>
                        <li>
                            <a role="menuitem" tabindex="-1"
                               href="{{ route('pos.index') }}" target="_blank"
                               class="mb-1 mt-1 me-1">
                                <i class="fa fa-cash-register"></i> Open POS
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

        {{-- Mobile header --}}
        <div class="logo-container d-md-none">
            <a href="{{ route('dashboard') }}" class="logo">
                <img class="pt-2" src="{{ asset('assets/img/billtrix-logo-black.png') }}" width="35%" alt="Logo" />
            </a>
            <div id="userbox-mobile" class="userbox" style="float:right !important;">
                <a href="#" data-bs-toggle="dropdown" style="margin-right:20px;">
                    <div class="profile-info">
                        <span class="name">{{ auth()->user()->name }}</span>
                        <span class="role">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</span>
                    </div>
                    <i class="fa custom-caret"></i>
                </a>
                <div class="dropdown-menu">
                    <ul class="list-unstyled">
                        <li>
                            <a role="menuitem" tabindex="-1"
                               href="#changePassword"
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

    {{-- Body --}}
    <section class="body">
        <div class="inner-wrapper cust-pad">

            @include('layouts.sidebar')

            <section role="main" class="content-body">

                {{-- Flash messages --}}
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @yield('content')

            </section>
        </div>
    </section>

    <footer>
        <div class="text-end">
            <div>Powered By <a target="_blank" href="https://syitrix.com/">SyiTrix</a></div>
        </div>
    </footer>

    {{-- Vendor JS --}}
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/nanoscroller/nanoscroller.js') }}"></script>
    <script src="{{ asset('assets/vendor/magnific-popup/jquery.magnific-popup.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/media/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/select2/js/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap-multiselect/js/bootstrap-multiselect.js') }}"></script>
    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="{{ asset('assets/js/theme.init.js') }}"></script>

    <script>
    // ── Hide loader once page is ready ───────────────────────────────────────
    $(window).on('load', function () {
        $('#loader').addClass('hidden');
    });

    // ── DataTables + Select2 init ─────────────────────────────────────────────
    $(document).ready(function () {
        $('.select2').select2();
        $('.datatable').DataTable({ pageLength: 25, order: [[0, 'desc']] });

        // Save sidebar scroll position
        var sidebarEl = document.querySelector('#sidebar-left .nano-content');
        if (sidebarEl) {
            sidebarEl.addEventListener('scroll', function () {
                localStorage.setItem('sidebar-left-position', sidebarEl.scrollTop);
            });
        }
    });

    // ── Toggle password visibility ────────────────────────────────────────────
    function togglePw(fieldId, btn) {
        var input = document.getElementById(fieldId);
        var icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // ── Change Password (single-fire AJAX) ───────────────────────────────────
    (function () {
        var form = document.getElementById('changePasswordForm');

        // Block native form submission entirely so Porto theme cannot re-fire it
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }, true);

        document.getElementById('cp-submit-btn').addEventListener('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var btn     = this;
            var alertEl = document.getElementById('cp-alert');
            var csrf    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (btn.dataset.submitting === '1') return;

            var newPw  = document.getElementById('cp_new').value;
            var confPw = document.getElementById('cp_confirm').value;

            if (newPw !== confPw) {
                alertEl.className   = 'alert alert-danger';
                alertEl.textContent = 'Passwords do not match.';
                return;
            }
            if (newPw.length < 8) {
                alertEl.className   = 'alert alert-danger';
                alertEl.textContent = 'New password must be at least 8 characters.';
                return;
            }

            btn.dataset.submitting = '1';
            btn.disabled           = true;
            btn.textContent        = 'Saving…';
            alertEl.className      = 'alert d-none';

            var payload = new FormData();
            payload.append('_token',                    csrf);
            payload.append('current_password',          document.getElementById('cp_current').value);
            payload.append('new_password',              newPw);
            payload.append('new_password_confirmation', confPw);

            fetch('/change-my-password', {
                method : 'POST',
                headers: {
                    'X-CSRF-TOKEN'     : csrf,
                    'Accept'           : 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest',
                },
                body: payload,
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    alertEl.className   = 'alert alert-success';
                    alertEl.textContent = data.message || 'Password changed successfully.';
                    ['cp_current', 'cp_new', 'cp_confirm'].forEach(function (id) {
                        var el = document.getElementById(id);
                        el.value = '';
                        el.type  = 'password';
                    });
                    form.querySelectorAll('.pw-toggle i').forEach(function (icon) {
                        icon.className = 'fas fa-eye';
                    });
                    setTimeout(function () {
                        if (typeof $.magnificPopup !== 'undefined') $.magnificPopup.close();
                        alertEl.className = 'alert d-none';
                    }, 1500);
                } else {
                    var msgs = [];
                    if (data.errors) {
                        msgs = Array.isArray(data.errors)
                            ? data.errors
                            : Object.values(data.errors).flat();
                    }
                    alertEl.className   = 'alert alert-danger';
                    alertEl.textContent = msgs.join(' ') || 'Something went wrong.';
                }
            })
            .catch(function () {
                alertEl.className   = 'alert alert-danger';
                alertEl.textContent = 'Network error. Please try again.';
            })
            .finally(function () {
                btn.disabled           = false;
                btn.textContent        = 'Change Password';
                btn.dataset.submitting = '0';
            });
        });
    })();
    </script>

    @stack('scripts')
</body>
</html>
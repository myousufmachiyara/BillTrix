<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} | Login</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800|Shadows+Into+Light" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/animate/animate.compat.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/all.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/magnific-popup/magnific-popup.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/owl.carousel/assets/owl.carousel.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/owl.carousel/assets/owl.theme.default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/skins/default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}" />
    <script src="{{ asset('assets/vendor/modernizr/modernizr.js') }}"></script>

    <style>
        .resp-cont { width: 60%; }
        @media (max-width: 768px) { .resp-cont { width: 80%; } }
    </style>
</head>
<body style="background:#fff;">

<div class="row" style="min-height:100vh; margin:0;">

    {{-- Left: Login Form --}}
    <div class="col-12 col-md-6 text-center d-flex align-items-center justify-content-center">
        <div class="container resp-cont">

            <h2 class="mb-0 text-primary">Welcome Back</h2>
            <p class="text-dark mb-4">Please Login To Continue</p>

            @if($errors->any())
            <div class="alert alert-danger text-center" style="border-radius:10px;">
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('status'))
            <div class="alert alert-success text-center" style="border-radius:10px;">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-primary"
                              style="border-top-left-radius:15px;border-bottom-left-radius:15px;">
                            <i class="bx bx-user text-4"></i>
                        </span>
                        <input class="form-control" required name="email"
                               placeholder="Username or Email" type="text"
                               value="{{ old('email') }}"
                               style="border-top-right-radius:15px;border-bottom-right-radius:15px;" />
                    </div>
                </div>

                <div class="form-group mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-primary"
                              style="border-top-left-radius:15px;border-bottom-left-radius:15px;">
                            <i class="bx bx-lock text-4"></i>
                        </span>
                        <input class="form-control" required name="password"
                               placeholder="Password" type="password"
                               id="password" autocomplete="off"
                               style="border-top-right-radius:15px;border-bottom-right-radius:15px;" />
                    </div>
                </div>

                <div class="col-sm-12">
                    <span class="mt-1 mx-2 text-start d-flex align-items-center justify-content-between">
                        <label style="cursor:pointer; font-size:0.9rem;">
                            <input type="checkbox" onclick="showPassword()"> Show Password
                        </label>
                        <label style="cursor:pointer; font-size:0.9rem;">
                            <input type="checkbox" name="remember" id="remember"> Remember Me
                        </label>
                    </span>
                    <button type="submit" class="btn btn-primary mt-2"
                            style="font-size:0.9rem;padding:8.52px 18px;border-radius:15px;width:100%">
                        Continue
                    </button>
                </div>
            </form>

            <p class="text-center text-muted mt-3 mb-3">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All Rights Reserved.
            </p>
        </div>
    </div>

    {{-- Right: Carousel --}}
    <div class="col-md-6 d-none d-lg-block" style="padding:0;">
        <div class="owl-carousel owl-theme mb-0"
             data-plugin-carousel
             data-plugin-options='{ "dots": false, "nav": true, "items": 1, "autoplay": true }'>
            <img src="{{ asset('assets/img/slide1.png') }}"
                 style="height:100vh; width:100%; object-fit:cover;" alt="">
        </div>
    </div>

</div>

<script src="{{ asset('assets/vendor/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendor/owl.carousel/owl.carousel.js') }}"></script>
<script src="{{ asset('assets/js/theme.js') }}"></script>
<script src="{{ asset('assets/js/custom.js') }}"></script>
<script src="{{ asset('assets/js/theme.init.js') }}"></script>

<script>
    function showPassword() {
        var x = document.getElementById("password");
        x.type = (x.type === "password") ? "text" : "password";
    }
</script>
</body>
</html>
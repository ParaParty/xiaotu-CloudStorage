<!-- register.blade.php -->
<head>
    @section('title','注册')
    @include('layout.head')
    {{--    <script src="{{ asset('assets/js/color-modes.js') }}"></script>--}}

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .b-example-divider {
            width: 100%;
            height: 3rem;
            background-color: rgba(0, 0, 0, .1);
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
        }

        .bi {
            vertical-align: -.125em;
            fill: currentColor;
        }

        .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden;
        }

        .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .btn-bd-primary {
            --bd-violet-bg: #712cf9;
            --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

            --bs-btn-font-weight: 600;
            --bs-btn-color: var(--bs-white);
            --bs-btn-bg: var(--bd-violet-bg);
            --bs-btn-border-color: var(--bd-violet-bg);
            --bs-btn-hover-color: var(--bs-white);
            --bs-btn-hover-bg: #6528e0;
            --bs-btn-hover-border-color: #6528e0;
            --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
            --bs-btn-active-color: var(--bs-btn-hover-color);
            --bs-btn-active-bg: #5a23c8;
            --bs-btn-active-border-color: #5a23c8;
        }

        .bd-mode-toggle {
            z-index: 1500;
        }

        html,
        body {
            height: 100%;
        }

        .form-signin {
            max-width: 330px;
            padding: 1rem;
        }

        .form-signin .form-floating:focus-within {
            z-index: 2;
        }

        #register_form input[name="password_confirmation"]  {
            margin-bottom: 10px;
        }

        #register_form input {
            margin-bottom: -1px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }


        .title {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .title .line-1 {
            font-size: 50px;
            margin-bottom: 10px;
        }

        /*.title .line-2 {*/
        /*    font-size: 43px;*/
        /*    margin-left: 130px;*/
        /*    margin-top: -30px;*/
        /*    margin-bottom: 30px;*/
        /*}*/

    </style>
</head>
<body class="align-items-center pb-4 bg-body-tertiary">
<div class="alert alert-primary alert-dismissible d-grid mx-3 mt-2" role="alert">
    <div>暂停注册！{{ date('Y:m:d H:i:s') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@include('layout.color-mode')
<main class="form-signin w-100 m-auto">
    <div class="title">
        <img class="mb-4" src="{{ asset('assets/img/Xuri.ico') }}" alt="" width="120" height="120">
        {{--        <span class="sub-title line-1">旭日云</span>--}}
        {{--        <span class="sub-title line-2">XuriCloud</span>--}}
        <span class="sub-title line-1">XuriCloud</span>
    </div>
 

    <h1 class="h4 mb-3 fw-normal">Please sign up</h1>
    <form id="register_form" method="POST" action="{{ route('user.register') }}">
        @csrf
        <div class="form-floating">
            <input name="name" type="text" class="form-control" placeholder="name@example.com">
            <label for="name">Name</label>
        </div>
        <div class="form-floating">
            <input name="email" type="email" class="form-control" placeholder="name@example.com">
            <label for="email">Email address</label>
        </div>
        <div class="form-floating">
            <input name="password" type="password" class="form-control" placeholder="Password">
            <label for="password">Password</label>
        </div>
        <div class="form-floating">
            <input name="password_confirmation" type="password" class="form-control" placeholder="Confirm password">
            <label for="password_confirmation">Confirm password</label>
        </div>
    </form>
    <button id="register_btn" class="btn btn-primary w-100 py-2 mt-2">Sign up</button>
    <p class="mt-5 mb-3 text-body-secondary">© 2021–2023</p>
</main>

<script type="module">
    $('#register_btn').click((qualifiedName, value) => {
        if ($('#password_confirmation').val() === $('#password').val()) {
            let n=$('input[name="password_confirmation"]').prop('disabled', true)
            $('#register_form').submit();
            n.removeAttr('disabled')
        } else {
            alert('密码不一致!');
        }
    });
</script>
</body>



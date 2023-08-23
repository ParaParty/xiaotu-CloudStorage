<head>

    @include('layout.head')
    @yield('head')
</head>
<body>

@include('layout.navbar')
@yield('body-begin')
<main>
    @yield('main')
</main>
@yield('body-end')
@include('layout.footer')

</body>

@include('layouts.home.head')
<title>Home</title>
<body class="animsition">

    @include('layouts.home.header')
   @include('layouts.home.newcart')


@include('layouts.home.hero')


   @include('layouts.home.banner')
   @include('layouts.home.products-overview')

    


    @include('layouts.home.footer')

    <!-- Back to top -->
    <div class="btn-back-to-top" id="myBtn">
        <span class="symbol-btn-back-to-top">
            <i class="zmdi zmdi-chevron-up"></i>
        </span>
    </div>

    @include('layouts.home.script')

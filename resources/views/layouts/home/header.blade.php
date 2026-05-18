<!-- Header -->
<header>

    <!-- Header desktop -->
    <div class="container-menu-desktop">

        <!-- Topbar -->
        <div class="top-bar">
            <div class="content-topbar flex-sb-m h-full container">

                <!-- LEFT -->
                <div class="left-top-bar">
                    Free shipping for standard order over $100
                </div>

                <!-- RIGHT -->
                <div class="right-top-bar flex-w h-full">

                    <a href="#" class="flex-c-m trans-04 p-lr-25">
                        Help & FAQs
                    </a>

                    {{-- GUEST --}}
                    @guest

                        <a href="{{ route('login') }}" class="flex-c-m trans-04 p-lr-25">
                            Login
                        </a>

                        <a href="{{ route('register') }}" class="flex-c-m trans-04 p-lr-25">
                            Register
                        </a>

                    @endguest


                    {{-- AUTH USER --}}
                    @auth

                        <a href="#" class="flex-c-m trans-04 p-lr-25">
                            Hi, {{ auth()->user()->name }}
                        </a>

                        {{-- ADMIN PANEL --}}
                        @if(auth()->user()->role == 'admin')

                            <a href="{{ route('dashboard') }}" class="flex-c-m trans-04 p-lr-25">
                                Admin Panel
                            </a>

                        @endif

                        {{-- LOGOUT --}}
                        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                            @csrf

                            <button type="submit"
                                style="border:none;background:none;color:#fff;"
                                class="flex-c-m trans-04 p-lr-25 mt-2">
                                Logout
                            </button>
                        </form>

                    @endauth

                  

                </div>
            </div>
        </div>

        <!-- DESKTOP MENU -->
        <div class="wrap-menu-desktop">

            <nav class="limiter-menu-desktop container">

                <!-- Logo -->
                <a href="{{ route('home') }}" class="logo">
                    <h3>Our Logo</h3>
                </a>

                <!-- Menu -->
                <div class="menu-desktop">

                    <ul class="main-menu">

                        <li class="active-menu">
                            <a href="{{ route('home') }}">Home</a>
                        </li>

                        <li>
                            <a href="{{ route('shop') }}">Shop</a>
                        </li>

                        <li class="label1" data-label1="hot">
                            <a href="{{ route('features') }}">Features</a>
                        </li>

                        <li>
                            <a href="{{ route('blogs') }}">Blog</a>
                        </li>

                        <li>
                            <a href="{{ route('about') }}">About</a>
                        </li>

                        <li>
                            <a href="{{ route('contact') }}">Contact</a>
                        </li>

                    </ul>

                </div>

                <!-- ICONS -->
                <div class="wrap-icon-header flex-w flex-r-m">

                    <!-- SEARCH -->
                    <div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 js-show-modal-search">
                        <i class="zmdi zmdi-search"></i>
                    </div>

                    <!-- CART -->
                    @auth

                        <a href="{{ route('cart') }}"
                            class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti"
                            data-notify="{{ $cartCount ?? 0 }}">

                            <i class="zmdi zmdi-shopping-cart"></i>
                        </a>

                    @else

                        <a href="{{ route('login') }}"
                            class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11">

                            <i class="zmdi zmdi-shopping-cart"></i>

                        </a>

                    @endauth


                    <!-- WISHLIST -->
                    @auth

                        <a href="#"
                            class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti"
                            data-notify="0">

                            <i class="zmdi zmdi-favorite-outline"></i>

                        </a>

                    @else

                        <a href="{{ route('login') }}"
                            class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11">

                            <i class="zmdi zmdi-favorite-outline"></i>

                        </a>

                    @endauth

                </div>

            </nav>

        </div>

    </div>





    <!-- MOBILE HEADER -->
    <div class="wrap-header-mobile">

        <!-- Logo -->
        <div class="logo-mobile">
            <a href="{{ route('home') }}">
                <h3>OurLogo</h3>
            </a>
        </div>

        <!-- Icons -->
        <div class="wrap-icon-header flex-w flex-r-m m-r-15">

            <!-- SEARCH -->
            <div class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 js-show-modal-search">
                <i class="zmdi zmdi-search"></i>
            </div>

            <!-- CART -->
            @auth

                <a href="{{ route('cart') }}"
                    class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti"
                    data-notify="{{ $cartCount ?? 0 }}">

                    <i class="zmdi zmdi-shopping-cart"></i>

                </a>

            @else

                <a href="{{ route('login') }}"
                    class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10">

                    <i class="zmdi zmdi-shopping-cart"></i>

                </a>

            @endauth


            <!-- WISHLIST -->
            @auth

                <a href="#"
                    class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti"
                    data-notify="0">

                    <i class="zmdi zmdi-favorite-outline"></i>

                </a>

            @else

                <a href="{{ route('login') }}"
                    class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10">

                    <i class="zmdi zmdi-favorite-outline"></i>

                </a>

            @endauth

        </div>

        <!-- MOBILE BUTTON -->
        <div class="btn-show-menu-mobile hamburger hamburger--squeeze">
            <span class="hamburger-box">
                <span class="hamburger-inner"></span>
            </span>
        </div>

    </div>





    <!-- MOBILE MENU -->
    <div class="menu-mobile">

        <!-- TOPBAR MOBILE -->
        <ul class="topbar-mobile">

            <li>
                <div class="left-top-bar">
                    Free shipping for standard order over $100
                </div>
            </li>

            <li>

                <div class="right-top-bar flex-w h-full">

                    {{-- GUEST --}}
                    @guest

                        <a href="{{ route('login') }}" class="flex-c-m p-lr-10 trans-04">
                            Login
                        </a>

                        <a href="{{ route('register') }}" class="flex-c-m p-lr-10 trans-04">
                            Register
                        </a>

                    @endguest


                    {{-- AUTH --}}
                    @auth

                        <a href="#" class="flex-c-m p-lr-10 trans-04">
                            {{ auth()->user()->name }}
                        </a>

                        @if(auth()->user()->role == 'admin')

                            <a href="{{ route('dashboard') }}" class="flex-c-m p-lr-10 trans-04">
                                Admin Panel
                            </a>

                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button type="submit"
                                style="border:none;background:none;"
                                class="flex-c-m p-lr-10 trans-04 mt-2">
                                Logout
                            </button>

                        </form>

                    @endauth

                </div>

            </li>

        </ul>





        <!-- MOBILE MENU -->
        <ul class="main-menu-m">

            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('shop') }}">Shop</a></li>
            <li><a href="{{ route('features') }}">Features</a></li>
            <li><a href="{{ route('blogs') }}">Blog</a></li>
            <li><a href="{{ route('about') }}">About</a></li>
            <li><a href="{{ route('contact') }}">Contact</a></li>

        </ul>

    </div>





    <!-- SEARCH MODAL -->
    <div class="modal-search-header flex-c-m trans-04 js-hide-modal-search">

        <div class="container-search-header">

            <button class="flex-c-m btn-hide-modal-search trans-04 js-hide-modal-search">
                <img src="{{ asset('templates/images/icons/icon-close2.png') }}" alt="CLOSE">
            </button>

            <form class="wrap-search-header flex-w p-l-15">

                <button class="flex-c-m trans-04">
                    <i class="zmdi zmdi-search"></i>
                </button>

                <input class="plh3"
                    type="text"
                    name="search"
                    placeholder="Search...">

            </form>

        </div>

    </div>

</header>
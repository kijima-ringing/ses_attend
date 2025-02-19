<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="is-admin" content="{{ Auth::check() ? Auth::user()->admin_flag : '0' }}">

    <title>Adseed勤怠システム</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/jquery-ui/jquery-ui.css') }}" rel="stylesheet">

    @yield('addCss')
</head>
<body>
<meta name="user-id" content="{{ Auth::id() }}">
@auth
    <meta name="is-admin" content="{{ Auth::user()->admin_flag }}">
@else
    <meta name="is-admin" content="0">
@endauth
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                Adseed勤怠システム
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('ログイン') }}</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->last_name }} さんでログイン中<span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('ログアウト') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @auth
                @if(!Auth::user()->admin_flag)
                    @php
                        $hasUnreadMessages = \App\Models\ChatMessage::whereIn(
                            'chat_room_id',
                            \App\Models\ChatRoom::where('user_id', Auth::id())->pluck('id')
                        )
                        ->where('user_id', '!=', Auth::id())
                        ->where('read_flag', 0)
                        ->exists();
                    @endphp
                    @if($hasUnreadMessages)
                        <div class="col-md-12 alert alert-danger" style="max-width: 1140px; margin: auto; margin-top: 30px;">
                            <div class="container text-center">
                                未読のメッセージがあります。
                            </div>
                        </div>
                    @endif
                @endif
            @endauth
        @can('admin')
            @include('layouts.sidebar')
        @else
            <!-- メインコンテンツ -->
                <main class="py-4">
                    <div class="container">
                        @if (session('flash_message'))
                            <div class="row">
                                <div class="col-md-12 alert alert-info">
                                    {{ session('flash_message') }}
                                </div>
                            </div>
                        @endif
                        @if (session('error_message'))
                            <div class="row">
                                <div class="col-md-12 alert alert-danger">
                                    {{ session('error_message') }}
                                </div>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="row">
                                <div class="col-md-12 alert alert-danger">
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    @yield('content')
                </main>
            @endcan
            <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}"></script>
        <script src="{{ asset('vendor/jquery-ui/jquery-ui.js') }}"></script>
        <script src="{{ asset('vendor/jquery-ui/jquery.mtz.monthpicker.js') }}"></script>
        <script src="{{ asset('js/common.js') }}"></script>
        <script src="{{ asset('js/checkAdminFlag.js') }}"></script>
        @yield('addJs')
    </div>
</body>
</html>

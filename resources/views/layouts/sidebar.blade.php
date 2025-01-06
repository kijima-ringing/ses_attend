<div class="container-fluid">
    <div class="row">
        <nav class="col-md-1 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky pt-5">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        管理者用
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.attendance_header.index') }}">
                            勤怠一覧
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.settings.edit') }}">
                            全体設定
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.department.index') }}">
                            部門一覧
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users.index') }}">
                            社員一覧
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.request.index') }}">
                            申請一覧
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="col-sm-10 col-sm-offset-3 main pl-5">
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
        </div>
    </div>
</div>

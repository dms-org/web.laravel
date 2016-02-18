@extends('dms::template.template')
<?php /** @var \Dms\Core\Auth\IUser $user */ ?>
<?php /** @var array $navigation */ ?>
@section('body-content')
    <body class="hold-transition skin-blue sidebar-mini">

    <div class="wrapper">

        <header class="main-header">
            <!-- Logo -->
            <a href="{{ route('dms::index') }}" class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini"><strong>DMS</strong></span>
                <!-- logo for regular state and mobile devices -->
                <span class="logo-lg"><strong>DMS</strong> <small>{{ '{' . request()->server->get('SERVER_NAME') . '}' }}</small></span>
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>

                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <!-- Messages: style can be found in dropdown.less-->
                        <li class="dropdown messages-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-envelope-o"></i>
                                <span class="label label-success">4</span>
                            </a>
                            <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <span>{{ $user->getUsername() }}</span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- Menu Body-->
                                <li class="user-body">
                                    <a href="{{ route('dms::auth.user.profile') }}">{{ $user->getEmailAddress() }}</a>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="{{ route('dms::auth.user.profile') }}"
                                           class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="{{ route('dms::auth.logout') }}" class="btn btn-default btn-flat">Log
                                            out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <!-- Control Sidebar Toggle Button -->
                        <li>
                            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">
            <section class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel">
                    <div class="pull-left info">
                        <p>{{ $user->getUsername() }}</p>
                        <a href="{{ route('dms::auth.user.profile') }}">
                            <i class="fa fa-circle text-success"></i> {{ $user->getEmailAddress() }}
                        </a>
                    </div>
                </div>
                <!-- search form -->
                <form action="{{ route('dms::search') }}" method="get" class="sidebar-form">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Search...">
                        <span class="input-group-btn">
                            <button type="submit" name="search" id="search-btn" class="btn btn-flat">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                </form>
                <!-- /.search form -->
                <!-- sidebar menu: : style can be found in sidebar.less -->
                <ul class="sidebar-menu">
                    <li class="header">INSTALLED PACKAGES</li>
                    <li class="active treeview">
                    @foreach($navigation as $url => $label)
                        @if(is_array($label))
                            <li class="treeview">
                                <a href="javascript:void(0)">
                                    <span>{{ $url }}</span>
                                    <i class="fa fa-angle-left pull-right"></i>
                                </a>
                                <ul class="treeview-menu">
                                    @foreach($label as $url => $innerLabel)
                                        <li><a href="{{ $url }}"><i class="fa fa-circle-o"></i> {{ $innerLabel }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}">
                                    <span>{{ $label }}</span>
                                    <i class="fa fa-angle-left pull-right"></i>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </section>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    {{ $pageTitle }}
                    @if (!empty($pageSubtitle))
                        <small>{{ $pageSubtitle }}</small>
                    @endif
                </h1>
                <ol class="breadcrumb">
                    @if (!empty($breadcrumbs))
                        @foreach ($breadcrumbs as $link => $label)
                            <li>
                                <a href="{{ $link }}">
                                    @if ($link === url('/')) <i class="fa fa-dashboard"></i> @endif {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    @endif
                    <li class="active">{{ $pageTitle }} </li>
                </ol>
            </section>

            <section class="content">
                @yield('content')
            </section>
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <div class="pull-right hidden-xs">
                <b>Version</b> {{ \Dms\Core\ICms::VERSION }}
            </div>
            <span>
                For issues or enquiries please contact
                <a href="{{ config('dms.contact.website') }}">{{ config('dms.contact.company') }}</a>.
            </span>
        </footer>
    </div>
    </body>
@endsection

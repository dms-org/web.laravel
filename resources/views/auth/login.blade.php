@extends('dms::template.auth')
@section('content')
    <p class="login-box-msg">Log in to continue</p>

    <form action="{{ route('dms::auth.login') }}" method="post">
        {!! csrf_field() !!}

        <div class="form-group has-feedback{{ $errors->has('username') ? ' has-error' : '' }}">
            <input type="text" name="username" class="form-control" placeholder="Username" value="{{ old('username') }}">
            <span class="fa fa-user form-control-feedback"></span>

            @if ($errors->has('username'))
                <span class="help-block">
                    <strong>{{ $errors->first('username') }}</strong>
                </span>
            @endif
        </div>
        <div class="form-group has-feedback{{ $errors->has('password') ? ' has-error' : '' }}">
            <input type="password" name="password" class="form-control" placeholder="Password">
            <span class="fa fa-lock form-control-feedback"></span>

            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif
        </div>
        <div class="row">
            <!-- /.col -->
            <div class="col-xs-12">
                <button type="submit" class="btn btn-primary btn-block btn-flat">Log In</button>
            </div>
            <!-- /.col -->
        </div>
    </form>

    <br>
    <a href="{{ route('dms::auth.password.forgot') }}">I forgot my password</a>
@endsection

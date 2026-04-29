@include('header')

        <!-- /. header-section-->

    <div class="widget-form">
        <h3 class="text-white mb30"> Register Your Self</h3>

        @if(Session::has('message'))
            <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
        @endif

        <!-- validation message -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post">
            @csrf
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Enter your name..." class="form-control" required maxlength="25">
                    </div>
                </div>
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Enter your email..." class="form-control" required>
                    </div>
                </div>
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Enter password (8 to 15 digit...)" class="form-control" minlength="8" maxlength="15" required>
                    </div>
                </div>
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="form-group">
                        <input type="password" name="password2" placeholder="Re-enter password (8 to 15 digit...)" class="form-control" minlength="8" maxlength="15" required>
                    </div>
                </div>
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="form-group">
                        <input type="text" name="mobile" placeholder="Enter mobile number" class="form-control" minlength="10" maxlength="10" required>
                    </div>
                </div>

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <input type="submit" name="register" value="Register" class="btn btn-primary" style="margin-left: 10px;">
                </div>

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <a href="/login" class="btn btn-primary" style="margin-top: 20px;">Back to Login</a>
                </div>

            </div>
        </form>
        <!-- /.form -->
    </div>

@include('footer')
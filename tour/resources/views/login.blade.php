@include('header')

     
        <!-- /. header-section-->

    <div class="widget-form">
        <h3 class="text-white mb30"> Login Now </h3>
         @if(Session::has('message'))
            <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
        @endif
        <form method="post">
            @csrf
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Enter your email..." class="form-control" required>
                    </div>
                </div>
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Enter your password..." class="form-control" minlength="8" maxlength="15" required>
                    </div>
                </div>

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <input type="submit" name="login" value="Loing" class="btn btn-primary">
                    <a href="/register" class="btn btn-primary" style="margin-left: 10px;">Register Now</a>
                </div>

            </div>
        </form>
        <!-- /.form -->
    </div>


   

@include('footer')
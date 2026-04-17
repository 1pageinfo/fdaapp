<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Login | FESCOM</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="{{ asset('theme/vendors/feather/feather.css') }}">
  <link rel="stylesheet" href="{{ asset('theme/vendors/ti-icons/css/themify-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('theme/vendors/css/vendor.bundle.base.css') }}">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="{{ asset('theme/css/vertical-layout-light/style.css') }}">
  <!-- endinject -->
  <link rel="shortcut icon" href="{{ asset('theme/images/logo.jpg') }}" />
</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <div class="brand-logo">
                <img src="{{ asset('theme/images/logo.jpg') }}" alt="logo" style="width:80px;" >
              </div>
              <h4>New here?</h4>
              <h6 class="font-weight-light">Signing up is easy. It only takes a few steps</h6>
              <form class="pt-3" method="POST" action="{{ route('register.submit') }}">
                     @csrf
                <div class="form-group">
                  <input type="text" name="name" class="form-control form-control-lg" id="exampleInputUsername1" required value="{{ old('name') }}" placeholder="Enter your name">
                </div>
                  @error('name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                <div class="form-group">
                  <input type="email"  name="email"  class="form-control form-control-lg" id="exampleInputEmail1" required value="{{ old('email') }}" placeholder="Enter Your Email">
                </div>
                 @error('email')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror


                <div class="form-group">
                  <input type="password" name="password" class="form-control form-control-lg" id="exampleInputPassword1" placeholder="Enter New Password" required>
                </div>

                  @error('password')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
  <div class="form-group">
                  <input type="password" name="password_confirmation" class="form-control form-control-lg" id="exampleInputPassword1" placeholder="Confirm New Password" required>
                </div>

                
                <div class="mb-4">
                  <div class="form-check">
                    <label class="form-check-label text-muted">
                      <input type="checkbox" class="form-check-input" required>
                      I agree to all Terms & Conditions
                    </label>
                  </div>
                </div>
                <div class="mt-3">
                   <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">Register</button>
                </div>
                <div class="text-center mt-4 font-weight-light">
                  Already have an account? <a href="{{ route('login') }}" class="text-primary">Login</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- content-wrapper ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->
  <!-- plugins:js -->
    <script src="{{ asset('theme/vendors/js/vendor.bundle.base.js') }}"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="{{ asset('theme/js/off-canvas.js') }}"></script>
  <script src="{{ asset('theme/js/hoverable-collapse.js') }}"></script>
  <script src="{{ asset('theme/js/template.js') }}"></script>
  <script src="{{ asset('theme/js/settings.js') }}"></script>
  <script src="{{ asset('theme/js/todolist.js') }}"></script>
  <!-- endinject -->
</body>

</html>

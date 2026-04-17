<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | FESCOM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ffffffff, #ffffffff);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border-radius: 15px;
            overflow: hidden;
        }
        .card-header {
            font-size: 1.3rem;
            font-weight: 600;
            text-align: center;
            color: #212529;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d4613ff;
        }
        .btn-primary {
            border-radius: 8px;
        }
        .forgot-footer a {
            text-decoration: none;
        }
        .forgot-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        Forgot Password
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <!-- Email -->
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                                @error('email')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Send Link Button -->
                            <button type="submit" class="btn btn-success w-100">Send Reset Link</button>

                            <!-- Back to Login -->
                            <p class="mt-3 text-center forgot-footer">
                                <a href="{{ route('login') }}">Back to Login</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

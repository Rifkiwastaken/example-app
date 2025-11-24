<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIBIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 400px;
            margin: 5% auto;
        }
        .logo {
            color: #28a745;
            font-weight: bold;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .welcome-text {
            text-align: center;
            color: #333;
            margin-bottom: 2rem;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0.75rem;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-login {
            background-color: #28a745;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-weight: 500;
            width: 100%;
        }
        .btn-login:hover {
            background-color: #218838;
        }
        .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
        }
        .links {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }
        .links a {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-card">
                    <div class="logo">SIBIT</div>
                    <div class="welcome-text">Selamat Datang</div>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Keep me signed in
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login">Sign In</button>
                        
                        <div class="links">
                            <a href="#">Forgot your password?</a>
                            <a href="#">No account? Learn more.</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


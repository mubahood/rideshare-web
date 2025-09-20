<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('admin.name') }} | Admin Login</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    
    @if (!is_null($favicon = Admin::favicon()))
        <link rel="shortcut icon" href="{{ $favicon }}">
    @endif
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #040404;
            --primary-dark: #FF9900;
            --accent-color: #ECC60F;
            --success-color: #4CAF50;
            --error-color: #F44336;
            --warning-color: #FF9800;
            --gray-50: #f7f7f7;
            --gray-100: #f2f2f2;
            --gray-200: #e6e6e6;
            --gray-300: #cccccc;
            --gray-400: #999999;
            --gray-500: #666666;
            --gray-600: #37474F;
            --gray-700: #263238;
            --gray-800: #1a1a1a;
            --gray-900: #0d0d0d;
            --border-radius: 0px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: linear-gradient(135deg, #040404 0%, #FF9900 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.5;
        }

        .login-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }

        .login-header {
            background: var(--primary-color);
            padding: 40px 30px;
            text-align: center;
            color: var(--accent-color);
        }

        .login-logo {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 400;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group:last-of-type {
            margin-bottom: 32px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 400;
            background: var(--gray-50);
            transition: all 0.2s ease-in-out;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgb(59 130 246 / 0.1);
        }

        .form-input::placeholder {
            color: var(--gray-400);
        }

        .form-input.error {
            border-color: var(--error-color);
            background: rgb(254 242 242);
        }

        .form-input.error:focus {
            border-color: var(--error-color);
            box-shadow: 0 0 0 3px rgb(239 68 68 / 0.1);
        }

        .error-message {
            display: flex;
            align-items: center;
            margin-top: 8px;
            font-size: 14px;
            color: var(--error-color);
            font-weight: 500;
        }

        .error-icon {
            width: 16px;
            height: 16px;
            margin-right: 6px;
            flex-shrink: 0;
        }

        .remember-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
        }

        .checkbox-input {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            accent-color: var(--primary-color);
        }

        .checkbox-label {
            font-size: 14px;
            color: var(--gray-600);
            font-weight: 400;
            user-select: none;
            cursor: pointer;
        }

        .forgot-password {
            font-size: 14px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease-in-out;
        }

        .forgot-password:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            padding: 14px 24px;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            position: relative;
            overflow: hidden;
        }

        .login-button:hover {
            background: #d4b00e;
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .login-footer {
            text-align: center;
            padding: 20px 30px;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
        }

        .login-footer-text {
            font-size: 13px;
            color: var(--gray-500);
        }

        .login-footer-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer-link:hover {
            text-decoration: underline;
        }

        /* Success and error alerts */
        .alert {
            padding: 12px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .alert-success {
            background: rgb(236 253 245);
            color: var(--success-color);
            border: 1px solid rgb(167 243 208);
        }

        .alert-error {
            background: rgb(254 242 242);
            color: var(--error-color);
            border: 1px solid rgb(252 165 165);
        }

        .alert-icon {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            flex-shrink: 0;
        }

        /* Input icons */
        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--gray-400);
            pointer-events: none;
        }

        .form-input.with-icon {
            padding-left: 48px;
        }

        /* Responsive design */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                margin: 0;
            }
            
            .login-header,
            .login-body,
            .login-footer {
                padding-left: 20px;
                padding-right: 20px;
            }
            
            .login-header {
                padding-top: 30px;
                padding-bottom: 30px;
            }
            
            .login-body {
                padding-top: 30px;
                padding-bottom: 30px;
            }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="login-logo">{{ config('admin.name') }}</div>
            <div class="login-subtitle">Admin Portal</div>
        </div>

        <!-- Body -->
        <div class="login-body">
            <!-- Success/Error Messages -->
            @if ($errors->any())
                <div class="alert alert-error">
                    <svg class="alert-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    <svg class="alert-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ admin_url('auth/login') }}" method="post" id="loginForm">
                @csrf
                
                <!-- Email/Username/Phone Input -->
                <div class="form-group">
                    <label for="username" class="form-label">Email, Username, or Phone</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                        </svg>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input with-icon {{ $errors->has('username') ? 'error' : '' }}" 
                            placeholder="Enter your email, username, or phone number"
                            value="{{ old('username') }}"
                            required
                            autocomplete="username"
                        >
                    </div>
                    @if ($errors->has('username'))
                        <div class="error-message">
                            <svg class="error-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $errors->first('username') }}
                        </div>
                    @endif
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input with-icon {{ $errors->has('password') ? 'error' : '' }}" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                    @if ($errors->has('password'))
                        <div class="error-message">
                            <svg class="error-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>

                <!-- Remember me & Forgot password -->
                <div class="remember-section">
                    @if (config('admin.auth.remember'))
                        <div class="checkbox-container">
                            <input 
                                type="checkbox" 
                                id="remember" 
                                name="remember" 
                                value="1" 
                                class="checkbox-input"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <label for="remember" class="checkbox-label">Remember me</label>
                        </div>
                    @endif
                    
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="login-button" id="submitButton">
                    <span id="buttonText">Sign In</span>
                    <span id="buttonLoading" class="loading hidden"></span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="login-footer">
            <p class="login-footer-text">
                © {{ date('Y') }} {{ config('admin.name') }}. 
                Made with ❤️ by <a href="https://twitter.com/8TechConsults" target="_blank" class="login-footer-link">8Technologies</a>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const submitButton = document.getElementById('submitButton');
            const buttonText = document.getElementById('buttonText');
            const buttonLoading = document.getElementById('buttonLoading');

            form.addEventListener('submit', function() {
                // Show loading state
                submitButton.disabled = true;
                buttonText.classList.add('hidden');
                buttonLoading.classList.remove('hidden');
            });

            // Reset form state if there are errors
            if (document.querySelector('.error-message')) {
                submitButton.disabled = false;
                buttonText.classList.remove('hidden');
                buttonLoading.classList.add('hidden');
            }

            // Auto-focus first input
            const firstInput = document.getElementById('username');
            if (firstInput && !firstInput.value) {
                firstInput.focus();
            }

            // Remove error state on input
            document.querySelectorAll('.form-input.error').forEach(function(input) {
                input.addEventListener('input', function() {
                    this.classList.remove('error');
                    const errorMessage = this.parentNode.parentNode.querySelector('.error-message');
                    if (errorMessage) {
                        errorMessage.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
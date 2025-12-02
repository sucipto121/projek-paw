<?php
session_start();

// Deteksi apakah user sudah login
$already = isset($_SESSION['user']);
$force   = (isset($_GET['force']) && $_GET['force'] === '1');

// Fungsi aman untuk htmlspecialchars
function e($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Buat CSRF token jika belum ada, atau dipaksa reset
if (!$already || $force) {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
        $_SESSION['csrf_token_time'] = time();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login Admin — Restoran Laut Nusantara</title>
    <meta name="theme-color" content="#ff6b35">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .bg-gradient {
            position: absolute;
            top: -50%;
            right: -20%;
            width: 800px;
            height: 800px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 0;
        }

        .bg-gradient::before {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -50%;
            width: 600px;
            height: 600px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .card {
            background: white;
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-container {
            margin-bottom: 16px;
        }

        .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 8px 24px rgba(255, 107, 53, 0.3);
            border: 4px solid #fff;
            margin: 0 auto;
            display: block;
        }

        .brand h1 {
            font-size: 24px;
            font-weight: 700;
            color: #ff6b35;
            margin-bottom: 8px;
        }

        .muted {
            color: #666;
            font-size: 14px;
        }

        .alert {
            background: #fff5f0;
            border: 2px solid #ffd4c0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            color: #ff6b35;
            font-size: 14px;
            line-height: 1.6;
        }

        .alert strong {
            font-weight: 600;
        }

        .field {
            display: block;
            margin-bottom: 20px;
        }

        .label-text {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #ff6b35;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        .password-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrap input {
            padding-right: 50px;
        }

        .toggle-pwd {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            padding: 8px;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        .toggle-pwd:hover {
            opacity: 1;
        }

        .row {
            display: flex;
            margin-bottom: 24px;
        }

        .row.between {
            justify-content: space-between;
            align-items: center;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
            cursor: pointer;
        }

        .checkbox input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #ff6b35;
        }

        .btn {
            width: 100%;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn.primary {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
        }

        .btn.primary:active {
            transform: translateY(0);
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin: 16px 0;
        }

        .btn.secondary {
            flex: 1;
            background: #fff5f0;
            color: #ff6b35;
            border: 2px solid #ffe8e0;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn.secondary:hover {
            background: #ffe8e0;
            border-color: #ffd4c0;
        }

        .btn.logout {
            flex: 1;
            background: #fff4f4;
            color: #c62828;
            border: 2px solid #ffebee;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn.logout:hover {
            background: #ffebee;
            border-color: #ffcdd2;
        }

        .link {
            color: #ff6b35;
            text-decoration: none;
            font-weight: 600;
        }

        .link:hover {
            text-decoration: underline;
        }

        .card-footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #f0f0f0;
            font-size: 13px;
        }

        .note {
            font-size: 13px;
            color: #666;
            margin-top: 12px;
            text-align: center;
        }

        @media (max-width: 480px) {
            .card {
                padding: 32px 24px;
            }

            .brand h1 {
                font-size: 20px;
            }

            .logo-img {
                width: 70px;
                height: 70px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>
    <main class="login-container">
        <section class="card">
            <div class="brand">
                <div class="logo-container">
                    <img src="images/logo.jpg" alt="Logo Restoran Laut Nusantara" class="logo-img">
                </div>
                <h1>Restoran Laut Nusantara</h1>
                <p class="muted">Masuk untuk mengelola pesanan dan menu</p>
            </div>

            <?php
                $user = is_array($_SESSION['user'] ?? null) ? $_SESSION['user'] : [];
            ?>
            <?php if ($already && ! $force): ?>
                <div class="alert" role="alert">
                    Anda sudah masuk sebagai <strong><?php echo e($user['nama'] ?? $user['username'] ?? ''); ?></strong>.
                </div>
                <div class="action-buttons">
                    <a class="btn secondary" href="index.php">Lanjut ke Dashboard</a>
                    <a class="btn logout" href="logout.php">Logout</a>
                </div>
                <p class="note">Atau <a href="login.php?force=1" class="link">masuk sebagai akun lain</a>.</p>
            <?php else: ?>

            <?php if (!empty($_GET['err'])): ?>
                <div class="alert" role="alert"><?php echo e($_GET['err']); ?></div>
            <?php endif; ?>

            <form id="loginForm" action="proses_login.php" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">

                <!-- Hidden dummy inputs to reduce browser autofill of credentials -->
                <input type="text" name="fakeusernameremembered" id="fakeusernameremembered" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;" tabindex="-1" autocomplete="off">
                <input type="password" name="fakepasswordremembered" id="fakepasswordremembered" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;" tabindex="-1" autocomplete="off">

                <label class="field">
                    <span class="label-text">Username</span>
                    <input name="username" id="username" type="text" required placeholder="admin" maxlength="191" autocomplete="off" value="">
                </label>

                <label class="field">
                    <span class="label-text">Kata Sandi</span>
                    <div class="password-wrap">
                        <input id="password" name="password" type="password" required placeholder="••••••••" maxlength="128" autocomplete="new-password" value="">
                        <button type="button" id="togglePwd" class="toggle-pwd" aria-label="Tampilkan kata sandi">👁️</button>
                    </div>
                </label>

                <div class="row between">
                    <label class="checkbox">
                        <input type="checkbox" name="remember"> Ingat saya
                    </label>
                </div>

                <button id="submitBtn" class="btn primary" type="submit">Masuk</button>
            </form>
            <?php endif; ?>

            <footer class="card-footer muted">Sistem manajemen restoran — versi lokal</footer>
        </section>
    </main>

    <script>
        // Toggle password visibility
        const togglePwd = document.getElementById('togglePwd');
        const passwordInput = document.getElementById('password');
        
        if (togglePwd && passwordInput) {
            togglePwd.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '🙈';
            });
        }

        // Form validation
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value;
                
                if (!username || !password) {
                    e.preventDefault();
                    alert('Username dan password harus diisi!');
                    return false;
                }
            });
        }
    </script>
</body>
</html>
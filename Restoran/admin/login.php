<?php
session_start();

// If user already logged in and not forcing the login form, show a friendly notice
$already = isset($_SESSION['user']);
$force = (isset($_GET['force']) && $_GET['force'] === '1');

// Function to escape output
function e($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Only create CSRF token when the login form will be shown
if (!($already && !$force)) {
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
    <title>Login Admin — Restoran</title>
    <link rel="stylesheet" href="css/login.css">
    <meta name="theme-color" content="#0b74de">
</head>
<body>
    <div class="bg-gradient"></div>
    <main class="login-container">
        <section class="card">
            <div class="brand">
                <div class="logo">R</div>
                <h1>Restoran Admin</h1>
                <p class="muted">Masuk untuk mengelola pesanan dan menu</p>
            </div>

            <?php if ($already && ! $force): ?>
                <div class="alert" role="alert">
                    Anda sudah masuk sebagai <strong><?php echo e($_SESSION['user']['nama'] ?? $_SESSION['user']['username']); ?></strong>.
                </div>
                <div style="display:flex;gap:10px;margin:12px 0">
                    <a class="btn" style="flex:1;background:#edf7ff;color:var(--bg1);border:1px solid rgba(11,116,222,0.12);text-align:center;line-height:36px;text-decoration:none" href="index.php">Lanjut ke Dashboard</a>
                    <a class="btn" style="flex:1;background:#fff4f4;color:#9b1b1b;border:1px solid rgba(255,107,107,0.12);text-align:center;line-height:36px;text-decoration:none" href="logout.php">Logout / Ganti akun</a>
                </div>
                <p style="font-size:13px;color:var(--muted);margin-top:6px">Atau <a href="login.php?force=1" class="link">masuk sebagai akun lain</a>.</p>
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
                    <span class="label-text">Username atau Email</span>
                    <input name="username" id="username" type="text" required placeholder="admin atau email@contoh.com" maxlength="191" autocomplete="off" value="">
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
                    <a class="link" href="#">Lupa kata sandi?</a>
                </div>

                <button id="submitBtn" class="btn primary" type="submit">Masuk</button>
            </form>
            <?php endif; ?>

            <footer class="card-footer muted">Sistem manajemen restoran — versi lokal</footer>
        </section>
    </main>

    <script src="login.js"></script>
</body>
</html>

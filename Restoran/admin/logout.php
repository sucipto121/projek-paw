<?php
session_start();
// Clear all session variables
$_SESSION = [];

// If session cookie exists, delete it
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'],
		$params['secure'], $params['httponly']
	);
}

if (isset($_COOKIE['remember'])) {
	setcookie('remember', '', time() - 42000, '/');
	unset($_COOKIE['remember']);
}

session_destroy();

// Redirect to login page with message
header('Location: ../pengguna/index.php');
exit;

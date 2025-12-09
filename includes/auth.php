<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function find_user_by_username($username) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    return $stmt->fetch();
}
function login_user($username, $password) {
    $user = find_user_by_username($username);
    if (!$user) return false;
    if (!password_verify($password, $user['password_hash'])) return false;
    $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username'], 'role' => $user['role']];
    return true;
}
function logout_user() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
function is_logged_in() {
    return isset($_SESSION['user']);
}
function current_user() {
    return $_SESSION['user'] ?? null;
}
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . APP_BASE . '/login.php');
        exit;
    }
}
function require_role($role) {
    if (!is_logged_in()) {
        header('Location: ' . APP_BASE . '/login.php');
        exit;
    }
    $user = current_user();
    if ($user['role'] !== $role) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}
?>
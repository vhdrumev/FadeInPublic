<?php

session_start();

//ini_set('session.use_strict_mode', 1);
//ini_set('session.cookie_httponly', 1);
//ini_set('session.cookie_secure', 1);
//ini_set('session.use_only_cookies', 1);

if (isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'accepted') {
    if (empty($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
        $pdo = getDatabaseConnection();

        try {
            $cookieData = explode(':', $_COOKIE['remember_me']);
            if (count($cookieData) !== 2) {
                throw new Exception("Invalid cookie format.");
            }

            list($userId, $cookieToken) = $cookieData;

            $stmt = $pdo->prepare("SELECT id, username, password, role, remember_token FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if ($user && !empty($user['remember_token']) && hash_equals($user['remember_token'], $cookieToken)) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

                setRememberMeCookie($user['id'], $pdo);
            } else {
                deleteRememberMeCookie();
            }
        } catch (Exception $e) {
            error_log("Remember Me error: " . $e->getMessage());
            deleteRememberMeCookie();
        }
    }
}

function setRememberMeCookie($userId, $pdo)
{
    $secureToken = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("UPDATE user SET remember_token = ? WHERE id = ?");
    $stmt->execute([$secureToken, $userId]);

    setcookie('remember_me', "$userId:$secureToken", [
        'expires' => time() + (30 * 24 * 60 * 60),
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

function deleteRememberMeCookie()
{
    setcookie('remember_me', '', time() - 3600, '/', '', true, true);
}

?>

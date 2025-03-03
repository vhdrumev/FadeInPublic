<?php
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cookie-consent.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    setcookie('remember_me', '', time() - 3600, '/', '', true, true);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

if (isset($_COOKIE['remember_me'])) {
    list($userId, $cookieToken) = explode(':', $_COOKIE['remember_me']);
    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user && hash_equals($cookieToken, hash('sha256', $user['password'] . $user['id']))) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
        } else {
            setcookie('remember_me', '', time() - 3600, '/', '', true, true);
        }
    } catch (PDOException $e) {
        die("An error occurred: " . htmlspecialchars($e->getMessage()));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $usernameOrEmail = trim($_POST['username_or_email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    if ($csrfToken !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $pdo = getDatabaseConnection();

    if ($action === 'signup') {
        if (!$username || !$password || !$email || !$confirmPassword) {
            die("Please provide username, password, email, and confirm password.");
        }

        if ($password !== $confirmPassword) {
            die("Passwords do not match.");
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM user WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                die("Username or email already exists.");
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO user (username, email, password, role, first_name, middle_name, last_name, age, phone, address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $defaultRole = 'user';
            $stmt->execute([$username, $email, $hashedPassword, $defaultRole, $firstName, $middleName, $lastName, $age ?: null, $phone ?: null, $address ?: null]);

            $userId = $pdo->lastInsertId();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['user_role'] = $defaultRole;

            if ($rememberMe) {
                $cookieValue = $userId . ':' . hash('sha256', $hashedPassword . $userId);
                setcookie('remember_me', $cookieValue, time() + 30 * 24 * 60 * 60, '/', '', true, true);
            }

            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            $redirectPath = "/fadein/";
            header("Location: " . $redirectPath);

        } catch (PDOException $e) {
            die("An error occurred during signup: " . htmlspecialchars($e->getMessage()));
        }
    } elseif ($action === 'login') {
        if (!$usernameOrEmail || !$password) {
            die("Please provide your username/email and password.");
        }

        try {
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM user WHERE username = ? OR email = ?");
            $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                die("Invalid username/email or password.");
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            if ($rememberMe) {
                $cookieValue = $user['id'] . ':' . hash('sha256', $user['password'] . $user['id']);
                setcookie('remember_me', $cookieValue, time() + 30 * 24 * 60 * 60, '/', '', true, true); // 30 days
            }

            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            $redirectPath = "/fadein/";
            header("Location: " . $redirectPath);

        } catch (PDOException $e) {
            die("An error occurred during login: " . htmlspecialchars($e->getMessage()));
        }
    } else {
        die("Invalid action. Use 'signup' or 'login'.");
    }
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login & Signup</title>
        <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL . 'public/style/design/authentication.css' ?>" />
    </head>
    <body>
    <div class="container">
        <?php if (!empty($_SESSION['user_id'])): ?>
        <div class="sub-container" style="margin: 0 auto;">
            <div class="logged_in">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <form method="GET" action="">
                    <button type="submit" name="action" value="logout">Logout</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="sub-container">
            <form class="form" method="POST" action="">
                <h2>Join FadeIn Today!</h2>
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" name="action" value="signup">Sign Up</button>
            </form>
        </div>

        <div class="sub-container">
            <form class="form" method="POST" action="">
                <h2>Welcome Back!</h2>
                <input type="text" name="username_or_email" placeholder="Username or Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <div class="remember_me"><input type="checkbox" name="remember_me">    <label for="remember_me">Remember me</label></div>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" name="action" value="login">Login</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    </body>
    </html>
    <?php
}

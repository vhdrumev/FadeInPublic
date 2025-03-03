<?php
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/auth.php';

require_once __DIR__ . '/cookie-consent.php';


if (!isset($_SESSION['user_id'])) {
    $redirectPath = "/fadein/authentication";
    header("Location: " . $redirectPath);
    exit;
}

$pdo = getDatabaseConnection();
$userIdentifier = $_GET['user'] ?? null;

try {
    if ($userIdentifier) {
        $stmt = $pdo->prepare("SELECT username, email, first_name, middle_name, last_name, age, phone, address, created_at, role, profile_picture_path, bio, pronouns FROM user WHERE username = ?");
        $stmt->execute([$userIdentifier]);
    } else {
        $stmt = $pdo->prepare("SELECT username, email, first_name, middle_name, last_name, age, phone, address, created_at, role, profile_picture_path, bio, pronouns FROM user WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }
} catch (PDOException $e) {
    die("An error occurred: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL . 'public/style/design/profile.css' ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL . 'public/style/design/profile.css' ?>" />
</head>
<body>
<div class="container">
    <h2>User Profile</h2>
    <div class="profile-info">
        <div class="profile-picture">
            <img src="<?php echo htmlspecialchars(BASE_URL . (!empty($user['profile_picture_path']) ? $user['profile_picture_path'] : '/public/upload/profile/default.png')); ?>" alt="Profile Picture" />
        </div>
        <p><span>Username:</span> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><span>Bio: </span> <?php echo htmlspecialchars($user['bio']); ?></p>
        <p><span>Pronouns: </span> <?php echo htmlspecialchars($user['pronouns']); ?></p>
        <p><span>Email:</span> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><span>First Name:</span> <?php echo htmlspecialchars($user['first_name'] ?? 'N/A'); ?></p>
        <p><span>Middle Name:</span> <?php echo htmlspecialchars($user['middle_name'] ?? 'N/A'); ?></p>
        <p><span>Last Name:</span> <?php echo htmlspecialchars($user['last_name'] ?? 'N/A'); ?></p>
        <p><span>Age:</span> <?php echo htmlspecialchars($user['age'] ?? 'N/A'); ?></p>
        <p><span>Phone:</span> <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></p>
        <p><span>Address:</span> <?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></p>
        <p><span>Account Created:</span> <?php echo htmlspecialchars($user['created_at']); ?></p>
        <p><span>Account Role: </span><?php echo htmlspecialchars($user['role']); ?></p>
    </div>
    <div class="footer">&copy; 2024 FadeIn. All rights reserved.</div>
</div>
</body>
</html>

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
$message = '';

try {
    $stmt = $pdo->prepare("SELECT username, email, first_name, middle_name, last_name, age, phone, address, profile_picture_path, bio, pronouns FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $firstName = $_POST['first_name'] !== '' ? $_POST['first_name'] : null;
        $middleName = $_POST['middle_name'] !== '' ? $_POST['middle_name'] : null;
        $lastName = $_POST['last_name'] !== '' ? $_POST['last_name'] : null;
        $age = $_POST['age'] !== '' ? $_POST['age'] : null;
        $phone = $_POST['phone'] !== '' ? $_POST['phone'] : null;
        $address = $_POST['address'] !== '' ? $_POST['address'] : null;
        $bio = $_POST['bio'] !== '' ? $_POST['bio'] : null;
        $pronouns = $_POST['pronouns'] !== '' ? $_POST['pronouns'] : null;

        $passwordStmt = $pdo->prepare("SELECT password FROM user WHERE id = ?");
        $passwordStmt->execute([$_SESSION['user_id']]);
        $hashedPassword = $passwordStmt->fetchColumn();

        if (!password_verify($currentPassword, $hashedPassword)) {
            $message = "Current password is incorrect.";
        } else {
            $profilePicturePath = $user['profile_picture_path'];
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['profile_picture'];
                $uploadDirectory = __DIR__ . '/../public/upload/profile/';
                $hashedFileName = md5(uniqid(rand(), true)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                $uploadPath = $uploadDirectory . $hashedFileName;

                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $profilePicturePath = '/public/upload/profile/' . $hashedFileName;
                } else {
                    $message = "Failed to upload profile picture.";
                }
            }

            $updateQuery = "UPDATE user SET username = ?, email = ?, first_name = ?, middle_name = ?, last_name = ?, age = ?, phone = ?, address = ?, profile_picture_path = ?, bio = ?, pronouns = ?";
            $params = [$username, $email, $firstName, $middleName, $lastName, $age, $phone, $address, $profilePicturePath, $bio, $pronouns];

            if (!empty($newPassword)) {
                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateQuery .= ", password = ?";
                $params[] = $newHashedPassword;
            }
            $updateQuery .= " WHERE id = ?";
            $params[] = $_SESSION['user_id'];

            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute($params);

            $message = "Profile updated successfully.";
        }
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
    <title>Edit Profile</title>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL . 'public/style/design/account.css' ?>" />
</head>
<body>
<div class="container">
    <div class="sub-container">
        <h2>Edit Profile</h2>
        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div>
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div>
                <label for="bio">Bio</label>
                <input type="text" name="bio" id="bio" value="<?php echo htmlspecialchars($user['bio']); ?>" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div>
                <label for="pronouns">Pronouns</label>
                <input type="text" name="pronouns" id="pronouns" value="<?php echo htmlspecialchars($user['pronouns']); ?>" required>
            </div>
            <div>
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
            </div>
            <div>
                <label for="middle_name">Middle Name</label>
                <input type="text" name="middle_name" id="middle_name" value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>">
            </div>
            <div>
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
            </div>
            <div>
                <label for="age">Age</label>
                <input type="number" name="age" id="age" value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>">
            </div>
            <div>
                <label for="phone">Phone</label>
                <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            <div>
                <label for="address">Address</label>
                <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
            </div>
            <div>
                <label for="profile_picture">Profile Picture</label>
                <input type="file" name="profile_picture" id="profile_picture">
                <?php if ($user['profile_picture_path']): ?>
                    <p>Current: <img src="<?php echo BASE_URL . htmlspecialchars($user['profile_picture_path']); ?>" alt="Profile Picture" width="100"></p>
                <?php endif; ?>
            </div>
            <div>
                <label for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password" required>
                <p>You must enter your current password to update your profile.</p>
            </div>
            <div>
                <label for="new_password">New Password (Optional)</label>
                <input type="password" name="new_password" id="new_password">
            </div>
            <button type="submit">Update Profile</button>
        </form>
    </div>
</div>
</body>
</html>

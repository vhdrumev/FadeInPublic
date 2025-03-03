<?php
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/auth.php';

require_once __DIR__ . '/cookie-consent.php';

if (empty($_SESSION['user_id'])) {
    $redirectPath = "/fadein/authentication";
    header("Location: " . $redirectPath);
    exit;
}

$userId = $_SESSION['user_id'];
$pdo = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $followId = intval($_POST['follow_id'] ?? 0);

    if (!$followId || $followId === $userId) {
        die("Invalid follow ID.");
    }

    try {
        if ($action === 'follow') {
            $stmt = $pdo->prepare("SELECT type FROM follows WHERE sender_id = ? AND receiver_id = ?");
            $stmt->execute([$userId, $followId]);
            $existingFollow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingFollow) {
                die("Already following.");
            }

            $stmt = $pdo->prepare("INSERT INTO follows (sender_id, receiver_id, type) VALUES (?, ?, 'f')");
            $stmt->execute([$userId, $followId]);

            $stmt = $pdo->prepare("SELECT id FROM follows WHERE sender_id = ? AND receiver_id = ?");
            $stmt->execute([$followId, $userId]);

            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE follows SET type = 'm' WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)")
                    ->execute([$userId, $followId, $followId, $userId]);
            }

            echo "Followed!";
        } elseif ($action === 'unfollow') {
            $stmt = $pdo->prepare("DELETE FROM follows WHERE sender_id = ? AND receiver_id = ?");
            $stmt->execute([$userId, $followId]);

            $stmt = $pdo->prepare("SELECT id FROM follows WHERE sender_id = ? AND receiver_id = ? AND type = 'm'");
            $stmt->execute([$followId, $userId]);

            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE follows SET type = 'f' WHERE sender_id = ? AND receiver_id = ?")
                    ->execute([$followId, $userId]);
            }

            echo "Unfollowed!";
        } else {
            die("Invalid action.");
        }
    } catch (PDOException $e) {
        die("Error: " . htmlspecialchars($e->getMessage()));
    }
}

try {
    $stmt = $pdo->prepare("
        SELECT f.sender_id, f.receiver_id, f.type, 
               u.username AS sender_name, u2.username AS receiver_name
        FROM follows f
        JOIN user u ON f.sender_id = u.id
        JOIN user u2 ON f.receiver_id = u2.id
        WHERE f.sender_id = ? OR f.receiver_id = ?
    ");
    $stmt->execute([$userId, $userId]);
    $follows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Follows</title>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL . 'public/style/design/follow.css'; ?>" />
</head>
<body>
<h1>Following & Mutual Follows</h1>

<h2>Following</h2>
<table>
    <thead>
    <tr>
        <th>Following</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($follows as $follow): ?>
        <?php if ($follow['sender_id'] === $userId): ?>
            <tr>
                <td><?php echo htmlspecialchars($follow['receiver_name']); ?></td>
                <td><?php echo $follow['type'] === 'm' ? 'Mutual' : 'Following'; ?></td>
                <td class="actions">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="follow_id" value="<?php echo $follow['receiver_id']; ?>">
                        <button type="submit" name="action" value="unfollow">Unfollow</button>
                    </form>
                </td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Followers</h2>
<table>
    <thead>
    <tr>
        <th>Follower</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($follows as $follow): ?>
        <?php if ($follow['receiver_id'] === $userId): ?>
            <tr>
                <td><?php echo htmlspecialchars($follow['sender_name']); ?></td>
                <td><?php echo $follow['type'] === 'm' ? 'Mutual' : 'Follower'; ?></td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Follow Someone</h2>
<form method="POST">
    <input type="number" name="follow_id" placeholder="User ID to Follow" required>
    <button type="submit" name="action" value="follow">Follow</button>
</form>
</body>
</html>

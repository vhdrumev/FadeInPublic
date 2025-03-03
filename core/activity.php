<?php

require_once '../connection.php';
require_once __DIR__ . '/auth.php';

if (!isset($_SESSION['user_id'])) {
    $redirectPath = "/fadein/authentication";
    header("Location: " . $redirectPath);
    exit;
}

$pdo = getDatabaseConnection();

try {
    $stmtLikes = $pdo->prepare("SELECT `post_id` FROM `post_likes` WHERE `user_id` = ?");
    $stmtLikes->execute([$_SESSION['user_id']]);
    $likes = $stmtLikes->fetchAll(PDO::FETCH_ASSOC);

    $stmtComments = $pdo->prepare("SELECT `post_id` FROM `post_comments` WHERE `user_id` = ?");
    $stmtComments->execute([$_SESSION['user_id']]);
    $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    die("An error occurred: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Activity</title>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL . './public/style/design/activity.css' ?>" />
</head>
<body>
<div class="container">
    <h2>Post Likes you have</h2>
    <div class="sub-container">
        <?php if (!empty($likes)): ?>
            <?php foreach ($likes as $like): ?>
                <a href="post/<?php echo $like['post_id'] ?>" class="like"><?php echo htmlspecialchars($like['post_id']); ?></a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No likes found.</p>
        <?php endif; ?>
    </div>

    <h2>Post Comments you have</h2>
    <div class="sub-container">
        <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
                <a href="post/<?php echo $comment['post_id'] ?>" class="comment"><?php echo htmlspecialchars($comment['post_id']); ?></a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No comments found.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>



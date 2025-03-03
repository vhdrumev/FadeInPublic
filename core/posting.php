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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['text'] ?? '');
    $hashtagsInput = trim($_POST['hashtags'] ?? '');
    $userId = $_SESSION['user_id'];
    $uploadedFiles = [];

    if (empty($text)) {
        die("Post content cannot be empty.");
    }

    $hashtags = array_filter(array_map('trim', explode(',', $hashtagsInput)));
    if (count($hashtags) > 10) {
        die("You can add up to 10 hashtags only.");
    }

    if (isset($_FILES['files']) && count($_FILES['files']['name']) > 0) {
        $totalFiles = count($_FILES['files']['name']);
        if ($totalFiles > 10) {
            die("You can upload up to 10 files only.");
        }

        $uploadDirectory = __DIR__ . '/../public/upload/post/';
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        foreach ($_FILES['files']['name'] as $key => $filename) {
            $tmpName = $_FILES['files']['tmp_name'][$key];
            if (!is_uploaded_file($tmpName) || empty($tmpName)) {
                continue;
            }
            $fileType = mime_content_type($tmpName);

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];

            if (!in_array($fileType, $allowedTypes)) {
                die("File type not allowed for: $filename.");
            }

            $fileHash = hash('sha256', file_get_contents($tmpName) . uniqid(rand(), true));
            $newFilename = $fileHash . '.' . pathinfo($filename, PATHINFO_EXTENSION);

            $destination = $uploadDirectory . $newFilename;
            if (move_uploaded_file($tmpName, $destination)) {
                $uploadedFiles[] = '/public/upload/post/' . $newFilename;
            } else {
                die("Failed to upload file: $filename.");
            }
        }
    }

    $filesJson = empty($uploadedFiles) ? null : json_encode($uploadedFiles);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO posts (user_id, text, files, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $text, $filesJson]);
        $postId = $pdo->lastInsertId();

        if (!empty($hashtags)) {
            $stmt = $pdo->prepare("INSERT INTO hashtags (post_id, name) VALUES (?, ?)");
            foreach ($hashtags as $hashtag) {
                $stmt->execute([$postId, $hashtag]);
            }
        }

        $pdo->commit();

        $redirectPath = "/fadein/";
        header("Location: " . $redirectPath);
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("An error occurred: " . htmlspecialchars($e->getMessage()));
    }
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create a Post</title>
        <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL . 'public/style/design/posting.css'; ?>" />
    </head>
    <body>
    <div class="container">
        <h2>Create a Post</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <textarea name="text" placeholder="Write something..." rows="4" required></textarea>
            <textarea name="hashtags" placeholder="Add hashtags (comma-separated, max 10)" rows="2"></textarea>
            <label for="files">Upload Files (max 10 files):</label>
            <input type="file" name="files[]" multiple accept="image/*,application/pdf,text/plain">
            <button type="submit">Post</button>
        </form>
        <div class="footer">© 2024 FadeIn. All rights reserved.</div>
    </div>
    </body>
    </html>
    <?php
}
?>

<?php
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/module/markdown.php';
require_once __DIR__ . '/module/emoticon.php';
require_once __DIR__ . '/auth.php';

require_once __DIR__ . '/cookie-consent.php';


$pdo = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die("You must be signed in to perform this action.");
    }

    $userId = $_SESSION['user_id'];

    if (isset($_POST['like_action'], $_POST['post_id'])) {
        $postId = intval($_POST['post_id']);
        $action = $_POST['like_action'];

        try {
            if ($action === 'like') {
                $stmt = $pdo->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
                $stmt->execute([$postId, $userId]);
            } elseif ($action === 'unlike') {
                $stmt = $pdo->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
                $stmt->execute([$postId, $userId]);
            }
        } catch (PDOException $e) {
            die("Failed to process like action: " . htmlspecialchars($e->getMessage()));
        }
    }
    if (isset($_POST['comment_action'], $_POST['post_id'], $_POST['comment_text']) && $_POST['comment_action'] === 'add') {
        $postId = intval($_POST['post_id']);
        $commentText = trim($_POST['comment_text']);

        if (empty($commentText)) {
            die("Comment cannot be empty.");
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO post_comments (post_id, user_id, comment, parent_comment_id) VALUES (?, ?, ?, NULL)");
            $stmt->execute([$postId, $userId, $commentText]);
        } catch (PDOException $e) {
            die("Failed to add comment: " . htmlspecialchars($e->getMessage()));
        }
    }

    if (isset($_POST['comment_action'], $_POST['post_id'], $_POST['comment_text'], $_POST['parent_comment_id']) && $_POST['comment_action'] === 'reply') {
        $postId = intval($_POST['post_id']);
        $parentCommentId = intval($_POST['parent_comment_id']);
        $commentText = trim($_POST['comment_text']);

        if (empty($commentText)) {
            die("Reply cannot be empty.");
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO post_comments (post_id, user_id, comment, parent_comment_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$postId, $userId, $commentText, $parentCommentId]);
        } catch (PDOException $e) {
            die("Failed to reply to comment: " . htmlspecialchars($e->getMessage()));
        }
    }

    if (isset($_POST['comment_like_action'], $_POST['comment_id'])) {
        $commentId = intval($_POST['comment_id']);
        $action = $_POST['comment_like_action'];

        try {
            if ($action === 'like') {
                $stmt = $pdo->prepare("SELECT 1 FROM comment_likes WHERE comment_id = ? AND user_id = ?");
                $stmt->execute([$commentId, $userId]);
                $alreadyLiked = $stmt->fetch();

                if (!$alreadyLiked) {
                    $stmt = $pdo->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
                    $stmt->execute([$commentId, $userId]);
                }
            } elseif ($action === 'unlike') {
                $stmt = $pdo->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
                $stmt->execute([$commentId, $userId]);
            }
        } catch (PDOException $e) {
            die("Failed to process comment like action: " . htmlspecialchars($e->getMessage()));
        }
    }

    if (isset($_POST['comment_action'], $_POST['comment_id']) && $_POST['comment_action'] === 'delete') {
        $commentId = intval($_POST['comment_id']);

        try {
            $pdo->beginTransaction();

            function deleteCommentAndChildren($commentId, $pdo) {
                $stmt = $pdo->prepare("SELECT id FROM post_comments WHERE parent_comment_id = ?");
                $stmt->execute([$commentId]);
                $childComments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($childComments as $childComment) {
                    deleteCommentAndChildren($childComment['id'], $pdo);
                }

                $stmt = $pdo->prepare("DELETE FROM post_comments WHERE id = ?");
                $stmt->execute([$commentId]);

                $stmt = $pdo->prepare("DELETE FROM comment_likes WHERE comment_id = ?");
                $stmt->execute([$commentId]);
            }

            deleteCommentAndChildren($commentId, $pdo);

            $pdo->commit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            die("Failed to delete comment and its children: " . htmlspecialchars($e->getMessage()));
        }
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

if (isset($_GET['post'])) {
    $postId = intval($_GET['post']);

    try {
        $stmt = $pdo->prepare("
            SELECT p.id AS post_id, p.text, p.created_at, p.files, u.username,
                   COUNT(pl.id) AS like_count,
                   GROUP_CONCAT(DISTINCT ul.username SEPARATOR ', ') AS liked_by
            FROM posts p
            INNER JOIN user u ON p.user_id = u.id
            LEFT JOIN post_likes pl ON p.id = pl.post_id
            LEFT JOIN user ul ON pl.user_id = ul.id
            WHERE p.id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        $commentsStmt = $pdo->prepare("
            SELECT c.id AS comment_id, c.post_id, c.comment, c.created_at, u.username, c.user_id, c.parent_comment_id,
                   (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id) AS like_count
            FROM post_comments c
            INNER JOIN user u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ");
        $commentsStmt->execute([$postId]);
        $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

        $commentsByPost = [];
        foreach ($comments as $comment) {
            $commentsByPost[$comment['post_id']][] = $comment;
        }
    } catch (PDOException $e) {
        die("Failed to fetch the post or comments: " . htmlspecialchars($e->getMessage()));
    }
} else {
    die("Post ID is missing.");
}

function renderComments($comments, $postId, $parentId = null, $level = 0)
{
    global $pdo;

    foreach ($comments as $comment) {
        if ($comment['post_id'] == $postId && $comment['parent_comment_id'] == $parentId) {
            echo '<div class="comment" style="margin-left: ' . ($level * 20) . 'px;">';
            echo '<div class="username">' . htmlspecialchars($comment['username']) . '</div>';
            echo '<div class="created_at">' . date("F j, Y, g:i A", strtotime($comment['created_at'])) . '</div>';
            echo '<div class="comment-text">' . nl2br(htmlspecialchars($comment['comment'])) . '</div>';

            $likesStmt = $pdo->prepare("
                SELECT u.username
                FROM comment_likes cl
                INNER JOIN user u ON cl.user_id = u.id
                WHERE cl.comment_id = ?
            ");
            $likesStmt->execute([$comment['comment_id']]);
            $likes = $likesStmt->fetchAll(PDO::FETCH_COLUMN);

            echo '<div class="likes">';
            echo '<span>' . $comment['like_count'] . ' likes </span>';
            if ($likes) {
                echo '<small>Liked by: ' . htmlspecialchars(implode(', ', $likes)) . '</small>';
            }
            echo '</div>';

            if (isset($_SESSION['user_id'])) {
                $commentLikedStmt = $pdo->prepare("SELECT 1 FROM comment_likes WHERE comment_id = ? AND user_id = ?");
                $commentLikedStmt->execute([$comment['comment_id'], $_SESSION['user_id']]);
                $isCommentLiked = $commentLikedStmt->fetch();

                echo '<form method="POST">';
                echo '<input type="hidden" name="comment_id" value="' . $comment['comment_id'] . '">';
                echo '<button type="submit" name="comment_like_action" value="' . ($isCommentLiked ? 'unlike' : 'like') . '">' . ($isCommentLiked ? 'Unlike' : 'Like') . '</button>';
                echo '</form>';
            }

            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']) {
                echo '<form method="POST">';
                echo '<input type="hidden" name="comment_id" value="' . $comment['comment_id'] . '">';
                echo '<button type="submit" name="comment_action" value="delete">Delete</button>';
                echo '</form>';
            }

            if (isset($_SESSION['user_id'])) {
                echo '<form method="POST">';
                echo '<input type="hidden" name="post_id" value="' . $postId . '">';
                echo '<input type="hidden" name="parent_comment_id" value="' . $comment['comment_id'] . '">';
                echo '<textarea name="comment_text" placeholder="Reply..."></textarea>';
                echo '<button type="submit" name="comment_action" value="reply">Reply</button>';
                echo '</form>';
            }

            if (hasReplies($comments, $comment['comment_id'])) {
                echo '<button class="toggle-replies" data-comment-id="' . $comment['comment_id'] . '" onclick="toggleReplies(this)">Show Replies</button>';
            }

            echo '<div class="replies" id="replies-' . $comment['comment_id'] . '" style="display:none;">';
            renderComments($comments, $postId, $comment['comment_id'], $level + 1);
            echo '</div>';
            echo '</div>';
        }
    }
}

function hasReplies($comments, $parentId): bool {
    foreach ($comments as $comment) {
        if ($comment['parent_comment_id'] == $parentId) {
            return true;
        }
    }
    return false;
}

function convertToArray($input) {
    $input = trim($input, '[]');
    $paths = array_map(function($path) {
        return trim($path, ' "');
    }, explode(", ", $input));

    return $paths;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post</title>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL . 'public/style/design/post.css' ?>" />
</head>
<body>
<div class="header">FadeIn</div>
<div class="container">
    <?php if ($post): ?>
        <div class="post">
            <div class="username"><?php echo htmlspecialchars($post['username']); ?></div>
            <div class="created_at"><?php echo date("F j, Y, g:i A", strtotime($post['created_at'])); ?></div>
            <div class="text"><?php echo parseEmoticons(parseMarkdown(htmlspecialchars($post['text']))); ?></div>
            <div class="files">
                <?php if (isset($post['files'])): ?>
                    <?php foreach (convertToArray($post['files']) as $path): ?>
                        <img alt="Could not load image" loading="lazy" width="100px" height="100px" src="<?php echo '../../fadein/' . $path ?>"/>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #aaa; font-size: 12px;">This post has no files uploaded to it</p>
                <?php endif; ?>
            </div>
            <div class="likes">
                <span><?php echo $post['like_count']; ?> likes</span>
                <?php if (!empty($post['liked_by'])): ?>
                    <small>Liked by: <?php echo htmlspecialchars($post['liked_by']); ?></small>
                <?php endif; ?>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $userLikedStmt = $pdo->prepare("SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?");
                $userLikedStmt->execute([$post['post_id'], $_SESSION['user_id']]);
                $isLiked = $userLikedStmt->fetch();
                ?>
                <form method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                    <button type="submit" name="like_action" value="<?php echo $isLiked ? 'unlike' : 'like'; ?>">
                        <?php echo $isLiked ? 'Unlike' : 'Like'; ?>
                    </button>
                </form>
            <?php endif; ?>

            <div class="comments">
                <h4>Comments</h4>
                <?php renderComments($commentsByPost[$postId] ?? [], $postId); ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                        <textarea name="comment_text" placeholder="Add a comment..."></textarea>
                        <button type="submit" name="comment_action" value="add">Post Comment</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <p>Post not found.</p>
    <?php endif; ?>
</div>

<script type="text/javascript">
    window.addEventListener("DOMContentLoaded", function(event) {
        var scrollpos = localStorage.getItem('scrollpos');
        if (scrollpos) window.scrollTo(0, scrollpos);
    });
    window.onbeforeunload = function(e) {
        localStorage.setItem('scrollpos', window.scrollY);
    };

</script>

<script>
    function toggleReplies(button) {
        const commentId = button.getAttribute('data-comment-id');
        const repliesDiv = document.getElementById('replies-' + commentId);
        if (repliesDiv.style.display === 'none') {
            repliesDiv.style.display = 'block';
            button.textContent = 'Hide Replies';
        } else {
            repliesDiv.style.display = 'none';
            button.textContent = 'Show Replies';
        }
    }
</script>


</body>
</html>

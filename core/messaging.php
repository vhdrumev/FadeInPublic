<?php
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/module/timeago.php';
require_once __DIR__ . '/auth.php';

if (!isset($_SESSION['user_id'])) {
    $redirectPath = "/fadein/authentication";
    header("Location: " . $redirectPath);
    exit;
}

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, 
               (SELECT COUNT(*) 
                FROM messages m 
                WHERE m.sender_id = u.id 
                  AND m.receiver_id = ? 
                  AND m.is_seen = 0 
                  AND m.deleted = 0) AS unread_count
        FROM user u
        WHERE u.id != ?
    ");
    $stmt->execute([$userId, $userId]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Failed to fetch users: " . htmlspecialchars($e->getMessage()));
}

$messages = [];
$selectedUser = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['chat_with'])) {
    $selectedUser = (int)$_GET['chat_with'];

    try {
        $stmt = $pdo->prepare("
            SELECT m.id, m.message, m.created_at, m.is_seen, m.sender_id, m.deleted, u.username AS sender_username
            FROM messages m
            INNER JOIN user u ON m.sender_id = u.id
            WHERE ((m.sender_id = ? AND m.receiver_id = ?)
                   OR (m.sender_id = ? AND m.receiver_id = ?))
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$userId, $selectedUser, $selectedUser, $userId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $updateStmt = $pdo->prepare("
            UPDATE messages
            SET is_seen = 1
            WHERE sender_id = ? AND receiver_id = ? AND is_seen = 0
        ");
        $updateStmt->execute([$selectedUser, $userId]);
    } catch (PDOException $e) {
        die("Failed to fetch messages: " . htmlspecialchars($e->getMessage()));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'], $_POST['message'])) {
    $receiverId = (int)$_POST['receiver_id'];
    $message = trim($_POST['message']);

    if (empty($message)) {
        die("Message cannot be empty.");
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, message, created_at, is_seen, deleted) 
            VALUES (?, ?, ?, NOW(), 0, 0)
        ");
        $stmt->execute([$userId, $receiverId, $message]);
        header("Location: messaging.php?chat_with=$receiverId");
        exit;
    } catch (PDOException $e) {
        die("Failed to send message: " . htmlspecialchars($e->getMessage()));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message_id'])) {
    $messageId = (int)$_POST['delete_message_id'];

    try {
        $stmt = $pdo->prepare("UPDATE messages SET deleted = 1 WHERE id = ? AND sender_id = ?");
        $stmt->execute([$messageId, $userId]);
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => htmlspecialchars($e->getMessage())]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch_messages'])) {
    $selectedUser = (int)$_GET['chat_with'];

    try {
        $stmt = $pdo->prepare("
    SELECT m.id, m.message, 
           m.created_at, 
           m.is_seen, m.deleted, u.username AS sender_username, m.sender_id
    FROM messages m
    INNER JOIN user u ON m.sender_id = u.id
    WHERE ((m.sender_id = ? AND m.receiver_id = ?)
           OR (m.sender_id = ? AND m.receiver_id = ?))
    ORDER BY m.created_at ASC
");

        $stmt->execute([$userId, $selectedUser, $selectedUser, $userId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($messages as &$message) {
            $message['pretty_date'] = timeAgo($message['created_at']);
        }



        echo json_encode($messages);
        exit;
    } catch (PDOException $e) {
        die("Failed to fetch messages: " . htmlspecialchars($e->getMessage()));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaging</title>
    <script src="<?php echo BASE_URL . '/public/script/jquery.js'; ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL . './public/style/design/messaging.css'; ?>" />
</head>
<body>
<div class="container">
    <div class="user-list">
        <h3>Users</h3>
        <?php foreach ($users as $user): ?>
            <div class="user">
                <a href="/fadein/core/messaging.php?chat_with=<?php echo $user['id']; ?>">
                    <?php echo htmlspecialchars($user['username']); ?>
                    <?php if ($user['unread_count'] > 0): ?>
                        <span class="unread-count">(<?php echo $user['unread_count']; ?>)</span>
                    <?php endif; ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="messages">
        <?php if ($selectedUser): ?>
            <h3>Chat with
                <?php
                $selectedUsername = ($user = current(array_filter($users, fn($u) => $u['id'] === $selectedUser))) ? $user['username'] : "Unknown";
                echo htmlspecialchars($selectedUsername);
                ?>
            </h3>
            <div id="message-container">
                <?php foreach ($messages as $message): ?>
                    <div class="message">
                        <div class="sender"><?php echo htmlspecialchars($message['sender_username']); ?></div>
                        <div class="time"><?php echo timeAgo(date('F j, Y, g:i A', strtotime($message['created_at']))); ?></div>
                        <div class="text">
                            <?php if ($message['deleted']): ?>
                                <em>Message deleted</em>
                            <?php else: ?>
                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                <?php if ($message['sender_id'] === $userId): ?>
                                    <form method="POST" class="delete-form" data-message-id="<?php echo $message['id']; ?>">
                                        <button type="button" class="delete-button">Delete</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($message['sender_id'] === $userId): ?>
                            <div class="status">
                                <?php echo $message['is_seen'] ? "Seen" : "Sent"; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="POST" action="">
                <textarea name="message" placeholder="Type your message..." rows="3" required></textarea>
                <input type="hidden" name="receiver_id" value="<?php echo $selectedUser; ?>">
                <button type="submit">Send</button>
            </form>
        <?php else: ?>
            <h3>Select a user to start chatting</h3>
        <?php endif; ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        function fetchMessages() {
            let chatWith = <?php echo $selectedUser ?? 0; ?>;
            if (chatWith === 0) return;

            let messageContainer = $('#message-container');
            let isScrolledToBottom = messageContainer.scrollTop() + messageContainer.innerHeight() >= messageContainer[0].scrollHeight;

            $.ajax({
                url: '/fadein/core/messaging.php',
                type: 'GET',
                data: {
                    fetch_messages: true,
                    chat_with: chatWith
                },
                dataType: 'json',
                success: function(response) {
                    if (response) {
                        messageContainer.empty();

                        response.forEach(function(message) {
                            let text = message.deleted ?
                                "<em>Message deleted</em>" :
                                message.message.replace(/\n/g, "<br>");
                            let status = message.sender_id == <?php echo $userId; ?> ?
                                (message.is_seen ? "Seen" : "Sent") : "";

                            let deleteButton = message.sender_id == <?php echo $userId; ?> && !message.deleted ?
                                `<form method="POST" class="delete-form" data-message-id="${message.id}">
                                <button type="button" class="delete-button">Delete</button>
                            </form>` : "";

                            let messageHTML = `
                            <div class="message">
                                <div class="sender">${message.sender_username}</div>
                                <div class="time">${message.pretty_date}</div>
                                <div class="text">${text}</div>
                                ${deleteButton}
                                <div class="status">${status}</div>
                            </div>`;
                            messageContainer.append(messageHTML);
                        });

                        if (isScrolledToBottom) {
                            messageContainer.scrollTop(messageContainer[0].scrollHeight);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error fetching messages:', error);
                }
            });
        }

        $(document).on('click', '.delete-button', function(e) {
            e.preventDefault();
            let form = $(this).closest('.delete-form');
            let messageId = form.data('message-id');

            $.ajax({
                url: '/fadein/core/messaging.php',
                type: 'POST',
                data: { delete_message_id: messageId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        fetchMessages();
                    } else {
                        alert('Error deleting message: ' + response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error deleting message:', error);
                }
            });
        });

        setInterval(fetchMessages, 1000);

        $('form').submit(function(e) {
            e.preventDefault();

            let message = $('textarea[name="message"]').val();
            let receiverId = $('input[name="receiver_id"]').val();

            if (!message.trim()) {
                alert("Message cannot be empty.");
                return;
            }

            $.ajax({
                url: '/fadein/core/messaging.php',
                type: 'POST',
                data: {
                    receiver_id: receiverId,
                    message: message
                },
                success: function() {
                    $('textarea[name="message"]').val('');
                    fetchMessages();
                },
                error: function(xhr, status, error) {
                    alert('Error sending message: ' + error);
                }
            });
        });
    });

</script>

<script>
    var userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    document.cookie = "timezone=" + userTimezone + "; path=/; max-age=" + (30 * 24 * 60 * 60);
</script>


</body>
</html>

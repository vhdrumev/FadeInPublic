<?php

require_once __DIR__ . '/../../connection.php';

function FadeInAlgorithm(array|null $previousPosts = []): array {
    $pdo = getDatabaseConnection();
    $userId = $_SESSION['user_id'] ?? null;
    $postIds = [];

    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT id FROM posts WHERE user_id = :userId AND created_at >= NOW() - INTERVAL 7 DAY");
        $stmt->execute(['userId' => $userId]);
        $myPosts = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare("SELECT receiver_id FROM follows WHERE sender_id = :userId");
        $stmt->execute(['userId' => $userId]);
        $followedUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $followedUsers = array_merge([$userId], $followedUsers);

        $postIds = [];

        if (!empty($followedUsers)) {
            $placeholders = implode(',', array_fill(0, count($followedUsers), '?'));
            $stmt = $pdo->prepare("SELECT id FROM posts WHERE user_id IN ($placeholders) AND created_at >= NOW() - INTERVAL 7 DAY ORDER BY created_at DESC");
            $stmt->execute($followedUsers);
            $postIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        $postIds = array_merge($myPosts, $postIds);
    }

    $previousPostIds = implode(",", array_map('intval', $previousPosts));

    $sql = "SELECT id FROM posts";
    if (!empty($previousPosts)) {
        $sql .= " WHERE id NOT IN ($previousPostIds)";
    }

    $stmt = $pdo->query($sql);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($posts) <= 5) {
        foreach ($posts as $post) {
            $previousPosts[] = $post['id'];
        }
        return $previousPosts;
    }

    shuffle($posts);
    $selectedPosts = array_slice($posts, 0, 5);

    foreach ($selectedPosts as $post) {
        $previousPosts[] = $post['id'];
    }

    return array_merge($postIds, $previousPosts);

}
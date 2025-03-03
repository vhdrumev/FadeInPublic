<?php

require_once __DIR__ . '/../../connection.php';

function getPostsByIds(array $postIds) {
    if (empty($postIds)) {
        return [];
    }
    $placeholders = rtrim(str_repeat('?, ', count($postIds)), ', ');

    $sql = "
        SELECT p.id AS post_id, p.text, p.created_at, p.files, u.username,
               COUNT(pl.id) AS like_count,
               GROUP_CONCAT(DISTINCT ul.username SEPARATOR ', ') AS liked_by
        FROM posts p
        INNER JOIN user u ON p.user_id = u.id
        LEFT JOIN post_likes pl ON p.id = pl.post_id
        LEFT JOIN user ul ON pl.user_id = ul.id
        WHERE p.id IN ($placeholders)
        GROUP BY p.id
        ORDER BY FIELD(p.id, " . implode(", ", array_fill(0, count($postIds), '?')) . ")
    ";
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($postIds, $postIds));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

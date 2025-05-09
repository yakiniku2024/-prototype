<?php
session_start();

require('db_connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$icon = $_SESSION['user_icon'] ?? 'default_icon.jpg';
$comment = $_POST['comment'] ?? '';
$media = null;

// メディアがアップロードされた場合
if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
    $media_name = uniqid() . '-' . basename($_FILES['media']['name']);
    $media_path = 'uploads/' . $media_name;
    move_uploaded_file($_FILES['media']['tmp_name'], $media_path);
    $media = $media_name;
}

// 投稿をデータベースに保存
$stmt = $pdo->prepare("INSERT INTO posts (user_id, username, icon, comment, media) VALUES (:user_id, :username, :icon, :comment, :media)");
$stmt->execute([
    ':user_id' => $user_id,
    ':username' => $username,
    ':icon' => $icon,
    ':comment' => $comment,
    ':media' => $media
]);

header('Location: search.php');
exit;
?>

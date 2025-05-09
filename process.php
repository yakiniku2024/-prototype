<?php

if (isset($_POST)) {
    $username = $_POST['username'];
    $profile_picture = $_POST['profile_picture'];
}

require('db_connection.php');

try {
    // データベースに更新
    $stmt = $pdo->prepare("UPDATE accounts SET profile_picture = :profile_picture WHERE username = :username");
    $stmt->bindParam(':profile_picture', $profile_picture, PDO::PARAM_STR);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
} catch (PDOException $e) {
    die('データベースエラー: ' . $e->getMessage());
}
?>
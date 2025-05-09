<?php

$host = 'localhost';
$dbname = 'sns';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
$db_user = 'root'; // MySQL のユーザー名
$db_password = ''; // MySQL のパスワード

try {
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('データベースエラー: ' . $e->getMessage());
}
?>


<?php
session_start();

// データベース接続設定
require('db_connection.php');

try {
    
    $username = $_SESSION['username'];

    // POSTリクエストがあればアイコンを保存
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['icon'])) {
        $file = $_FILES['icon'];
        $uploadDir = 'uploads/'; // アップロード先のディレクトリ
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        // ファイルタイプとエラー確認
        if (in_array($file['type'], $allowedTypes) && $file['error'] === UPLOAD_ERR_OK) {
            // 一意なファイル名を生成
            $fileName = uniqid() . '_' . basename($file['name']);
            $filePath = $uploadDir . $fileName;

            // ファイルを移動
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // データベースにパスを保存
                $stmt = $pdo->prepare("UPDATE accounts SET icon_path = :icon_path WHERE username = :username");
                $stmt->bindParam(':icon_path', $filePath, PDO::PARAM_STR);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();

                // プロフィールページにリダイレクト
                header('Location: profile.php');
                exit;
            } else {
                $error = 'ファイルのアップロードに失敗しました。';
            }
        } else {
            $error = '無効なファイル形式、またはアップロードエラーが発生しました。';
        }
    }
} catch (PDOException $e) {
    die('データベースエラー: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アイコン変更</title>
    <link rel="stylesheet" href="aicon.css">
    <link href="https://fonts.googleapis.com/css2?family=Hachi+Maru+Pop&display=swap" rel="stylesheet">
</head>

<body>
    <h1>アイコン変更</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form action="icon_upload.php" method="POST" enctype="multipart/form-data">
        <label for="icon">新しいアイコン画像を選択</label>
        <input type="file" name="icon" id="icon" accept="image/*" required>
        <button type="submit">アップロード</button>
    </form>
    <a href="profile.php">プロフィールページに戻る</a>
</body>

</html>

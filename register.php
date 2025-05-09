<?php
// データベース接続情報
require('db_connection.php');

$message = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // フォーム入力値を取得
        $username = $_POST['Username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['Confirm_Password'] ?? '';

        // バリデーション
        if (empty($username) || empty($password) || empty($confirm_password)) {
            $message = '全ての項目を入力してください。';
        } elseif ($password !== $confirm_password) {
            $message = 'パスワードが一致しません。';
        } else {
            // 同じユーザー名が存在するかチェック
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM accounts WHERE username = :username");
            $check_stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $check_stmt->execute();
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                $message = '同じユーザー名が既に登録されています。';
            } else {
                // パスワードをハッシュ化
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // データベースに登録
                $stmt = $pdo->prepare("INSERT INTO accounts (username, password) VALUES (:username, :password)");
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $message = 'アカウントの登録が完了しました！';
                } else {
                    $message = '登録に失敗しました。';
                }
            }
        }
    }
} catch (PDOException $e) {
    $message = 'データベースエラー: ' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規アカウント作成</title>
    <link rel="stylesheet" href="account.css">
    <link href="https://fonts.googleapis.com/css2?family=Hachi+Maru+Pop&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>新規アカウント作成</h1>
        <?php if (!empty($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <h2>ユーザー名</h2>
            <input name="Username" type="text" placeholder="ユーザー名を入力" value="<?= htmlspecialchars($_POST['Username'] ?? '') ?>">

            <h2>パスワード</h2>
            <input name="password" type="password" placeholder="パスワードを入力" minlength="6" maxlength="20">

            <h2>パスワード確認</h2>
            <input name="Confirm_Password" type="password" placeholder="パスワードを再度入力">

            <button class="back-button" onclick="window.open ('login.php')">戻る</button>
            <button class="submit-button">確定</button>
        </form>
    </div>
</body>
</html>

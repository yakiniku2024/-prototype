<?php
// データベース接続情報
require('db_connection.php');

// セッション開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = 'ユーザー名とパスワードを入力してください。';
    } else {
        // データベースからユーザー情報を取得
        $stmt = $pdo->prepare("SELECT * FROM accounts WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // ログイン成功
            $_SESSION['username'] = $user['username'];

            // 成功したら検索画面にリダイレクト
            header('Location: search.php');
            exit;
        } else {
            $message = 'ユーザー名またはパスワードが間違っています。';
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Hachi+Maru+Pop&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>ログイン</h1>
        <?php if (!empty($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <h2>ユーザー名</h2>
            <input name="username" type="text" placeholder="ユーザー名を入力してください" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            <h2>パスワード</h2>
            <input name="password" type="password" placeholder="パスワードを入力してください">
            <button>ログイン</button>
        </form>
        <a href="register.php">新規アカウント作成</a>
    </div>
</body>
</html>

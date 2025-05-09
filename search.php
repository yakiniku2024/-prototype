<?php
session_start();
 
// データベース接続設定
require('db_connection.php');

// セッションが存在しない場合、ログインページにリダイレクト
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
 
// ユーザー情報取得
$username = $_SESSION['username'];
 
try {
    // ユーザーの現在の情報を取得
    $stmt = $pdo->prepare("SELECT birthdate, intro, icon_path FROM accounts WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
 
    // 全ユーザーの投稿を取得（ログイン中のユーザーを最初に表示したい場合は工夫が必要）
$stmt_posts = $pdo->prepare("
SELECT p.comment, p.media_path, p.created_at, a.username, a.icon_path
FROM posts p
JOIN accounts a ON p.username = a.username
ORDER BY p.created_at DESC
");
$stmt_posts->execute();
$posts = $stmt_posts->fetchAll(PDO::FETCH_ASSOC);

 
    // 投稿が送信された場合、データベースに保存
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])) {
        $comment = $_POST['comment'];
 
        if (!empty($_FILES['media']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/ogg'];
            $file_type = mime_content_type($_FILES['media']['tmp_name']);
        
            if (in_array($file_type, $allowed_types)) {
                $upload_dir = 'uploads/';
                $upload_file = $upload_dir . basename($_FILES['media']['name']);
        
                if (move_uploaded_file($_FILES['media']['tmp_name'], $upload_file)) {
                    $stmt_insert = $pdo->prepare("INSERT INTO posts (username, comment, media_path) VALUES (:username, :comment, :media_path)");
                    $stmt_insert->bindParam(':username', $username);
                    $stmt_insert->bindParam(':comment', $comment);
                    $stmt_insert->bindParam(':media_path', $upload_file);
                    $stmt_insert->execute();
        
                    header('Location: search.php'); // 自動リダイレクト
                    exit;
                }
            } else {
                echo "対応していないファイル形式です。";
            }
        }        
    }
 
    // ユーザー名で検索
    if (isset($_GET['search'])) {
        $searchTerm = $_GET['search'];
        $stmt_search = $pdo->prepare("SELECT username, icon_path FROM accounts WHERE username LIKE :searchTerm");
        $stmt_search->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
        $stmt_search->execute();
        $searchResults = $stmt_search->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $searchResults = [];
    }
 
    // ユーザー名で検索
    if (isset($_GET['search'])) {
        $searchTerm = $_GET['search'];
        $stmt_search = $pdo->prepare("SELECT username, icon_path FROM accounts WHERE username LIKE :searchTerm");
        $stmt_search->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
        $stmt_search->execute();
        $searchResults = $stmt_search->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $searchResults = [];
    }
    
} catch (PDOException $e) {
    die("データベース接続エラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hachi+Maru+Pop&display=swap" rel="stylesheet">
    <title>検索画面</title>

    <script>
        function scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({ behavior: 'smooth' });
            }
        }
    </script>

</head>

<body>
    <div class="container">
        <aside class="sidebar">
            <h2>MENU</h2>
            <a href="search.php" class="btn-flat-bottom-border">
                <span>ホーム</span>
            </a>
            <a href="profile.php" class="btn-flat-bottom-border">
                <span>プロフィール</span>
            </a>
            <a href="login.php" class="btn-flat-bottom-border">
                <span>ログアウト</span>
            </a>
            <!-- サイドバー下部に画像を追加 -->
            <img src="img/th.jpg" alt="サイドバーの画像">
        </aside>

        <main class="main-content">
            <div class="post-area">
                <!-- ユーザー投稿エリア -->
                <div class="profile">
                    <a href="profile.php"><img src="<?= htmlspecialchars($user['icon_path'] ?? 'th.jpg'); ?>"
                            alt="" class="profile-icon" id="profile-icon"></a>

                    <div class="user-info">
                        <h3>
                            <?= htmlspecialchars($username) ?>
                        </h3>
                    </div>
                </div>

                <!-- 投稿フォーム -->
                <form action="search.php" method="POST" enctype="multipart/form-data">
                    <textarea name="comment" class="post-textarea" placeholder="コメント"></textarea>
                    <div class="media-upload">
                        <label for="upload-media" class="upload-button">画像・動画をアップロード</label>
                        <input type="file" name="media" id="upload-media" accept="image/*,video/*" multiple>
                        <button class="post-button">投稿</button>
                    </div>
                </form>
            </div>

            <!-- 投稿一覧 -->
            <div class="posts-list">
                <?php if ($posts): ?>
                <?php foreach ($posts as $post): ?>
                <div class="post">
                    <div class="post-header">
                        <a href="who_profile.php?username=<?= htmlspecialchars($post['username']); ?>"><img
                                src="<?= htmlspecialchars($post['icon_path'] ?? 'default_icon.jpg'); ?>" alt="ユーザーアイコン"
                                class="post-icon"></a>
                        <div class="post-user-info">
                            <h4>
                                <a href="who_profile.php?username=<?= htmlspecialchars($post['username']); ?>">
                                    <?= htmlspecialchars($post['username']); ?>
                                </a>
                            </h4>
                        </div>
                    </div>
                    <div class="post-content">
                        <p>
                            <?= htmlspecialchars($post['comment']); ?>
                        </p>
                        <?php if (!empty($post['media_path'])): ?>
    <?php if (preg_match('/\.(mp4|webm|ogg)$/i', $post['media_path'])): ?>
        <!-- 動画の場合 -->
        <video controls class="post-media">
            <source src="<?= htmlspecialchars($post['media_path']); ?>" type="video/mp4">
            お使いのブラウザは動画の再生に対応していません。
        </video>
    <?php else: ?>
        <!-- 画像の場合 -->
        <img src="<?= htmlspecialchars($post['media_path']); ?>" alt="投稿メディア" class="post-media">
    <?php endif; ?>
<?php endif; ?>
                    </div>
                    <div class="post-footer">
                        <span>
                            <?= htmlspecialchars($post['created_at']); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <?php endif; ?>
            </div>
        </main>

        <!-- 検索エリア -->
        <div class="search-section">
            <h2>ユーザー検索</h2>
            <form action="search.php" method="get">
                <input type="text" id="search-input" name="search" placeholder="ユーザー名を検索">
                <button type="submit" class="search-button"><i class="fas fa-search"></i></button>
            </form>

            <div class="search-results">
                <?php if (!empty($searchResults)): ?>
                <ul>
                    <?php foreach ($searchResults as $result): ?>
                    <li>
                        <a href="who_profile.php?username=<?= htmlspecialchars($result['username']) ?>">
                            <img src="<?= htmlspecialchars($result['icon_path'] ?? 'default_icon.jpg'); ?>" alt="アイコン"
                                class="profile-icon" id="profile-icon">
                            <?= htmlspecialchars($result['username']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>

                <?php endif; ?>
            </div>

            <!-- 右下に固定のスクロールトップボタン -->
            <button id="scroll-to-top" aria-label="ページトップに戻る" title="Top">
                <i class="fas fa-arrow-up"></i>
            </button>
            <script>
                // スクロールトップボタンの表示/非表示
                window.onscroll = function () {
                    let button = document.getElementById("scroll-to-top");
                    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
                        button.classList.add("show");
                    } else {
                        button.classList.remove("show");
                    }
                };

                // ボタンクリックでスクロールトップに戻る
                document.getElementById("scroll-to-top").onclick = function () {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                };
            </script>
        </div>
</body>

</html>
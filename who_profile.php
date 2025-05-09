<?php
session_start();
 
// データベース接続設定
require('db_connection.php');
 
try {
     // ログインユーザーの情報を取得
     $username = $_SESSION['username'];
     
    // プロフィール表示するユーザーを決定
    $profileUsername = $_GET['username'] ?? $_SESSION['username'];  // パラメータがなければログインユーザーを表示

    // ユーザーの現在の情報を取得
    $stmt = $pdo->prepare("SELECT birthdate, intro, icon_path FROM accounts WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // プロフィール情報を取得
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE username = :username");
    $stmt->bindParam(':username', $profileUsername, PDO::PARAM_STR);
    $stmt->execute();
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
 
    // ユーザーが見つからなかった場合
    if (!$profile) {
        die('ユーザー情報が見つかりませんでした。');
    }
 
    // 指定したユーザーの投稿のみを取得
$stmt_posts = $pdo->prepare("
SELECT p.comment, p.media_path, p.created_at, a.username, a.icon_path
FROM posts p
JOIN accounts a ON p.username = a.username
WHERE p.username = :profileUsername
ORDER BY p.created_at DESC
");
$stmt_posts->bindParam(':profileUsername', $profileUsername, PDO::PARAM_STR);
$stmt_posts->execute();
$posts = $stmt_posts->fetchAll(PDO::FETCH_ASSOC);

 
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
    die('データベースエラー: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="who_profile.css">
    <!-- FontAwesome の CDN を追加 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hachi+Maru+Pop&display=swap" rel="stylesheet">
    <title>プロフィール</title>

    <!-- 検索 -->
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
            <a href="search.php" class="btn-flat-bottom-border"><span>ホーム</span></a>
            <a href="profile.php" class="btn-flat-bottom-border"><span>プロフィール</span></a>
            <a href="login.php" class="btn-flat-bottom-border"><span>ログアウト</span></a>
            <!-- サイドバー下部に画像を追加 -->
            <img src="img/th.jpg" alt="サイドバーの画像">
        </aside>

        <main class="main-content">
            <div class="post-area">
                <!-- ユーザー情報 -->
                <div class="profile">
                    <img src="<?= htmlspecialchars($profile['icon_path'] ?? 'th.jpg'); ?>" alt=""
                        class="profile-icon">
                    <div class="user-info">
                        <h3>
                            <?= htmlspecialchars($profile['username']) ?>
                        </h3>
                        <p>生年月日:
                            <?= htmlspecialchars($profile['birthdate'] ?? '不明') ?>
                        </p>
                        <p>自己紹介:
                            <?= htmlspecialchars($profile['intro'] ?? '未設定') ?>
                        </p>
                    </div>
                </div>
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
                        <a href="profile.php?username=<?= htmlspecialchars($result['username']) ?>">
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
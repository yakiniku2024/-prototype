<?php
session_start();
// データベース接続設定
require('db_connection.php');

try {
    // ログインユーザーの情報を取得
    $username = $_SESSION['username'];

    // POSTリクエストを受け取った場合（プロフィールの更新）
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $birthdate = $_POST['birthdate'] ?? '';
        $intro = $_POST['intro'] ?? '';

        // データベースに更新
        $stmt = $pdo->prepare("UPDATE accounts SET birthdate = :birthdate, intro = :intro WHERE username = :username");
        $stmt->bindParam(':birthdate', $birthdate, PDO::PARAM_STR);
        $stmt->bindParam(':intro', $intro, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
    }

    // ユーザーの現在の情報を取得
    $stmt = $pdo->prepare("SELECT birthdate, intro, icon_path FROM accounts WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //自分の投稿しか見れない
    $stmt_posts = $pdo->prepare("
    SELECT p.comment, p.media_path, p.created_at, a.username, a.icon_path
    FROM posts p
    JOIN accounts a ON p.username = a.username
    WHERE p.username = :username
    ORDER BY p.created_at DESC");
$stmt_posts->bindParam(':username', $username, PDO::PARAM_STR);
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
    <link rel="stylesheet" href="profile.css">
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
 
        // 編集ボタンのクリックでプロフィールを更新する
        function editProfile() {
            const birthdate = document.getElementById('birthdate').value;
            const intro = document.getElementById('intro').value;
 
            // ここで編集内容を適用する
            document.getElementById('profile-birthdate').innerText = `生年月日: ${birthdate}`;
            document.getElementById('profile-intro').innerText = intro;
 
            // 編集フォームを非表示にする
            document.getElementById('edit-form').style.display = 'none';
            document.getElementById('edit-button').style.display = 'inline-block';
        }
 
        // プロフィール編集ボタンのクリックでフォームを表示
        function showEditForm() {
            document.getElementById('edit-form').style.display = 'block';
            document.getElementById('edit-button').style.display = 'none';
        }
 
        // アイコン変更の処理
        function changeIcon(event) {
            const file = event.target.files[0]; // ユーザーが選択した画像
            const reader = new FileReader();
 
            // 画像を読み込んだ後に表示
            reader.onload = function (e) {
                const imageUrl = e.target.result;
                document.getElementById('profile-icon').src = imageUrl; // アイコン画像を更新
            };
 
            if (file) {
                reader.readAsDataURL(file); // 画像を読み込む
            }
        }
 
        function performSearch() {
            const input = document.getElementById('search-input').value;
            alert('検索キーワード: ' + input);
        }
    </script>
</head>
 
<body>
    <!-- 画面全体 -->
    <div class="container">
        <!-- サイドバー -->
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
 
        <!-- メインコンテンツ -->
        <main class="main-content">
            <div class="post-area">
                <!-- ユーザー投稿エリア -->
                <div class="profile">
                    <!-- アイコンとユーザー名を横並びに配置 -->
                    <div class="profile-header">
                        <img src="<?= htmlspecialchars($user['icon_path'] ?? 'th.jpg'); ?>" alt="ユーザーアイコン"
                            class="profile-icon" id="profile-icon" onclick="location.href='icon_upload.php';">
 
                        <!-- アイコン変更用のinput -->
                        <input type="file" id="icon-upload" accept="image/*" onchange="changeIcon(event)"
                            style="display: none;">
                        <h3 id="profile-username">
                            <?= htmlspecialchars($username) ?>
                        </h3> <!-- ユーザー名 -->
                    </div>
 
                    <div class="user-info">
                        <p id="profile-birthdate">生年月日:
                            <?= htmlspecialchars($user['birthdate'] ?? '----年-月-日') ?>
                        </p>
                        <p id="profile-intro">
                            <?= htmlspecialchars($user['intro'] ?? '自己紹介の内容がここに表示されます。') ?>
                        </p>
                    </div>
                    <!-- 編集ボタン -->
                    <button id="edit-button" onclick="showEditForm()">編集</button>
 
                    <!-- プロフィール編集フォーム -->
                    <div id="edit-form" style="display: none;">
                        <form action="profile.php" method="POST" enctype="multipart/form-data">
                            <label for="birthdate">生年月日</label>
                            <input type="date" id="birthdate" name="birthdate"
                                value="<?= htmlspecialchars($user['birthdate'] ?? '') ?>">
                            <br>
                            <label for="intro">自己紹介文</label>
                            <textarea id="intro" name="intro"
                                placeholder="自己紹介文"><?= htmlspecialchars($user['intro'] ?? '') ?></textarea>
                            <br>
                            <button type="submit" class="edit-button">保存</button>
                        </form>
 
                    </div>
 
                </div>
            </div>
 
            <!-- 投稿一覧 -->
            <div class="posts-list">
    <?php if ($posts): ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="post-header">
                    <a href="who_profile.php?username=<?= htmlspecialchars($post['username']); ?>"><img src="<?= htmlspecialchars($post['icon_path'] ?? 'th.jpg'); ?>" alt="" class="post-icon"></a>
                    <div class="post-user-info">
                        <h4>
                            <a href="who_profile.php?username=<?= htmlspecialchars($post['username']); ?>">
                                <?= htmlspecialchars($post['username']); ?> </a>
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
                    <span><?= htmlspecialchars($post['created_at']); ?></span>
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
            <form action="profile.php" method="get">
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
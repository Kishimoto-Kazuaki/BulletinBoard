<?php
//セッションを開始
session_start();

// 共通項目を呼び出し
require "../common.php";

//変数を初期化
$name = '';
$password = '';

//入力されているか否かを確認する変数の初期化
$error = [
    'name' => '',
    'password' => '',
];

//フォームが送信された場合
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    //ブラウザの入力を取得
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    // 入力値が空の場合、エラーにblankを代入
    if(isset($name) && $name === ''){
        $error['name'] = 'blank';
    }
    if(isset($password) && $password === ''){
        $error['password'] = 'blank';
    }
}

// ユーザー名の重複チェック機能
// データベースに接続
$db = db_conect();
// 入力された文字列と同じ文字列のカラムがいくつあるかを調べる
$stmt = $db->prepare('select count(*) from members where name=?');
if(!$stmt){
    die($db->error);
}
$stmt->bind_param('s', $name);
$success = $stmt->execute();
if(!$success){
    die($db->error);
}
// select count(*)の結果を受け取る変数$cntを作成、重複していれば1、していなければ0が戻り値
$stmt->bind_result($cnt);
$stmt->fetch();

//エラーがなければデータベースに会員情報を登録後トップ画面へ遷移
if(empty(array_filter($error)) && $cnt === 0 && strlen($password) >= 4){
    //パスワードの暗号化
    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

    // データベースに接続
    $db = db_conect();
    //データベースのnameとpasswordカラムを、入力フォームと結びつける
    $stmt = $db->prepare('insert into members (name, password) VALUES (?, ?)');
    if(!$stmt){
        die ($db->error);
    }
    $stmt->bind_param('ss', $name, $hashed_pass);
    // 実行する
    $success = $stmt->execute();
    if(!$success){
        die($db->error);
    }
    // セッションにidとnameを登録
    
    $stmt = $db->prepare('select id from members where name = ?');
    if(!$stmt){
        die($db->error);
    }
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->fetch();
    $_SESSION['id'] = $id;
    $_SESSION['name'] = $name;
    // トップ画面に遷移
    header('Location: ../index.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>新規登録画面</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div class="h-75 w-75 justify-content-center border border-primary border-3 rounded-3">
            <h1 class="w-100 p-3 m-0 text-center">新規登録画面</h1>
            <form action="" method="post" class="w-100 h-75 p-3 d-flex flex-column justify-content-between">
                <div class="form-group">
                    <label for="name">ユーザー名</label>
                    <input type="text" id="name" name="name" class="form-control form-control-lg" value="<?php echo h($name, ENT_QUOTES, "UTF-8"); ?>">
                    
                    <!-- ユーザー名のバリデーション -->
                    <?php if($error['name'] === 'blank'): ?>
                        <span class="error">ユーザー名を入力してください（20文字以内）。</span>
                    <!-- ユーザー名が20文字以内かを確認 -->
                    <?php elseif(mb_strlen($name) > 20): ?>
                        <span class="error">ユーザー名は20文字以内にしてください。</span>  
                    <!-- メールアドレスが重複していないかを確認 -->
                        <?php elseif($cnt > 0): ?>
                        <span class="error">そのユーザー名は既に使われています。別のユーザー名を入力してください。</span>
                    <?php endif;?>
                
                </div>
                <div class="form-group">
                    <label for="password">パスワード(半角英数字4文字以上)</label>
                    <input type="password" id="password" name="password" class="form-control form-control-lg" value="<?php echo h($password, ENT_QUOTES, "UTF-8"); ?>">
                    
                    <!-- パスワードのバリデーション -->
                    <?php if($error['password'] === 'blank'): ?>
                        <p class="error">パスワードを入力してください。</p>
                    <?php elseif($password !== '' && strlen($password) <= 3): ?>
                        <p class="error">パスワードは4文字以上で入力してください。</p>
                    <?php endif; ?>
                </div>
                
                <div class="d-flex flex-column justify-content-center">
                    <button type="submit" class="btn btn-primary mx-auto w-50">登録する</button>
                </div>
                <div class="d-flex flex-column justify-content-center">
                    <a href="login.php" class="text-center mx-auto w-50 link text-primary">ログインへ</a>        
                </div>
            </form>
        </div>
    </div>
</body>
</html>
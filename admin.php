<?php
session_start();
//共通のファイルを取得
require 'common.php';

//ログインボタンが押された時に入力項目の取得
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // エラーを確認する変数の初期化
    $error = [
        'name' => '',
        'password' => '',
        'auth' => '',
    ];
    // 入力されているかを判断
    if(strlen($name) === 0){
        $error['name'] = 'blank';
    }
    if(strlen($password) === 0){
        $error['password'] = 'blank';
    }
    // 入力されている場合
    if(empty(array_filter($error))){
        // 入力されたユーザー名のパスワードがデータベースにあるかどうかを判断
        $db = db_conect();
        $stmt = $db->prepare('select id, name, password from admin where name=?');
        if(!$stmt){
            die($db->error);
        }
        $stmt->bind_param('s', $name);
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }
        $stmt->bind_result($adm_id, $adm_name, $adm_pass);
        $stmt->fetch();
        //取得したパスワードが、登録されているものと同じかを検証
        if($password === $adm_pass){
            //セッションIDを生成
            $_SESSION['adm_id'] = $adm_id;
            $_SESSION['adm_name'] = $adm_name;
            header('Location: admin_index.php');
            exit();
        } else {
            $error['auth'] = 'denial';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ログイン画面</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div class="h-75 w-75 justify-content-center border border-primary border-3 rounded-3">
            <h1 class="w-100 p-3 m-0 text-center">掲示板（管理者用）</h1>
            <form action="" method="post" class="w-100 h-75 p-3 d-flex flex-column justify-content-between">
                <div class="form-group">
                    <label for="name">ユーザー名</label>
                    <input type="text" id="name" name="name" value="<?php if(isset($name)){echo h($name);} ?>" class="form-control form-control-lg">
                    <?php if(isset($name) && $error['name'] === 'blank'): ?>
                        <span class="error">ユーザー名を入力してください</span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input type="password" id="password" name="password" value="<?php if(isset($password)){echo h($password);} ?>" class="form-control form-control-lg">
                    <?php if(isset($password) && $error['password'] === 'blank'): ?>
                        <span class="error">パスワードを入力してください</span><br>
                    <?php elseif(isset($name) && isset($password) && $error['auth'] === 'denial'): ?>
                        <span class="error">ユーザー名かパスワードが正しくありません</span>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-center">
                    <input type="submit" class="btn btn-primary w-50" value="ログイン">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
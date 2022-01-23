<?php
// セッションを開始
session_start();
$id = $_SESSION['id'];
$name = $_SESSION['name'];

//共通のファイルを取得
require 'common.php';
// データベースに接続
$db = db_conect();

//変更するボタンが押された時に入力項目の取得
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $now_password = filter_input(INPUT_POST, 'now_password', FILTER_SANITIZE_STRING);
    $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
    $conf_new_password = filter_input(INPUT_POST, 'conf_new_password', FILTER_SANITIZE_STRING);

    //入力されているか否かを確認する配列の初期化
    $error = [
        'now_password' => '',
        'new_password' => '',
        'conf_new_password' => '',
        'auth' => '',
        'compare' => ''
    ];
    
    //入力されているかを判断
    if(strlen($now_password) === 0){
        $error['now_password'] = 'blank';
    }
    if(strlen($new_password) === 0){
        $error['new_password'] = 'blank';
    }
    if(strlen($conf_new_password) === 0){
        $error['conf_new_password'] = 'blank';
    }


    // 入力項目に入力されたパスワードがデータベースにあるかどうかを判断
    if($error['now_password'] === '' && $error['new_password'] === '' && $error['conf_new_password'] === ''){
        $stmt = $db->prepare('select password from members where id = ? limit 1');
        if(!$stmt){
            die($db->error);
        }
        $stmt->bind_param('i', $id);
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }
        $stmt->bind_result($hashed_pass);
        $stmt->fetch();
        var_dump($hashed_pass) . '<br/>';
        var_dump(password_hash($now_password, PASSWORD_DEFAULT));
        exit;
        //取得したパスワードが、登録されているものと同じでなければエラーを出す
        if(password_hash($now_password, PASSWORD_DEFAULT) !== $hashed_pass){
            $error['auth'] = 'denial';
        } else {
            echo 'Success';
            exit;
            /*新しいパスワードと新しいパスワード（確認）が同じかを確認
            同じ場合は、パスワードをハッシュ化してデータベースを上書き*/
            if($new_password === $conf_new_password){
                $hashed_pass = password_hash($new_password, PASSWORD_DEFAULT);
                $db->prepare('update members set password = $hashed_pass where id = ? limit 1');
                if(!$stmt){
                    die($db->error);
                }
                $stmt->bind_param('i', $id);
                $success = $stmt->execute();
                if(!$success){
                    die($db->error);
                }
                header('Location: index.php');
                exit;
            } else {
                $error['compare'] = 'diff';
            }
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
    <title>ログイン</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <!-- jQuery読み込み -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- BootstrapのJS読み込み -->
    <script src="js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div class="h-75 w-75 justify-content-center border border-primary border-3 rounded-3">
            <h1 class="w-100 p-3 m-0 text-center">パスワード変更</h1>
            <form action="" method="post" class="w-100 h-75 p-3 d-flex flex-column justify-content-between">
                <div class="form-group">
                    <label for="now_password">現在のパスワード</label>
                    <input type="password" id="now_password" name="now_password" class="form-control form-control-lg">
                    <?php if(isset($now_password) && $error['now_password'] === 'blank'): ?>
                        <span class="error">現在のパスワードを入力してください</span><br>
                    <?php elseif(isset($now_password) && $error['auth'] = 'denial'): ?>
                        <span class="error">現在のパスワードが正しくありません</span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="new_password">新しいパスワード</label>
                    <input type="password" id="new_password" name="new_password" class="form-control form-control-lg">
                    <?php if(isset($new_password) && $error['new_password'] === 'blank'): ?>
                        <span class="error">新しいパスワードを入力してください</span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="conf_new_password">新しいパスワード（確認）</label>
                    <input type="password" id="conf_new_password" name="conf_new_password" class="form-control form-control-lg">
                    <?php if(isset($conf_new_password) && $error['conf_new_password'] === 'blank'): ?>
                        <span class="error">新しいパスワード（確認）を入力してください</span><br>
                    <?php elseif(isset($new_password)  && isset($conf_new_password) && $error['compare'] === 'diff'): ?>
                        <span class="error">新しいパスワード（確認）が正しくありません</span>
                    <?php elseif(isset($new_password)  && isset($conf_new_password) && $error['auth'] === ''): ?>
                        <script>alert('パスワードは正しく変更されました。');</script>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-center m-3">
                    <input type="submit" class="btn btn-primary w-50" value="変更する">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
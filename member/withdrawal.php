<?php
// セッションを開始
session_start();
$id = $_SESSION['id'];

//共通のファイルを取得
require '../common.php';

// ログインしていなければログイン画面に戻る
if(!isset($id)){
    header('Location: login.php');
    exit();
}

// 「はい」ボタンが押された場合
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // データベースに接続
    $db = db_conect();
    // idが一致するユーザー情報を削除する。
    $stmt = $db->prepare('delete from members where id=? limit 1');
    if(!$stmt){
        die($db->error);
    }
    $stmt->bind_param('i', $id);
    $success = $stmt->execute();
    if(!$success){
        die($db->error);
    }
    // セッションの中身を削除
    unset($id, $_SESSION['id'], $_SESSION['name']);
    header('Location: login.php');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>退会確認</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div class="h-25 w-75 justify-content-center border border-primary border-3 rounded-3">
            <h1 class="w-100 pt-3 m-0 text-center">本当に退会しますか？</h1>
            <div class="w-100 row pt-4 m-0 ">
                <form action="" method="post" class="col-6 d-flex justify-content-center mb-0 ">
                    <input type="submit" value="はい" class="btn btn-danger w-75 block">
                </form>
                <div class="col-6 d-flex justify-content-center">
                    <a class="btn btn-primary w-75 block" href="../index.php">いいえ</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
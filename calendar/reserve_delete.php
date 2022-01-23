<?php
// セッションを開始
session_start();

// urlからreserve_idを取得
$reserve_id = $_GET['reserve_id'];

//共通のファイルを取得
require '../common.php';

//ログインされているかを確認
if(isset($_SESSION['id']) && isset($_SESSION['name'])){
    //セッションで渡された変数を$nameに格納
    $name = $_SESSION['name'];
    } else if(isset($_SESSION['adm_id']) && isset($_SESSION['adm_name'])){
        $adm_id = $_SESSION['adm_id'];
        $adm_name = $_SESSION['adm_name'];
    } else {
        //ログインしていなければログイン画面に戻る
        header('Location: member/login.php');
        exit();
}


// 「はい」ボタンが押された場合
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // データベースに接続
    $db = db_conect();
    // reserve_idが一致する予約情報を削除する。
    $stmt = $db->prepare('delete from calendar where reserve_id=? limit 1');
    if(!$stmt){
        die($db->error);
    }
    $stmt->bind_param('i', $reserve_id);
    $success = $stmt->execute();
    if(!$success){
        die($db->error);
    }
    // $reserve_idの中身を削除
    unset($reserve_id);
    header('Location: calendar.php');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>削除確認</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div class="h-25 w-75 justify-content-center border border-primary border-3 rounded-3">
            <h1 class="w-100 pt-3 m-0 text-center">本当に削除しますか？</h1>
            <div class="w-100 row pt-4 m-0 ">
                <form action="" method="post" class="col-6 d-flex justify-content-center mb-0 ">
                    <input type="submit" value="はい" class="btn btn-danger w-75 block">
                </form>
                <div class="col-6 d-flex justify-content-center">
                    <a class="btn btn-primary w-75 block" href="calendar.php">いいえ</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
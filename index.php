<?php
// セッションを開始
session_start();

// 共通のファイルを取得
require 'common.php';

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

// 表示するお知らせ記事のデータを降順に2つ取得
$db = db_conect();
$stmt = $db->prepare('select * from notice order by notice_id desc limit 2');
if(!$stmt){
    die($db->error);
}
$success = $stmt->execute();
if(!$success){
    die($db->error);
}
$stmt->bind_result($notice_id, $user_id, $title, $contents, $file, $created);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>トップページ</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- ヘッダー部分 -->
    <nav class="navbar navbar-light d-flex justify-content-between" style="background-color: #e3f2fd;">
            <h1>&nbsp;<a href="index.php">掲示板トップページ</a></h1>
            <div class="d-flex align-items-end">
                <p class="menu-item">ようこそ
                    <?php if(isset($name)){echo $name;} else if(isset($adm_name)){echo $adm_name;} ?>
                    さん&nbsp;|&nbsp;</p>
                <p class="menu-item"><a class="text-primary" href="member/logout.php">ログアウト</a></p>
            </div>
    </nav>

    <!-- メイン部分 -->
    <div class="row" style="height: 100%; width: 100%;">
        <nav class="col-3 text-center">
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="notice/notice.php">お知らせ一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="calendar/calendar.php">集会所使用予定</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="https://www.town.nagi.okayama.jp/gyousei/chousei/kouhou_nagi/index.html" target="_blank">広報誌</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="committee_member.php">役員一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="terms/terms.php">規約</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="opinion/opinion.php">改善意見</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="member/withdrawal.php">退会</a>
        </nav>
        <div class="col-9 mt-3">
            <div class="d-flex justify-content-center">
                <h2>最新のお知らせ２件</h2>
            </div>
                <?php while($stmt->fetch()): ?>
                    <ul class="list-group border border-dark">
                        <li class="list-group-item"><b>件名</b>：<?php echo $title ?></li>
                        <li class="list-group-item"><b>内容</b>：<?php echo $contents ?></li>
                        <li class="list-group-item"><b>ファイル</b>：
                            <?php if($file !== null):?>
                                <a href="<?php echo'./notice/files/' . $file;?>" style="color:blue;">添付ファイルを開く</a>
                            <?php else: ?>
                                添付ファイルはありません。
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item"><b>投稿日時</b>：<?php echo $created; ?></li>
                    </ul>
                    <br>
                <?php endwhile; ?>
            <div class="mt-3 d-flex justify-content-end">
                <a class="btn btn-success" href="notice/create_notice.php">お知らせ投稿</a>
            </div>
        </div>
    </div>
</body>
</html>
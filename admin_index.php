<?php
// セッションを開始
session_start();

// 共通のファイルを取得
require 'common.php';

//ログインされているかを確認
if(isset($_SESSION['adm_id']) && isset($_SESSION['adm_name'])){
        $adm_id = $_SESSION['adm_id'];
        $adm_name = $_SESSION['adm_name'];
    } else {
        //ログインしていなければログイン画面に戻る
        header('Location: admin.php');
        exit();
}

// 各値を代入する配列を準備
$member = [];
$notice = [
    'notice_id' => [],
    'title' => [],
    'contents'=> [],
    'file' => [],
    'created' => [],
];
$reserve = [
    'reserve_id' => [],
    'reserver_name' => [],
    'reserve_contents' => [],
    'date' => [],
];
$opinion = [];

// 現在の登録者の数と名前を取得
$db = db_conect();
$stmt1 = $db->prepare('select name from members');
if(!$stmt1){
    die($db->error);
}
$success = $stmt1->execute();
if(!$success){
    die($db->error);
}
$stmt1->bind_result($name);
while ($stmt1->fetch()) {
    array_push($member, $name);
}

// 現在のお知らせを取得
$stmt2 = $db->prepare('select * from notice order by notice_id desc');
if(!$stmt2){
    die($db->error);
}
$success = $stmt2->execute();
if(!$success){
    die($db->error);
}
$stmt2->bind_result($notice_id, $user_id, $title, $contents, $file, $created);
while ($stmt2->fetch()) {
    array_push($notice['notice_id'], $notice_id);
    array_push($notice['title'], $title);
    array_push($notice['contents'], $contents);
    array_push($notice['file'], $file);
    array_push($notice['created'], $created);
}

// 現在のカレンダーの予約情報を取得
$stmt3 = $db->prepare('select reserve_id, reserver_name, reserve_contents, date from calendar order by reserve_id desc');
if(!$stmt3){
    die($db->error);
}
$success = $stmt3->execute();
if(!$success){
    die($db->error);
}
$stmt3->bind_result($reserve_id, $reserver_name, $reserve_contents, $date);
while ($stmt3->fetch()) {
    array_push($reserve['reserve_id'], $reserve_id);
    array_push($reserve['reserver_name'], $reserver_name);
    array_push($reserve['reserve_contents'], $reserve_contents);
    array_push($reserve['date'], $date);
}

// 現在の改善意見を取得
$stmt4 = $db->prepare('select opinion_contents from opinion');
if(!$stmt4){
    die($db->error);
}
$success = $stmt4->execute();
if(!$success){
    die($db->error);
}
$stmt4->bind_result($opinion_contents);
while ($stmt4->fetch()) {
    array_push($opinion, $opinion_contents);
}

// 現在の参加人数を取得
$stmt5 = $db->prepare('select count(*) from attendance where absence = "attend"');
if(!$stmt5){
    die($db->error);
}
$success = $stmt5->execute();
if(!$success){
    die($db->error);
}
$stmt5->bind_result($people_counts);
while ($stmt5->fetch()) {
    array_push($people, $people_counts);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>管理画面</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- ヘッダー部分 -->
    <nav class="navbar navbar-light d-flex justify-content-between" style="background-color: #e3f2fd;">
            <h1>&nbsp;<a href="index.php">管理画面</a></h1>
            <div class="d-flex align-items-end">
                <p class="menu-item">ようこそ
                    <?php if(isset($adm_name)){echo $adm_name;} ?>
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
        </nav>
        <div class="col-9 mt-3">
            <div>現在の登録者数：<?php echo count($member) ?></div>
            <div style="height: 100px;" class="border border-dark border-2 overflow-scroll">
                <?php for ($i=0; $i < count($member); $i++): ?>
                    <p><b>名前</b>：<?php echo h($member[$i]); ?></p>
                    <!-- <a class="me-2 btn btn-danger" href="member/withdrawal.php">退会させる</a> -->
                <?php endfor; ?>
            </div>
            <br>
            <div>現在のお知らせ記事数：<?php echo count($notice['notice_id']) ?></div>
            <div style="height: 200px;" class="border border-dark border-2 overflow-scroll">
            <?php for($i=0; $i<count($notice['notice_id']) ; $i++): ?>
                <ul class="list-group m-2 border border-dark">
                    <li class="list-group-item"><b>件名</b>：<?php echo h($notice['title'][$i]); ?></li>
                    <li class="list-group-item"><b>内容</b>：<?php echo h($notice['contents'][$i]); ?></li>
                    <li class="list-group-item"><b>ファイル</b>：
                        <?php if($notice['file'][$i] !== null):?>
                            <a href="<?php echo'./notice/files/' . $notice['file'][$i];?>" style="color:blue;">添付ファイルを開く</a>
                        <?php else: ?>
                            添付ファイルはありません。
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item"><b>投稿日時</b>：<?php echo $notice['created'][$i]; ?></li>
                </ul>
                <div class="d-flex justify-content-end">
                    <!-- 編集ボタンが押された場合 -->
                        <a class="me-2 btn btn-warning" href="./notice/change_notice.php?notice_id=<?php echo $notice['notice_id'][$i] ?>">編集する</a>
                    <!-- 削除ボタンが押された場合 -->
                        <a class="me-2 btn btn-danger" href="./notice/delete_notice.php?notice_id=<?php echo $notice['notice_id'][$i] ?>">削除する</a>
                </div>
            <?php endfor; ?>
            </div>
            <br>
            <div>現在の予約数：<?php echo count($reserve['reserve_id']) ?></div>
            <div style="height: 200px;" class="border border-dark border-2 overflow-scroll">
            <?php for($i=0; $i<count($reserve['reserve_id']); $i++): ?>
                <ul class="list-group m-2 border border-dark">
                    <li class="list-group-item"><b>予約者</b>：<?php echo h($reserve['reserver_name'][$i]); ?></li>
                    <li class="list-group-item"><b>内容</b>：<?php echo h($reserve['reserve_contents'][$i]); ?></li>
                    <li class="list-group-item"><b>予約日</b>：<?php echo $reserve['date'][$i]; ?></li>
                </ul>
                <div class="d-flex justify-content-end">
                    <!-- 削除ボタンが押された場合 -->
                    <a class="me-2 btn btn-danger" href="./notice/notice_delete.php?notice_id=<?php echo $notice_id ?>">削除する</a>
                </div>
            <?php endfor; ?>
            </div>
            <br>
            <div>現在の改善意見数：<?php echo count($opinion) ?></div>
            <div style="height: 150px;" class="border border-dark border-2 overflow-scroll">
                <?php for ($i=0; $i < count($opinion); $i++): ?>
                    <p><b>内容</b>：<?php echo h($opinion[$i]); ?></p>
                <?php endfor; ?>
            </div>
            <br>
            <div class="border border-dark border-2 overflow-scroll">
            現在の総会参加人数：
                <?php 
                    for ($i=0; $i < count($people); $i++){
                        echo h($people[$i]);
                    }
                ?>
            </span>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// // セッションを開始
session_start();

// 共通のファイルを取得
require '../common.php';
$id = '';
//ログインされているかを確認
if(isset($_SESSION['id']) && isset($_SESSION['name'])){
    //セッションで渡された変数を$name,$idに格納
    $name = $_SESSION['name'];
    $id = $_SESSION['id'];
    } else if(isset($_SESSION['adm_id']) && isset($_SESSION['adm_name'])){
        $adm_id = $_SESSION['adm_id'];
        $adm_name = $_SESSION['adm_name'];
    } else {
        //ログインしていなければログイン画面に戻る
        header('Location: member/login.php');
        exit();
}


// urlからreserve_idを取得
$reserve_id = $_GET['reserve_id'];

// データベースに接続
$db = db_conect();
if(!$db){
    die($db->error);
}
// 詳細を表示したいreserve_idのレコードを取得
$stmt = $db->prepare('select id, reserver_name, reserve_contents, date from calendar where reserve_id = ?');
$stmt->bind_param('i', $reserve_id);
$success = $stmt->execute();
if(!$success){
    die($db->error);
}
$stmt->bind_result($user_id, $reserver_name, $reserve_contents, $date);
$stmt->fetch();

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>カレンダー</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="../Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- 共通のcssファイルを読み込み -->
    <link href="../style.css" rel="stylesheet">
</head>
<body>
    <!-- ヘッダー部分 -->
    <nav class="navbar navbar-light d-flex justify-content-between" style="background-color: #e3f2fd;">
            <h1>&nbsp;<a href="../index.php">官舎掲示板</a></h1>
            <div class="d-flex align-items-end">
                <p class="menu-item">ようこそ
                    <?php if(isset($name)){echo $name;} else if(isset($adm_name)){echo $adm_name;} ?>
                    さん&nbsp;|&nbsp;</p>
                <p class="menu-item"><a class="text-primary" href="../member/logout.php">ログアウト</a></p>
            </div>
    </nav>

    <!-- メイン部分 -->
    <div class="row" style="height: 100%; width: 100%;">
        <!-- サイドバー -->
        <nav class="col-3 text-center">
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../notice/notice.php">お知らせ一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="calendar.php">集会所使用予定</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="https://www.town.nagi.okayama.jp/gyousei/chousei/kouhou_nagi/index.html" target="_blank">広報誌</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../committee_member.php">役員一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../terms/terms.php">規約</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../opinion/opinion.php">改善意見</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../member/withdrawal.php">退会</a>
        </nav>
        <!-- 予約フォーム -->
        <div class="col-9 mt-3">
           <form action="" method="post"　class="form-group">
                <div>
                    <label for="reserver_name"><b>予約者名</b></label><br>
                    <input type="text" class="form-control" value="<?php echo $reserver_name; ?>" readonly>
                </div>
                <div>
                    <label for="reserve_contents"><b>予約内容</b></label><br>
                    <textarea class="form-control" cols="50" rows="5" readonly><?php echo $reserve_contents; ?></textarea>
                </div>
                <div>
                    <label for="date"><b>予約日時</b></label><br>
                    <input type="text" class="form-control" value="<?php echo date('Y年n月d日',strtotime($date)); ?>" readonly>
                </div>
            </form>
            <br>
            <div class="d-flex justify-content-end">
                <?php if($user_id === $id || isset($adm_id)): ?>
                    <a class="me-2 btn btn-danger" href="reserve_delete.php?reserve_id=<?php echo $reserve_id ?>">削除する</a>
                <?php endif; ?>
            </div>
        </div>
</body>
</html>
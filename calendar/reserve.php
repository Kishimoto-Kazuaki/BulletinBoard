<?php
// セッションを開始
session_start();

// 共通のファイルを取得
require '../common.php';

//ログインされているかを確認
if(isset($_SESSION['id']) && isset($_SESSION['name'])){
    //セッションで渡された変数を$nameに格納
    $id = $_SESSION['id'];
    $name = $_SESSION['name'];
    } else if(isset($_SESSION['adm_id']) && isset($_SESSION['adm_name'])){
        $adm_id = $_SESSION['adm_id'];
        $adm_name = $_SESSION['adm_name'];
    } else {
        //ログインしていなければログイン画面に戻る
        header('Location: member/login.php');
        exit();
}


// エラーを確認する配列を準備
$error = [
    'reserver_name' => '',
    'reserve_contents' => '',
    'date' => ''
];

// 「予約」ボタンが押された場合
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // 各入力内容を取得
    $reserver_name = filter_input(INPUT_POST, 'reserver_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $reserve_contents = filter_input(INPUT_POST, 'reserve_contents', FILTER_SANITIZE_SPECIAL_CHARS);
    $date = $_POST['date'];
    
    // 内容が入力されているかを確認
    if(isset($reserver_name) && $reserver_name === ''){
        $error['reserver_name'] = 'blank';
    }
    if(isset($reserve_contents) && $reserve_contents === ''){
        $error['reserve_contents'] = 'blank';
    }
    if(isset($date) && $date === ''){
        $error['date'] = 'blank';
    } else if($date !== ''){
        // データベースに接続
        $db = db_conect();
        if(!$db){
            die($db->error);
        }
        // 既に予約されている日付でないかを確認
        $stmt = $db->prepare('select count(*) from calendar where date=?');
        if(!$stmt){
            die($db->error);
        }
        $stmt->bind_param('s', $date);
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }
        // select count(*)の結果を受け取る変数$cntを作成、重複していれば1、していなければ0が戻り値
        $stmt->bind_result($cnt);
        $stmt->fetch();
        if($cnt !== 0){
            $error['date'] = 'duplicate';
        }
    }
    
    // 各内容が入力されている場合
    if($error['reserver_name'] === '' && $error['reserve_contents'] === '' && $error['date'] === ''){
        $db = db_conect();
        if(!$db){
            die($db->error);
        }
        $stmt = $db->prepare('insert into calendar(id, reserver_name, reserve_contents, date) values(?, ?, ?, ?)');
        if(!$stmt){
            die($db->error);
        }
        $stmt->bind_param('isss',$id, $reserver_name, $reserve_contents, $date);
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }
        header('Location: calendar.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>カレンダー</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
                <p class="menu-item"><a class="text-primary" href="member/logout.php">ログアウト</a></p>
            </div>
    </nav>

    <!-- メイン部分 -->
    <div class="row" style="height: 100%; width: 100%;">
        <!-- サイドバー -->
        <nav class="col-3 text-center">
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../notice/notice.php">お知らせ一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="./calendar.php">集会所使用予定</a>
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
                    <label for="reserver_name">予約者名</label>
                    <input type="text" name="reserver_name" id="reserver_name" class="form-control" value="<?php if(isset($reserver_name)) { echo h($reserver_name);} ?>">
                    <?php echo $name; if(isset($error['reserver_name']) && $error['reserver_name'] === 'blank'): ?>
                        <p class="error">予約者名を入力してください。</p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="reserve_contents">予約の内容</label>
                    <input type="text" name="reserve_contents" id="reserve_contents" class="form-control" value="<?php if(isset($reserve_contents)) { echo h($reserve_contents);} ?>">
                    <?php if(isset($error['reserve_contents']) && $error['reserve_contents'] === 'blank'): ?>
                        <p class="error">予約の内容を入力してください。</p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="date">予約日時</label><br>
                    <input type="date" name="date" class="">
                    <?php if(isset($error['date']) && $error['date'] === 'blank'): ?>
                        <p class="error">日時を入れてください。</p>
                    <?php elseif($error['date'] === 'duplicate'): ?>
                        <p class="error">その日は既に予約されています。</p>
                    <?php endif; ?>
                    
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">予約</button>
                </div>
            </form>
            <!-- 集会所の説明文 -->
            <br>
            <div class="border border-info">
                <p class="text-danger d-flex justify-content-center">予約前に確認！</p>
                <ul>
                    <li>滝本地区住民なら誰でも使用できます（官舎住民以外も使用します）。</li>
                    <li>滝本官舎自治会長が管理しています。</li>
                    <li>飲食（酒類含む）可能です。</li>
                    <li>私的利用の場合、使用料¥1,000を徴収します。</li>
                    <li>鍵は自治会長が保管していますので、使用する日までに受領してください。</li>
                    <li>細々した規則は、鍵と一緒にお渡しします。</li>
                </ul>
            </div>
        </div>
</body>
</html>
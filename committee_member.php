<?php
session_start();

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

// 各入力値を入れる配列を準備
$position = ['自治会長','副会長兼体育文化',
            '会計','会計監査','環境衛生','愛育委員',
            '1号棟長','2号棟長','3号棟長','防火管理者',];
$room = [];
$member = [];

// 更新するボタンを押した時に、部屋番号と名前の入力値を取得
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    for ($i=0; $i<count($position); $i++) { 
        $room[] = filter_input(INPUT_POST, 'room'.$i, FILTER_SANITIZE_SPECIAL_CHARS);
        $member[] = filter_input(INPUT_POST, 'member'. $i, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>役員一覧</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
    <!-- jQuery読み込み -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- BootstrapのJS読み込み -->
    <script src="js/bootstrap.min.js"></script>

</head>
<body>
    <!-- ヘッダー部分 -->
    <nav class="navbar navbar-light d-flex justify-content-between" style="background-color: #e3f2fd;">
            <h1>&nbsp;<a href="index.php">官舎掲示板</a></h1>
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
            <form action="" method="post">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                        <th scope="col">役職</th>
                        <th scope="col">部屋番号</th>
                        <th scope="col">名前</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i=0; $i< count($position); $i++): ?>
                        <tr>
                            <th scope="row" class="col-4">
                                <?php echo $position[$i]; ?>
                            </th>
                            <td>
                                <input type="text" class="col-6 text-center border border-white" name="room<?php echo $i ?>" value="<?php if(isset($room[$i])){echo $room[$i];} ?>" <?php if(!isset($_SESSION['adm_id'])): ?>disabled<?php endif ?>>
                            </td>
                            <td>
                                <input type="text" class="col-6 text-center border border-white" name="member<?php echo $i ?>" value="<?php if(isset($member[$i])){echo $member[$i];} ?>" <?php if(!isset($_SESSION['adm_id'])): ?>disabled<?php endif ?>>
                            </td>
                        </tr>
                        <?php endfor ?>
                    </tbody>
                </table>
                <span class="text-danger">※個人情報のため、試験運用中は表示していません。</span>
                <?php if(isset($_SESSION['adm_id'])): ?>
                    <div class="d-flex justify-content-end">
                        <input type="submit" class="btn btn-success" value="更新する">
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
<?php
// セッションを開始
session_start();

// 共通のファイルを取得
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


// データベースから、入力された予約を取得しカレンダーに反映させる
// calendarテーブルから、入力された情報を取得
$db = db_conect();
$stmt = $db->prepare('select * from calendar');
$success = $stmt->execute();
// reserve_idと日付を格納する配列を準備
$reserve_date=[];
$reserve_ids=[];
if(!$success){
    die($db->error);
}
$stmt->bind_result($reserve_id, $id, $reserver_name, $reserve_contents, $r_date);
while($stmt->fetch()){
    // $reserve_dateに各日にちを代入
    array_push($reserve_date, date('Y-m-j',strtotime($r_date)));
    // $reserve_idsに各$reserve_idを代入
    array_push($reserve_ids, $reserve_id);    
}
// $reserve_dateの配列の値をキー、$reserve_idsの配列の値を値として新しい配列を生成
$reserve_ids = array_combine($reserve_date, $reserve_ids);

// カレンダーを作成する
// タイムゾーンを設定
date_default_timezone_set('Asia/Tokyo');

//前月、次月リンクが押された場合は、GETパラメータから年月を取得
if(isset($_GET['ym'])){
    $ym = $_GET['ym'];
} else {
    //今月の年月を表示
    $ym = date('Y-m');
}

// 今の年月のタイムスタンプを作成する
$timestamp = strtotime($ym);

// 今日の日付　フォーマット例)2022-01-1
$today = date('Y-m-j');

// カレンダーのタイトルを作成 例)2021年6月
$html_title = date('Y年n月', $timestamp);

// 前月・次月の年月を取得
// mktimeは前・次月のタイムスタンプを返す。mktime(hour,minute,second,month,day,year);
$prev = date('Y-m', mktime(0, 0, 0, date('m', $timestamp)-1, 1, date('Y', $timestamp)));
$next = date('Y-m', mktime(0, 0, 0, date('m', $timestamp)+1, 1, date('Y', $timestamp)));

// 該当月の日数を取得(tは指定した月の日数を表す)
$day_count = date('t', $timestamp);

// 1日が何曜日か0:日　1:月　2:火 ... 6:土
// mktimeを使う(wは曜日を表す)
$youbi = date('w', mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp)));

//カレンダー作成の準備
$weeks = [];
$week = '';

// 第1週目:空のセルを追加
// 例)1日が火曜日だった場合、日・月曜日の2つ分の空セルを追加する
$week .= str_repeat('<td></td>', $youbi);

for($day = 1; $day <= $day_count; $day++, $youbi++){

    // 例)2022-01-1形式の日付を代入する
    $date = $ym . '-' . $day;

    if($today == $date){
        //今日の日付の場合は、class="today"をつける
        $week .= '<td class="today">' . $day;
    // データベースで取得した日付と、描画するときの日付が同じ場合
    } else if(in_array($date, $reserve_date)){
        /* 予約済と表示し、リンク先として予約の詳細を表示する。
        GETを使用して予約詳細画面で$reserve_idを取得できるよう、
        $reserve_idsから$reserve_dateがキーの値を、urlに代入する。*/
        $week .= '<td>' . $day . '<br>' . '<a class="text-primary" 
            href="reserve_detail.php?reserve_id=' . $reserve_ids[$date] . '">' . '予約済' . '</a>';
    } else {
        $week .= '<td>' . $day;
    }
     
    $week .= '</td>';

    //週終わり、または、月終わりの場合
    if($youbi % 7 == 6 || $day == $day_count){

        if($day == $day_count){
            //月の最終日の場合、空セルを追加
            //例)最終日が水曜日の場合、木・金・土曜日の空セルを追加
            $week .= str_repeat('<td></td>', 6 - $youbi % 7);
        }

        // week配列にtrと$weekを追加する
        $weeks[] = '<tr>' . $week . '</tr>';

        //weekをリセット
        $week = '';
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
    <!-- Google Fontsから使用するフォントを取得 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap" rel="stylesheet">

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
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="./calendar.php">集会所使用予定</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="https://www.town.nagi.okayama.jp/gyousei/chousei/kouhou_nagi/index.html" target="_blank">広報誌</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../committee_member.php">役員一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../terms/terms.php">規約</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../opinion/opinion.php">改善意見</a>
                <a class="btn btn-warning btn-lg m-3" style="width: 80%;" href="../attendance.php">総会の出欠</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../member/withdrawal.php">退会</a>
        </nav>
        <!-- カレンダー -->
        <div class="col-9 mt-3 container calendar">
            <h3 class="mb-1 text-center"><a href="?ym=<?php echo $prev; ?>" class="text-primary">&lt;</a>&nbsp;<?php echo $html_title; ?>&nbsp;<a href="?ym=<?php echo $next; ?>" class="text-primary">&gt;</a></h3>
            <table class="table table-bordered">
                <tr>
                    <th style="width: 14.29%;">日</th>
                    <th style="width: 14.29%;">月</th>
                    <th style="width: 14.29%;">火</th>
                    <th style="width: 14.29%;">水</th>
                    <th style="width: 14.29%;">木</th>
                    <th style="width: 14.29%;">金</th>
                    <th style="width: 14.29%;">土</th>
                </tr>
                <?php
                    foreach ($weeks as $week) {
                        echo $week;
                    }
                ?>
            </table>
            <div class="d-flex justify-content-end">
                <a href="./reserve.php" class="btn btn-success">予約画面へ</a>
            </div>
        </div>
    </div>
</body>
</html>
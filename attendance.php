<?php
// セッションを開始
session_start();

// 共通のファイルを取得
require 'common.php';
$id = '';
//ログインされているかを確認
if(isset($_SESSION['id']) && isset($_SESSION['name'])){
    //セッションで渡された変数を$nameに格納
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
// エラーを記録する変数を定義
$error = "";

// 「提出」ボタンが押された場合
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    // 棟名を取得
    $building = filter_input(INPUT_POST, 'selectName1', FILTER_SANITIZE_SPECIAL_CHARS);
    // 部屋番号を取得
    $room = filter_input(INPUT_POST, 'selectName2', FILTER_SANITIZE_SPECIAL_CHARS);
    //参加の有無を取得
    $absence = filter_input(INPUT_POST, 'absence', FILTER_SANITIZE_SPECIAL_CHARS);

    // データベースに接続
    $db = db_conect();
    if(!$db){
        die($db->error);
    }

    // 選択した棟名と部屋番号が既にデータベースにないかを確認
    $stmt = $db->prepare('select count(*) from attendance where building=? and room=?');
    if(!$stmt){
        die($db->error);
    }
    $stmt->bind_param('ss', $building, $room);
    $success = $stmt->execute();
    if(!$success){
        die($db->error);
    }
    // select count(*)の結果を受け取る変数$cntを作成、重複していれば1、していなければ0が戻り値
    $stmt->bind_result($cnt);
    $stmt->fetch();
    if($cnt === 0){
        $error = "null";
    } else {
        $error = "double";
    }
    $db->close();
    
    // 重複していなければ、データを登録する。
    if($error === "null"){
        // データベースに接続
        $db = db_conect();
        if(!$db){
            die($db->error);
        }
        // データベースに棟名、部屋番号、出欠を追加する
        $stmt = $db->prepare('insert into attendance(building, room, absence) values(?, ?, ?)');
        $stmt->bind_param('sss',$building, $room, $absence);
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }
    }
}
?>

<!-- 以下javascript -->
<script type = "text/javascript">
// 棟名、部屋番号の選択を実装
function functionName()
    {
        let select1 = document.forms.formName.selectName1; //変数select1を宣言
        let select2 = document.forms.formName.selectName2; //変数select2を宣言
        let k = 0; //表示番号の値
        select2.options.length = 0; // 選択肢の数がそれぞれに異なる場合、これが重要
        
        if (select1.options[select1.selectedIndex].value === "1" || select1.options[select1.selectedIndex].value === "3")
            {
                for (let i=1; i<=5; i++) {
                    for (let j=1; j<=6; j++) {
                        let text = i + '0' + j;
                        if (true) {
                            select2.options[k] = new Option(text);
                            k++;
                        }
                    }
                }
            }
         
        else if (select1.options[select1.selectedIndex].value == "2")
            {
                for (let i=1; i<=5; i++) {
                    for (let j=1; j<=8; j++) {
                        let text = i + '0' + j;
                        if (true) {
                            select2.options[k] = new Option(text);
                            k++;
                        }
                    }
                }
            }
    }
// 登録する際のポップアップを実装
function confirm_test() {
    var select = confirm("既に提出されている部屋番号です。n上書きしますか？n「OK」で上書きn「キャンセル」で中止");
    return select;
}
</script>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>お知らせ一覧</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body  onLoad="functionName()">
    <!-- ヘッダー部分 -->
    <nav class="navbar navbar-light d-flex justify-content-between" style="background-color: #e3f2fd;">
            <h1>&nbsp;<a href="../index.php">官舎掲示板</a></h1>
            <div class="d-flex align-items-end">
                <p class="menu-item">ようこそ
                    <?php if(isset($name)){echo $name;} else if(isset($adm_name)){echo $adm_name;} ?>
                    さん&nbsp;|&nbsp;</p>
                <p class="menu-item text-primary"><a class="text-primary" href="../member/logout.php">ログアウト</a></p>
            </div>
    </nav>

    <!-- メイン部分 -->
    <div class="row h-75 w-100">
        <nav class="col-3 text-center">
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="notice/notice.php">お知らせ一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="calendar/calendar.php">集会所使用予定</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="https://www.town.nagi.okayama.jp/gyousei/chousei/kouhou_nagi/index.html" target="_blank">広報誌</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="committee_member.php">役員一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="terms/terms.php">規約</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="opinion/opinion.php">改善意見</a>
                <a class="btn btn-warning btn-lg m-3" style="width: 80%;" href="attendance.php">総会の出欠</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="member/withdrawal.php">退会</a>
        </nav>
        <div class="col-9 mt-3">
            <div class="d-flex justify-content-center">
                <h2>定期総会参加の有無</h2>
            </div>
            <br>
            <form name="formName" method="post" action="">
                <div>
                    <!--選択肢その1-->
                    <select name="selectName1" onChange="functionName()">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                    号棟
                    <!--選択肢その2（選択肢その1の項目によって変化）-->
                    <select name="selectName2"></select>
                    号室
                    <!--選択肢その3-->
                    <select name="absence">
                        <option value="attend">参加します</option>
                        <option value="delegation">委任します</option>
                    </select>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">提出</button>
                </div>
                <?php 
                if($error === "double"){
                    $alert = "<script type='text/javascript'>alert('既に登録されています。');</script>";
                    echo $alert;
                    $eeror = "";
                } else {
                    $alert = "<script type='text/javascript'>alert('登録しました。');</script>";
                    echo $alert;
                    $eeror = "";
                } 
                ?>
            </form>
        </div>
    </div>
</body>
</html>
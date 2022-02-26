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

// エラーを取得する変数を用意
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // 内容を取得
    $contents = filter_input(INPUT_POST, 'contents', FILTER_SANITIZE_SPECIAL_CHARS);
    // 内容が空白の場合、エラー変数に値を格納
    if(empty($contents)){
        $error= 'blank';
    }
    // 内容が記入されている場合、データベースに登録
    if(empty($error)){
        // データベースに接続
        $db = db_conect();
        if(!$db){
            die($db->error);
        }
        // データベースにお知らせの件名、内容、ファイルを追加する
        $stmt = $db->prepare('insert into opinion(opinion_contents)  value(?)');
        $stmt->bind_param('s',$contents);
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }
        echo <<<EOD
        <script type="text/javascript">
        alert("提出されました。ご意見ありがとうございました。");
        // 「ご意見」の内容を削除
        Field.clear("contents");
        </script>
EOD;

        // 「ご意見」の内容を削除
        
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>お知らせ投稿</title>
    <!-- BootstrapのCSS読み込み -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
        <nav class="col-3 text-center">
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../notice/notice.php">お知らせ一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../calendar/calendar.php">集会所使用予定</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="https://www.town.nagi.okayama.jp/gyousei/chousei/kouhou_nagi/index.html" target="_blank">広報誌</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../committee_member.php">役員一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../terms/terms.php">規約</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="opinion.php">改善意見</a>
                <a class="btn btn-warning btn-lg m-3" style="width: 80%;" href="../attendance.php">総会の出欠</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../member/withdrawal.php">退会</a>
        </nav>
        <div class="col-9 mt-3">
            <form action="" method="post" enctype="multipart/form-data" class="form-group">
                <div>
                    <label for="contents">ご意見</label>
                    <textarea name="contents" id="contents" class="form-control" cols="30" rows="10"></textarea>
                    <?php if(isset($contents) && $error === 'blank'): ?>
                        <p class="error">内容を入力してください。</p>
                    <?php endif; ?>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">提出する</button>
                </div>
            </form>
            <br>
            <div class="border border-dark">
                <p class="text-danger d-flex justify-content-center">※記入時の注意事項</p>
                <p>具体的に、どの部分をどうして欲しいのかを書いてください。詳細がわかりやすければ、その分早く修正できます。<br>
                    例）規約を１ページずつ表示するのは手間なので、まとめて一つの画像で見れるようにしてもらいたい。
                </p>
            </div>
        </div>
    </div>
</body>
</html>
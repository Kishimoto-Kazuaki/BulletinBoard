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


// エラーを取得する配列を用意
$error = [
    'title' => '',
    'contents' => '',
    'file' => ''
];

// データベースに接続
$db = db_conect();
if(!$db){
    die($db->error);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // 件名を取得
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
    // 件名が空白の場合、エラー配列に値を格納
    if(empty($title)){
        $error['title'] = 'blank';
    }

    // 内容を取得
    $contents = filter_input(INPUT_POST, 'contents', FILTER_SANITIZE_SPECIAL_CHARS);
    // 内容が空白の場合、エラー配列に値を格納
    if(empty($contents)){
        $error['contents'] = 'blank';
    }

    // ファイルを取得
    $file = $_FILES['file'];
    // ファイルが存在するか、またエラーが起こっていないかをチェック
    if($file['name'] !== '' && $file['error'] === 0){
        // ファイルのmimeタイプを取得
        $f_type = mime_content_type($file['tmp_name']);
        // ファイルが.jpg、.png、.pdf、.doc、.xls、.pptx以外を受け付けないようにする
        if($f_type !== 'image/jpeg' && $f_type !== 'image/png' && $f_type !== 'pplication/pdf'){
            // ファイルが受け付けない拡張子の場合、エラー配列に値を格納
            $error['file'] = 'type_error';
        } else {
            // ファイルが適切な拡張子だった場合、ファイルを新しい位置に移動する
            $filename = date('YmdHis') . '_' . $file['name'];
            move_uploaded_file($file['tmp_name'], './files/' . $filename);
            // ファイルネームをセッションに登録
            $_SESSION['filename'] = $filename;
        }
    }
 
    // タイトルと内容が記入されている場合、データベースに登録
    if($error['title'] === '' && $error['contents'] === '' && $error['file'] === ''){
        // データベースにお知らせの件名、内容、ファイルを追加する
        $stmt = $db->prepare('insert into notice(id, title, contents, file)  value(?, ?, ?, ?)');
        $stmt->bind_param('isss', $id, $title, $contents, $filename);
        $success = $stmt->execute();
        if(!$success){
            die($db->error);
        }
        
        // お知らせ一覧画面に遷移
        header('Location: ./notice.php');
        exit();
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
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="notice.php">お知らせ一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../calendar/calendar.php">集会所使用予定</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="https://www.town.nagi.okayama.jp/gyousei/chousei/kouhou_nagi/index.html" target="_blank">広報誌</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../committee_member.php">役員一覧</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../terms/terms.php">規約</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../opinion/opinion.php">改善意見</a>
                <a class="btn btn-info btn-lg m-3" style="width: 80%;" href="../member/withdrawal.php">退会</a>
        </nav>
        <div class="col-9 mt-3">
            <form action="" method="post" enctype="multipart/form-data" class="form-group">
                <div>
                    <label for="title">件名</label>
                    <input type="text" name="title" id="title" class="form-control">
                    <?php if(isset($error['title']) && $error['title'] === 'blank'): ?>
                        <p class="error">件名を入力してください。</p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="contents">内容</label>
                    <textarea name="contents" id="contents" class="form-control" cols="30" rows="10"></textarea>
                    <?php if(isset($error['contents']) && $error['contents'] === 'blank'): ?>
                        <p class="error">内容を入力してください。</p>
                    <?php endif; ?>
                </div>
                <div>
                    <br>
                    <input type="file" name="file" class="form-control">
                    <?php if(isset($error['file']) && $error['file'] === 'type_error'): ?>
                        <p class="error">ファイルの拡張子は.jpg、.png、.pdfのどれかにして下さい。</p>
                    <?php endif; ?>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">投稿する</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
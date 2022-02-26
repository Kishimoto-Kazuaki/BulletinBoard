<?php
// bulletin_boardデータベースに接続
function db_conect(){
    $db = new mysqli('mysql81.conoha.ne.jp','16xl8_kkazu533','************','16xl8_portfolio');
    //エラーチェック
    if(!$db){
        die($db->error);
    }
    return $db;
}

//htmlspecialcharsを短縮
function h($value){
    return htmlspecialchars($value, ENT_QUOTES);
}

?>
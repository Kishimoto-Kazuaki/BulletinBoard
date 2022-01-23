<?php
// bulletin_boardデータベースに接続
function db_conect(){
    $db = new mysqli('localhost:8889','root','root','bulletin_board');
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
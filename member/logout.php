<?php
session_start();

//セッションの内容を削除
unset($_SESSION['id']);
unset($_SESSION['name']);
unset($_SESSION['adm_id']);
unset($_SESSION['adm_name']);
//ログイン画面に遷移
header('Location: login.php');

?>
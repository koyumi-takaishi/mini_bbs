<?php
session_start();
require('dbconnect.php');

// セッションにidがセットされていたら
if (isset($_SESSION['id'])) {
	// $idにセッションidを代入
	$id = $_REQUEST['id'];
	// 投稿を検査する
	// SQL:postsテーブルからidが?のものをすべて選択
	$messages = $db->prepare('SELECT * FROM posts WHERE id=?');
	// ?に$idを指定してSQL実行
	$messages->execute(array($id));
	// $messagesから一つずつ取り出し
	$message = $messages->fetch();
	// 投稿のmember_idとセッションのidが一致したら
	if ($message['member_id'] == $_SESSION['id']) {
		// 削除する
		// SQL:postsテーブルからidが?のものを削除
		$del = $db->prepare('DELETE FROM posts WHERE id=?');
		// ?に$idを指定してSQL実行
		$del->execute(array($id));
	}
}
// index.phpに遷移して終了
header('Location: index.php'); exit();
?>

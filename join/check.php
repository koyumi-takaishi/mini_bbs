<?php
// セッションスタートするよ！（ブラウザ閉じるまでデータ保持するよ！）
session_start();
// データベース接続用のdbconnect.phpを呼び出すよ！
require('../dbconnect.php');

// $_SESSION['join']になにも含まれていなかった場合
if (!isset($_SESSION['join'])) {
	// index.phpに遷移
	header('Location: index.php');
	exit();
}

// $_POSTが空でなかったら（確認フォームで送信されたら）
if (!empty($_POST)) {
	// 登録処理をする
	// prepareメソッドでSQL文を組み立て。prepareメソッド使うと危険性が高いSQL文を無害化してくれる！
	// SQL:membersテーブルにデータ挿入準備
	$statement = $db->prepare('INSERT INTO members SET name=?, email=?,	password=?, picture=?, created=NOW()');

	// executeメソッドで?の箇所に挿入したい内容を指定する
	echo $ret = $statement->execute(array(
		$_SESSION['join']['name'],
		$_SESSION['join']['email'],
		// sha1ファンクションでパスワード暗号化
		sha1($_SESSION['join']['password']),
		$_SESSION['join']['image']
	));

	// 登録が終わったのでセッションからデータ削除
	unset($_SESSION['join']);

	// thanks.phpにページ遷移
	header('Location: thanks.php');
	exit();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="../css/style.css" />
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>会員登録</h1>
  </div>
  <div id="content">
		<form action="" method="post">
			<input type="hidden" name="action" value="submit" />
			<dl>
			<dt>ニックネーム</dt>
			<dd>
				<!-- htmlタグとか入力されても文字列として出力する。ENT_QUOTES（シングルクオートとダブルクオートを共に変換） -->
				<?php echo htmlspecialchars($_SESSION['join']['name'], ENT_QUOTES); ?>
			</dd>
			<dt>メールアドレス</dt>
			<dd>
				<!-- htmlタグとか入力されても文字列として出力する。ENT_QUOTES（シングルクオートとダブルクオートを共に変換） -->
				<?php echo htmlspecialchars($_SESSION['join']['email'], ENT_QUOTES); ?>
			</dd>
			<dt>パスワード</dt>
			<dd>
			【表示されません】
			</dd>
			<dt>写真など</dt>
			<dd>
				<img src="../member_picture/<?php echo htmlspecialchars($_SESSION['join']['image'], ENT_QUOTES); ?>" width="100" height="100" alt="" />
			</dd>
			</dl>
			<div><a href="index.php?action=rewrite">&laquo;&nbsp;書き直す</a> | <input
			type="submit" value="登録する" /></div>
		</form>
  </div>

</div>
</body>
</html>

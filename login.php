<?php
require('dbconnect.php');
session_start();

// Cookieが空じゃなかったら
if ($_COOKIE['email'] != '') {
	// Cookieの情報を$_POSTに代入
	$_POST['email'] = $_COOKIE['email'];
	$_POST['password'] = $_COOKIE['password'];
	$_POST['save'] = 'on';
}


if (!empty($_POST)) {
	// ログインの処理
	// emailとpasswordが空じゃなかったら
	if ($_POST['email'] != '' && $_POST['password'] != '') {
		// SQL:membersテーブルからemailとpasswordを選択
		$login = $db->prepare('SELECT * FROM members WHERE email=? AND password=?');
		// 入力された情報を?に代入。パスワードは暗号化。
		$login->execute(array(
			$_POST['email'],
			sha1($_POST['password'])
		));
		// $loginから情報を取り出す
		$member = $login->fetch();

		// $memberが空じゃ無かったら（入力した会員情報が存在していたら）
		if ($member) {
			// ログイン成功
			// セッションに情報保存
			$_SESSION['id'] = $member['id'];
			$_SESSION['time'] = time();

			// ログイン情報をCookieに記録する
			if ($_POST['save'] == 'on') {
				// 14日後まで保存しておく
				setcookie('email', $_POST['email'], time() + 60 * 60 * 24 * 14);
				setcookie('password', $_POST['password'], time() + 60 * 60 * 24 * 14);
			}

			// index.phpに遷移
			header('Location: index.php');
			exit();
		} else {
			// ログイン失敗
			$error['login'] = 'failed';
		}
	} else {
		// emailかpasswordが空ですよー！
		$error['login'] = 'blank';
	}
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="robots" content="noindex">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="./css/style.css" />
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>ログインする</h1>
		</div>
		<div id="content">
			<div id="lead">
				<p>メールアドレスとパスワードを記入してログインしてください。</p>
				<p>入会手続きがまだの方はこちらからどうぞ。</p>
				<p>&raquo;<a href="join/">入会手続きをする</a></p>
			</div>
			<form action="" method="post">
				<dl>
					<dt>メールアドレス</dt>
					<dd>
						<input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['email'], ENT_QUOTES); ?>" />
						<?php if ($error['login'] == 'blank') : ?>
							<p class="error">* メールアドレスとパスワードをご記入ください</p>
						<?php endif; ?>
						<?php if ($error['login'] == 'failed') : ?>
							<p class="error">* ログインに失敗しました。正しくご記入ください。</p>
						<?php endif; ?>
					</dd>
					<dt>パスワード</dt>
					<dd>
						<input type="password" name="password" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['password'], ENT_QUOTES); ?>" />
					</dd>
					<dt>ログイン情報の記録</dt>
					<dd>
						<input id="save" type="checkbox" name="save" value="on"><label for="save">次回からは自動的にログインする</label>
					</dd>
				</dl>
				<div><input type="submit" value="ログインする" /></div>
			</form>
		</div>

	</div>
</body>

</html>
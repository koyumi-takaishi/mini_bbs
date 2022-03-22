<?php
// セッションスタートするよ！（ブラウザ閉じるまでデータ保持するよ！）
require('../dbconnect.php');

// セッション開始（ブラウザ閉じるまでデータ保持するよ！）
session_start();

// グローバル変数の$_POSTが空じゃなかったら
if (!empty($_POST)) {
	// エラー項目の確認

	// $_POST['name']が空だったら
	if ($_POST['name'] == '') {
		// $error['name']にblankを代入
		$error['name'] = 'blank';
	}
	// $_POST['email']が空だったら
	if ($_POST['email'] == '') {
		// $error['email']にblankを代入
		$error['email'] = 'blank';
	}
	// $_POST['password']の文字列の長さが4未満だったら
	if (strlen($_POST['password']) < 4) {
		// $error['password']にlengthを代入
		$error['password'] = 'length';
	}
	// $_POST['password']が空だったら
	if ($_POST['password'] == '') {
		// $error['password']にblankを代入
		$error['password'] = 'blank';
	}
	// $_FILESはファイルアップロードのときにファイルが代入される変数
	// input要素のname属性がキーになる（ここではimage）
	$fileName = $_FILES['image']['name'];

	// ファイル名($fileName)が空じゃなかったら
	if (!empty($fileName)) {
		// $fileNameの後ろから３文字を切り出して$extに代入
		$ext = substr($fileName, -3);
		// $extがjpgでない、かつ、$extがgifでない場合
		if ($ext != 'jpg' && $ext != 'gif') {
			// $error['image']にtypeを代入
			$error['image'] = 'type';
		}
	}

	// 重複アカウントのチェック
	// エラーがなかったら
	if (empty($error)) {
		// prepareメソッドでSQL文を組み立て。prepareメソッド使うと危険性が高いSQL文を無害化してくれる！
		// SQL:emailが?のものをmembersテーブルから探してすべてカウントする
		$member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE	email=?');
		// ?にフォームで送信されたemailを挿入
		$member->execute(array($_POST['email']));
		// 結果を取り出して$recordへ代入
		$record = $member->fetch();
		// cntが0より大きかったら
		if ($record['cnt'] > 0) {
			// エラーの処理
			$error['email'] = 'duplicate';
		}
	}

	// $errorが空だったら
	if (empty($error)) {
		// 画像をアップロードする
		// 他の人とファイル名が被らないように今の時間を先頭につける
		$image = date('YmdHis') . $_FILES['image']['name'];
		// member_pictureフォルダに保存
		move_uploaded_file($_FILES['image']['tmp_name'], '../member_picture/' . $image);

		// $_SESSION['join']に$_POSTの中身を代入
		$_SESSION['join'] = $_POST;
		$_SESSION['join']['image'] = $image;

		// check.phpへページ遷移
		header('Location: check.php');

		// おわり！！！！！！
		exit();
	}
}

// 書き直し
// URLパラメータのactionがrewriteだったら
if ($_REQUEST['action'] == 'rewrite') {
	// $_POSTに$_SESSIONの内容を戻す
	$_POST = $_SESSION['join'];
	// ファイルアップロードはやり直してもらう必要があるから、そのメッセージを出すための処理
	$error['rewrite'] = true;
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

	<link rel="stylesheet" href="../css/style.css" />
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>会員登録</h1>
		</div>
		<div id="content">
			<p>次のフォームに必要事項をご記入ください。</p>
			<p>メールアドレスはダミーで大丈夫です！パスワードは暗号化して保存されます！</p>
			<form action="" method="post" enctype="multipart/form-data">
				<dl>
					<dt>ニックネーム<span class="required">必須</span></dt>
					<dd>
						<!-- エラーで戻ってきても、valueには入力した内容が表示される。htmlspecialcharsでhtmlタグを入力してもタグと認識されないようにする -->
						<input type="text" name="name" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['name'], ENT_QUOTES); ?>" />
						<?php if ($error['name'] == 'blank') : ?>
							<p class="error">* ニックネームを入力してください</p>
						<?php endif; ?>
					</dd>

					<dt>メールアドレス<span class="required">必須</span></dt>
					<dd>
						<!-- エラーで戻ってきても、valueには入力した内容が表示される。htmlspecialcharsでhtmlタグを入力してもタグと認識されないようにする -->
						<input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['email'], ENT_QUOTES); ?>" />
						<?php if ($error['email'] == 'blank') : ?>
							<p class="error">* メールアドレスを入力してください</p>
						<?php endif; ?>
						<?php if ($error['email'] == 'duplicate') : ?>
							<p class="error">* 指定されたメールアドレスはすでに登録されています</p>
						<?php endif; ?>
					</dd>

					<dt>パスワード<span class="required">必須</span></dt>
					<dd>
						<!-- エラーで戻ってきても、valueには入力した内容が表示される。htmlspecialcharsでhtmlタグを入力してもタグと認識されないようにする -->
						<input type="password" name="password" size="10" maxlength="20" value="<?php echo htmlspecialchars($_POST['password'], ENT_QUOTES); ?>" />
						<?php if ($error['password'] == 'blank') : ?>
							<p class="error">* パスワードを入力してください</p>
						<?php endif; ?>
						<?php if ($error['password'] == 'length') : ?>
							<p class="error">* パスワードは4文字以上で入力してください</p>
						<?php endif; ?>
					</dd>

					<dt>写真など</dt>
					<dd><input type="file" name="image" size="35" />
						<?php if ($error['image'] == 'type') : ?>
							<p class="error">* 写真などは「.gif」または「.jpg」の画像を指定してください
							</p>
						<?php endif; ?>
						<?php if (!empty($error)) : ?>
							<p class="error">* 恐れ入りますが、画像を改めて指定してください</p>
						<?php endif; ?>
					</dd>
				</dl>
				<div><input type="submit" value="入力内容を確認する" /></div>
			</form>
		</div>

	</div>
</body>

</html>
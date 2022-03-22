<?php
session_start();
require('dbconnect.php');

// セッションでidがセットされている、かつ、timeがセットされていて1時間以内だったら
if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
	// ログインしている
	// セッションに時間を記録
	$_SESSION['time'] = time();
	// SQL:membersテーブルからid=?のデータを取り出し
	$members = $db->prepare('SELECT * FROM members WHERE id=?');
	// セッションに保存されているidを?に代入
	$members->execute(array($_SESSION['id']));
	// データを一つずつ取り出し
	$member = $members->fetch();
} else {
	// ログインしていない
	// ログイン画面に遷移
	header('Location: login.php');
	exit();
}

// 投稿を記録する
// $_POSTが空じゃなかったら
if (!empty($_POST)) {
	// messageが空じゃなかったら
	if ($_POST['message'] != '') {
		// postsテーブルに投稿者idと投稿内容と投稿時刻を挿入
		$message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_post_id=?, created=NOW()');
		// member_idとmessageとreply_post_idに挿入する内容を指定
		$message->execute(array(
			$member['id'],
			$_POST['message'],
			$_POST['reply_post_id']
		));
		// index.phpに遷移
		header('Location: index.php'); exit();
	}
}

// 投稿を取得する
// パラメータのpageを$pageに代入
$page = $_REQUEST['page'];
// もしpageが設定されていなかったら
if ($page == '') {
	// 1ページ目を設定
	$page = 1;
}
// $pageにマイナスの値が設定されたときは1が設定されるようにする
$page = max($page, 1);

// 最終ページを取得する
// postsテーブルから全部カウント
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
// $countsからデータ取り出し
$cnt = $counts->fetch();
// 最大ページ数を取得（cntを5で割って切り上げ）
$maxPage = ceil($cnt['cnt'] / 5);
// $pageに最大ページより大きい数字が設定されたときは最大ページを設定する
$page = min($page, $maxPage);

// そのページで表示する最初の投稿（3ページ目だったら2*5=10件目から表示）
$start = ($page - 1) * 5;
// 仮に$startにマイナスの値が設定されたら0を設定する
$start = max(0, $start);

// SQL:membersテーブルからnameとpicture、postsテーブルからすべて選択、membersのidとpostsのmember_idが一致するものを、投稿日時の降順で、開始位置?から5件分
$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?, 5');
// $startが文字列なので数字として?に代入してSQL実行
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();

// 返信の場合
// パラメータにresがセットされていたら
if (isset($_REQUEST['res'])) {
	// SQL:membersテーブルとpostsテーブルからmembersのnameとpictureとpostsの情報すべて取得、membersのidとpostsのmember_idが一致する、かつ、postsのidが?のもの、投稿日時の降順で
	$response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m,	posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
	// ?にresの値を入れて取り出し
	$response->execute(array($_REQUEST['res']));
	// $tableに$responseを一つずつ格納していく
	$table = $response->fetch();
	// レス元の名前とメッセージを$messageに格納
	$message = '@' . $table['name'] . ' ' . $table['message'];
}

// htmlspecialcharsのショートカット
function h($value) {
	return htmlspecialchars($value, ENT_QUOTES);
}

// 本文内のURLにリンクを設定します
function makeLink($value) {
	return mb_ereg_replace("(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)",'<a href="\1\2">\1\2</a>' , $value);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="./css/style.css" />
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>ひとこと掲示板</h1>
  </div>
  <div id="content">
		<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
		<form action="" method="post">
		<dl>
			<dt><?php echo h($member['name']); ?>さん、メッセージをどうぞ</dt>
		<dd>
			<textarea name="message" cols="50" rows="5"><?php echo h($message); ?></textarea>
			<!-- 返信先のidを送信 -->
			<input type="hidden" name="reply_post_id" value="<?php echo h($_REQUEST['res']); ?>" />
		</dd>
		</dl>
		<div>
		<input type="submit" value="投稿する" style="margin-bottom: 30px;" />
		</div>
		</form>

		<?php
		// postsのデータを一つずつ取り出し。
		foreach ($posts as $post):
		?>
		<div class="msg">
			<!-- 投稿者の画像 -->
			<?php 
				// $fileNameの後ろから３文字を切り出して$extに代入
				$ext = substr($post['picture'], -3);
				// $extがjpgでない、かつ、$extがgifでない場合、noimage画像を出力
				if ($ext != 'jpg' && $ext != 'gif') { ?>
				<img src="member_picture/noimage.jpg" width="48" height="48" alt="NoImage" />
			<?php } else { ?>
				<img src="member_picture/<?php echo h($post['picture']); ?>" width="48" height="48" alt="<?php echo h($post['name']); ?>" />
			<?php } ?>
			<!-- 投稿内容と投稿者名 -->
			<p><?php echo makeLink(h($post['message']));?><span class="name">（<?php echo h($post['name']); ?>）</span>[<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]</p>
			<!-- 投稿日時 -->
			<p class="day">
				<a href="view.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['created']); ?></a>
				<?php
				// リプだったら
				if ($post['reply_post_id'] > 0):
					?>
					<a href="view.php?id=<?php echo h($post['reply_post_id']); ?>">返信元のメッセージ</a>
					<?php
				endif;
				?>
				<?php
				// ログイン中のidと投稿のidが一致したら
				if ($_SESSION['id'] == $post['member_id']):
					?>
					[<a href="delete.php?id=<?php echo h($post['id']); ?>" style="color:#F33;">削除</a>]
					<?php
				endif;
				?>
			</p>
		</div>
		<?php
		endforeach;
		?>
  </div>

	<ul class="paging">
		<?php
		// $pageが1より大きかったら
		if ($page > 1) {
		?>
			<li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a></li>
		<?php
		// $pageが1以下だったら
		} else {
		?>
			<li>前のページへ</li>
		<?php
		}
		?>
		<?php
		// $pageが最大ページより小さかったら
		if ($page < $maxPage) {
		?>
			<li><a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a></li>
		<?php
		// $pageが最大ページだったら
		} else {
		?>
			<li>次のページへ</li>
		<?php
		}
		?>
	</ul>

</div>
</body>
</html>

<?php
session_start();

$password = "123456";

$GLOBALS['tips'] = NULL;

//初始化验证数据
if (!isset($_SESSION['login_hash'])) {
	$_SESSION['login_hash'] = array('login_num' => 1, 'last_time' => time(), 'locked' => FALSE, 'is_login' => FALSE);
}
//解锁判断
if (is_lock()) {
	if (time() - locked_time() > 300) {
		unlock();
	}
} else {
	if (is_login_too_much()) {
		do_lock();
	}
}

//登陆流程开始
if (isset($_POST['password'])) {
	if ($_POST['password'] == $password) {
		logined();
		unlock();
	} else {
		update_time();
		add_login_num();
		msg('密码错误');
	}
}

//添加文章
if (isset($_POST['postname']) && isset($_POST['postcontent']) && is_login()) {
	$title = $_POST['postname'];
	$content = $_POST['postcontent'];
	markdown($title, $content);
	echo "<script>alert('添加成功')></script>";
}

/**
 * transcribe()
 * 来自LazyPHP
 */
$_GET = transcribe($_GET);
$_POST = transcribe($_POST);
$_REQUEST = transcribe($_REQUEST);

function transcribe($aList, $aIsTopLevel = true) {
	$gpcList = array();
	$isMagic = get_magic_quotes_gpc();

	foreach ($aList as $key => $value) {
		if (is_array($value)) {
			$decodedKey = ($isMagic && !$aIsTopLevel) ? stripslashes($key) : $key;
			$decodedValue = transcribe($value, false);
		} else {
			$decodedKey = stripslashes($key);
			$decodedValue = ($isMagic) ? stripslashes($value) : $value;
		}
		$gpcList[$decodedKey] = $decodedValue;
	}
	return $gpcList;
}

function do_lock() {
	$_SESSION['login_hash']['locked'] = TRUE;
}

function unlock() {
	$_SESSION['login_hash']['locked'] = FALSE;
	$_SESSION['login_hash']['login_num'] = 1;
}

function is_lock() {
	return $_SESSION['login_hash']['locked'];
}

function is_login_too_much() {
	return $_SESSION['login_hash']['login_num'] >= 10;
}

function locked_time() {
	return $_SESSION['login_hash']['last_time'];
}

function update_time() {
	if ($_SESSION['login_hash']['login_num'] < 10) {
		$_SESSION['login_hash']['last_time'] = time();
	}
}

function add_login_num() {
	if ($_SESSION['login_hash']['login_num'] < 10) {
		$_SESSION['login_hash']['login_num'] += 1;
	}
}

function msg($text = '') {
	if ($text == '') {
		return $GLOBALS['tips'];
	} else {
		$GLOBALS['tips'] = $text;
	}
}

function is_login() {
	return $_SESSION['login_hash']['is_login'];
}

function logined() {
	$_SESSION['login_hash']['is_login'] = TRUE;
}

function markdown($title, $content) {
	$data = file_get_contents('./templates/single_tpl.php');
	$template_tag = array('{title}', '{marktime}', '{content}');
	$contents = array($title, date('Y-m-d H:m:s'), $content);
	$html_data = str_replace($template_tag, $contents, $data);
	file_put_contents('./mark/' . time() . '.html', $html_data);
}
?>
<html>
	<head>
		<meta charset="utf-8" />
		<title>TinyLib Control Panel</title>
		<link rel="stylesheet" href="style.css" />
		<link rel="stylesheet" href="normalize.css" />
	</head>
	<body>
		<div class="panel">
			<div class="panel-header">
				<h1 class="header-title">TinyLib Control Panel</h1>
			</div>
			<div class="panel-main">
				<?php
			if(is_login()) :
				?>
				<form name="mark" action="" method="post">
					<p>
						<label for="post-name">文章标题 </label>
						<br/>
						<input type="text" id="post-name" name="postname" placeholder="例如：全国人民喜迎油价上涨"/>
					</p>
					<p>
						<label for="post-content">文章内容</label>
						<br/>
						<textarea type="text" id="post-content" name="postcontent"></textarea>
						<div class="tips">
							单纯文本，不支持格式，请填写HTML代码。
						</div>
					</p>
					<p>
						<input type="submit" value="发布">
					</p>
				</form>
				<?php else : ?>
				<form method="post" method="">
					<div class="tips"> <?php echo msg(); ?></div>
					<input <?php echo is_lock() ? 'disabled' : ''; ?> type="text" name="password" placeholder="<?php echo is_lock() ? 'You are locked' : 'Enter Your Code'?>"/>
					<br />
					<input <?php echo is_lock() ? 'disabled' : ''; ?> type="submit" value="Signup">
				</form>
				<?php endif; ?>
			</div>
		</div>

		<div class="fooer">
			<a href="mailto:csdk@Outlook.com">
				Contact Me
			</a>
		</div>
	</body>
</html>
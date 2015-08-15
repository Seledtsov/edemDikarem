<?php
	$admin_user='default';
	$admin_password='silfida';

	$request_path=parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	if(!isset($_SERVER['PHP_AUTH_USER']) || ($_POST['SeenBefore'] == 1 && $_POST['OldAuth'] == $_SERVER['PHP_AUTH_USER'])) {
		header('WWW-Authenticate: Basic realm="Please login"');
		header('HTTP/1.0 401 Unauthorized');
		header('status: 401 Unauthorized');
		header('Content-Type: text/html; charset=windows-1251');
		die("Access forbidden. <a href='$request_path?login=1'>Login</a>");
		exit();
	}elseif($_SERVER['PHP_AUTH_USER']!=$admin_user && ! isset($_GET['logout']) && !isset($_POST['SeenBefore'])){
		$href = "http://".$_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW']."@".$_SERVER['SERVER_NAME'].'/';
	?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>

	</head><body><h1>Вы авторизованы!</h1>
	<? echo "<p>Вы авторизованы как: " . htmlspecialchars($_SERVER['PHP_AUTH_USER']) . "<br />";
    echo "<form action='' method='post'>\n";
    echo "<input type='hidden' name='SeenBefore' value='1' />\n";
    echo "<input type='hidden' name='OldAuth' value=\"" . htmlspecialchars($_SERVER['PHP_AUTH_USER']) . "\" />\n";
    echo "<input type='submit' value='Сменить пользователя' />\n";
    echo "</form></p>\n";?>
	<a href="/">На главную</a></body>
	</html><?
	}elseif((@$_SERVER['PHP_AUTH_USER']!=$admin_user || @$_SERVER['PHP_AUTH_PW']!=$admin_password) && !isset($_GET['login'])) {
		$href = "http://".$admin_user.":".$admin_password."@".$_SERVER['SERVER_NAME'].$request_path;
	?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		<head>

		</head><body><script>location.href = '<?=$href;?>';</script><?
				echo '<h1>Требуется авторизация.</h1>';
				//echo "<a href='$request_path?login=1'>Login</a>";
				echo "<form action='' method='post'>\n";
			    echo "<input type='hidden' name='SeenBefore' value='1' />\n";
			    echo "<input type='hidden' name='OldAuth' value=\"" . htmlspecialchars($_SERVER['PHP_AUTH_USER']) . "\" />\n";
			    echo "<input type='submit' value='Сменить пользователя' />\n";
			    echo "</form></p>\n";
			?><a href="/">На главную</a>
		</body>
	</html>
	<?
	}else{?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		<head>
		</head><body>
			<?
				echo '<h1>Сменить пользователя.</h1>';
				echo "<p>Вы авторизованы как: " . htmlspecialchars($_SERVER['PHP_AUTH_USER']) . "<br />";
				echo "<form action='' method='post'>\n";
			    echo "<input type='hidden' name='SeenBefore' value='1' />\n";
			    echo "<input type='hidden' name='OldAuth' value=\"" . htmlspecialchars($_SERVER['PHP_AUTH_USER']) . "\" />\n";
			    echo "<input type='submit' value='Сменить пользователя' />\n";
			    echo "</form></p>\n";
			?><a href="/">На главную</a>
		</body>
	</html>
	<?}?>
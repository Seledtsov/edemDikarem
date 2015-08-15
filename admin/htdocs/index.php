<?
ini_set('display_errors', 1);
ini_set('error_reporting', 15);
ini_set('log_errors', 1);



if(getenv("g_INC"))
	include_once(getenv("g_INC")."conf.php");
else
	include_once($_SERVER['DOCUMENT_ROOT']."/inc/conf.php");
//phpinfo();

include(PATH_INC."inc.php");


show_admin_menu($conn);

?>

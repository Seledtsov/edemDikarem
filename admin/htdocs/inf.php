<?
if(getenv("g_INC"))
	include_once(getenv("g_INC")."conf.php");
else
	include_once($_SERVER['DOCUMENT_ROOT']."/inc/conf.php"); 
phpinfo();
?>
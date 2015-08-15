<?
	//генераци€ скрипта дл€ заполнени€ метаданных
	include(getenv("g_INC")."conf.php");
	include(PATH_INC."inc.php");
	include(PATH_INC."func.php");
	//print_r($_SERVER['DOCUMENT_ROOT']);
	$dirname = IMAGE_PATH.IMAGE_GALLERY;//'/home/client45/01www/agency/htdocs/images/gallery';
	echo scan_dir($dirname,'true');
	echo '<script language="JavaScript">setTimeout("window.close(\'dci\')", 5000 );</script>';
?>

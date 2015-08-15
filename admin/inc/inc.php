<?
	if(!DEFINED("NO_SES"))
		session_start();
	set_time_limit(600);
	ini_set('post_max_size', '100M');
	ini_set('upload_max_filesize', '100M');
	//ini_set('upload_tmp_dir', '/tmp');
	//ini_set('memory_limit', '100M');
	if(DBASE=="ORACLE")
	{
		include_once(PATH_INC."func/db_func_ora.php");
		$conn=db_connect(DB, DB_USER, DB_PASSWD);
	}
	elseif (DBASE == "POSTGRESQL")
	{
		include_once(PATH_INC."func/db_func_post.php");
		if(DEFINED("LOW_CONNECT") && DEFINED("DB_LOW_USER"))
		{
			$conn=db_connect(DB, DB_LOW_USER, DB_PASSWD, DB_HOST);
			if (function_exists("low_user"))
				low_user();
		}
		else
			$conn=db_connect(DB, DB_USER, DB_PASSWD, DB_HOST);
	}
	if(!$conn)
	{
		echo"Dbase error";
		exit();
	}
	else
	{
		$encoding = pg_client_encoding($conn);
		if($encoding=="UTF8"){
			pg_set_client_encoding($conn, "WIN1251");
			//$encoding = pg_client_encoding($conn);
		}
		//echo"Connect";
	}

	if(!$SCRIPT_NAME)
		foreach($argv as $k_av=> $v_av)
		{
			$argv_ar=split("=", $v_av);
			$argv_name=$argv_ar[0];
			$$argv_name= trim($argv_ar[1]);
	}
	include_once(PATH_INC."func/func_html.php");
	include_once(PATH_INC."func/func.php");
	include_once(PATH_INC."func/func_entity.php");
	include_once(PATH_INC."func/func".DBASE_ATTR.".php");
	include_once(PATH_INC."func/db_structure_edit".DBASE_ATTR.".php");

	our_const($conn);

	include_once(PATH_INC."const.php");
	include_once(PATH_INC."const_admin.php");
	//$css_ar[]="/css/admin.css";
	$css_ar=array("/css/admin.css","/css/calendar.css");
	if(!DEFINED("NO_AUTH"))
	{
		if($REMOTE_USER || DEFINED("GUEST_USER"))
		{
			$user_info_sql = "select id,  first_name, second_name, family from ".TABLE_PRE."users where name = '".($REMOTE_USER?$REMOTE_USER:GUEST_USER)."'";
			//echo"user_info_sql=$user_info_sql<br>";
			$USER_INFO = db_getArray($conn, $user_info_sql, 2);
			if($USER_INFO['ID'])
				define("g_USER_ID", $USER_INFO['ID']);
		}

		if(!defined("g_USER_ID"))
		{
			$user_info_sql="select count(*) as c from  ".TABLE_PRE."users";
			$user_info=db_getArray($conn, $user_info_sql, 2);
			if(!$user_info['C'])//ни одного пользователя нет
			{
				DEFINE("NO_USER", 1);
			}
			else
			{
				$request_path=parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
				die("<h1>У вас нет прав на доступ к данному ресурсу</h1><a href='/login.php?login=1'>login</a>");
			}
		}
	}

	if(count($over_id))
	{
		$addit_string_ar['over_id']=$over_id;
		$addit_string_ar['over_arm_id']=$over_arm_id;
	}
	if($lookup)
	{
		$addit_string_ar['lookup']=$lookup;
		$addit_string_ar['lookup_name']=$lookup_name;
	}
	if($search)
	{
		$addit_string_ar['search']=$search;
		if($sort_by_search && !$sort_by)
			$sort_by=$sort_by_search;
		$addit_string_ar['sort_by_search']=$sort_by_search;
	}
	if($our_pref)
		$addit_string_ar['our_pref']=$our_pref;
	if($print)
		$addit_string_ar['print']=$print;
	if($print_form)
		$addit_string_ar['print_form']=$print_form;
	$addit_string_ar['our_arm_id']=$our_arm_id;
	//$addit_string_ar['our_ent_id']=$our_ent_id;
	$addit_string_ar['sort_by']=$sort_by;
	$addit_string_ar['page']=$page;
	if(HOST=="mpr" || HOST=="rzd_en")
		include_once(PATH_INC_HOST."/inc.php");

	/*Коды ошибок*/
	$ERROR_CODE[1]="Невозможно добавление колонки";
	$ERROR_CODE[2]="Ошибка при сохранении данных";
	$ERROR_CODE[3]="Невозможно удаление колонки";
	$ERROR_CODE[4]="Невозможно добавление таблицы";
	$ERROR_CODE[5]="Невозможно удаление таблицы";
	$ERROR_CODE[6]="Не произошло сохранение шаблона на диск";
	$ERROR_CODE[7]="Не призошел пересчет райтинга";

	//echo"MKTIME_CORR=".MKTIME_CORR.", ".mktime(0, 0, 0, 1, 1, 1963)."-".date('d-m-Y', mktime(0, 0, 0, 1, 1, 1963)).", ".mktime(0, 0, 0, 12, 10, 1963)."-".date('d-m-Y', mktime(0, 0, 0, 12, 10, 1963))."<br>";
	//echo"2071 - ".mktime(0, 0, 0, 12, 10, 2071)."<br>";
?>

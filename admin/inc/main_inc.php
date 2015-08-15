<?
$time_ar_all[600]=mktime()-$start_time_global;
if(!DEFINED("NO_SES"))
    session_start();
$time_ar_all[602]=mktime()-$start_time_global;
set_time_limit(120);
$time_ar_all[603]=mktime()-$start_time_global;
if(DBASE=="ORACLE")
   {
   include_once(PATH_INC."func/db_func_ora.php");
   $conn=db_connect(DB, DB_USER, DB_PASSWD);
   }
elseif (DBASE == "POSTGRESQL")
  {
   include_once(PATH_INC."func/db_func_post.php");
	$time_ar_all[604]=mktime()-$start_time_global;
	$conn=db_connect(DB, DB_USER, DB_PASSWD, DB_HOST);
	$time_ar_all[605]=mktime()-$start_time_global;
   //echo"conn=$conn, ".DB.", ".DB_USER.", ".DB_PASSWD.", ".DB_HOST."<br>";
  }
if(!$conn)
    {
    echo"Error database - main";
    exit();
    }
//echo"be";
$time_ar_all[610]=mktime()-$start_time_global;
include_once(PATH_INC."func/func_html.php");
include_once(PATH_INC."func/func.php");

include_once(PATH_INC."func/func_entity.php");
$time_ar_all[620]=mktime()-$start_time_global;
$our_const_xml=our_const($conn);
$time_ar_all[630]=mktime()-$start_time_global;
include_once(PATH_INC."const.php");
$time_ar_all[640]=mktime()-$start_time_global;
include_once(PATH_INC."const_main.php");
$time_ar_all[650]=mktime()-$start_time_global;
if(!$nav_id && defined("FORUM_ID") && ereg("phpBB", g_URL))
        $nav_id=FORUM_ID;

include_once(PATH_INC_HOST."/func/func_main.php");
$time_ar_all[660]=mktime()-$start_time_global;
include_once(PATH_INC."phrase.php");
$time_ar_all[670]=mktime()-$start_time_global;
include_once(PATH_INC_HOST."/main_inc.php");

$post['g_URL']=g_URL;

//определяем, отдавать ли броузеру xml или html
//echo$REMOTE_ADDR;
if($REMOTE_ADDR=="127.0.0.1")
	{
	DEFINE("BROUSER_XML", 0);//1 - отдавать в xml
	}
elseif(strpos($HTTP_USER_AGENT, "MSIE 6.0"))
	{
	DEFINE("BROUSER_XML", 0);//1 - отдавать в xml
	}
else
	{
	DEFINE("BROUSER_XML", 0);
	}
if($save_form)//сохраняем из формы снаружи
	{
	//$arm_id
	}
$time_ar_all[650]=mktime()-$start_time_global;

//if(!DEFINED("NO_SES"))
//	session_write_close();
?>
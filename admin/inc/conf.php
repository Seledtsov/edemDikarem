<?
ini_set('display_errors', 0);
ini_set('error_reporting', 15);
ini_set('log_errors', 1);
ini_set('error_reporting', 7);

// ***************** Параметров к БД ****************************//
define("TABLE_PRE", "TI_");
// ***************** Конец параметров к БД ****************************//

if($HTTP_HOST)
	define("g_HOST", $HTTP_HOST);
elseif($_SERVER["HTTP_HOST"])
	define("g_HOST", $_SERVER["HTTP_HOST"]);

if(!defined("g_HOST"))//если из командной строки
	{
	$argv = $_SERVER["argv"];
	foreach($argv as $k_av=> $v_av)
		{
		$argv_ar=split("=", $v_av);
		if($argv_ar[0]=="host")
			define("HOST", trim($argv_ar[1]));
		elseif($argv_ar[0]=="period")
			define("PERIOD", trim($argv_ar[1]));
		elseif($argv_ar[0]=="lang")
			define("lang", trim($argv_ar[1]));
		$argv_name=$argv_ar[0];
		$$argv_name= trim($argv_ar[1]);
		}
	}
elseif(eregi("dikar", g_HOST))
	{
	define("HOST", "dikar");
	}
elseif(eregi("hr-praktiki", g_HOST))
	{
	define("HOST", "hr-praktiki");
	}
elseif(eregi("oooinex", g_HOST)) 
	{
	define("HOST", "oooinex");
	}

define("DBASE", "POSTGRESQL");
define("DBASE_ATTR", "_post");//используется для подсоединения специфимческих для данной базы файлов
define("DB_HOST", "localhost");
define("DB_USER", "pst");
define("DB_PASSWD", "pst");

define("DB_LOW_USER", "pst");//пользователь со сниженным приоритетом
define("BASE_TRUE", "t");
define("BASE_FALSE", "f");
define("BASE_NULL", "null");


if(HOST=="oooinex") 	{
	DEFINE("DB", "oooinex");
	define("PUBLISH_LIST", "2");
} elseif(HOST=="dikar") {
	DEFINE("DB", "dikar");
	define("PUBLISH_LIST", "2");
} elseif(HOST=="hr-praktiki") {
	DEFINE("DB", "hr-praktiki");
	define("PUBLISH_LIST", "2");
}

if(getenv("g_INC"))
	define("PATH_INC", getenv("g_INC"));
elseif($g_INC)
	define("PATH_INC", $g_INC);
if(getenv("PATH_INC_HOST"))
	define("PATH_INC_HOST", getenv("PATH_INC_HOST"));
else
	define("PATH_INC_HOST", PATH_INC.HOST);
//echo"PATH_INC=".PATH_INC."\r\n";
 $g_uid = 1;
 $g_url = getenv('SCRIPT_NAME');

if($SCRIPT_NAME)
   define("g_URL", $SCRIPT_NAME);
elseif($_SERVER["SCRIPT_NAME"])
   define("g_URL", $_SERVER["SCRIPT_NAME"]);



 if($QUERY_STRING)
    define("g_QUERY", $QUERY_STRING);
 elseif($_SERVER["QUERY_STRING"])
    define("g_QUERY", $_SERVER["QUERY_STRING"]);


 $g_query = getenv('QUERY_STRING');

 define("DELETE", "Удаление");
 define("ADD", "Добавление");
 define("UPDATE", "Редактирование");

define("PUBLISH", "PUBLISH_STATE_ID");
define("DATE_MAIN", "DATE_MAIN");
define("ORDER_NUM", "SORT_ORDER");
define("NAV", "NAVIGATION");
DEFINE("PUBLISH_LIST", 2);
DEFINE("OUR_SERVER", "http://loc.fntr");//vneshnyaja chast' - dlja rassylok
if(ereg(",", PUBLISH_LIST))
   define("PUBLISH_SQL", " in (".PUBLISH_LIST.")");
else
   define("PUBLISH_SQL", "=".PUBLISH_LIST);

DEFINE("POST_OBJ", 1);//нет привязки рассылки к навигации, только сущностей
/*SYSTEM PARAMETERS*/
DEFINE("PHP_STR_PATH", "/usr/bin/php");//путь к php - для запуска из строки

DEFINE("APACHE_BIN", "/usr/bin/");
if(eregi("loc", g_HOST))
	{
	define("SQL_LOG_FILE", "E:/svn/trunk/t3/logs/sql_log_".HOST.".log");
	global $_SERVER;
	define("XML_LOG_FILE", "E:/svn/trunk/t3/logs/xml_log_".HOST."_".$_SERVER['REMOTE_ADDR'].".log");
	define("TEST_SERVER_FLAG", 1);//флаг, что тестовый сервер
	define("LOG_PATH", "E:/svn/trunk/t3/logs/");
	define("LOG_PATH", "E:/svn/trunk/t3/logs/");
	}
else
	{
	define("SQL_LOG_FILE", "/var/www/sql_log_".HOST.".log");
	define("LOG_PATH", "/var/www/");
	define("LOG_DIR", "/tmp/");//путь к директории логов
	}
	DEFINE("POST_SERVER", "localhost");//server dlja otpravki pochty
if(!((eregi("loc", g_HOST) && $REMOTE_ADDR!='127.0.0.1') || eregi("test", g_HOST) ))
	{
	DEFINE("CACHE_YES", 1);
	}

//mktime_error
ini_set('display_errors', 0);
if(mktime(0, 0, 0, 1, 1, 1969)==-1 || mktime(0, 0, 0, 1, 1, 1969)==mktime(0, 0, 0, 1, 1, 1960))
	DEFINE("MKTIME_CORR", 30);//прибавляем столько лет
else
	DEFINE("MKTIME_CORR", 0);
ini_set('display_errors', 0);
?>

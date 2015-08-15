<?
//define("ADODB_DIR", etenv("g_INC")."adodb");
ini_set('display_errors', 0);
ini_set('error_reporting', 15);
ini_set('log_errors', 1);
//ini_set('error_log', 'nk/t3/logs/t3_php.log');
//phpinfo();
ini_set('error_reporting', 7);
//echo"<pre>"; print_r($_SESSION);
//print_r($_POST);
//print_r($_FILES);
// ***************** Параметров к БД ****************************//
define("TABLE_PRE", "TI_");
//echo"be";
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
elseif(eregi("fntr", g_HOST))//ФНТР
	{
    	define("HOST", "fntr");
	if(eregi("eng", g_HOST))
		DEFINE("lang", "eng");
	else
		DEFINE("lang", "rus");
   	}
elseif(eregi("ettc", g_HOST))//ФНТР
	{
    	define("HOST", "ettc08");
	if(eregi("rus", g_HOST))
		DEFINE("lang", "rus");
	else
		DEFINE("lang", "eng");
  	}
elseif(eregi("dvgazeta", g_HOST))//ФНТР
	{
    	define("HOST", "dvgazeta");
  	}
elseif(eregi("inex\.expert", g_HOST))//ФНТР
	{
    	define("HOST", "inexexpert");
  	}
elseif(eregi("tehnosk", g_HOST))//Технос-К
	{
    define("HOST", "tehnosk");
    }
elseif(eregi("kasyanov", g_HOST) )
      {
      define("HOST", "kasyanov");
      if(eregi("eng", g_HOST))
		DEFINE("lang", "eng");
      else
		DEFINE("lang", "rus");
      }
elseif(eregi("dikar", g_HOST))
        {
    define("HOST", "dikar");
    }
elseif(eregi("hr-praktiki", g_HOST))
        {
    define("HOST", "hr-praktiki");
    }
elseif(eregi("agency", g_HOST)) {
	if(!DEFINED("NO_SES")){
   	 session_start();
	}
	ini_set('error_log', '/home/client45/02logs/agency.php.log');
    define("HOST", "agency");
    $lang = "rus";
    if(eregi("eng", g_HOST) || $_REQUEST["lang"]=="en" || $_REQUEST["lang"]=="eng"){
		$lang="eng";
	}elseif(eregi("rus", g_HOST) || $_REQUEST["lang"]=="rus"){
		$lang= "rus";
	}elseif(isset($_SESSION["user_lang"])){
		$lang = $_SESSION["user_lang"];
	}else{
		$lang= "rus";
	}
	DEFINE("lang", $lang);
}
elseif(eregi("oooinex", g_HOST)) {
    define("HOST", "oooinex");
    }
define("DBASE", "POSTGRESQL");
define("DBASE_ATTR", "_post");//используется для подсоединения специфимческих для данной базы файлов
//define("DB", "lite");
//define("DB_HOST", "uran.tinfor.ru");
define("DB_HOST", "localhost");
if(lang=="eng" && HOST=="agency"){
			DEFINE("DB_USER", "public_en");
			DEFINE("DB_PASSWD", "pst_en");
}else{
	define("DB_USER", "pst");
	define("DB_PASSWD", "pst");
}
define("DB_LOW_USER", "pst");//пользователь со сниженным приоритетом
define("BASE_TRUE", "t");
define("BASE_FALSE", "f");
define("BASE_NULL", "null");


if(HOST=="fntr")
	{
	if(!DEFINED("lang"))
		DEFINE("lang", "rus");
	if(lang=="eng")
		DEFINE("DB", "fntr_eng");
	else
		DEFINE("DB", "fntr");
	}
elseif(HOST=="tehnosk")
	{
        DEFINE("DB", "tehnosk");
        define("FORUM_IN_SITE", 1);
        define("PUBLISH_LIST", "2");
	}
elseif(HOST=="ettc08")
	{
        DEFINE("DB", "ettc08");
        define("PUBLISH_LIST", "2");
	}
elseif(HOST=="dvgazeta")
	{
        DEFINE("DB", "dvgazeta");
        define("PUBLISH_LIST", "2");
	}
elseif(HOST=="kasyanov")
	{
    	DEFINE("DB", "kasyanov");
        define("PUBLISH_LIST", "2");
	}
elseif(HOST=="parus")
	{
    	DEFINE("DB", "parus");
        define("PUBLISH_LIST", "2");
	}
elseif(HOST=="oooinex")
	{
    	DEFINE("DB", "oooinex");
      define("PUBLISH_LIST", "2");
	}
elseif(HOST=="inexexpert")
	{
    	DEFINE("DB", "inexexpert");
      define("PUBLISH_LIST", "2");
	}
elseif(HOST=="dikar")
        {
        DEFINE("DB", "dikar");
        define("PUBLISH_LIST", "2");
        }
elseif(HOST=="hr-praktiki")
        {
        DEFINE("DB", "hr-praktiki");
        define("PUBLISH_LIST", "2");
        }
elseif(HOST=="agency")
{
        DEFINE("DB", "agency");
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
//echo"g_HOST=".g_HOST."<br>";
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
//echo"g_HOST=".SQL_LOG_FILE."<br>";
	DEFINE("POST_SERVER", "localhost");//server dlja otpravki pochty
//USE cache
if(!((eregi("loc", g_HOST) && $REMOTE_ADDR!='127.0.0.1') || eregi("test", g_HOST) ))
	{
	//echo"cache<br>";
	DEFINE("CACHE_YES", 1);
	}
//DEFINE("CACHE_YES", 1);

//mktime_error
ini_set('display_errors', 0);
if(mktime(0, 0, 0, 1, 1, 1969)==-1 || mktime(0, 0, 0, 1, 1, 1969)==mktime(0, 0, 0, 1, 1, 1960))
	DEFINE("MKTIME_CORR", 30);//прибавляем столько лет
else
	DEFINE("MKTIME_CORR", 0);
//echo"MKTIME_CORR=".MKTIME_CORR."<br>";
ini_set('display_errors', 0);
//echo mktime(0, 0, 0, 1, 1, 2057);
?>

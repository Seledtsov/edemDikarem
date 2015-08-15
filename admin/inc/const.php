<?
//колонки для дерева
define("PARENT_ID", "SORT_ORDER");
define("REAL_ORDER", "REAL_".ORDER_NUM);
define("KID_COUNT", "KID_COUNT");
define("ID_PATH", "ID_PATH");
//конец колонок для дерева

//Номер версии админки
if(!defined("VERSION"))
    define("VERSION", "1.0.026");
//время ожидания - от поступления  в очередь до индексации
if(!defined("INDEXER_WAIT_MINUT"))
    define("INDEXER_WAIT_MINUT", 10);
//echo"INDEXER_WAIT_MINUT=".INDEXER_WAIT_MINUT."\r\n";
//время ожидания - от поступления  в очередь до очистки кеша
if(!defined("CLEAR_CACHE_MINUT"))
	define("CLEAR_CACHE_MINUT", 1);
//формат даты базы
if(!defined("DATE_FORMAT"))
    define("DATE_FORMAT", "Y-m-d H:i:s");
//формат промежуточной даты
if(!defined("DATE_FORMAT_TMP"))
    define("DATE_FORMAT_TMP", "Y-m-d H:i:s");
// ID арма редактирования БД Мультимедиа
if(!defined("BD_MM_ARM_ID"))
    define("BD_MM_ARM_ID", "16");
//строковый разделитель
if(!defined("RAZD"))
    define("RAZD", "=---=");
//строковый разделитель в навигации
if(!defined("RAZD_NAV"))
    define("RAZD_NAV", "!!!");

// ID арма редактирования полей арма
if(!defined("ARM_ID"))
    define("ARM_ID", "10");
//для вопросов-ответов - сколько вопросов нужно показывать в развернутом виде
if(!defined("QUESTION_MAIN_NUM"))
    define("QUESTION_MAIN_NUM", 2);
//начальный год для выпадающих списков
if(!DEFINED("FROM_YEAR"))
    DEFINE("FROM_YEAR", 2004);
if(!DEFINED("ITEMS_ON_PAGE"))
    DEFINE("ITEMS_ON_PAGE", 30);
define("BIG_ORDER", 10000);
//максимальное время выполнения скрипта - для отладки
//if(!defined("SQL_TIME_MAX"))
//    define("SQL_TIME_MAX", 1);

if(!defined("RAZD_PARAMS"))
    define("RAZD_PARAMS", ";;;");
//ограничение на количество символов в результате поиска
if(!DEFINED("CHAR_ON_PAGE"))
    DEFINE("CHAR_ON_PAGE", 400);

//количество пролистовочных страниц, показываемых за один раз
if(!DEFINED("PAGES_ON_PAGE"))
    DEFINE("PAGES_ON_PAGE", 9);

if(DBASE=="ORACLE")//для того, чтобы при поиске даты меньше какой-то срабатывала включая
   DEFINE("LOWER_DATE_CONST", 86400);
if(!DEFINED("ERROR404"))
	DEFINE("ERROR404", "/");
//ссылки для картинок
DEFINE("IMAGE_LINK", "/images/image.html");
DEFINE("FORUM_IN_SITE", 1);
DEFINE("DEF_PATH", "/index.html");
include_once(PATH_INC_HOST."/const.php");
//навигация
if(!DEFINED("NAVIGATION"))
	DEFINE("NAVIGATION", "NAVIGATION");
?>
<?
//количество элементов в списке по умолчанию
if(!DEFINED("DEF_ITEMS_ON_PAGE"))
    DEFINE("DEF_ITEMS_ON_PAGE", 10);

//количество результатов поиска на странице
if(!DEFINED("SEARCH_RES_ITEMS"))
    DEFINE("SEARCH_RES_ITEMS", 10);

//первая страница по умолчанию
if(!DEFINED("FIRST_PAGE"))
    DEFINE("FIRST_PAGE", "/index.html");

if(!defined("ADMIN_TITLE"))
    define("ADMIN_TITLE", "МПР");

include_once(PATH_INC_HOST."/const_main.php");
?>
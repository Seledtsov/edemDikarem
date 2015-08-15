<?

if(!defined("NO_CONTROL_REALIZE"))
    define("NO_CONTROL_REALIZE", " онтрол не реализован дл€ данного варианта");
//ћаксимальный пересчитываемый рейтинг по умолчанию

if(!defined("MAX_RATING"))
    define("MAX_RATING", 10);
//ѕуть к стил€м внешнего сайта дл€ подт€гивани€ в html-редактор
if(!defined("HTML_EDITOR_CSS"))
    define("HTML_EDITOR_CSS", "/css_site/mk.css");
//ќписание стил€ special дл€ html-редактора
if(!defined("HTML_EDITOR_SPECIAL"))
    define("HTML_EDITOR_SPECIAL", "font-size: 16px; color: #999999; font-weight: bold; padding-right: 10px;");
//ќписание стил€ дл€ body дл€ html-редактора
if(!defined("HTML_EDITOR_BODY"))
    define("HTML_EDITOR_BODY", "FONT-SIZE: 12px; FONT-FAMILY: Verdana,Arial,Times; COLOR:#000000;");
//предел длины дл€ отображени€ в поле в форме
if(!defined("MAX_INPUT"))
    DEFINE("MAX_INPUT", 80);
?>
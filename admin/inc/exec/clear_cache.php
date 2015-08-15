<?
//файл сброса кеша 
define("NO_SES", 1);
DEFINE("NO_AUTH", 1);
DEFINE("LOW_CONNECT", 1);//использовать пользователя со сниженным приоритетом

include(getenv("g_INC")."conf.php");
echo"g_INC=".getenv("g_INC")."\r\n";
ignore_user_abort(true);
$MAX_LIMIT_TIME=86400;
set_time_limit($MAX_LIMIT_TIME);
echo"<pre>";
//$argv = $_SERVER["argv"];
echo"\r\n\r\nSTART $SCRIPT_NAME - ".date("d-m-Y H:i:s")."\r\n";
$start_time=mktime();
include(PATH_INC."inc.php");
db_disconnect($conn);
$sleep_count=1;
while($MAX_LIMIT_TIME>(mktime()-$start_time + INDEXER_WAIT_MINUT*5))
	{
	$conn=db_connect(DB, DEFINED("DB_LOW_USER")?DB_LOW_USER:DB_USER, DB_PASSWD, DB_HOST);
	if (function_exists("low_user"))
			low_user();
	//считывание очереди
	$sel_line="SELECT id, table_id, rel_id, date_main"
					." FROM cham_cache_line"
					." WHERE date_main<(".db_sysdate()."-".db_oper_min(CLEAR_CACHE_MINUT).") ORDER BY date_main ASC";
	$sel_line=db_limit($sel_line, 0, 20);
	//echo"sel_line=$sel_line\r\n";
	$res_line=db_getArray($conn, $sel_line);
	if(count($res_line)>0)
		{
		foreach($res_line as $k=>$v)
			{
			db_begin($conn);
			$clear=ClearCache_real($conn, $v['TABLE_ID'], $v['REL_ID']);
			if($clear)
				{
				$del_q="DELETE FROM cham_cache_line WHERE id=".$v['ID'];
				$del=db_query($conn, $del_q);
				}
			db_commit($conn);
			echo"table_id=".$v['TABLE_ID'].", rel_id=".$v['REL_ID'].", clear=$clear, del=$del\r\n";
			}
		$sleep_count=1;
		db_disconnect($conn);
		}
	else
		{
		db_disconnect($conn);
		echo"sleep - ".date("d-m-Y H:i:s")."(".INDEXER_WAIT_MINUT*$sleep_count.")\r\n";
		sleep(INDEXER_WAIT_MINUT*60*$sleep_count);
		if($sleep_count<3)
			$sleep_count++;
		}
	if(eregi("loc.", $SERVER_NAME))
		{
		echo"server_name=$SERVER_NAME<br>";
		exit();
		}
	}
echo"\r\nEND ".g_URL." - ".date("d-m-Y H:i:s")."\r\n\r\n\r\n";
?>
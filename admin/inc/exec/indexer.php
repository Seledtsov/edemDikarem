<?
//файл индексации - считывание из очереди
define("NO_SES", 1);
DEFINE("NO_AUTH", 1);
DEFINE("LOW_CONNECT", 1);//использовать пользователя со сниженным приоритетом

include(getenv("g_INC")."conf.php");
ignore_user_abort(true);
$MAX_LIMIT_TIME=86300;
set_time_limit($MAX_LIMIT_TIME);
echo"g_INC=".getenv("g_INC")."\r\n";
echo"<pre>";
//$argv = $_SERVER["argv"];

echo"\r\n\r\nSTART $SCRIPT_NAME - ".date("d-m-Y H:i:s")."\r\n";
echo"lang=".lang.", host=".HOST.", DB=".DB."\r\n";
$start_time=mktime();
//путь и имя файла логов без расширения
$log_file=LOG_DIR.HOST."_index_proc_".lang.".".date("w");
unlink($log_file.".log");
unlink($log_file.".err");
include(PATH_INC."inc.php");

echo __LINE__." ".date("H:i:s")."Deleting start\r\n";
//удаляем индексацию удаленных записей
$sel_tab="SELECT DISTINCT i.ref_columns_id, er.table_id, CASE WHEN section_id IS NULL THEN 0 ELSE 1 END AS section_flag"
					." FROM ti_search_index i, ti_entity_ref er"
					." WHERE  i.ref_columns_id=er.id ";
//echo"sel_tab=$sel_tab\r\n";
$res_tab=db_getArray($conn, $sel_tab);
foreach($res_tab as $k_d=>$v_d)
	{
	$sel_name="SELECT name FROM ti_tables WHERE id=".$v_d['TABLE_ID'];
	$res_name=db_getArray($conn, $sel_name, 2);
	if($v_d['SECTION_FLAG'])
		{
		$del_q="DELETE FROM ti_search_index "
				." WHERE ref_columns_id=".$v_d['REF_COLUMNS_ID']
					." AND NOT EXISTS(SELECT id FROM ".$res_name['NAME']." t WHERE t.id=section_id)";
		}
	else
		{
		$del_q="DELETE FROM ti_search_index "
				." WHERE ref_columns_id=".$v_d['REF_COLUMNS_ID']
					." AND NOT EXISTS(SELECT id FROM ".$res_name['NAME']." t WHERE t.id=rel_id)";
		}
	//echo  __LINE__." ".date("H:i:s")."del_q=$del_q\r\n";
	$del=db_query($conn, $del_q);
	//echo"del_q=$del_q, $del\r\n";
	}
echo __LINE__." ".date("H:i:s")."Deleting finish\r\n";
//exit();
//========================
//индексация
db_disconnect($conn);
$conn="";
echo $MAX_LIMIT_TIME.'>'.(mktime()-$start_time + 1800);
while($MAX_LIMIT_TIME>(mktime()-$start_time + 1800))
	{
	if(!$conn)
		{
		$conn=db_connect(DB, DEFINED("DB_LOW_USER")?DB_LOW_USER:DB_USER, DB_PASSWD, DB_HOST);
		db_begin($conn);
		}
      //ИНДЕКСАЦИЯ СУЩНОСТЕЙ
      $sel_ind_q="select id, entity_id, table_id, rel_id, main_rel_id, rating, date_main
                   from ".TABLE_PRE."indexer_wait
                   where status=0 and date_main<(".db_sysdate()."-".db_oper_min(INDEXER_WAIT_MINUT).")
                   order by rating DESC, date_main ASC";
      $sel_ind_q=db_limit($sel_ind_q, 0, 50);
      echo __LINE__."sel_ind_q=$sel_ind_q\r\n";
      $res_ind=db_getArray($conn, $sel_ind_q,1);
	//print_r($res_ind);
      $id_list="";
      foreach($res_ind as $k=>$v)
        {
        echo"$k=".$v['ID']."\r\n";
        $id_list.=($id_list?", ":"").$v['ID'];
        }
      if(count($res_ind))
         {//print_r($res_ind);
         foreach($res_ind as $k=>$v)
                 {

				if($v['RATING']>-100)// || (day("w")=6 && $MAX_LIMIT_TIME/2<(mktime()-$start_time)))
					{
					if($v['RATING']<=0)
						sleep(60);

					$start_time_one=mktime();
					if($v['TABLE_ID'] || $v['ENTITY_ID'])
						{
						echo"entity_id=".$v['ENTITY_ID']." table_id=".$v['TABLE_ID']." id=".$v['REL_ID']."\r\n";
 						$res_exec=our_exec(PATH_INC."exec/index_proc.php", (DEFINED("NICE_INDEXER")?NICE_INDEXER." ":"").PHP_STR_PATH, $log_file.".log", $log_file.".err", "entity_id=".$v['ENTITY_ID']." table_id=".$v['TABLE_ID']." id=".$v['REL_ID']." inc=".PATH_INC. " host=".HOST." lang=".lang);
						echo"res_exec=".$res_exec." - time=".(mktime()-$start_time_one)."\r\n";
						if($res_exec)
							{
							$del_q="delete from ".TABLE_PRE."indexer_wait where entity_id".($v['ENTITY_ID']?"=".$v['ENTITY_ID']:" IS NULL")." and  rel_id=".$v['REL_ID']." and table_id".($v['TABLE_ID']?"=".$v['TABLE_ID']:" IS NULL")." and main_rel_id".($v['MAIN_REL_ID']?"=".$v['MAIN_REL_ID']:" IS NULL");
							$del=db_query($conn, $del_q);
							}
						else
							{
							echo"ERROR (".__LINE__.")!\r\nconn=$conn\r\n";
							$del=db_update($conn, TABLE_PRE."indexer_wait", array(DATE_MAIN, "rating"), array(db_date(mktime()+60*60*24), -1), array("date", "int"), "id=".$v['ID']);
							sleep(10);
							}
						echo"del=$del\r\n";
						}
					db_commit($conn);
					/*if(date("G")>8 && date("G")<21)
						sleep(10);*/
					//exit;
					}//if($v['RATING']>-100 || day("w")=6)
				else
					{
					echo __LINE__."SLEEP\r\n";
					sleep(INDEXER_WAIT_MINUT*60);
					}
                }
         }//КОНЕЦ УСЛОВИЯ, ЧТО ЕСТЬ СУЩНОСТИ НА ИНДЕКСАЦИЮ
     elseif(!eregi("loc.", $SERVER_NAME))//if(!count($res_ind))//если нечего индексировать
		{
		db_disconnect($conn);
		$conn="";
		echo __LINE__."SLEEP\r\n";
        sleep(INDEXER_WAIT_MINUT*60);
		}
	if(eregi("loc.", $SERVER_NAME))
		{
		echo"server_name=$SERVER_NAME<br>";
		break;
		}
     }//while($MAX_LIMIT_TIME>(mktime()-$start_time + 1200))

//Ставим в очередь на индексацию те записи, что, по идее, должны быть проиндексированы, но не индексировались
$conn=db_connect(DB, DEFINED("DB_LOW_USER")?DB_LOW_USER:DB_USER, DB_PASSWD, DB_HOST);
echo __LINE__." ".date("H:i:s")." - line start\r\n";
$sel_tab="SELECT DISTINCT et.entity_id, t.id, t.name"
			." FROM ti_layout l, ti_layout_types lt,  ti_entity_table et, ti_tables t"
			." WHERE l.layout_type_id=lt.id AND lt.code_name='indexer' AND l.entity_id=et.entity_id"
					." AND et.main=1 AND et.table_id=t.id";
echo"sel_tab=$sel_tab<br>";
$res_tab=db_getArray($conn, $sel_tab);
//print_r($res_tab);
foreach($res_tab as $v)
	{
	$sel_id="SELECT id FROM ".$v['NAME']." t"
				." WHERE NOT EXISTS(SELECT id FROM ti_search_index si"
									." WHERE si.entity_id=".$v['ENTITY_ID']." AND si.parent_id IS NULL"
											." AND si.rel_id=t.id)".
						" AND NOT EXISTS(SELECT id FROM ti_indexer_wait_bad"
										." WHERE table_id=".$v['ID']." AND rel_id=t.id)";
	//echo"sel_id=$sel_id\r\n";
	$res_id=db_getArray($conn, $sel_id);

	foreach($res_id as $v2)
		{
		echo __LINE__." Add -". $v2['ID'].", ". $v['ID']."\r\n";
		indexer_wait($conn, "", $v2['ID'], $v['ID'], BASE_NULL, 0);
		db_insert($conn, "ti_indexer_wait_bad", array("table_id", "rel_id"), array($v['ID'], $v2['ID']), array("int", "int"));
		if($MAX_LIMIT_TIME<(mktime()-$start_time + 600))
			{
			echo"TIME LIMIT ".__LINE__."<br>";
			break;
			}
		}
	if($MAX_LIMIT_TIME<(mktime()-$start_time + 600))
		{
		echo"TIME LIMIT ".__LINE__."<br>";
		break;
		}
	}
echo __LINE__." ".date("H:i:s")." - line finish\r\n";
//$del_q="DELETE FROM ti_indexer_wait_bad WHERE date_main<(".db_sysdate()."-".db_oper_min(24*60*30).")";
//$del=db_query($conn, $del_q);

echo"\r\nEND ".g_URL." - ".date("d-m-Y H:i:s")."\r\n\r\n\r\n";
?>
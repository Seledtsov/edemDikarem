<?
//файл индексации
define("NO_SES", 1);
define("INDEXER_SCRIPT", 1);
DEFINE("NO_AUTH", 1);
DEFINE("LOW_CONNECT", 1);//использовать пользователя со сниженным приоритетом
if(!getenv("g_INC"))
    {
    $argv = $_SERVER["argv"];
    foreach($argv as $k_av=> $v_av)
                {
                $argv_ar=split("=", $v_av);
                $argv_name=$argv_ar[0];
                $$argv_name= trim($argv_ar[1]);
                }
    $g_INC=$inc;
    }
else
    $g_INC=getenv("g_INC");
include($g_INC."conf.php");
echo"DB_LOW_USER=".DB_LOW_USER."\r\n";
ignore_user_abort(true);
set_time_limit(36000);

ini_set('display_errors', 1);
ini_set('error_reporting', 7);

$argv = $_SERVER["argv"];
echo"\r\n\r\nSTART $SCRIPT_NAME - ".date("d-m-Y H:i:s")."\r\n\r\n";
echo"lang=".lang.", host=".HOST.", DB=".DB."\r\n";
$start_time=mktime();

include(PATH_INC."inc.php");
include_once(PATH_INC."func/admin.php");
include_once(PATH_INC."func/func_layout_xml.php");
include_once(PATH_INC."func/func_indexer".DBASE_ATTR.".php");


IF(!defined("PUBLISH_INDEX"))
	define("PUBLISH_INDEX", PUBLISH_LIST);
IF(!defined("PUBLISH_INDEX_PART"))
	define("PUBLISH_INDEX_PART", PUBLISH_INDEX);
//echo"PUBLISH_INDEX=".PUBLISH_INDEX."\r\n";

$search_publish_ar=split(",", PUBLISH_INDEX);
//print_r($search_publish_ar);
foreach($search_publish_ar as $k_p=>$v_p)
        {
		//echo"$k_p=>$v_p\r\n";
        $PUBLISH_INDEX[$v_p]=$v_p;
        }
$search_publish_part_ar=split(",", PUBLISH_INDEX_PART);
//print_r($search_publish_part_ar);
foreach($search_publish_part_ar as $k_p=>$v_p)
        {
		echo"$k_p=>$v_p\r\n";
        $PUBLISH_INDEX_PART[$v_p]=$v_p;
        }
//print_r($PUBLISH_INDEX);
//вырезаем лишние знаки перед индексацией
function prep_text($text)
         {
         $text=ereg_replace("\r\n", " ", $text);
         $text=eregi_replace("<PRE>", "", $text);
         $text=eregi_replace("</PRE>", "", $text);
         $text=eregi_replace("<P>", "\r\n", $text);
         $text=eregi_replace("<br>", "\r\n", $text);
         $text=strip_tags($text);
         $text=ereg_replace("&nbsp;", " ", $text);
         $text=ereg_replace(" {2,}", " ", $text);
         $text=ereg_replace("\r\n{1,}", " ", $text);
         $text=ereg_replace("\r{1,}", " ", $text);
         $text=ereg_replace("\n{1,}", " ", $text);
         $text=ereg_replace("^[[:space:]]*", "", $text);
         $text=ereg_replace("[[:space:]]*$", "", $text);
         $text=ereg_replace("  {2,}", " ", $text);
         $text=ereg_replace("%", "&#037;", $text);
         return $text;
         }
//=======================================================================
//вставка данных в search_index
function ins_search_index($conn, $ret_ar, $entity_id, $rel_id, $ar, $level=1, $parent_id="")
	{
	echo"*************************************************\r\n";
	extract($ar);
	//print_r($ret_ar);
	//print_r($tab_cols_name);
	//echo"date_main=$date_main\r\n";
	foreach($ret_ar as $k=>$v)
		{
		$ref_colimns_id=$k;
		//echo"k=$k\r\n";
		if(is_array($v))
		{
		foreach($v as $k1=>$v1)
			{
			foreach($v1 as $k2=>$v2)
				{
				//echo"k2=$k2 - ".$tab_cols_name[$k2]."\r\n";
				if(is_int($k2))
					{
					if($tab_cols_name[$k2]=='ID' || $tab_cols_name[$k2]==PUBLISH || $tab_cols_name[$k2]==DATE_MAIN)
						$text="";
					else
						$text=prep_text($v2);

					//echo"v2=$v2\r\n";
					if(trim($text) || $tab_cols_name[$k2]=='ID')
						{
						$ins_res=db_insert($conn, TABLE_PRE."search_index",
							array("entity_id", "column_id", "date_main", "date_index", PUBLISH, "full_text", "upper_full_text", "len", "section_id", "rating", "rel_id", "parent_id", "ref_columns_id"),
							array($entity_id, $k2, $date_main, db_sysdate(), $publish, $text, strtoupper($text), strlen($text), ($level==1?BASE_NULL:$v1['ID']), $rating_ar[$k][$k2], $rel_id, $parent_id, $k),
							array("int", "int", "date", "date", "int", "text", "text", "int", "int", "int", "int", "int", "int"), array("show_ins"=>1));
						pre_indexer($conn, $ins_res);

						if($tab_cols_name[$k2]=='ID' && is_array($v1['subs']) )
							{
							//print_r($v1['subs']);
							ins_search_index($conn, $v1['subs'], $entity_id, $rel_id, $ar, $level+1, $ins_res);
							}
						}
					}
				}
			}
		}
		}
	}
//конец ins_search_index

//================================================================================

function sel_ins_index($conn, $sel_ind_q, $ar)
{
         extract($ar);
         echo"sel_ind_q=$sel_ind_q\r\n";
		 global $publish;
         /*
         echo"table=$table, ".count($table)."\r\n";
         foreach($table as $k=>$v)
               {
               echo"table[$k]=>$v\r\n";
               foreach($v as $k1=>$v1)
                       {
                       echo"table[$k][$k1]=>$v1\r\n";
                       }
               }
         */
        global $PUBLISH_INDEX, $PUBLISH_INDEX_PART;
		//print_r($PUBLISH_INDEX);
        $res_ind_ar=array();
        $res_ind_ar=db_getArray($conn, $sel_ind_q);
        foreach($res_ind_ar as $k_ind=>$res_ind)
                {
                if(!$PUBLISH_INDEX[$res_ind[PUBLISH]] && $table['MAIN'])
                    {
                    echo"No publish=".$res_ind[PUBLISH]."\r\n";
                    //exit();
                    }
                elseif($table['MAIN'])
                       {
					   //если надо учитывать привязку к навигации
						if(DEFINED("NAV_INDEX"))
						   {
							$publish=publish_state_nav($res_ind[PUBLISH], $res_publish_nav);
						   }
						else
						   {
							$publish=$res_ind[PUBLISH];
						   }
                       echo"MAIN publish=$publish\r\n";
                       $ret['publish']=$publish;
                       $date_main=date_format_ar($res_ind[DATE_MAIN], "", "U");
					   //echo"DM=".$res_ind[DATE_MAIN]."\r\n";
                       $ret['date_main']=$date_main;
                       }
				elseif(isset($res_ind[PUBLISH]) && !$PUBLISH_INDEX_PART[$res_ind[PUBLISH]])//зависимая таблиа и есть стаус публикации - но не опубликована
					{
					echo"PART PUBLISH-".$res_ind[PUBLISH]."\r\n";
					return 0;
					}
                //echo"publish=$publish\r\n";
                //if(!$res_ind[PUBLISH] || $PUBLISH_INDEX[$res_ind[PUBLISH]])
                //{
                foreach($res_ind as $k1=>$v1)
                        {
                        //echo"$k1=$v1\r\n";
                        if(strtoupper($k1)!="ID"  && $k1!=PUBLISH && $k1!=DATE_MAIN)
                           {
                           //echo"$k1=$v1\r\n";
                           if(!is_numeric($k1))
                               {
                               if($v[$k1]['REF_ID'] && $v1 && $v1==intval($v1))
                                  {
                                  $sel_ref_col_q="select t.name from ".TABLE_PRE."tables t, ".TABLE_PRE."columns c where c.table_id=t.table_id and c.column_id=".$v[$k1]['REF_ID'];
                                  echo"sel_ref_col_q=$sel_ref_col_q\r\n";
                                  $res_ref_col=db_getArray($conn, $sel_ref_col_q, 2);
                                  $sel_ref_text="select name from ".$res_ref_col['NAME']." where id=$v1";
                                  echo"sel_ref_text=$sel_ref_text\r\n";
                                  $res_ref_text=db_getArray($conn, $sel_ref_text, 2);
                                  $section_id=$v1;
                                  $v1=$res_ref_text['NAME'];
                                  }
                               elseif(!$v['MAIN'])
                                       {
                                       $section_id=$res_ind['ID'];
                                       }
                               else
                                   {
                                   $section_id=0;
                                   }
                               //echo"$k1=$v1\r\n";
                               $v1=prep_text($v1);
                               //echo"\r\nv1=$v1\r\n\r\n";
                               if($v1)
                                  {
                                  //echo"\r\n 2 v1=$v1\r\n\r\n";
								  //echo"date_main=$date_main\r\n";
                                  $ind_id=db_insert($conn, TABLE_PRE."search_index",
                                          array("ID", "entity_id", "column_id", "rel_id", "date_main", "date_index", PUBLISH, "full_text", "len", "section_id", "rating"),
                                          array("", $entity_id, $table[$k1]['ID'], $id, $date_main, db_sysdate(), ($publish?$publish:BASE_NULL), $v1, strlen($v1), $section_id, $table[$k1]['RATING']),
                                          array('ID', 'int', 'int', 'int', 'date', 'date', 'int', 'clob', 'int', 'int', 'int'), array("show_ins"=>0));
                                  echo"ind_id=$ind_id\r\n";
                                  $pre_indexer=pre_indexer($conn, $ind_id);
                                  echo"pre_indexer($conn, $ind_id)=$pre_indexer\r\n";
                                  $ind_ar[]=$ind_id;
                                  }
                               }
                           }//конец условия, что не ID
                        elseif($k1=="ID")
                                {
                                $ret['id_ar'][$v1]=$v1;
                                }
                        }
                //}
                }//конц обрабтки конкретной таблицы
return $ret;
}
//================================================
//ИНДЕСАЦИЯ =================
//================================================
echo"<pre>";
echo"entity_od=$entity_id, table_id=$table_id, id=$id\r\n";
//получаем список индексируемых сущностей и отображений, где индексируется эта таблица
$sel_lay="SELECT DISTINCT l.id AS layout_id, l.name AS layout_name, l.entity_id, "
								." rc.tree_level, rc.id AS rc_id"
				." FROM ti_layout l, ti_layout_types lt, ti_layout_ref_columns lrc, ti_layout_columns lc, ti_columns c, ti_ref_columns rc"
				." WHERE l.layout_type_id=lt.id AND lt.code_name='indexer' AND lrc.layout_id=l.id AND lc.layout_rc_id=lrc.id AND lc.column_id=c.id AND c.table_id=$table_id AND lc.show_xml_flag IS NOT NULL AND lrc.ref_columns_id=rc.id".($entity_id?" AND l.entity_id=$entity_id":"");
echo"sel_lay=$sel_lay\r\n";
echo $res_lay=db_getArray($conn, $sel_lay);
if(!$res_lay || !count($res_lay))
	{
	echo"ERROR(".__LINE__.")!!! No layout for this entity\r\n";
	}else{
foreach($res_lay as $k=>$v)
	{
	db_begin($conn);
	//echo"layout_info\r\n";
	//print_r($v);
	$entity_id=$v['ENTITY_ID'];
	if($v['TREE_LEVEL']==1 && del_indexer($conn, $id, $v['ENTITY_ID']))//главная таблица
		{
		echo"FULL INDEXATION\r\n";
		//получаем xml записи
		$xml=layout_xml($conn, $v['LAYOUT_ID'], array("id"=>$id));
		if(DEFINED("XML_LOG_FILE"))
			{
			$struct_xml=struct_xml($xml);
			file_log (XML_LOG_FILE, $REQUEST_URI, "w");
			file_log (XML_LOG_FILE, $struct_xml);
			}
		//echo "line=".__LINE__."\r\n";
		//echo struct_xml($xml)."\r\n";
		//преобразуем в дерево
		$xmlstr=utf8_encode(convert_cyr_string(xml_header().$xml, "w", "i"));
		//echo "line=".__LINE__."\r\n";
		if(!$dom=domxml_open_mem($xmlstr))
			{
			echo"Error - domxml_open_mem\r\n";
			}
		//echo "line=".__LINE__." DOM_XML=".DOM_XML."\r\n";
		if(DOM_XML==1)
			{
			$elements=$dom->get_elements_by_tagname($v['LAYOUT_NAME']);
			//$element=$dom->get_elements_by_tagname("*");
			$element=$elements[0];
			//echo "line=".__LINE__."\r\n";
			}
		elseif(DOM_XML==2)
			{
			$elements=$dom->getElementsByTagName($v['LAYOUT_NAME']);
			//echo "line=".__LINE__."\r\n";
			foreach($elements as $element)
				{
				//echo "line=".__LINE__."\r\n";
				break;
				}
			}
		//echo "line=".__LINE__."\r\n";
		//проверяем, что есть результат, и он 1
		$cnt=ti_get_element_by_path($element, "RES_COUNT", array("cnt_flag"=>1, "dom_tmp"=>$dom));
		//echo "line=".__LINE__."\r\n";
		//echo "line=".__LINE__.", cnt=".print_r($cnt)."\r\n";
		if($cnt["count"]==1)
			{
			//echo "line=".__LINE__."\r\n";
			foreach($cnt["elements"] as $cnt_vals)
				{
				//$cnt_val=$cnt_vals->get_content();
				$cnt_val=get_content_xml($cnt_vals);
				//echo"cnt_val=$cnt_val<br>";
				/*
				if($cnt_val!=1)
					{
					db_commit($conn);
					echo "To many results for indexer (".__LINE__.")!\r\n\r\n";
					echo"END $SCRIPT_NAME - ".date("d-m-Y H:i:s")."\r\n";
					exit;
					}
					*/
				break;
				}
			}
		else
			{
			db_commit($conn);
			echo "NO results for indexer (".__LINE__.")!\r\n\r\n";
			echo"END $SCRIPT_NAME - ".date("d-m-Y H:i:s")."\r\n";
			exit;
			}

		//получаем рейтинги с путями
		$sel_rating="SELECT * FROM (SELECT rc.xml_name_path||'/'||upper(c.name) AS xml_path,"
							." upper(c.name) AS column_name, lc.column_id, lc.rating, ct.unit_name,"
							." rc.id_path, rc.tree_level, rc.parent_id, lrc.ref_columns_id AS rc_id"
							.", ".db_case("upper(c.name)", array("ID"=>1), 0)." AS id_flag"
					." FROM ti_layout_ref_columns lrc, ti_layout_columns lc, ti_columns c,"
								." ti_column_types ct, ti_ref_columns rc"
					." WHERE lrc.id=lc.layout_rc_id AND lc.column_id=c.id AND lrc.ref_columns_id=rc.id"
							." AND c.column_type_id=ct.id AND (lc.rating IS NOT NULL OR lc.show_xml_flag=1)"
							." AND lrc.layout_id=".$v['LAYOUT_ID']
					.") f ORDER BY id_path ASC, id_flag DESC";
		//echo __LINE__." sel_rating=$sel_rating\r\n";
		$res_rating=db_getArray($conn, $sel_rating);
		//print_r($res_rating);
		//echo __LINE__." count_rating=".count($res_rating)."\r\n";
		//определяем дату материала и статус публикации
		if(!is_array($res_rating) || count($res_rating)==0)//нет рейтинга для индексации
			{
			//db_commit($conn);
			echo __LINE__." No ratings\r\n\r\n";
			//echo"END $SCRIPT_NAME - ".date("d-m-Y H:i:s")."\r\n";
			//exit;
			break;
			}
		foreach($res_rating as $k_r=>$v_r)
			{
			$path_find=ereg_replace("^/", "", $v_r['XML_PATH']);
			$res_rating[$k_r]['XML_PATH_FIND']=$path_find;
			if($v_r['UNIT_NAME']=="date" && !$date_main)
				{
				//echo __LINE__."date_path=".$v_r['XML_PATH']."\r\n";
				$el_date=ti_get_element_by_path($element, $path_find, array("dom_tmp"=>$dom));
				//echo"el_date(".$path_find.")=$el_date\r\n";
				$year=ti_get_element_by_path($el_date, "./YEAR", array("dom_tmp"=>$dom));
				$month=ti_get_element_by_path($el_date, "./MONTH", array("dom_tmp"=>$dom));
				$day=ti_get_element_by_path($el_date, "./DAY", array("dom_tmp"=>$dom));
				$hour=ti_get_element_by_path($el_date, "./HOUR", array("dom_tmp"=>$dom));
				$minutes=ti_get_element_by_path($el_date, "./MINUTES", array("dom_tmp"=>$dom));
				$sec=ti_get_element_by_path($el_date, "./SECONDS", array("dom_tmp"=>$dom));
				//$date_main=mktime($hour->get_content(), $minutes->get_content(), $sec->get_content(), $month->get_content(), $day->get_content(), $year->get_content());
				//$date_main= $year->get_content().'-'. $month->get_content().'-'.$day->get_content().' '.$hour->get_content().':'.$minutes->get_content().':'.$sec->get_content();
				$date_main=get_content_xml($year).'-'.get_content_xml($month).'-' .get_content_xml($day).' ' .get_content_xml($hour).':'.get_content_xml($minutes).':' .get_content_xml($sec);
				//echo __LINE__." date_main=$date_main\r\n";
				}
			elseif($v_r['TREE_LEVEL']==1 && $v_r['COLUMN_NAME']==PUBLISH)
				{
				//echo __LINE__." publish - ".$v_r['XML_PATH']."\r\n";
				$publish_el=ti_get_element_by_path($element, $path_find, array("dom_tmp"=>$dom));
				//echo __LINE__." publish\r\n";
				//print_r($publish_el);
				//echo __LINE__." Found $publish_el->nodeValue\r\n";
				//$publish=$publish_el->get_content();
				$publish=get_content_xml($publish_el);

				//echo __LINE__." publish=$publish\r\n";
				}
			}
		//echo __LINE__."date=".date("d-m-Y H:i:s", $date_main)."\r\n";

		//обходим дерево xml c занесением в индексацию
		foreach($res_rating as $k_r=>$v_r)
			{
			/*
			if($v_r['TREE_LEVEL']!=1)//если это не первый уровень - определяем id записи
				{
				//$id_el_path=ereg_replace("/".$v_r['COLUMN_NAME']."$", "/ID", $v_r['XML_PATH_FIND']);
				$id_el=ti_get_element_by_path($element, "./ID");
				$id_tmp=$id_el->get_content();
				echo"id_tmp=$id_tmp\r\n";
				}
			*/
			$el_path=ereg_replace("/".$v_r['COLUMN_NAME']."$", "", $v_r['XML_PATH_FIND']);
			//echo __LINE__."path=".$el_path.", col=".$v_r['COLUMN_NAME']."\r\n";
			$el_list=ti_get_element_by_path($element, $el_path, array("cnt_flag"=>1, "dom_tmp"=>$dom));
			$level=$v_r['TREE_LEVEL'];
			$text="";
			//echo"el_list_count=".$el_list['count']."\r\n";
			if($el_list['count']>0)
				{
				foreach($el_list['elements'] as $k_el=>$v_el)
					{
					$text="";

					//else
					//	{
						if($level!=1)//если это не первый уровень - определяем id записи и id родительской записи
							{
							$id_el=ti_get_element_by_path($v_el, "./ID", array("dom_tmp"=>$dom));
							$id_tmp=get_content_xml($id_el);
							$id_pel=ti_get_element_by_path($v_el, "../ID", array("dom_tmp"=>$dom));
							if(!is_object($id_el))
								$id_el=ti_get_element_by_path($v_el, "../../ID", array("dom_tmp"=>$dom));
							if(is_object($id_pel))
								{
								//echo"parent\r\n";
								$id_tmp_parent=get_content_xml($id_pel);
								$parent_id=$par_id_ar[$v_r['PARENT_ID']][$id_tmp_parent];
								}
							//echo"id_tmp=$id_tmp, id_tmp_parent=$id_tmp_parent\r\n";
							}
						else
							{
							$id_tmp="";
							$parent_id="";
							}
					if($v_r['ID_FLAG'])
						{
						$text="";
						}
					else
						{
						$tmp_el=ti_get_element_by_path($v_el, "./".$v_r['COLUMN_NAME'], array("dom_tmp"=>$dom));
						if(is_object($tmp_el))
							{
							$text=html_entity_decode(convert_cyr_string(utf8_decode(get_content_xml($tmp_el)), "i", "w"));
							$text=prep_text($text);
							}
						}
					if(($v_r['RATING'] && $text)|| $v_r['ID_FLAG'])
						{
						//echo"text=$text\r\n";
						echo"len=".strlen($text)."\r\n";
						if(strlen($text)>100000)
							{
							db_rollback($conn);
							indexer_wait($conn, BASE_NULL, $id, $table_id, BASE_NULL, -100);
							echo __LINE__." Text is too long (length=".strlen($text).")!\r\n\r\n";
							echo"END $SCRIPT_NAME - ".date("d-m-Y H:i:s")."\r\n";
							exit;
							}
						$ins_res=db_insert($conn, TABLE_PRE."search_index",
							array("entity_id", "column_id", "date_main", "date_index", PUBLISH, "full_text", "upper_full_text", "len", "section_id", "rating", "rel_id", "parent_id", "ref_columns_id"),
							array($entity_id, $v_r['COLUMN_ID'], $date_main?$date_main:BASE_NULL, db_sysdate(), $publish, $text, strtoupper($text), strlen($text), ($level==1?BASE_NULL:$id_tmp), $v_r['RATING'], $id, $parent_id, $v_r['RC_ID']),
							array("int", "int", "date", "date", "int", "text", "text", "int", "int", "int", "int", "int", "int"), array("show_ins"=>0));
						if($v_r['ID_FLAG'])
							{
							$par_id_ar[$v_r['RC_ID']][$id_tmp?$id_tmp:$id]=$ins_res;
							}
						else
							{
							//echo __LINE__. "text=\r\n=====================\r\n$text\r\n=====================\r\n";
							//echo __LINE__." Start pre_indexer - ".date("H:i:s")."\r\n";
							pre_indexer($conn, $ins_res);
							//echo __LINE__." End pre_indexer - ".date("H:i:s")."\r\n";
							}
						//print_r($par_id_ar);
						}
					}//foreach($el_list['elements'] as $k_el=>$v_el)
				}//if($el_list['count']>0)
			//$text=;
			//ins_search_index($conn, $ret_ar, $entity_id, $id, array("publish"=>$publish, "date_main"=>$date_main, "tab_cols"=>$tab_cols, "tab_cols_name"=>$tab_cols_name, "rating_ar"=>$rating_ar));
			}//foreach($res_rating as $k_r=>$v_r)
		echo __LINE__." Start index_operation - ".date("H:i:s")."\r\n";
        $index_operation=index_operation($conn, $id, $entity_id);
		echo __LINE__." Start full_rating - ".date("H:i:s")."\r\n";
		$full_rating=full_rating($conn, $id, $entity_id);
		echo __LINE__." ".date("H:i:s")." index_operation=$index_operation, full_rating=$full_rating\r\n";
		db_commit($conn);

		}
	elseif($v['TREE_LEVEL']>1)//если не главная таблица
		{
		//пока просто ставим в очередь более высокий уровень
		$sel_par="SELECT rc.dir, c.name AS column_name, t.name AS table_name, rc.parent_id"
					." FROM ti_ref_columns rc, ti_tables t, ti_columns c"
					." WHERE rc.id=".$v['RC_ID']." AND c.id=rc.ref_column_id AND t.id=c.table_id";
		//echo"sel_par=$sel_par\r\n";
		$res_par=db_getArray($conn, $sel_par, 2);
		//print_r($res_par);
		//получаем id таблицы родительского уровня
		$sel_tab_par_id="SELECT et.table_id FROM ti_ref_columns rc, ti_entity_table et"
							." WHERE rc.id=".$res_par['PARENT_ID']." AND et.id=rc.entity_table_id";
		$res_tab_par_id=db_getArray($conn, $sel_tab_par_id, 2);
		//echo"sel_tab_par_id=$sel_tab_par_id\r\n";

		if($res_par['DIR']==1)//связь типа предприятие->город
			{
			$sel_par_id="SELECT id FROM ".$res_par['TABLE_NAME']." WHERE ".$res_par['COLUMN_NAME']."=$id";
			$res_par_id=db_getArray($conn, $sel_par_id);
			//print_r($res_par_id);
			foreach($res_par_id as $k_pi=>$v_pi)
				{
				//echo"$k_pi=$v_pi\r\n";
				indexer_wait($conn, $v['ENTITY_ID'], $v_pi['ID'], $res_tab_par_id['TABLE_ID']);
				}
			}
		db_commit($conn);
		}
	}
	}
echo"\r\n\r\nEND $SCRIPT_NAME - ".date("d-m-Y H:i:s")." (executing time - ".(mktime()-$start_time)." sec)\r\n";
?>
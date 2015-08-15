<?
//функции для управления сущностями
//====================================================
//получение информации о содержимом сущности
//type=1 - только о таблицах, type=2 - таблицы и колонки
function object_info($conn, $id, $type=2)
         {
         $sel_tab_q="SELECT t.id as table_id, t.NAME, t.about, t.main, ".db_isnull()."(et.main, 0) as et_main
                     FROM ".TABLE_PRE."tables t, ".TABLE_PRE."entity_table et where et.table_id=t.id and et.entity_id=$id order by et.main DESC";
         //echo"sel_tab_q=$sel_tab_q<br>";
         $res_tab=db_getArray($conn, $sel_tab_q);
         if($type==2)
            {
            $i=0;
            foreach($res_tab as $k=>$v)
                 {
                 $sel_col_q="select id as column_id, name, about, ref_column_id
                             FROM ".TABLE_PRE."columns where table_id=".$v['TABLE_ID'];
                 //echo"sel_col_q=$sel_col_q<br>";
                 $res_tab['COLS'][$v['TABLE_ID']]=db_getArray($conn, $sel_col_q);
                 }
            }
         return $res_tab;
         }
//======================================
function get_object_tree($conn, $ar, $val_ar)
{
extract($val_ar);
print_r($tables);
//foreach($ar as $k=>$v)
//        {
        echo"\r\nget_object_tree - LEVEL=$level\r\n";
        echo"table- ".$ar['TABLE_ID']."-".$ar['TABLE_NAME']."\r\n";
        foreach($tables as $k=>$v)
                {
                if($ar['TABLE_ID']!=$k && !$v['MAIN'])
                    {
                    //echo"  2 - $k=".$v['TABLE_NAME']."(".$k.")\r\n";
                    $res_col_ref1=array();
                    $res_col_ref2=array();
                    $this_id=count($ref_ar);
                    //echo"ended_list[".$ar['TABLE_ID']."]=".$ended_list[$ar['TABLE_ID']]."\r\n";
                    if(!$ended_list[$ar['TABLE_ID']])
                        {
                        //получаем информацию о связи между таблицами - сначала смотрим ссылку на главную
                        $sel_col_ref1="select c.id, c.name from ".TABLE_PRE."columns c, ".TABLE_PRE."tables t
                                       where c.table_id=t.id and t.id=$k and
                              c.ref_column_id=(select id from ".TABLE_PRE."columns  where table_id=".$ar['TABLE_ID']." and upper(name)='ID')";
                        echo"sel_col_ref1=$sel_col_ref1\r\n<br>\r\n";
                        $res_col_ref1=db_getArray($conn, $sel_col_ref1, 2);

                        }
                    else
                        $res_col_ref1=array();
                    //echo"this_id=$this_id\r\n";
                    if(count($res_col_ref1))
                       {
                       //echo "REF1!\r\n";
                       $ref_ar[$this_id]['TABLE_ID']=$k;
                       $ref_ar[$this_id]['TABLE_NAME']=$v['TABLE_NAME'];
                       $ref_ar[$this_id]['SVIZ_TYPE']=1;
                       $ref_ar[$this_id]['SVIZ_COL_ID'][0]=$res_col_ref1['ID'];
                       $ref_ar[$this_id]['SVIZ_COL_NAME'][0]=$res_col_ref1['NAME'];
                       $sel_ind_q="select ".$v['COLUMN_LIST']." from ".$v['TABLE_NAME']." where ".$res_col_ref1['NAME']."=$id";
                       $flag_sviz=1;
                       //$this_id++;
                       }
                    else//смотрим на ссылки из главной таблицы
                          {
                          //echo"REF2!\r\n";
                          $id_col_list="";
                          $sel_col_ref2="select c.id, c.name from ".TABLE_PRE."columns c, ".TABLE_PRE."tables t
                                      where c.table_id=t.id and t.id=".$ar['TABLE_ID']." and
                                      c.ref_column_id=(select id from ".TABLE_PRE."columns  where table_id=$k and upper(name)='ID')";

                          $res_col_ref2=db_getArray($conn, $sel_col_ref2);
							echo"sel_col_ref2=$sel_col_ref2,   $res_col_ref2\r\n";
                          if(count($res_col_ref2))
                             {
                             //echo"count(res_col_ref2)=".count($res_col_ref2)."\r\n";
                             $ref_ar[$this_id]['TABLE_ID']=$k;
                             $ref_ar[$this_id]['TABLE_NAME']=$v['TABLE_NAME'];
                             $ref_ar[$this_id]['SVIZ_TYPE']=2;
                             $flag_sviz=1;
                             $ended_list[$k]=1;//означает, что нельзя дальше искать по тем, кто на нее ссылается (случай событие->фотка<-фоторепортаж)
                             //echo"ended_list[$k]=1\r\n";
                             foreach($res_col_ref2 as $k_ref=>$v_ref)
                                     {
                                     $sviz_id=count($ref_ar[$this_id]['SVIZ_COL_ID']);
                                     $ref_ar[$this_id]['SVIZ_COL_ID'][$sviz_id]=$v_ref['ID'];
                                     $ref_ar[$this_id]['SVIZ_COL_NAME'][$sviz_id]=$v_ref['NAME'];
                                     //echo"ref2=$k_ref=>$v_ref\r\n";
                                     $id_col_list.=($id_col_list?", ":"").$v_ref['NAME'];
                                     }
                             //$this_id++;
                             }
                          }
                    if($ref_ar[$this_id]['TABLE_ID'])
                       {
                       $for_object_tree=array("ended_list"=>$ended_list, "tables"=>$tables, "level"=>($level+1) );
                       $ret_object_tree=get_object_tree($conn, $ref_ar[$this_id], $for_object_tree);
                       $ref_ar[$this_id]['sub']=$ret_object_tree['ret_ref'];
                       }
                    }
                }//конец цикла по tables
        //}

        /*
        if(count($ref_ar))//&& !$level)
           {
           foreach($ref_ar as $k_ref=>$v_ref)
                   {
                   echo"5 k_ref=$k_ref=".$v_ref['TABLE_ID']."\r\n";
                   $for_object_tree=array("ended_list"=>$ended_list, "tables"=>$tables, "level"=>($level+1) );
                   $ret_object_tree=get_object_tree($conn, $v_ref, $for_object_tree);
                   }
           }
        */
        //$ret_ref_ar['sub']=$ref_ar;
        $ret['ended_list']=$ended_list;
        $ret['ret_ref']=$ref_ar;
        return $ret;
}
//конец get_object_tree
//================================================================================

function object_tree($conn, $res_rating, $ar=array())
         {
         if(is_array($ar) && count($ar) )
            extract($ar);
foreach($res_rating as $k=>$v)
        {
        //if(!$column_list[$v['NAME']])
        //    $column_list[$v['NAME']]="id";
        //$column_list[$v['NAME']].=",".$v['COL_NAME'];
        //$rating[$v['NAME']][$v['COL_NAME']]=$v['RATING'];
        //echo$v['NAME']." - ".$v['MAIN']."<br>";
        if(!$tables[$v['TABLE_ID']]['COLUMN_LIST'])
            $tables[$v['TABLE_ID']]['COLUMN_LIST']="ID";
        $tables[$v['TABLE_ID']]['MAIN']=$v['MAIN'];
        $tables[$v['TABLE_ID']]['COLUMN_LIST'].=",".$v['COL_NAME'];
        $tables[$v['TABLE_ID']]['TABLE_NAME']=$v['NAME'];
        $tables[$v['TABLE_ID']][$v['COL_NAME']]['RATING']=$v['RATING'];
        $tables[$v['TABLE_ID']][$v['COL_NAME']]['ID']=$v['COLUMN_ID'];
        $tables[$v['TABLE_ID']][$v['COL_NAME']]['REF_ID']=$v['REF_COLUMN_ID'];
        $tables[$v['TABLE_ID']]['COLS'][]=$v['COL_NAME'];
        if($v['ENTITY_NAME'])
           $ref_tab_ar[0]['ENTITY_NAME']=$v['ENTITY_NAME'];
        if($v['SEARCH_TEMPLATE_ID'])
           {
           $search_template=get_byId($conn, TABLE_PRE."search_templates", $v['SEARCH_TEMPLATE_ID'], "code_name");
           $ref_tab_ar[0]['SEARCH_TEMPLATE']=$search_template['CODE_NAME'];
           }
        }
$flag_sviz=1;
//выстраиваем иерархию для подзапросов в связанных таблицах
//for($i_t=0; $i_t<2; $i_t++)
//{
foreach($tables as $k=>$v)
        {
        //echo"-------------------------------------------\r\n";
        //echo"\r\n\r\nTABLES[$k]=".$v['TABLE_NAME']."\r\n";
        if($v['MAIN'])
           {
           //echo"main_table=".$v['TABLE_NAME']."<br>";
           $v['COLUMN_LIST'].=", ".PUBLISH.", ". db_date_char(DATE_MAIN)." AS ".DATE_MAIN;
           if($method=="search" && !$tables[$v['TABLE_ID']]['NAME']['ID'])
              $v['COLUMN_LIST']=", NAME";
           $tables[$k]['COLUMN_LIST']=$v['COLUMN_LIST'];
           $sel_ind_q="select ".$v['COLUMN_LIST']." from ".$v['TABLE_NAME']." where id=$id";
           $main_table_id=$k;
           $main_table=$v['TABLE_NAME'];
           $ref_tab_ar[0]['TABLE_ID']=$k;
           $ref_tab_ar[0]['TABLE_NAME']=$v['TABLE_NAME'];
           }
        }

        $for_object_tree=array("ended_list"=>$ended_list, "tables"=>$tables, "level"=>0);
        $ret_object_tree=get_object_tree($conn, $ref_tab_ar[0], $for_object_tree);
        $ref_tab_ar[0]['sub']=$ret_object_tree['ret_ref'];
        $ret['ref_tab_ar']=$ref_tab_ar;
        $ret['tables']=$tables;
        return $ret;
        }

//=======================================================================
//получаем данные по структуре - недоработанная функция
//=======================================================================
function object_sql($conn, $ref_ar, $ar)
	{
	extract($ar);
	if(!$id_par)
		$id_par=$id;
	echo"\r\nLEVEL=$level\r\n";
	//echo"publish=$publish\r\n";

	if(!$level)
		{
		$sel_q="select ".$tables[$ref_ar['TABLE_ID']]['COLUMN_LIST']." from ".$ref_ar['TABLE_NAME']." where id=$id";
		//echo"sel_q=$sel_q\r\n";
		$for_sel_index=array("id"=>$id, "table"=>$tables[$ref_ar['TABLE_ID']], "entity_id"=>$entity_id, "publish"=>$publish, "date_main"=>$date_main, "res_publish_nav"=>$res_publish_nav);
	
		//$ret_ins_index=sel_ins_index($conn, $sel_q, $for_sel_index);
		//extract($ret_ins_index);
		}
	else
		{
		echo"sviz\r\n";
		foreach($id_ar as $k_id=>$v_id)
			{
			if($ref_ar['SVIZ_TYPE']==1)//сслыка типа фоторепортажа на событие
				{
				$sel_q="select ".$tables[$ref_ar['TABLE_ID']]['COLUMN_LIST']." from ".$ref_ar['TABLE_NAME']." where ".$ref_ar['SVIZ_COL_NAME'][0]."=$v_id";
				echo"sel_q=$sel_q\r\n";
				$for_sel_index=array("id"=>$id, "table"=>$tables[$ref_ar['TABLE_ID']], "entity_id"=>$entity_id, "publish"=>$publish, "date_main"=>$date_main);
				//$ret_ins_index=sel_ins_index($conn, $sel_q, $for_sel_index);
				//extract($ret_ins_index);
				}
			else//ссылка типа события на картинку
				{
				foreach($ref_ar['SVIZ_COL_NAME'] as $k_sv=>$v_sv)
					{
					echo"$k_sv=>$v_sv\r\n";
					$col_val_list.=($col_val_list?", ":"").$v_sv;
					}
				echo"col_val_list=$col_val_list\r\n";
				$sel_prev="select $col_val_list from ".$tables[$table_prev_id]['TABLE_NAME']." where id=$v_id";
				echo"sel_prev=$sel_prev\r\n";
				$res_prev=db_getArray($conn, $sel_prev, 2);
				foreach($res_prev as $k_prev=>$v_prev)
					{
					if(eregi("[a-z]", $k_prev) && $v_prev)
						{
						$sel_q="select ".$tables[$ref_ar['TABLE_ID']]['COLUMN_LIST']." from ".$ref_ar['TABLE_NAME']." where id=".$v_prev;
						echo"sel_q=$sel_q\r\n";
						$for_sel_index=array("id"=>$id, "table"=>$tables[$ref_ar['TABLE_ID']], "entity_id"=>$entity_id, "publish"=>$publish, "date_main"=>$date_main);
						//$ret_ins_index=sel_ins_index($conn, $sel_q, $for_sel_index);
						//extract($ret_ins_index);
						}
					}
				}
			}
		}
	$for_object_tree=array("id"=>$id, "id_par"=>$id_par, "id_ar"=>$id_ar, "tables"=>$tables, "table_prev_id"=>$ref_ar['TABLE_ID'], "level"=>($level+1), "entity_id"=>$entity_id, "publish"=>$publish, "date_main"=>$date_main);
	foreach($ref_ar['sub'] as $k=>$v)
        {
        object_sql($conn, $v, $for_object_tree);
        }
	}
//конец object_sql
//================================================================================
//запрос структуры из ref_columns
function get_ref_columns_str($conn, $entity_id, $ar=array())
	{
	extract($ar);
	$sel_str_q="SELECT DISTINCT er1.id, ".($from=="search"?"er1.name, ":"")."er1.entity_id, er1.table_id, er1.tree_level, er1.id_path, er1.parent_table_id, er1.dir, er1.ref_column_id, t.name as table_name, t.main as table_main, c.name as ref_column_name FROM ".TABLE_PRE."columns c RIGHT JOIN ".TABLE_PRE."entity_ref er1 ON c.id=er1.ref_column_id, ".TABLE_PRE."tables t, ".TABLE_PRE."entity_ref er2  WHERE er2.entity_id=".$entity_id." AND t.id=er1.table_id AND er1.id_path LIKE(trim(er2.id_path)||'%') ORDER BY er1.id_path";
	//echo"sel_str_q=$sel_str_q\r\n";
	$res_str=db_getArray($conn, $sel_str_q);
	foreach($res_str as $k_s=>$v_s)
			{
			$tables[$v_s['TABLE_ID']]['TABLE_NAME']=$v_s['TABLE_NAME'];
			$tables[$v_s['TABLE_ID']]['TABLE_MAIN']=$v_s['TABLE_MAIN'];
			$tables_str.=($tables_str?", ":"").$v_s['TABLE_ID'];
			$str_ar[$v_s['ID']]=$v_s;
			if(!$v_s['PARENT_TABLE_ID'])//если главная таблица
				{
				$str_ar[$v_s['ID']]['sel_q']="SELECT [COLUMN_LIST] FROM ".$v_s['TABLE_NAME']." t WHERE id=:var";
				$str_ar[$v_s['ID']]['list_sel_q']="SELECT [COLUMN_LIST] FROM ".$v_s['TABLE_NAME']." t";
				}
			else
				{
				if($v_s['DIR']==1)//прямая связь
					{
					$str_ar[$v_s['ID']]['sel_q']="SELECT [COLUMN_LIST] FROM ".$v_s['TABLE_NAME']." t WHERE id=(SELECT ".$v_s['REF_COLUMN_NAME']." FROM ".$tables[$v_s['PARENT_TABLE_ID']]['TABLE_NAME']." WHERE id=:var)";
					}
				elseif($v_s['DIR']==2)//обратная связь
					{
					$str_ar[$v_s['ID']]['sel_q']="SELECT [COLUMN_LIST] FROM ".$v_s['TABLE_NAME']." t WHERE t.".$v_s['REF_COLUMN_NAME']."=:var";
					}
				}
			}//конец цикла по $res_str
	if($from=="search")
		{
		$sel_ent_info="SELECT name, search_template_id FROM ".TABLE_PRE."entities WHERE id=$entity_id";
		$ent_info=db_getArray($conn, $sel_ent_info, 2);
		if($ent_info['SEARCH_TEMPLATE_ID'])
			{
			$templ=get_byId($conn, TABLE_PRE."search_templates", $ent_info['SEARCH_TEMPLATE_ID'], 'code_name, layout_id');
			$ent_info['SEARCH_TEMPLATE']=$templ['CODE_NAME'];
			$ent_info['LAYOUT_ID']=$templ['LAYOUT_ID'];
			}
		
		$ret=array("str_ar"=>$str_ar, "ent_info"=>$ent_info);
		}
	else
		$ret=array("tables"=>$tables, "str_ar"=>$str_ar, "tables_str"=>$tables_str, "res_str"=>$res_str);
	return $ret;
	}
//====================================================
//проход по всем уровням записи - после запроса из ref_columns
function sel_tree($conn, $res_str, $str_ar, $rel_id, $level, $k_start, $ar=array())
			{
			extract($ar);
			/*
			col_num=1 - обозначает, что использовать не имена колонок, а их id
			*/
			//echo"col_num=$col_num\r\n";
			$ret_ar=array();
			if($level==1)
				{
				//echo"=============================================================\r\n";
				//print_r($tab_cols);
				//print_r($res_str);
				//echo"=============================================================\r\n";
				}
			//echo"level=$level, k_start=$k_start\r\n";
			foreach($res_str as $k_s=>$v_s)
				{
				if($k_s>=$k_start)
					{
					//echo"level=$level, ".$v_s['TREE_LEVEL'].", table=".$v_s['TABLE_NAME']."\r\n";
					if($level==$v_s['TREE_LEVEL'] && $str_ar[$v_s['ID']]['prep_sel'])
						{
						//db_BindByName($str_ar[$v_s['ID']]['prep_sel'], ':var', &$rel_id, -1);
						$bind_ar=array();
						if($bind_ar_nav && $level==1)
							$bind_ar=$bind_ar_nav;
						//echo"bind_ar 1<br>";
						//print_r($bind_ar);
						//echo"bind_ar_nav<br>";
						//print_r($bind_ar_nav);
						if($rel_id)
							{

							$bind_ar[]=db_for_bind("var", $rel_id);
							//echo"level=$level<br>";
							//echo"bind_ar<br>";
							//print_r($bind_ar);
							$res_rel=db_getArray($conn, $str_ar[$v_s['ID']]['prep_sel'], 1, array("parse"=>1, "bind_ar"=>$bind_ar));
							//$res_rel=db_getArray($conn, $str_ar[$v_s['ID']]['sel_q'], 1, array("bind_ar"=>$bind_ar));
							//print_r($res_rel);
							}
						elseif(!$rel_id && $level==1)
							{
							$res_rel=db_getArray($conn, $str_ar[$v_s['ID']]['list_sel_q'], 1, array("bind_ar"=>$bind_ar));
							}
						//$res_rel=db_getArray($conn, $str_ar[$v_s['ID']]['sel_q'], 1, array("bind_ar"=>$bind_ar));
						//print_r($res_rel);
						if($col_num)
							{
							foreach($res_rel as $k_rr=>$v_rr)
								{
								foreach($v_rr as $k_rr1=>$v_rr1)
									{
									if(!is_int($k_rr1))
										{
										echo"table_id=".$v_s['TABLE_ID'].", cols=$k_rr1\r\n";
										$res_rel1[$k_rr][$tab_cols[$v_s['TABLE_ID']][$k_rr1]]=$v_rr1;
										}
									}
								$res_rel1[$k_rr]['ID']=$res_rel[$k_rr]['ID'];

								if($res_rel[$k_rr][PUBLISH])
									$res_rel1[$k_rr][PUBLISH]=$res_rel[$k_rr][PUBLISH];
								if($level==1)
									{
									if($res_rel[$k_rr][DATE_MAIN])
										$res_rel1[$k_rr][DATE_MAIN]=$res_rel[$k_rr][DATE_MAIN];
									}
								}

							$res_rel=$res_rel1;
							}
							
						//echo"\r\n".$str_ar[$v_s['ID']]['sel_q']."-".$rel_id."\r\n";
						//if($level>0)
						//	print_r($res_rel);
						if($col_num==1)
							$index_res=$v_s['ID'];
						else
							$index_res=$v_s['TABLE_NAME'].($v_s['REF_COLUMN_NAME']?"__".$v_s['REF_COLUMN_NAME']:"");
						$ret_ar[$index_res]=$res_rel;
						if(is_array($res_rel))
							{
							foreach($res_rel as $k_r=>$v_r)
								{
								//echo"id=".$v_r['ID'].", $index_res\r\n";
								$sel_tree_ret=sel_tree($conn, $res_str, $str_ar, $v_r['ID'], $level+1, $k_s+1, $ar);
								//echo"sel_tree_ret\r\n";
								//print_r($sel_tree_ret);
								if(count($sel_tree_ret))
									{
									if($col_num)
										$ret_ar[$index_res][$k_r]['subs']=$sel_tree_ret;
									else
										$ret_ar[$index_res][$k_r]=array_merge($ret_ar[$index_res][$k_r], $sel_tree_ret);
									}
								}
							}
						}
					elseif($level<$v_s['TREE_LEVEL'] )
						return $ret_ar;
					}
				}
			return $ret_ar;			
			}
//====================================================
//связь с навигацией
function ref_navigation($conn, $tables, $tables_str, $where="", $columns_list="", $ar=array())
	{
	extract($ar);
	//from - откуда вызвано, например layout
	if(!$columns_list)
		$columns_list="n.id";
	$sel_nav_cols="SELECT name as nav_col_name, table_id FROM  ".TABLE_PRE."columns WHERE ref_column_id=".NAV_ID." AND table_id IN(".$tables_str.")";
	//echo"sel_nav_cols=$sel_nav_cols\r\n<br>\r\n";
	$res_nav_cols=db_getArray($conn, $sel_nav_cols, 2);
	if(count($res_nav_cols))//есть ссылка на навигацию
			{
			if(!$tables[$res_nav_cols['TABLE_ID']]['TABLE_MAIN'])//связь через кросс-таблицу
				{
				$sel_nav_sviz="SELECT id, name FROM ".TABLE_PRE."columns WHERE table_id=".$res_nav_cols['TABLE_ID']." AND ref_column_id IS NOT NULL AND ref_column_id!=".NAV_ID;
				//echo"sel_nav_sviz=$sel_nav_sviz\r\n";
				$res_nav_sviz=db_getArray($conn, $sel_nav_sviz, 2);
				$sel_nav="SELECT ".$columns_list." FROM ".NAV." n, ".$tables[$res_nav_cols['TABLE_ID']]['TABLE_NAME']." s WHERE n.id=s.".$res_nav_cols['NAV_COL_NAME']." and s.".$res_nav_sviz['NAME']."=:var".$where;
				//echo"sel_nav=$sel_nav\r\n";
				if($from=="layout")
					{
					$parse_nav=$sel_nav;
					}
				else
					{
					$parse_nav=db_parse($conn, $sel_nav);
					}


				}
			}
	return $parse_nav;
	}
//========================================================
function res_cols($conn, $res_cols, $str_ar, $path_real, $res_str, $ar)
	{
		extract($ar);

		foreach($res_cols as $k_c=>$v_c)
			{
			//echo"col=".$v_c['COL_NAME']."\r\n";
			if($v_c['UNIT_NAME']=="date")
				$v_c['COL_NAME']=db_date_char($v_c['COL_NAME'])." AS ".$v_c['COL_NAME'];
			$str_ar[$v_c['ID']]['col_list'].=($str_ar[$v_c['ID']]['col_list']?", ":"").$v_c['COL_NAME'];
			$path_real[$str_ar[$v_c['ID']]['ID_PATH']]=$str_ar[$v_c['ID']]['ID_PATH'];
			$tab_cols[$v_c['TABLE_ID']][strtoupper($v_c['COL_NAME'])]=$v_c['COLUMN_ID'];
			$tab_cols_name[$v_c['COLUMN_ID']]=strtoupper($v_c['COL_NAME']);
			if(is_array($add_cols))
				{
				foreach($add_cols as $k_r=>$v_r)
					{
					$add_cols_ret[$v_r][$v_c['ID']][$v_c['COLUMN_ID']]=$v_c[$v_r];
					}
				}
			}
		//подготовка запросов
		foreach($res_str as $k_s=>$v_s)
			{
			if(!$v_s['PARENT_TABLE_ID'] && !eregi(PUBLISH, $str_ar[$v_s['ID']]['col_list']))//главная таблица и не выбирается статус публикации
				{
				$str_ar[$v_s['ID']]['col_list'].=($str_ar[$v_s['ID']]['col_list']?", ":"").PUBLISH;
				if(!$tab_cols[$v_s['TABLE_ID']][PUBLISH] || !$tab_cols_name[$tab_cols[$v_s['TABLE_ID']][PUBLISH]])
					{
					$sel_col_id="SELECT id FROM ".TABLE_PRE."columns WHERE table_id=".$v_s['TABLE_ID']." AND upper(name)='".PUBLISH."'";
					//echo"sel_col_id=$sel_col_id\r\n";
					$res_col_id=db_getArray($conn, $sel_col_id, 2);
					$tab_cols[$v_s['TABLE_ID']][PUBLISH]=$res_col_id['ID'];
					$tab_cols_name[$res_col_id['ID']]=PUBLISH;
					}
				if($from!="indexer")
					{
					$str_ar[$v_s['ID']]['sel_q'].=" AND ".PUBLISH.PUBLISH_SQL;
					$str_ar[$v_s['ID']]['list_sel_q'].=" WHERE ".PUBLISH.PUBLISH_SQL;
					}
				}

			if(!$str_ar[$v_s['ID']]['col_list'] ||(
				!eregi("[ ,]+id$", $str_ar[$v_s['ID']]['col_list']) 
				&& !eregi("^id[ ,]+", $str_ar[$v_s['ID']]['col_list']) 
				&& !eregi("[ ,]+id[ ,]+", $str_ar[$v_s['ID']]['col_list']) 
				&& !eregi("^id$", $str_ar[$v_s['ID']]['col_list'])) )//нет запроса id
				{				
				if($str_ar[$v_s['ID']]['col_list'] || test_path_array($str_ar[$v_s['ID']]['ID_PATH'], $path_real))//по этой ветке нужно идти
					{
					if(!$tab_cols[$v_s['TABLE_ID']]['ID'] || !$tab_cols_name[$tab_cols[$v_s['TABLE_ID']]['ID']])
						{
						$sel_col_id="SELECT id FROM ".TABLE_PRE."columns WHERE table_id=".$v_s['TABLE_ID']." AND upper(name)='ID'";
						$res_col_id=db_getArray($conn, $sel_col_id, 2);
						$tab_cols[$v_s['TABLE_ID']]['ID']=$res_col_id['ID'];
						$tab_cols_name[$res_col_id['ID']]="ID";
						}
					$str_ar[$v_s['ID']]['col_list'].=($str_ar[$v_s['ID']]['col_list']?", ":"")."ID";
					}
				}

			if($str_ar[$v_s['ID']]['col_list'])
					{
					$str_ar[$v_s['ID']]['sel_q']=str_replace("[COLUMN_LIST]", $str_ar[$v_s['ID']]['col_list'], $str_ar[$v_s['ID']]['sel_q']);
					if(!$v_s['PARENT_TABLE_ID'] && $from=="layout" && $parse_nav)//если главная таблица и есть связь с навигацией и из layout
						{
						$exists_nav="EXISTS (".str_replace(":var", "t.id", $parse_nav).")";
						if($id)
							$str_ar[$v_s['ID']]['sel_q'].=" AND ".$exists_nav;
						else
							$str_ar[$v_s['ID']]['list_sel_q']=str_replace("[COLUMN_LIST]", $str_ar[$v_s['ID']]['col_list'], $str_ar[$v_s['ID']]['list_sel_q'])." AND ".$exists_nav;

						//$sel_count="SELECT count(id) as C, max(id) as id FROM ".$str_ar[$v_s['ID']]['TABLE_NAME']." t WHERE ".PUBLISH.PUBLISH_SQL.$exists_nav;
						}
					//echo"sel_q=".$str_ar[$v_s['ID']]['sel_q']."\r\n";
					$str_ar[$v_s['ID']]['prep_sel']=db_parse($conn, $str_ar[$v_s['ID']]['sel_q']);
					}
			}//конец foreach $res_str

	$ret=array("str_ar"=>$str_ar, "path_real"=>$path_real, "tab_cols"=>$tab_cols, "tab_cols_name"=>$tab_cols_name, "add_cols_ret"=>$add_cols_ret);
	return $ret;
	}
//===========================================================
//проверяем ветку пути - нужно ли по ней идти
function test_path_array($str, $path_ar)
	{
	foreach($path_ar as $k=>$v)
		{
		if(eregi($str, $v))
			return 1;
		}
	return 0;
	}
//===========================================================
//подготавливаем запрос данных из нужной таблицы по дереву сущности
function table_sql($conn, $ar)
	{
	extract($ar);
	//если задано название таблицы, а не ее id
	if($table_name)
		{
		foreach($get_ref_columns_str_ret['tables'] as $k=>$v)
			{
			if(strtoupper($v['TABLE_NAME'])==strtoupper($table_name))
				{
				$table_id=$k;
				}
			}
		}
	//---------------------------
	if($table_id)
		{
		foreach($get_ref_columns_str_ret['str_ar'] as $k=>$v)
			{
			if($table_id==$v['TABLE_ID'])
				{
				$path_ar[$k]['ID_PATH']=$v['ID_PATH'];
				}
			}
		}
	foreach($get_ref_columns_str_ret['str_ar'] as $k=>$v)
		{
		foreach($path_ar as $k_path=>$v_path)
			{
			//echo RAZD_NAV.$k.RAZD_NAV.", ".$v_path['ID_PATH']."<br>";
			if(ereg(RAZD_NAV.$k.RAZD_NAV, $v_path['ID_PATH']))
				{
				if(!$path_ar[$k_path]['sel'])
					$path_ar[$k_path]['sel']="SELECT id FROM ".$v['TABLE_NAME']." SEL_Q";
				if($v['DIR']==1)//ссылка типа предприятие=>город
					{
					$path_ar[$k_path]['sel']=str_replace("SEL_Q", "WHERE ".$v['REF_COLUMN_NAME']." IN (SELECT id FROM ".$v['TABLE_NAME']." SEL_Q)", $path_ar[$k_path]['sel']);
					}
				elseif($v['DIR']==2)
					{
					$path_ar[$k_path]['sel']=str_replace("SEL_Q", "WHERE id IN(SELECT ".$v['REF_COLUMN_NAME']." FROM  ".$v['TABLE_NAME']." SEL_Q)", $path_ar[$k_path]['sel']);
					}
				//echo"column_id=$column_id, ".$v['REF_COLUMN_ID']."<br>";
				if(!$column_id || ($column_id && $column_id==$v['REF_COLUMN_ID']))
					$path_ar[$k_path]['yes']=1;
				
				}
			}
		//echo"\r\n";
		}
	//echo"<pre>";
	//print_r($path_ar);
	foreach($path_ar as $k=>$v)
		{
		if($v['yes'])
			{
			$v['sel']=str_replace("SEL_Q", ":var", $v['sel']);
			$ret=($ret?db_union($ret, $v['sel']):$v['sel']);
			}
		}
	return $ret;
	}
//=================================================
//запрос количества записей
//=================================================
function sel_count_ent($ar)
	{
	extract($ar);
	//echo"<pre>sel_count_ent";
	//print_r($ar);
	foreach($str_ar as $k=>$v)
		{
		if(!$v['PARENT_TABLE_ID'])//главная таблица
			{
			$exists_nav=" AND EXISTS (".str_replace(":var", "t.id", $parse_nav).")";
			$sel_count="SELECT count(id) as C, max(id) as id FROM ".$v['TABLE_NAME']." t WHERE ".PUBLISH.PUBLISH_SQL.$exists_nav;
			}
		}
	return array("sel_count"=>$sel_count);
	}
?>
<?
function res_cols_layout($conn, $res_cols, $str_ar, $path_real, $res_str, $ar)
	{
		extract($ar);
		foreach($res_cols as $k_c=>$v_c)
			{
			//echo"col=".$v_c['COL_NAME']."\r\n";
			$v_c['COL_NAME_ST']=$v_c['COL_NAME'];
			if($v_c['UNIT_NAME']=="date")
				$v_c['COL_NAME']=db_date_char($v_c['COL_NAME'])." AS ".$v_c['COL_NAME'];
			$str_ar[$v_c['ID']]['col_list'].=($str_ar[$v_c['ID']]['col_list']?", ":"").$v_c['COL_NAME'];
			$path_real[$str_ar[$v_c['ID']]['ID_PATH']]=$str_ar[$v_c['ID']]['ID_PATH'];
			$tab_cols[$v_c['TABLE_ID']][strtoupper($v_c['COL_NAME'])]=$v_c['COLUMN_ID'];
			$tab_cols_name[$v_c['COLUMN_ID']]=strtoupper($v_c['COL_NAME']);
			if($v_c['SORT_MAIN'])//надо сортировать по этой колонке
				{
				$col_order[$v_c['ID']].=($col_order[$v_c['ID']]?", ":"").$v_c['COL_NAME_ST']." ".($v_c['SORT_TYPE_ID']==1?"ASC":"DESC");
				}
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
					
					if($col_order[$v_s['ID']])
						{
						$str_ar[$v_s['ID']]['sel_q'].=" ORDER BY ".$col_order[$v_s['ID']];
						if($str_ar[$v_s['ID']]['list_sel_q'])
							$str_ar[$v_s['ID']]['list_sel_q'].=" ORDER BY ".$col_order[$v_s['ID']];
						}
					//echo"num_items=$num_items<br>";
					if($num_items && !$v_s['PARENT_TABLE_ID'])
						{
						if(!$page)
							$start_items=0;
						else
							$start_items=($page-1)*$num_items;
						$str_ar[$v_s['ID']]['sel_q']=db_limit($str_ar[$v_s['ID']]['sel_q'], $start_items, $num_items);
						if($str_ar[$v_s['ID']]['list_sel_q'])
							$str_ar[$v_s['ID']]['list_sel_q']=db_limit($str_ar[$v_s['ID']]['list_sel_q'], $start_items, $num_items);
						}
					//echo"sel_q=".$str_ar[$v_s['ID']]['sel_q']."\r\n<br>";
					$str_ar[$v_s['ID']]['prep_sel']=db_parse($conn, $str_ar[$v_s['ID']]['sel_q']);
					}
			}//конец foreach $res_str

	$ret=array("str_ar"=>$str_ar, "path_real"=>$path_real, "tab_cols"=>$tab_cols, "tab_cols_name"=>$tab_cols_name, "add_cols_ret"=>$add_cols_ret);
	return $ret;
	}
//===========================================================
function layout_info($conn, $layout_id, $ar=array())
	{
	extract($ar);
	$sel_layout="SELECT entity_id, layout_type_id, html_template_id, num_items FROM ".TABLE_PRE."layout WHERE id=:id";

	$bind_ar[]=db_for_bind("id", $layout_id);
	//echo"sel_layout=$sel_layout,  $layout_id<br>";
	$res_layout=db_getArray($conn, $sel_layout, 2, array("bind_ar"=>$bind_ar));
	//echo"<pre>";
	//print_r($res_layout);
	if($res_layout['ENTITY_ID'])
		{
		//дерево сущности
		$get_ref_columns_str_ret=get_ref_columns_str($conn, $res_layout['ENTITY_ID']);
		$tables=$get_ref_columns_str_ret['tables'];
		$tables_str=$get_ref_columns_str_ret['tables_str'];
		$str_ar=$get_ref_columns_str_ret['str_ar'];
		$res_str=$get_ref_columns_str_ret['res_str'];

		//print_r($str_ar);
		//связь с навигацией
		$parse_nav=ref_navigation($conn, $tables, $tables_str, " AND n.".REAL_PUBLISH.PUBLISH_SQL.($str_post_nav?" AND n.id IN ($str_post_nav)":""), "", array("from"=>"layout"));
		if($post['nav_id'])
			$parse_nav.=" AND n.id=:nav_id";
		//echo"<br><br>parse_nav=$parse_nav<br><br>";
		//запрос количества записей
		$sel_count=sel_count_ent(array("str_ar"=>$str_ar, "parse_nav"=>$parse_nav, "from"=>"layout"));

		return array("tables"=>$tables, "tables_str"=>$tables_str, "str_ar"=>$str_ar, "res_str"=>$res_str, "sel_count"=>$sel_count, "res_layout"=>$res_layout, "parse_nav"=>$parse_nav); 
		}
	}
/*=============================================================================*/
function sel_cols($conn, $ar)
	{		
	extract($ar);
	//запрос колонок
	if(!$id && $res_layout['NUM_ITEMS']!=1)//несколько записей
		{
		$whereSQL=" AND (lc.list_control_id IS NOT NULL OR lc.SORT_MAIN IS NOT NULL)";
		}
	else
		{
		$whereSQL=" AND (lc.card_control_id IS NOT NULL OR lc.SORT_MAIN IS NOT NULL)";
		}

	$sel_cols_q="SELECT DISTINCT er.id, c.id as column_id, upper(c.name) as col_name, er.table_id, lc.sort_main, lc.sort_type_id, ct.unit_name
						FROM ".TABLE_PRE."layout_ref_columns lrc, ".TABLE_PRE."columns c, 
							".TABLE_PRE."entity_ref er,	".TABLE_PRE."layout_columns lc, ".TABLE_PRE."column_types  ct
						WHERE  lrc.LAYOUT_ID=:lid AND er.entity_id=:entity_id AND 
							er.table_id=c.table_id AND c.id=lc.column_id AND lrc.id=lc.layout_rc_id 
							AND c.table_id=er.table_id AND er.id=lrc.ref_columns_id AND ct.id=c.column_type_id $whereSQL
						ORDER BY er.table_id DESC, lc.sort_main ASC";
	$bind_ar=array();
	$bind_ar[]=db_for_bind("lid", $lid);
	$bind_ar[]=db_for_bind("entity_id", $res_layout['ENTITY_ID']);
	//echo"<br><br>sel_cols_q=$sel_cols_q, lid=$lid, entity_id=".$res_layout['ENTITY_ID']."<br><br>\r\n\r\n";
	$res_cols=db_getArray($conn, $sel_cols_q, 1, array("bind_ar"=>$bind_ar));
	//print_r($res_cols);
	$res_cols_ret=res_cols_layout($conn, $res_cols, $str_ar, $path_real, $res_str, array("from"=>"layout", "parse_nav"=>$parse_nav, "id"=>$id, "num_items"=>$res_layout['NUM_ITEMS'], "page"=>$page));
	return $res_cols_ret;
	}

?>
<?
$search_type="all";
if($search_text_all)
	{
	$search_text=$search_text_all;
	}
elseif($search_text_any)
	{
	$search_text=$search_text_any;
	$search_type="any";
	}
elseif($search_text_direct)
	{
	$search_text=$search_text_direct;
	$search_type="phrase";
	}	
if(trim($search_text))
   {
   //функция обработки текста для вывода
   function text_obrab($text, $q_ar, $type=0)
        {
        //echo"1 text=$text<br>";
        $num_pos=0;

        //if(eregi("<br>", $text) || eregi("<p>", $text) || eregi("b>", $text) || eregi("&nbsp;", $text) ||  eregi("<table", $text))
        //        {
        $text=eregi_replace("\r\n", "", $text);
        $text=eregi_replace("&nbsp;", " ", $text);
        $text=eregi_replace("<br>{1,}", "\r\n", $text);
        $text=eregi_replace("<p[^>]*>{1,}", "\r\n", $text);
        $text=eregi_replace("</h[0-9]>", "\r\n", $text);
        $text=strip_tags($text);
        $text=eregi_replace("\r\n{1,}", "<br>", $text);
        $text=eregi_replace("<br>{1,}[[:space:]]{1,}\r\n{1,}", "<br>", $text);
        $text=eregi_replace("<br>{1,}", "\r\n", $text);
        $text=eregi_replace("^\r\n{1,}", "", $text);
        $text=eregi_replace("\r\n{1,}$", "", $text);
        //        }

        if(!$type && strlen($text)>CHAR_ON_PAGE)//если надо вывести кусок
                {
                foreach($q_ar as $q_key=>$q_val)
                        {
                        //if($q_val==sem_strtolower($q_val))
                                $tmp_pos=strpos(my_strtolower($text), my_strtolower($q_val));
                        //else
                        //        $tmp_pos=strpos($text, $q_val);
                        //$text=ereg_replace($q_val, "<b>$q_val</b>", $text);
                        if(!$num_pos || ($num_pos>$tmp_pos && $tmp_pos))
                                $num_pos=$tmp_pos;
                        }



                if($num_pos<(CHAR_ON_PAGE/2))
                        $num_pos=CHAR_ON_PAGE/2;
                //echo"2 text=$text<br>";
                //echo"num_pos=$num_pos<br>";
                $text=eregi_replace("(\r\n( ){0,})+", "<br>", trim(substr($text, ($num_pos-(CHAR_ON_PAGE/2)), CHAR_ON_PAGE)));

                if($num_pos>(CHAR_ON_PAGE/2))
                        {
                        $text=eregi_replace("^[^\f\r\t\n ]{1,} ", "", $text);
                        $text="...".$text;
                        }



                $text=eregi_replace("[\f\r\t\n ][^\f\r\t\n ]{1,}$", "", $text);//чтобу в конце не было обрыва слова

                $text=$text."...";

                }

        //echo"3 text=$text<br>";

        foreach($q_ar as $q_key=>$q_val)
                {
                $tmp_pos=0;
                $last_pos=0;
                $max_i=0;
                $new_text="";
                $max_i=substr_count(my_strtolower($text), my_strtolower($q_val));

                //echo"max_i=$max_i<br>text=$text<br>";

                for($i=0;$i<$max_i; $i++)
                        {
                        $tmp_pos=strpos(my_strtolower($text), my_strtolower($q_val));
                        //echo"tmp_pos=$tmp_pos<br>";
                        $last_pos=$tmp_pos+strlen($q_val);
                        //echo"last_pos=$last_pos<br>";
                        if($tmp_pos)
                                $new_text.=substr($text, 0, $tmp_pos);
                        //$new_text.="<span class=\"word\">".substr($text, $tmp_pos, strlen($q_val))."</span>";
						$new_text.="<!!!>".substr($text, $tmp_pos, strlen($q_val))."</!!!>";
                        //echo"$i - new_text=$new_text<br>";
                        $text=substr($text, $last_pos);
                        }
                //echo"$q_val - new_text=$new_text, text=$text<br>";
                //$text=eregi_replace($q_val, "<b>$q_val</b>", $text);
                $text=$new_text.$text;
                }
        //echo"1 text=$text<br><br>";
       //echo"2 text=$text<br><br>";
	   //$text=ereg_replace("<!!!>", "<span class=\"word\">", $text);
	   //$text=ereg_replace("<\/!!!>", "</span>", $text);
		$text=ereg_replace("<!!!>", "<em>", $text);
		$text=ereg_replace("<\/!!!>", "</em>", $text);
        $text=eregi_replace("\r\n", "<br>", $text);
		$text=htmlspecialchars($text);
        return $text;
        }

//конец функции обработки текста
//=-------------------------------------------
//НАЧАЛО ПОИСКА
//==================================
if(!$page)
	$page=1;
if($on_page)
	$res_on_page=$on_page;
else
	$res_on_page=SEARCH_RES_ITEMS;
if($search_type=='phrase')
	$search_ar[]=$search_text;
else
	$search_ar=search_str($search_text);
//фильтры поиска
if(is_array($search_entity_id))
   {
	foreach($search_entity_id as $k=>$v)
	   {
		$search_entity_id[$k]=intval($v);
		if($search_entity_id[$k])
			$search_entity_id_sql=add_text($search_entity_id_sql, $search_entity_id[$k], ", ");
	   }
	if($search_entity_id_sql)
		$where_sql.=" AND i.entity_id IN($search_entity_id_sql)";
   }
else
	{
	$search_entity_id=intval($search_entity_id);
	if($search_entity_id)
		$where_sql.=" AND i.entity_id=$search_entity_id";
   }
if($period=="week")
	$date_from=date("d.m.Y", mktime()-7*24*60*60);
elseif($period=="month")
	$date_from=date("d.m.Y", mktime(0,0,0, date("n")-1, date("d"), date("Y")));
//echo __LINE__."date_from=$date_from<br>";
if($date_from)
	{
	$date_from=date_calendar($date_from);
	//echo __LINE__."date_from=$date_from<br>";
	if($date_from)
		{
		$where_sql.=" AND i.".DATE_MAIN.">=".db_date($date_from);
		}
	}
if($date_to)
	{
	$date_to=date_calendar($date_to);
	if($date_to)
		{
		$where_sql.=" AND i.".DATE_MAIN."<".db_date($date_to+24*60*60);
		}
	}
$order_by=intval($order_by);
if($order_by==2)//сортировать по дате
	{
	$order_bySQL=" ORDER BY ".DATE_MAIN." DESC";
	$where_sql.=" AND i.".DATE_MAIN." IS NOT NULL";
	}
else
	$order_bySQL=" ORDER BY sum(rel) DESC";
//-----------------------------------------------------------
//проверяем, есть ли результаты
if(count($search_ar))
	{
	$sel_count_shab="SELECT DISTINCT i.entity_id, i.rel_id
                           FROM ".TABLE_PRE."search_word w, ".TABLE_PRE."search_index i
                                  WHERE w.search_index_id=i.id AND i.".PUBLISH.PUBLISH_SQL.$where_sql;
	foreach($search_ar as $k=>$v)
           {
           //if($v)
			$sub_sel_q="($sel_count_shab and w.upper_word like(upper('%$v%'))".$where_sql.")";
		   if($search_type=="all")//все слова
			   {
				if($sel_count_q)
					$sel_count_q=db_union($sel_count_q, $sub_sel_q);
				else
					$sel_count_q=$sub_sel_q;
			   }
			else
			   {
				$where_word=add_text($where_word, "w.upper_word like(upper('%$v%'))", " OR ");
			   }

           //echo"sel_count_q=$sel_count_q<br>";
           //проверяем, есть ли результаты по каждому из слов
           if(count($search_ar)>1)
              {
              $sel_count_ar="select count(*) as c from $sub_sel_q f ";
              //echo"sel_count_ar=$sel_count_ar<br>";
              $res_count_ar[$v]=db_getArray($conn, $sel_count_ar, 2);
			  $res_count_ar[$v]['text_c']=$v;
			  $ret_xml.="<SEARCH_WORD_RES cnt=\"".$res_count_ar[$v]['C']."\">$v</SEARCH_WORD_RES>";
              //echo"$v - ".$res_count_ar[$v]['C']."<br>";
              //if(!$res_count_ar[$v]['C'])
              }
           //$sel_count_q.=($sel_count_q?" union all ":"")."($sel_count_shab and upper(w.word) like(upper('%$v%')))";
           }//конец foreach($search_ar as $k=>$v)


	//проверяем, есть ли результаты по поиску
	if($search_type=="phrase")//по фразе поиск
		{
		$sel_count_q="select count(*) as  c from ti_search_index i WHERE upper_full_text like('%'||upper('$search_text')||'%') AND i.".PUBLISH.PUBLISH_SQL.$where_sql;
		}
	else
		{
		if($search_type=="all")//все слова
			{
			$sel_count_q="select entity_id, rel_id, count(*)  from ($sel_count_q) f1 group by entity_id, rel_id having count(*)=".count($search_ar);
			}
		elseif($search_type=="any")//любое из слов
			{
			$sel_count_q="SELECT DISTINCT i.entity_id, i.rel_id
                           FROM ".TABLE_PRE."search_word w, ".TABLE_PRE."search_index i
                                  WHERE w.search_index_id=i.id AND i.".PUBLISH.PUBLISH_SQL.$where_sql." AND (".$where_word.")";
			}
		$sel_count_q="select count(*) as  c from ($sel_count_q) f3";
		}
	//echo __LINE__." \r\nsel_count_q=$sel_count_q\r\n<br>";
	$res_count=db_getArray($conn, $sel_count_q, 2);
	//если есть подходящие - формируем сам запрос
	$ret_xml.="<SEARCH_RES cnt=\"".$res_count['C']."\">";
	//echo __LINE__." \r\cnt=".$res_count['C']."\r\n<br>";
	if($res_count['C'])
      {
      foreach($search_ar as $k=>$v)
           {
           $sel_q1="SELECT i.entity_id, i.rel_id,  (w.rel_doc+100000000) as rel, ".DATE_MAIN."
            FROM ".TABLE_PRE."search_word w, ".TABLE_PRE."search_index i
            WHERE w.search_index_id=i.id AND w.word_len=".strlen($v)." AND w.upper_word=upper('$v') AND i.".PUBLISH.PUBLISH_SQL.$where_sql;
           $sel_q2="SELECT i.entity_id, i.rel_id, (w.rel_doc/w.word_len*".strlen($v).") as rel, ".DATE_MAIN."
            FROM ".TABLE_PRE."search_word w, ".TABLE_PRE."search_index i
            WHERE w.search_index_id=i.id AND w.upper_word like(upper('%$v%')) AND  w.upper_word!=upper('$v') AND i.".PUBLISH.PUBLISH_SQL.$where_sql;
			

           $sel_union_q=db_union($sel_q1, $sel_q2);

           $sel_union_q="select entity_id, rel_id, ".DATE_MAIN.", sum(rel) as rel
                                           from ($sel_union_q) f2
                                                   group by entity_id, rel_id, ".DATE_MAIN;
           //echo"sel_union_q=$sel_union_q<br><br>";
           if($sel_all_q)
              $sel_all_q=db_union($sel_all_q, $sel_union_q);
           else
              $sel_all_q=$sel_union_q;

           //для запроса рейтинга по колонкам
           $search_arSQL.=($search_arSQL?" OR ":"")."(w.upper_word=upper('$v') AND w.word_len=".strlen($v).")";
           }//конец foreach($search_ar as $k=>$v)
		if($search_type=="all")
		  {
			$sel_q="SELECT entity_id, rel_id, sum(rel) AS sum_rel, ".db_date_char(DATE_MAIN)." as ".DATE_MAIN.", count(*) AS c FROM ($sel_all_q)  f GROUP BY entity_id, rel_id, ".DATE_MAIN." HAVING count(*)=".count($search_ar);
		  }
 		elseif($search_type=="any")
		  {
			$sel_q="SELECT entity_id, rel_id, sum(rel) AS sum_rel, ".db_date_char(DATE_MAIN)." as ".DATE_MAIN.", count(*) AS c FROM ($sel_all_q)  f GROUP BY entity_id, rel_id, ".DATE_MAIN;
		  }
		 elseif($search_type=="phrase")
		  {
			 $sel_q="SELECT distinct entity_id, rel_id, ".db_date_char(DATE_MAIN)." as ".DATE_MAIN." FROM ti_search_index i WHERE upper_full_text like('%'||upper('$search_text')||'%') AND i.".PUBLISH.PUBLISH_SQL.$where_sql;
		  }
	//echo __LINE__." sel_q=$sel_q<br>";
	if($search_type=="phrase")
		$sel_search_q=$sel_q;
	else
		$sel_search_q="$sel_q".$order_bySQL;
      if($res_count['C']>$res_on_page)
            {
            if(!$page)
                 $page=1;
            $sel_search_q=db_limit($sel_search_q, ($page-1)*$res_on_page, $res_on_page);
			//echo"max=".$res_count['C']."<br>";
			$pagelist_xml=pagelist_xml($res_count['C'], $res_on_page, $page, DEFINED("PAGE_SEARCH_LIST")?PAGE_SEARCH_LIST:"", $_REQUEST);
			}
		$start_mk=mktime();
      //echo"<br>".__LINE__."\r\nsel_search_q=$sel_search_q\r\n<br><br>";
      $res_search=db_getArray($conn, $sel_search_q);
	  //echo __LINE__."<br><pre>"; 
	  //print_r($res_search);
	  //echo"mktime=".(mktime()-$start_mk)."<br>";
      }//конец условия, что есть результаты поиска
   }//конец условия, что после чистки в масиве есть слова
   }




if(count($search_ar))
   {
	$show_search_page_ar["search_text"]=$search_text;
	$show_search_page_ar["res_count"]=$res_count['C'];
	$show_search_page_ar["res_count_ar"]=$res_count_ar;	
	$show_search_page_ar["search_ar"]=$search_ar;	
	$show_search_page_ar["page"]=$page;
	//$show_search_page_ar[""]=;	
   }

if($res_count['C'])
   {
   foreach($res_search as $k=>$v)
        {
		$ret_xml.="<SEARCH_RES_ROW><ENTITY_ID>".$v['ENTITY_ID']."</ENTITY_ID>";
		$num_order=($page-1)*$res_on_page+1+$k;
		$res_list_ar=array();
		//echo "<br>k=$k, num_order=$num_order<br>";
        //echo $v['ENTITY_ID']." - ".$v['REL_ID']." - ".$v['SUM_REL']."<br>";
		if(!$OBJECT_TREE[$v['ENTITY_ID']])
			$OBJECT_TREE[$v['ENTITY_ID']]=get_ref_columns_str($conn, $v['ENTITY_ID'], array("from"=>"search"));
		//echo"<pre>";
		//print_r($OBJECT_TREE[$v['ENTITY_ID']]);
        $sel_sub_q1="SELECT i.section_id, i.column_id,  i.ref_columns_id, (w.rel_doc+100000000) as rel
                        FROM ".TABLE_PRE."search_index i, ".TABLE_PRE."search_word w
                        WHERE i.id=w.search_index_id AND i.entity_id=".$v['ENTITY_ID']." AND i.rel_id=".$v['REL_ID']." AND
                        ($search_arSQL)";
        $sel_sub_q2="";
        foreach($search_ar as $k_s=>$v_s)
                    {
                    $sel_sub_q2_pr="SELECT i.section_id, i.column_id, i.ref_columns_id, (w.rel_doc/w.word_len*".strlen($v_s).") as rel
                        FROM ".TABLE_PRE."search_index i, ".TABLE_PRE."search_word w
                        WHERE i.id=w.search_index_id AND i.entity_id=".$v['ENTITY_ID']." AND i.rel_id=".$v['REL_ID']." AND
                        w.upper_word like('%'||upper('$v_s')||'%')";// AND w.word_len>".strlen($v_s)."";
                    if($sel_sub_q2)
                       $sel_sub_q2=db_union($sel_sub_q2,$sel_sub_q2_pr);
                    else
                        $sel_sub_q2=$sel_sub_q2_pr;
                    }
            $sel_sub_q=db_union($sel_sub_q1, $sel_sub_q2);
			$sel_sub_q="SELECT section_id, column_id, ref_columns_id, sum(rel) FROM ($sel_sub_q) f5  GROUP BY section_id, column_id, REF_COLUMNS_ID ORDER BY  sum(rel) DESC";
			//echo"<br>sel_sub_q=$sel_sub_q<br><br>\r\n";

			$sel_sub_q=db_limit($sel_sub_q, 0, 2);
			//echo"sel_sub_q=$sel_sub_q<br>";
			//$start_mk=mktime();
			$res_sub=db_getArray($conn, $sel_sub_q);
			//echo"mktime=".(mktime()-$start_mk)."<br>";
			//$res_list_ar['cols_rating']=$res_sub;
			$flag_cols=0;
			$flag_name=0;
			$flag_part=0;
			$i_other=0;
			$href_ar=href_object($conn, $v['ENTITY_ID'], $v['REL_ID'], 2);
			$ret_xml.="<HREF>".ar_xml($href_ar)."</HREF>";
			//echo"href_ar<pre>";
			//print_r($href_ar);
			$res_list_ar['href']=$href_ar['href'];
			$res_list_ar['ET_NAME']=$OBJECT_TREE[$v['ENTITY_ID']]['ent_info']['NAME'];
			$res_list_ar[DATE_MAIN]=$v[DATE_MAIN];
			//echo"date=".$v[DATE_MAIN]."<br>";
			$date_ar_tmp=date_format_ar($v[DATE_MAIN]);
			$ret_xml.="<DATE><YEAR>".$date_ar_tmp["Y"]."</YEAR><MONTH>".$date_ar_tmp["m"]."</MONTH>"
					."<DAY>".$date_ar_tmp["d"]."</DAY><HOUR>".$date_ar_tmp["H"]."</HOUR>"
					."<MINUTES>".$date_ar_tmp["i"]."</MINUTES>"
					."<SECONDS>".$date_ar_tmp["s"]."</SECONDS></DATE>";
			foreach($res_sub as $k_rs=>$v_rs)
					{
					if(!$COLUMNS_AR[$v_rs['COLUMN_ID']])
						$COLUMNS_AR[$v_rs['COLUMN_ID']]=get_byId($conn, TABLE_PRE.columns, $v_rs['COLUMN_ID'], "upper(name) as name");
					}
			if($OBJECT_TREE[$v['ENTITY_ID']]['ent_info']['SEARCH_TEMPLATE_ID'])//есть шаблон для отображения
				{
				//echo"YES<br>";
				$res_list_ar['SEARCH_TEMPLATE']=$OBJECT_TREE[$v['ENTITY_ID']]['ent_info']['SEARCH_TEMPLATE'];
				$res_list_ar['ENTITY_ID']=$v['ENTITY_ID'];
				$res_list_ar['REL_ID']=$v['REL_ID'];
				$res_list_ar['res_sub']=$res_sub;
				$ret_xml.=search_template(array("conn"=>$conn, "OBJECT_TREE"=>$OBJECT_TREE, "entity_id"=>$v['ENTITY_ID'], "id"=>$v['REL_ID']));
				}
			else
				{
				$col_list=array();
				foreach($res_sub as $k_rs=>$v_rs)
					{
					//echo $v_rs['COLUMN_ID']."-".$COLUMNS_AR[$v_rs['COLUMN_ID']]['NAME']."<br>";
					if($v_rs['SECTION_ID'])//из части
						{
						$col_list[$v_rs['REF_COLUMNS_ID']][$v_rs['SECTION_ID']]= add_text($col_list[$v_rs['REF_COLUMNS_ID']][$v_rs['SECTION_ID']],  $COLUMNS_AR[$v_rs['COLUMN_ID']]['NAME'], ", ");
						$flag_part=1;
						//echo"part<br>";
						}
					else
						{
						$col_list[$v_rs['REF_COLUMNS_ID']]=add_text($col_list[$v_rs['REF_COLUMNS_ID']], $COLUMNS_AR[$v_rs['COLUMN_ID']]['NAME'], ", "); 
						//echo"main, ".$col_list[$v_rs['REF_COLUMNS_ID']]."<br>";
						}

					if($COLUMNS_AR[$v_rs['COLUMN_ID']]['NAME']!="NAME" || $OBJECT_TREE[$v['ENTITY_ID']]['str_ar'][$v_rs['REF_COLUMNS_ID']]['TREE_LEVEL']!=1)
					//колонка не name или не главная таблица
						$flag_cols=1;
					else
						$flag_name=1;
					if($k_rs==1)
						break;
					}//конец foreach($res_sub as $k_rs=>$v_rs)
				//echo"col_list<pre>";
				//print_r($col_list);
				//print_r($OBJECT_TREE[$v['ENTITY_ID']]);
				foreach($OBJECT_TREE[$v['ENTITY_ID']]['str_ar'] as $k_ob=>$v_ob)
					{
					//echo"level=".$v_ob['TREE_LEVEL']."<br>";
					if($v_ob['TREE_LEVEL']==1)//по главной таблице
						{
						$res_list_ar['other_text_ar']=array();
						if(!$flag_name)
							$col_list[$k_ob]="name".($col_list[$k_ob]?", ":"").$col_list[$k_ob];
						$sel_main="SELECT ".$col_list[$k_ob]." FROM ".$v_ob['TABLE_NAME']." WHERE id=".$v['REL_ID'];
						//echo"sel_main=$sel_main<br>";
						$res_main=db_getArray($conn, $sel_main, 2);
						foreach($res_main as $k_m=>$v_m)
							{
							if($k_m=="NAME")
								{
								$res_list_ar['name']=text_obrab($v_m, $search_ar);
								}
							elseif($i_other==0)
								{
								$res_list_ar['other_text_ar'][$i_other]['ot_text']= text_obrab($v_m, $search_ar);
								//$ret_xml.="<TEXT>".text_obrab($v_m, $search_ar)."</TEXT>";
								$i_other++;
								}
							}
						$ret_xml.="<NAME>".$res_list_ar['name']."</NAME>";
						foreach($res_list_ar['other_text_ar'] as $other_k=>$other_val)
							{
							$ret_xml.="<OTHER_TEXT>".ar_xml($other_val, 1)."</OTHER_TEXT>";
							}
						}
					elseif($flag_part && is_array($col_list[$k_ob]) && $i_other==0)
						{
						$res_list_ar['other_text_ar']=array();
						foreach($col_list[$k_ob] as $k_p=>$v_p)
							{
							$sel_text="SELECT $v_p FROM ".$v_ob['TABLE_NAME']." WHERE id=$k_p";
							//echo"sel_text=$sel_text<br>";
							$res_text=db_getArray($conn, $sel_text, 2);
							foreach($res_text as $k_m=>$v_m)
								{
								if(eregi("[a-z]", $k_m) && $v_m)
									{
									$res_list_ar['other_text_ar'][$i_other]['ot_name']=$v_ob['NAME'];
									$res_list_ar['other_text_ar'][$i_other]['ot_text'] =text_obrab($v_m, $search_ar);
									$i_other++;
									}
								}
							}
						foreach($res_list_ar['other_text_ar'] as $other_k=>$other_val)
							{
							$ret_xml.="<OTHER_TEXT>".ar_xml($other_val, 1)."</OTHER_TEXT>";
							}
						}
					//echo"2col_list<pre>";
					//print_r($col_list);
					}
				}
		$ret_xml.="</SEARCH_RES_ROW>";
		}//конец цикла по результатам

   }//конец условия, что есть результаты
if(count($search_ar))
	$ret_xml.="</SEARCH_RES>".$pagelist_xml;
//$content=ob_get_contents();
//ob_end_clean();
//echo $ret_xml;
$res_list_ar=array();
/*
if(function_exists("show_search_page"))
	{
	//echo"show_search_page";
	//show_search_page($conn, $show_search_page_ar, $res_list_ar, array("nav_id"=>$nav_id));
	show_search_page($conn, $show_search_page_ar, $res_list_ar, array("nav_id"=>$nav_id, "OBJECT_TREE"=>$OBJECT_TREE, "COLUMNS_AR"=>$COLUMNS_AR));
	}
else
	echo $content;
	*/
//echo $content;

?>
<?
	$search_type="all";
	if(trim($search)){
		$search_text = trim($search);
	}elseif(trim($tags)){
		$search_text = trim($tags);
		unset($tags);
	}else{
		$search_text="";
	}
	if($all_field=='on'){
		$znak=' and ';
	}else{
		$znak=' or ';
	}
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

	//$search_ar=search_str($search_text);
	if(trim($search_text))
	{
		$sql = "SELECT foo.id, link_id, multimedia_id, mrubrikator_id, author_id, rank, foo.date_edit, foo.date_main,
		(COALESCE(authors_v.surname,' ') ||' '|| COALESCE(authors_v.name,' ')) as author_name,
		ts_headline('ru', foo.name, q) as name, ts_headline('ru', foo.keyword, q) as keyword,
		ts_headline('ru', foo.text, q) as text, search_link_id, search_link.nav_id,search_link.layer_id
		FROM ( SELECT id, link_id, multimedia_id, mrubrikator_id, author_id, date_edit, date_main, search_link_id, name, keyword, text,
		q, ts_rank( fts_search, q ) as rank
		FROM search_v, plainto_tsquery('ru', '{$search_text}' ) q WHERE fts_search @@ q ORDER BY rank DESC
		) AS foo left join search_link on (search_link_id=search_link.id) left join authors_v on (author_id=authors_v.id) ";
	}else{
		$sql = "SELECT search_v.id, link_id, multimedia_id, mrubrikator_id, author_id,(COALESCE(multimedia_id,0)+COALESCE(mrubrikator_id,0)+COALESCE(author_id,0)) as rank,
		search_v.date_edit, search_v.date_main, (COALESCE(authors_v.surname,' ') ||' '|| COALESCE(authors_v.name,' ')) as author_name,
		search_v.name, search_v.keyword, search_v.text, search_link_id, search_link.nav_id,search_link.layer_id
		FROM search_v left join search_link on (search_link_id=search_link.id) left join authors_v on (author_id=authors_v.id)";
	}
	$where_sql = "";
	$where=array();
	switch ($area) {
		case '1':$where[]=" search_link_id=".$area;break;
		case '2':$where[]=" search_link_id=".$area." or search_link_id=4 ";break; // Выполнится это
		case '3':$where[]=" search_link_id=".$area;break;
		default:  break;
	};
	if(intval($author)>0){$where[]=" author_id=".intval($author);}
	if(trim($tags)>0){
		$tagar=explode(';',$tags);
		foreach($tagar as $key=>$val){
			$tagar[$key] = "keyword like '%".trim($val)."%'";
		}
		$where[]=implode($znak,$tagar);
	}
	if($from)
	{
		$from=date_calendar($from);
		if($from)
		{
			if($to)
			{ echo $to;
				echo $to=date_calendar($to);
				if($to)
				{
					$where[]=" (date_main>=".db_date($from)." and (date_main<=".db_date($to )." + interval '1 day')) ";
				}
			}else{
				$where[]=" date_main>=".db_date($from);
			}
		}
	}elseif($to)
	{
		$to=date_calendar($to);
		if($to)
		{
			$where[]=" (date_main<=".db_date($to)." + interval '1 day')";
		}
	}


	if($ids){
		$where[]=" (link_id=".$ids." and search_link_id!=3 )";
	}
	if (count($where)>0){
		$where_sql = " where (".implode(') and (',$where).") " ;
		$where_sql1 = " where (".implode(') or (',$where).") and not (".implode(') and (',$where).")" ;
		$where_sql2 = " where (".implode(') or (',$where).")" ;
	}

	switch ($sort) {
		case 'desc':$order_by=" ORDER BY date_main DESC, rank DESC";break;
		case 'asc':$order_by=" ORDER BY date_main ASC, rank DESC";break; // Выполнится это
		case 'author':$order_by=" ORDER BY author_name ASC, rank DESC";break;
		default: $order_by="ORDER BY rank DESC"; break;
	};
	$count_sql =" OFFSET ".($on_page*($page-1))." LIMIT ".$on_page;

	//фильтры поиска


	//-----------------------------------------------------------
	//проверяем, есть ли результаты
	if(count($where)==1){
		$sel_q='('.$sql.' '.$where_sql.' '.$order_by.')'.$count_sql.'';
		$where_sql2 = $where_sql;
	}elseif(count($where)==2 && $area) {
		$sel_q='('.$sql.' '.$where_sql.' '.$order_by.') '.$count_sql.'';
		$where_sql2 = $where_sql;
	}elseif(($from||$to)&& count($where)==2){
		$sel_q='('.$sql.' '.$where_sql.' '.$order_by.') '.$count_sql.'';
		$where_sql2 = $where_sql;
	}elseif(($from||$to)&& count($where)==3 && $area){
		$sel_q='('.$sql.' '.$where_sql.' '.$order_by.') '.$count_sql.'';
		$where_sql2 = $where_sql;
	}else{
		$sel_q='('.$sql.' '.$where_sql.' '.$order_by.') union ('.$sql.' '.$where_sql1.'  '.$order_by.') '.$count_sql.'';
	}
    //echo $sel_q;
	$res_search=db_getArray($conn, $sel_q, 1);
	//print_r($res_search);
	if(trim($search_text))
	{
		$sel_count_q="SELECT count(id) as C FROM ( SELECT id, link_id, multimedia_id, mrubrikator_id, author_id, date_edit, date_main, search_link_id, name, keyword, text, q, ts_rank( fts_search, q ) as rank
		FROM search_v, plainto_tsquery('ru', '{$search_text}' ) q WHERE fts_search @@ q ORDER BY rank DESC ) AS foo ".$where_sql2;
	}else{
		$sel_count_q="SELECT count(id) as C FROM search_v    ".$where_sql2;
	}
	//echo $sel_count_q;
	$res_count=db_getArray($conn, $sel_count_q,2);
	//print_r($res_count);
	$sel_entity_q="select ti_layout.entity_id, ti_layout.id as rel_id from  cham_gather left join cham_layer_gather on(cham_layer_gather.gather_id = cham_gather.id)
	left join ti_layout on (cham_layer_gather.layout_id=ti_layout.id)  where cham_gather.class_name='newsearch'; ";
	$res_entity=db_getArray($conn, $sel_entity_q, 2);

	$ret_xml.='<SEARCH_RES cnt="'.intval($res_count['C']).'">';
	if($res_count['C'])
	{
		foreach($res_search as $k=>$v)
		{
			$ret_xml.="<SEARCH_RES_ROW><ENTITY_ID>".$res_entity['ENTITY_ID']."</ENTITY_ID>";
			if(intval($k)){
				$num_order=($page-1)*$res_on_page+1+$k;
				$ret_xml.="<NUM_ORDER>".($num_order)."</NUM_ORDER>";
			}
			$href_path="/index.html";
			if($v["NAV_ID"]) $href_path=href_prep($href_path, "nav_id={$v['NAV_ID']}");
			if($v["LAYER_ID"]) $href_path=href_prep($href_path, "layer_id={$v["LAYER_ID"]}");
			if($v['ID']) $href_path=href_prep($href_path, "id=".$v['LINK_ID']);
			$href_path = eregi_replace( "&", "&amp;", $href_path);
			//echo $href_path;
			//$ret_xml.="<HREF>".($href_path)."</HREF>";
 			$ret_xml.='<LayerLinks><Link name="Card">'.($href_path).'</Link>';
 			if($v["MULTIMEDIA_ID"] && $v["SEARCH_LINK_ID"]!=3){
 				$sel_column_id="select t.id,c.id as column_id from ti_tables t left join ti_columns c on (c.table_id=t.id)
                where t.name='multimedia_v' and c.column_type_id=4";
				$res_column=db_getArray($conn, $sel_column_id,2);
				if($res_column["COLUMN_ID"]){
					$href_path="/images/image_all.html";
					$href_path=href_prep($href_path, "col_id={$res_column["COLUMN_ID"]}");
					$href_path=href_prep($href_path, "s=3");
					$href_path=href_prep($href_path, "id=".$v['MULTIMEDIA_ID']);
					$href_path = eregi_replace( "&", "&amp;", $href_path);
					$ret_xml.='<Link name="Photo">'.($href_path).'</Link>';
				}
 			}elseif($v["SEARCH_LINK_ID"]==3){ //authors_v
				$sel_column_id="select t.id,c.id as column_id from ti_tables t left join ti_columns c on (c.table_id=t.id)
                where t.name='authors_v' and c.column_type_id=4";
                $res_column=db_getArray($conn, $sel_column_id,2);
				if($res_column["COLUMN_ID"]){
					$sel_column_id=" select id from authors_v where avatar is not null and id=".$v['LINK_ID'];
                	$res_column2=db_getArray($conn, $sel_column_id,2);
                	if($res_column["ID"]){
						$href_path="/images/image_all.html";
						$href_path=href_prep($href_path, "col_id={$res_column["COLUMN_ID"]}");
						$href_path=href_prep($href_path, "s=6");
						$href_path=href_prep($href_path, "id=".$v['LINK_ID']);
						$href_path = eregi_replace( "&", "&amp;", $href_path);
						$ret_xml.='<Link name="Photo">'.($href_path).'</Link>';
                	}
				}
 			}
			$ret_xml.='</LayerLinks>';


			foreach($v as $skey=>$sval){

				if(!is_integer($skey)){
					$skey = strtoupper($skey);
					if($skey!="DATE_MAIN")
					$ret_xml.="<".$skey.">".eregi_replace( "&", "&amp;", $sval)."</".$skey.">";
				}
			}
            if($v['DATE_MAIN']) $date_ar_tmp=date_format_ar($v['DATE_MAIN']);
            else $date_ar_tmp=date_format_ar($v['DATE_EDIT']);
			$ret_xml.="<DATE_MAIN><YEAR>".$date_ar_tmp["Y"]."</YEAR><MONTH>".$date_ar_tmp["m"]."</MONTH>"
			."<DAY>".$date_ar_tmp["d"]."</DAY><HOUR>".$date_ar_tmp["H"]."</HOUR>"
			."<MINUTES>".$date_ar_tmp["i"]."</MINUTES>"
			."<SECONDS>".$date_ar_tmp["s"]."</SECONDS></DATE_MAIN>";
			$ret_xml.="</SEARCH_RES_ROW>";
		}//конец цикла по результатам
	}//конец условия, что есть результаты
	//пролистовка
	//$max_pages = 20;
	//echo $res_count['C'];
	//echo $res_on_page;
	$PAGE_END_FLAG = true;
	$pagelist_xml.="<NUM_ITEMS>".$res_on_page."</NUM_ITEMS>";
	if($max_pages && $res_count['C']>($res_on_page*$max_pages))
	//есть ограничение на количество пролисотовок на странице и оно будет работать
	{
		$ppage=(ceil($page/$max_pages)-1)*$max_pages+1;
		if(ceil($res_count['C']/$res_on_page)>($ppage+$max_pages-1)){
			$max_ppage=$ppage+$max_pages-1;
		}else{
			$max_ppage=ceil($res_count['C']/$res_on_page);
		}
	}
	else
	{
		$ppage=1;
		$max_ppage=ceil($res_count['C']/$res_on_page);
	}
	//echo $ppage.' '.$max_ppage.' '.$url_page=$_SERVER['SCRIPT_NAME'];
	//print_r($ar);
	foreach($ar as $k_ar=>$v_ar)
	{
	if($k_ar!='page' && $k_ar!='PHPSESSID' && !ereg("phpbb", $k_ar))
	{
	$url_page=href_prep($url_page, "$k_ar=$v_ar");
	}
	}
	if($page!=1)//если не первая страница
	{
	if($PAGE_END_FLAG)
	$pagelist_xml.="<FirstPage>".htmlspecialchars(href_prep($url_page, "page=1"))."</FirstPage>";
	$pagelist_xml.="<PrevPage>".htmlspecialchars(href_prep($url_page, "page=".($page-1)))."</PrevPage>";
	}
	if($page!=ceil($res_count['C']/$res_on_page))//если не последняя страница
	{
	if($PAGE_END_FLAG)
	$pagelist_xml.="<LastPage>".htmlspecialchars(href_prep($url_page, "page=".ceil($res_count['C']/$res_on_page)))."</LastPage>";
	$pagelist_xml.="<NextPage>".htmlspecialchars(href_prep($url_page, "page=".($page+1)))."</NextPage>";
	}
	for($i_p=$ppage; $i_p<=$max_ppage; $i_p++)
	{
	$pagelist_xml.="<SelectablePage>";
	$pagelist_xml.="<Name>$i_p</Name>";
	if($page!=$i_p)
	{
	$url_page_tmp=href_prep($url_page, "page=$i_p");
	$pagelist_xml.="<Link>".htmlspecialchars($url_page_tmp)."</Link>";
	}
	$pagelist_xml.="</SelectablePage>";
	}
	$pagelist_xml="<PageTurning>$pagelist_xml</PageTurning>";

	$ret_xml.=$pagelist_xml."</SEARCH_RES>";
	$ret_xml.="";
?>
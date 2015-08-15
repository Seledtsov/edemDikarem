<?
//получаем элемент в XML по относительному пути ($path) от текущего элемента($element)
//по умолчанию возвращает первый подходящий элемент
if (function_exists("domxml_open_mem"))
	DEFINE("DOM_XML", 1);//4-й PHP
else
	{
	DEFINE("DOM_XML", 2);//5-й PHP
	//ini_set('display_errors', 1);
	//ini_set('error_reporting', 15);
	function domxml_open_mem($xmlstr)
		{
		$dom_tmp = new DOMDocument();
		$dom_tmp->loadXML($xmlstr);
		return $dom_tmp;
		}
	}

function get_content_xml($element, $ar=array())
	{
	extract($ar);
	if(DOM_XML==1)
		$ret=$element->get_content();
	elseif(DOM_XML==2)
		$ret=$element->nodeValue;
	return $ret;
	}
//echo"DOM_XML=".DOM_XML."<br>";
//===================================
function ti_get_element_by_path($element, $str_path, $ar=array())
	{
	global $show_err_test;
	extract($ar);
	//eсли cnt_flag - возвращаем, сколько элементов и массив этих подходящих элементов
	//echo"show_line=$show_line<br>";
	$res_count=0;
	if(isset($show_line))
		{
		//echo  __LINE__." - ".DOM_XML."\r\n";
		//echo __LINE__." ti_get_element_by_path($str_path)\r\n<br>";
		ini_set('display_errors', 1);
		ini_set('error_reporting', 15);
		}
	if(DOM_XML==1)
	{
	//echo __LINE__." ti_get_element_by_path($str_path)<br>";
	$count_up=substr_count($str_path, "../");
	//поднятие на нужное количество ступеней вверх
	//print_r($element);
	$tmp_element=$element;
	for($i=0; $i<$count_up; $i++)
		{
		$tmp_element=$tmp_element->parent_node();
		//echo"UP - ".$tmp_element->node_name()."<br>";
		}

	//если путь - от корня
	if(ereg("^/", $str_path))
		{
		//echo"ROOT - $str_path<br>";

		while($tmp_element->parent_node())
			{
			$tmp_element=$tmp_element->parent_node();
			//echo"UP - ".$tmp_element->node_name()."<br>";
			}
		}
	//echo"tmp_element<br>";
	//print_r($tmp_element);
	$str_path=str_replace("../", "", $str_path);
	$str_path=str_replace("./", "", $str_path);
	$str_path=ereg_replace("^/", "", $str_path);
	$str_path_ar=explode("/", $str_path);
	$child = $tmp_element->child_nodes();
	//спуск на нужный уровень
	$count_path=count($str_path_ar);
	//echo __LINE__."count_path=$count_path\r\n";
	//print_r($str_path_ar);
	//for($i=0; $i<$count_path; $i++)
	//	{
		//echo"i=$i<br>";
		//перебор всех детей и сравнение по имени
		foreach ($child as $k_ch=>$v_ch)
			{
			//print("$k_ch. ". $child[$k_ch]->node_type(). " ". $child[$k_ch]->node_name()."<br/>");
			if($child[$k_ch]->node_name()==$str_path_ar[0])
				{
				if($count_path>1)
					{
					//echo"str_path=$str_path<br>";
					//print_r($child[$k_ch]);
					$tmp_element=$child[$k_ch];
					//$child=array();
					//$child = $tmp_element->child_nodes();
					$tmp_ret=ti_get_element_by_path($tmp_element, str_replace($str_path_ar[0]."/", "", $str_path), $ar);
					if(!$cnt_flag)
						{
						//echo __LINE__."<pre>";
						//print_r($tmp_ret);
						return $tmp_ret;
						}
					else
						{
						$res_count+=$tmp_ret["count"];
						$res_element=array_merge($res_element, $tmp_ret["elements"]);
						}

					}
				else
					{
					$text=$child[$k_ch]->get_content();
					//echo"<br>text2($k_ch) =$text<br>";
					//print_r($child[$k_ch]);
					if(!$cnt_flag)
						return $v_ch;
					else
						{
						$res_count++;
						$res_element[]=$v_ch;
						}
					}
				}
			}//конец foreach ($child as $k_ch=>$v_ch)
		//}
	if($cnt_flag)
		{
		//echo __LINE__."<pre>";
		//print_r($res_element);
		return array("count"=>$res_count, "elements"=>$res_element);
		}
	}//if(DOM_XML==1)
	elseif(DOM_XML==2)
	{
	//$str_path=str_replace("./", "", $str_path);
	//$str_path=ereg_replace("^/", "", $str_path);
	if(!ereg("^/", $str_path) && !ereg("^../", $str_path) && !ereg("./", $str_path))
		$str_path="./".$str_path;
	if($show_line || $show_err_test)
		{
		ini_set('display_errors', 1);
		ini_set('error_reporting', 15);
		echo __LINE__." str_path=$str_path<br>\r\n";
		if($dom_tmp)
			echo __LINE__." dom_tmp YES<br>\r\n";
		else
			echo __LINE__." dom_tmp NO<br>\r\n";
		ini_set('display_errors', 1);
		ini_set('error_reporting', 7);
		}
	$xpath = new DOMXPath($dom_tmp);
	if($show_err_test ||$show_line )
		echo __LINE__."DOMXPath<br>\r\n query($str_path,  $element)";
	$elements = $xpath->query($str_path,  $element);
	if($show_err_test ||$show_line)
		echo __LINE__."<br>\r\n";
	if($show_err_test ||$show_line)
		echo __LINE__." $str_path<br>\r\n";
	$res_count=0;
	foreach ($elements as $entry)
		{
		if($show_line ||$show_err_test )
			echo __LINE__." Found $entry->nodeValue<br>\r\n";
		if(!isset($cnt_flag))
			return $entry;
		$res_count++;
		}
	if($cnt_flag)
		{
		return array("count"=>$res_count, "elements"=>$elements);
		}

	}//if(DOM_XML==2)
	//echo"ti_get_element_by_path END<br>";

	}
//конец ti_get_element_by_path
//=====================================================
//генерация xml отображения
function layout_get_res($conn, $ar, $ar_var)
{
	extract($ar);
	extract($ar_var);
	/*array( "count_sql"=>$count_sql, "bind_count_ar"=>$bind_count_ar, "res_layout"=>$res_layout, "arch_min"=>$arch_min, "arch_max"=>$arch_max, "xml_page"=>$xml_page, "xml_sql"=>$xml_sql, ""=>$, ""=>$, ""=>$)*/
	$res_count=db_getArray($conn, $count_sql, 2, array("bind_ar"=>$bind_count_ar));
	if($show_err_test==1)
		echo"\r\n<br>".__LINE__."<pre>count_sql=$count_sql<br>";
	//print_r($bind_count_ar);
	//echo"\r\n<br>".__LINE__."<pre>res_count<br>";
	//print_r($res_count);
	if($res_layout['CODE_NAME']=='calendar')//отображение календаря
		{
		if($arch_min && $arch_max)//границы календаря
			{
			$sel_line_arch=sel_dual($conn, "$arch_min AS min_date, $arch_max AS max_date");
			if($show_err_test)
				{
				echo __LINE__." sel_line_arch=$sel_line_arch<br><pre>";
				print_r($bind_arch);
				echo"</pre>";
				}
			$res_line_arch=db_getArray($conn, $sel_line_arch, 2, array("bind_ar"=>$bind_arch));
			//echo"<pre>res_line_arch<br>";
			//print_r($res_line_arch);
			$date_ar_min=date_format_ar($res_line_arch['MIN_DATE']);
			$date_ar_max=date_format_ar($res_line_arch['MAX_DATE']);
			//print_r($date_ar_min);
			//print_r($date_ar_max);
			}

		$bind_id=$bind_count_ar;
		$res_id=db_getArray($conn, $id_sql, 1, array("bind_ar"=>$bind_count_ar));
		if($show_err_test==1)
			echo"id_sql=$id_sql<br><br>\r\n";
		//echo"<pre>";
		//print_r($bind_count_ar);
		$date_calend_yes=array();
		$url_cal=$_SERVER['SCRIPT_NAME'];
		foreach($ar as $k_ar=>$v_ar)
			{
			//echo"$k_ar=$v_ar<br>";
			if($k_ar!='SelectedDate' && $k_ar!='PHPSESSID' && $k_ar!='date_arch' && $k_ar!='id' && $k_ar!="page")
				{
				$url_cal=href_prep($url_cal, "$k_ar=$v_ar");
				}
			}
		foreach($res_id as $v_id)
			{
			$date_ar=date_format_ar($v_id['CALEND_DATE']);
			$date_calend_yes[mktime(0, 0, 0, $date_ar['m'], $date_ar['d'], $date_ar['Y'])]=href_prep($url_cal, "date_arch=".$date_ar['d']."-".$date_ar['m']."-".$date_ar['Y']."&SelectedDate=".$date_ar['d']."-".$date_ar['m']."-".$date_ar['Y']);;
			}

		for($date_tmp=mktime(0, 0, 0, $date_ar_min['m'], $date_ar_min['d'], $date_ar_min['Y']); $date_tmp<mktime(0, 0, 0, $date_ar_max['m'], $date_ar_max['d'],$date_ar_max['Y']); $date_tmp+=60*60*24)
			{
			if($show_err_test)
					echo __LINE__." ".date('d-n-Y', $date_tmp)."<br>\r\n";
			if(date('d', $date_tmp)==date('d', $date_tmp-60*60*24))//защита от повторения дня для осеннего перевода времени
				$date_tmp+=60*60;
			if($date_tmp>=mktime(0, 0, 0, $date_ar_max['m'], $date_ar_max['d'],$date_ar_max['Y']))
				break;
			if($old_month!=date('m', $date_tmp))
				{
				$xml_page_tmp.='<Month><Number>'.date('n', $date_tmp).'</Number>';
				$xml_page_tmp.='<Year>'.date('Y', $date_tmp).'</Year>';
				//Ссылки на предыдущий/следующий месяца
				$url_month='/index.html';
				foreach($ar as $k_ar=>$v_ar)
					{
					//echo"$k_ar=$v_ar<br>";
					if($k_ar!='SelectedDate' && $k_ar!='PHPSESSID' && $k_ar!='date_arch' && $k_ar!='id' && $k_ar!="page")
						{
						$url_month=href_prep($url_month, "$k_ar=$v_ar");
						}
					}
				$prev_month = mktime(0, 0, 0, date("m", $date_tmp)-1, date("d", $date_tmp),   date("Y", $date_tmp));
				$next_month = mktime(0, 0, 0, date("m", $date_tmp)+1, date("d", $date_tmp),   date("Y", $date_tmp));
				$xml_page_tmp.='<PrevMonthLink>'.htmlspecialchars(href_prep($url_month, 'date_arch=01-'.date('n', $prev_month).'-'.date('Y', $prev_month))).'</PrevMonthLink>';
				$xml_page_tmp.='<NextMonthLink>'.htmlspecialchars(href_prep($url_month, 'date_arch=01-'.date('n', $next_month).'-'.date('Y', $next_month))).'</NextMonthLink>';
				/*else
								$xml_page_tmp.='<NextMonthLink>'.htmlspecialchars(href_prep($url_next, 'date_arch=01-01-'.date('Y', $date_tmp)+1)).'</NextMonthLink>';*/
					//$xml_page_tmp.='<PrevMonth link="'.$url_prev.'"/>';
				}
			$week_day=date('w', $date_tmp);
			if($week_day==0)
				$week_day=7;
			if($old_month!=date('m', $date_tmp) || $week_day==1)
				{
				$xml_page_tmp.="<Week>";
				}
			if($old_month!=date('m', $date_tmp) && $week_day<>1)
				{
				for($i_w=1; $i_w<$week_day; $i_w++)
					$xml_page_tmp.='<Day/>';
				}
			$xml_page_tmp.='<Day';
			if($date_calend_yes[$date_tmp])
				$xml_page_tmp.=" link=\"".htmlspecialchars($date_calend_yes[$date_tmp])."\"";;
			$xml_page_tmp.='>'.date('j', $date_tmp).'</Day>';
			if(date('d', $date_tmp)==date('t', $date_tmp) || $week_day==7)
				{
				if(date('d', $date_tmp)==date('t', $date_tmp) && $week_day!=7)
					//последний день месяца - не воскресение
					{
					for($i_w=$week_day+1; $i_w<=7; $i_w++)
						$xml_page_tmp.='<Day/>';
					}
				$xml_page_tmp.="</Week>";
				if(date('d', $date_tmp)==date('t', $date_tmp))
					$xml_page_tmp.='</Month>';
				}

			//$old_year=date('Y', $date_tmp);
			$old_month=date('m', $date_tmp);
			$old_week=$week_day;
			$start_cal=1;
			}
		$xml_res=array();//освобождаем память
		$xml_page_tmp="<".$res_layout['NAME'].">".$xml_page_tmp."</".$res_layout['NAME'].">";
		}
	elseif($res_count['C']>0 && $res_layout['CODE_NAME']=='random' && $res_layout['NUM_ITEMS'])
		//выбираем случайые id
		{
		$bind_id=$bind_count_ar;
		$res_id=db_getArray($conn, $id_sql, 1, array("bind_ar"=>$bind_count_ar));

		if($ret_type==2)
			{
			$ret['id_sql']=db_getArray($conn, $id_sql, 1, array("bind_ar"=>$bind_count_ar, "ret_sql"=>1));
			}
		for($i_r=1; $i_r<=$res_layout['NUM_ITEMS']; $i_r++)
			{
			$val=rand(0, $res_count['C']-1);
			$sel_id_ar[$res_id[$val]['ID']]=$i_r;
			$id_str=add_text($id_str, $res_id[$val]['ID'], ", ");

			/*$bind_id[]==db_for_bind("id_".$i_r, $val);
			$rownum_list=add_text($rownum_list, ":id_".$i_r, ",");
			*/
			}
		$xml_sql=str_replace("SELECT id", "SELECT ".db_case("id", $sel_id_ar)." AS sort_order", $xml_sql)." WHERE id IN($id_str) ORDER BY sort_order ASC";
		//echo"\r\n<br><br>random - xml_sql=$xml_sql<br><br>";
		$xml_res=db_getArray($conn, $xml_sql, 1, array("bind_ar"=>$bind_xml_ar));
		/*if($show_err_test)
			{
			echo __LINE__."\r\n\r\n<pre>xml_res<br>";
			print_r($xml_res);
			echo"\r\n\r\n";
			}*/
		foreach($xml_res as $k=>$v)
			{
			$xml_page_tmp.=$v['XML_GEN'];
			}
		$xml_res=array();//освобождаем память
		//echo $xml_page_tmp;
		$xml_page_tmp="<".$res_layout['NAME'].">".$xml_page_tmp."</".$res_layout['NAME'].">";
		}
	elseif($res_count['C']==1 && $res_layout['CODE_NAME']=='list' && $res_layout['SHOW_AS_CARD'])//переброс на карточку
		{
		$sel_card="SELECT detail_layer_id FROM cham_layer WHERE id=$layer_id";
		//echo"sel_card=$sel_card<pre><br>";
		$res_card=db_getArray($conn, $sel_card, 2);
		//print_r($res_card);
		$res_id=db_getArray($conn, $id_sql, 2, array("bind_ar"=>$bind_count_ar));
		$url_card="/index.html?layer_id=".$res_card['DETAIL_LAYER_ID']."&id=".$res_id['ID'];
		foreach($ar as $k_ar=>$v_ar)
			{
			//echo"$k_ar=$v_ar, strpos=".strpos($k_ar, "phpbb")."<br>";
			if($k_ar!='layer_id' && $k_ar!='id' && $k_ar!='PHPSESSID' && !strpos($k_ar, "hpbb") && substr($k_ar, 0, 2)!="__")
				{
				$url_card=href_prep($url_card, "$k_ar=$v_ar");
				}
			}
		header("Location:$url_card&one=1");
		echo"Location:$url_card";
		exit;
		}
	elseif($res_count['C']>0)// || $res_layout['CODE_NAME']=='card')
		{
		$pagelist_xml="";
		$on_page_name="on_page".$res_layout['ID'];
		//echo"on_page_name=$on_page_name<br><pre>";
		//print_r($res_layout);
		if ($$on_page_name>0)
			$res_layout['NUM_ITEMS']=$$on_page_name;
		//пролистовка
		if($res_layout['NUM_ITEMS'] && ($res_layout['NUM_ITEMS']<$res_count['C'] || $page>1)&& !$ret_id_sql)
			{
			if(!$res_layout['PAGE_LIST_FLAG'])//пролистовка не нужна
				{
				$id_sql=db_limit($id_sql, 0, $res_layout['NUM_ITEMS']);
				}
			else
				{
				if(!$page)
					$page=1;
				$pagelist_xml.="<NUM_ITEMS>".$res_layout['NUM_ITEMS']."</NUM_ITEMS>";
				$id_sql=db_limit($id_sql, ($page-1)*$res_layout['NUM_ITEMS'], $res_layout['NUM_ITEMS']);
				//echo"id_sql=$id_sql, $pagelist_xml<br>";
				if($res_layout['PAGE_LINKS_NUM'] && $res_count['C']>($res_layout['NUM_ITEMS']*$res_layout['PAGE_LINKS_NUM']) )
					//есть ограничение на количество пролисотовок на странице и оно будет работать
					{					$ppage=(ceil($page/$res_layout['PAGE_LINKS_NUM'])-1)*$res_layout['PAGE_LINKS_NUM']+1;
					if(ceil($res_count['C']/$res_layout['NUM_ITEMS'])>($ppage+$res_layout['PAGE_LINKS_NUM']-1))
						$max_ppage=$ppage+$res_layout['PAGE_LINKS_NUM']-1;
					else
						$max_ppage=ceil($res_count['C']/$res_layout['NUM_ITEMS']);
					}
				else
					{
					$ppage=1;
					$max_ppage=ceil($res_count['C']/$res_layout['NUM_ITEMS']);
					}
				$url_page=$_SERVER['SCRIPT_NAME'];
				foreach($ar as $k_ar=>$v_ar)
							{
							//echo"$k_ar=$v_ar<br>";
							if($k_ar!='page' && $k_ar!='PHPSESSID' && !ereg("phpbb", $k_ar))
								{
								$url_page=href_prep($url_page, "$k_ar=$v_ar");
								}
							}

				if($page!=1)//если не первая страница
					{
					if($res_layout['PAGE_END_FLAG'])
						$pagelist_xml.="<FirstPage>".htmlspecialchars(href_prep($url_page, "page=1"))."</FirstPage>";
					$pagelist_xml.="<PrevPage>".htmlspecialchars(href_prep($url_page, "page=".($page-1)))."</PrevPage>";
					}
				if($page!=ceil($res_count['C']/$res_layout['NUM_ITEMS']))//если не последняя страница
					{
					if($res_layout['PAGE_END_FLAG'])
						$pagelist_xml.="<LastPage>".htmlspecialchars(href_prep($url_page, "page=".ceil($res_count['C']/$res_layout['NUM_ITEMS'])))."</LastPage>";
					$pagelist_xml.="<NextPage>".htmlspecialchars(href_prep($url_page, "page=".($page+1)))."</NextPage>";
					}
				//echo"ppage=$ppage, max_ppage=$max_ppage<br>";
				for($i_p=$ppage; $i_p<=$max_ppage; $i_p++)
					{
					$pagelist_xml.="<SelectablePage>";
					$pagelist_xml.="<Name>$i_p</Name>";
					if($page!=$i_p)
						{
						//echo"$i_p - url_page=$url_page<br>";
						//echo"$i_p - url_page=$url_page<br>";
						$url_page_tmp=href_prep($url_page, "page=$i_p");
						//echo"$i_p - url_page=$url_page<br>";
						$pagelist_xml.="<Link>".htmlspecialchars($url_page_tmp)."</Link>";
						}
					$pagelist_xml.="</SelectablePage>";
					}
				$pagelist_xml="<PageTurning>$pagelist_xml</PageTurning>";
				}
			}
		$pagelist_xml.="<RES_COUNT>".$res_count['C']."</RES_COUNT>";
		//конец пролистовки
		//echo __LINE__." id_sql=$id_sql, ret_type=$ret_type, ret_id_sql=$ret_id_sql<br>";
		$res_id=db_getArray($conn, $id_sql, 1, array("bind_ar"=>$bind_count_ar));

		if($ret_type==2)
			{
			$ret['id_sql']=db_getArray($conn, $id_sql, 1, array("bind_ar"=>$bind_count_ar, "ret_sql"=>1));
			}
		if($res_layout['CODE_NAME']=='card' &&  $res_layout['LIST_LAYOUT_ID'])
			//карточка и задано, откуда брать порядок для списк - для пролистовки по карточке
			{
			$id_sql_list_ar=layout_xml($conn, $res_layout['LIST_LAYOUT_ID'], $ar, $layer_id, $layer_xml, array("ret_id_sql"=>1), $ret_type);
			//echo"id_sql_list_ar=<br><pre>\r\n";
			//print_r($id_sql_list_ar);
			$id_sql_list=$id_sql_list_ar['res_id'];
			if($ret_type==2)
				{
				$ret['id_sql']=$id_sql_list_ar['id_sql'];
				}
			//echo"id_sql_list=<br><pre>\r\n";
			//print_r($id_sql_list);
			$url_page=$_SERVER['SCRIPT_NAME'];
			foreach($ar as $k_ar=>$v_ar)
					{
					//echo"$k_ar=$v_ar<br>";
					if($k_ar!='page' && $k_ar!='PHPSESSID' && !ereg("phpbb", $k_ar) && strtoupper($k_ar)!='ID')
						{
						$url_page=href_prep($url_page, "$k_ar=$v_ar");
						}
					}
			$pagelist_xml.="<NUM_IN_LIST>".count($id_sql_list)."</NUM_IN_LIST>";
			foreach($id_sql_list as $k_id_list=>$v_id_list)
				{
				if(!$k_id_list && $v_id_list['ID']!=$id)//первый элемент
					{
					$pagelist_xml.="<FirstPage id_link=\"".$v_id_list['ID']."\">".htmlspecialchars(href_prep($url_page, "id=".$v_id_list['ID']))."</FirstPage>";
					}
				if($v_id_list['ID']==$id)
					{
					$pagelist_xml.="<ORDER_IN_LIST>".($k_id_list+1)."</ORDER_IN_LIST>";
					if($id_sql_list[$k_id_list-1]['ID'])//предыдущий элемент
						{
						$pagelist_xml.="<PrevPage id_link=\"".$id_sql_list[$k_id_list-1]['ID']."\">".htmlspecialchars(href_prep($url_page, "id=".$id_sql_list[$k_id_list-1]['ID']))."</PrevPage>";
						}
					if($id_sql_list[$k_id_list+1]['ID'])//следующий элемент
						{
						$pagelist_xml.="<NextPage id_link=\"".$id_sql_list[$k_id_list+1]['ID']."\">".htmlspecialchars(href_prep($url_page, "id=".$id_sql_list[$k_id_list+1]['ID']))."</NextPage>";
						}
					}

				if($k_id_list==count($id_sql_list)-1 && $v_id_list['ID']!=$id)//последний элемент
					{
					$pagelist_xml.="<LastPage id_link=\"".$v_id_list['ID']."\">".htmlspecialchars(href_prep($url_page, "id=".$v_id_list['ID']))."</LastPage>";
					}
				}
			$pagelist_xml="<PageTurning>$pagelist_xml</PageTurning>";
			}
		elseif($ret_id_sql)//если запрос на получение id_sql
			{
			//echo __LINE__." res_id=";
			//print_r($res_id);
			$ret['res_id']=$res_id;
			return $ret;
			}
		//echo"\r\n<br>".__LINE__." ".$res_layout['NAME']."<br>id_sql=$id_sql<br>\r\n";
		//echo"bind_ar<br><pre>";
		//print_r($bind_count_ar);

		//echo"res_id<br>";
		//print_r($res_id);
		foreach($res_id as $k=>$v)
			{
			$sel_id_ar[$v['ID']]=$k+1;
			$id_str=add_text($id_str, $v['ID'], ", ");
			}
		//echo"sel_id_ar<br>";
		//print_r($sel_id_ar);
		if($res_count['C']>1)
			$xml_sql=str_replace("SELECT id", "SELECT ".db_case("id", $sel_id_ar, '', array("int_ret"=>1))
				." AS global_order", $xml_sql)." WHERE id IN($id_str) ORDER BY global_order ASC";
		else
			$xml_sql.=" WHERE id IN($id_str)";

		if($show_err_test)
			echo"\r\n<br>".__LINE__.$res_layout['NAME']."<br>xml_sql=$xml_sql<br><br><pre>\r\n";
		//print_r($bind_xml_ar);
		$xml_res=db_getArray($conn, $xml_sql, 1, array("bind_ar"=>$bind_xml_ar));
		if($show_err_test)
			{
			echo __LINE__."xml_res";
			echo"xml_res<br> - ".htmlentities('…', ENT_COMPAT, 'cp1251');
			print_r($xml_res);
			}
		//echo"xml_res<br> - ".htmlentities('…', ENT_COMPAT, 'cp1251');
		//print_r($xml_res);
		foreach($xml_res as $k=>$v)
			{
			$xml_page_tmp.=$v['XML_GEN'];
			}
		$xml_res=array();//освобождаем память
		//echo $xml_page_tmp;
		$xml_page_tmp="<".$res_layout['NAME']."><LAYOUT_ID>$layout_id</LAYOUT_ID>" .$xml_page_tmp.$pagelist_xml.$filter_xml."</".$res_layout['NAME'].">";
		}
	elseif($res_count['C']==0)//нет результатов по запросу - возвращаем пустой xml с фильтрами
		{
		$xml_page_tmp="<".$res_layout['NAME']."><LAYOUT_ID>$layout_id</LAYOUT_ID>". $filter_xml."</".$res_layout['NAME'].">";
		}
	$xml_page.=$xml_page_tmp;
	return array("ret"=>$ret, "xml_page_tmp"=>$xml_page_tmp);
}//layout_get_res
//======================================================
//обработака, если в фильтр передается массив данных
function layout_get_res_ar($conn, $ar, $ar_var, $start=0)
	{
	extract($ar);
	extract($ar_var);
	$ar_tmp=$ar_var;
	//echo __LINE__." bind_count_ar=<pre>";
	//print_r($bind_count_ar);
	//echo __LINE__." bind_xml_ar=<pre>";
	//print_r($bind_xml_ar);
	$bind_tmp=$bind_xml_ar;
	foreach($bind_xml_ar as $k_bind=>$v_bind)
		{
		//echo __LINE__." $k_bind<br>";
		if($k_bind>=$start)
			{
			if(is_array($v_bind['val']))
				{
				foreach($v_bind['val'] as $k_v=>$v_v)
					{
					$bind_tmp[$k_bind]['val']=$v_v;
					$flag_ar=1;
					$ar_tmp['bind_xml_ar']=$bind_tmp;
					$ar_tmp['bind_count_ar']=$bind_tmp;
					$layout_get_res=layout_get_res_ar($conn, $ar, $ar_tmp, $k_bind);
					$res[]=$layout_get_res;
					//echo __LINE__."layout_get_res=<pre>";
					//print_r($layout_get_res);
					}
				}
			}
		}
	if(!$flag_ar)
		{
		//echo" NO FLAG_AR<br>";
		return layout_get_res($conn, $ar, $ar_var);
		}
	if(!$start)
		{
		//echo __LINE__."<pre>";
		//print_r($res);
		foreach($res as $k_r=>$v_r)
			{
			$ret['xml_page_tmp'].=$v_r['xml_page_tmp'];
			$ret['ret']['id_sql'][]=$v_r['ret']['id_sql'];
			}
		//echo __LINE__."<pre>";
		//print_r($ret);
		return $ret;
		}
	}
//======================================================
//получение xml  отображения  по id отображения
function layout_xml($conn, $layout_id, $ar, $layer_id="", $layer_xml="", $ad_arr=array(), $ret_type=1)
{
	$start_layout=mktime();
	//если ret_type=2 - то возвращаем еще и запросы по отображениям
	extract($ar);
	extract($ad_arr);
	//print_r($ad_arr);
	//echo"user_id_forum_ses=$user_id_forum_ses<br>";
$layout_ar[$layout_id]=array("LAYOUT_ID"=>$layout_id);
if($show_err_test==1)
	echo __LINE__." <b>layout_id=$layout_id (".$res_layout['NAME'].")</b><br>";
$sel_layout="SELECT l.*, lt.code_name FROM ti_layout l, ti_layout_types lt WHERE l.id=$layout_id AND lt.id=l.layout_type_id";
//echo __LINE__."sel_layout=$sel_layout<br>";
$res_layout=db_getArray($conn, $sel_layout, 2);
//echo"<pre>";
//echo __LINE__."res_layout<pre><br>";
//print_r($res_layout);

$xml_tmp="";
$xml_page_tmp="";
$sel_id_ar=array();
$id_str="";
$where_sub=array();
$lrc_er=array();
$sub_sql_ar=array();
//проверяем на фильтры из соседнего отображения
/*if($res_layout['CODE_NAME']!='manual')
	{
	$sel_filter_lay="SELECT "
					." FROM ti_layout_ref_columns lrc, ti_layout_columns lc, ti_layout_cols_preset lcp "
					." WHERE lc.layout_rc_id = lrc.id  AND lcp.layout_column_id = lc.id AND lrc.layout_id=$layout_id AND lcp.l_cols_preset_type_id=2";
	}
else
	{
	}*/
if($res_layout['CODE_NAME']!='manual')
	{
	$bind_count_ar=array();
	$sel_er_lr="SELECT id, ref_columns_id FROM ti_layout_ref_columns WHERE layout_id=$layout_id";
	$res_er_lr=db_getArray($conn, $sel_er_lr);
	foreach($res_er_lr as $k=>$v)
		{
		$er_lrc[$v['REF_COLUMNS_ID']]=$v['ID'];
		$lrc_er[$v['ID']]=$v['REF_COLUMNS_ID'];
		}
	//---------
	$sel_sub="SELECT * FROM TI_LAYOUT_TREE_SEL WHERE layout_id=$layout_id AND sub_sql IS NOT NULL  ORDER BY tree_level DESC";
	//echo __LINE__." sel_sub=$sel_sub<br>";
	$res_sub=db_getArray($conn, $sel_sub);
	if (count($res_sub)>0)
		{
		foreach($res_sub as $k=>$v)
			{
			$sub_sql_ar[$v['ID']]=$v;
			}
		$sel_filters="SELECT * FROM ti_layout_filter_sel WHERE layout_id=$layout_id";// AND (name<>'PRESET_TREE' OR name IS NULL)";
		$res_filters=db_getArray($conn, $sel_filters);
		//echo __LINE__." sel_filters=$sel_filters<br>";
		//echo"<pre>res_filters<br>";
		//print_r($res_filters);
		$bind_arch=array();
		foreach($res_filters as $k=>$v)
			{
			//echo"<pre>$k<br>";
			//print_r($v);
			//echo $v['NAME']."-".$$v['NAME'].", is_array=".is_array($$v['NAME']).", count-".count($$v['NAME'])."\r\n<br>";
			//echo __LINE__." filter_id=".$v['ID']."<br>";
			if($v['NAME']=="PRESET_TREE")
				{
				$filter_yes=0;
				}
			elseif($v['NAME']=='PUBLISH_TYPE')
				{
				$filter_yes=1;
				//echo"PUBLISH_LIST=".PUBLISH_LIST."<br>";
				$val=PUBLISH_LIST;
				//echo"2<br>";
				}
			elseif($v['NAME']=='LOGIN')
				{
				$filter_yes=1;
				//echo"PUBLISH_LIST=".PUBLISH_LIST."<br>";
				$val=USER_NAME;
				//echo"2<br>";
				}
			elseif($v['NAME']=='USER_ID')
				{
				$filter_yes=1;
				//echo"g_USER_ID=".g_USER_ID."<br>";
				$val=g_USER_ID;
				//echo"2<br>";
				}
			elseif (($v['FILTER_TYPE']=='var' || $v['NAME']=="date_arch") && $v['NAME'] &&
					($$v['NAME'] || (is_array($$v['NAME']) && count($$v['NAME'])>0) ))//переменная и она задана
				{
				//echo __LINE__."<br>";
				$filter_yes=1;
				unset($val);
				if(is_array($$v['NAME']))
					{
					//echo$v['NAME']."<pre>";
					//print_r($$v['NAME']);
					foreach($$v['NAME'] as $k_inv=>$v_inv)
						{
						$val=add_text($val, type_var_prep($v_inv, $v['UNIT_NAME']), ", ");
						//echo"Array!<br>";
						}
					}
				else
					$val=type_var_prep($$v['NAME'], $v['UNIT_NAME']);
				//echo"1 - ".$v['NAME'].", val=$val<br>";
				}
			elseif($v['FILTER_TYPE']=='layout')//берем значение из другого отображения
				{
				//echo __LINE__." filter_type=layout<br>";
				if(!$dom_tmp)
					{
					//echo __LINE__." ".struct_xml($layer_xml);
					//echo __LINE__." ".$v['NAME']."<br>";
					$xmlstr_tmp=utf8_encode(convert_cyr_string(xml_header()	."<CLIENT_AREA>$layer_xml</CLIENT_AREA>", "w", "i"));
					if(DOM_XML==1)
						{
						if(!$dom_tmp=domxml_open_mem($xmlstr_tmp))
							{
							echo"Error filters - domxml_open_mem<br>";
							echo struct_xml($xmlstr_tmp);
							}
						}
					elseif(DOM_XML==2)
						{
						//echo __LINE__." dom_tmp=$dom_tmp<br>";

						$dom_tmp = new DOMDocument();
						//phpinfo();
						if(!$dom_tmp)
							{
							echo"Error filters - ".__LINE__."<br>";
							}
						else
							{
							//echo __LINE__." dom_tmp=$dom_tmp<br>";
							$dom_tmp->loadXML($xmlstr_tmp);
							//$element_tmp=$elements_tmp->item(0)
							}
						}
					}//if(!$dom_tmp)
				if(DOM_XML==1)
					{
					$elements_tmp=$dom_tmp->get_elements_by_tagname("CLIENT_AREA");
					$element_tmp=$elements_tmp[0];
					$val_tmp=ti_get_element_by_path($element_tmp, ".".$v['NAME'], array("cnt_flag"=>1));
					//echo __LINE__."<pre>";
					//print_r($val_tmp);
					}
				elseif(DOM_XML==2)
					{
					$elements_tmp=$dom_tmp->getElementsByTagName("CLIENT_AREA");
					foreach($elements_tmp as $element_tmp)
						{
						break;
						}
					//echo __LINE__." dom_tmp<br>";
					$val_tmp=ti_get_element_by_path($element_tmp, ".".$v['NAME'], array("cnt_flag"=>1, "dom_tmp"=>$dom_tmp));
					}
				//echo struct_xml("<CLIENT_AREA>$layer_xml</CLIENT_AREA>");
				//print_r($elements_tmp);

				//echo __LINE__." find-".$v['NAME'].", count=".$val_tmp['count']."<br>";

				//print_r($val_tmp);
				if($val_tmp['count'] && ( DOM_XML==2 || is_object($val_tmp['elements'][0])))
					{
					if(DOM_XML==1)
						{
						$val=array();
						foreach($val_tmp['elements'] as $entry)
							{
							//$val_obj=$val_tmp['elements'][0];
							$val[]=$entry->get_content();
							//echo __LINE__."val=".$val_tmp."<br>";
							//break;
							}
						//echo __LINE__."val=<br><pre>";
						//print_r($val);
						}
					elseif(DOM_XML==2)
						{
						$val=array();
						foreach ($val_tmp['elements'] as $entry)
							{
							//echo  __LINE__." Found {$entry->previousSibling->previousSibling->nodeValue},"." by {$entry->nodeValue}<br>";
							$val[]=$entry->nodeValue;
							//break;
							}
						//echo __LINE__." val=$val<br>";
						//print_r($val);
						}
					if(count($val)==1)
						{
						$val=$val[0];
						}
					else
						{
						$var_ar_flag=1;
						}
					$filter_yes=1;
					}
				elseif(isset($v['DEFAULT_VALUE']))
					{
					$val=$v['DEFAULT_VALUE'];
					$filter_yes=1;
					}
				elseif($v['OPTIONAL_FLAG']==1)
					{
					$filter_yes=0;
					}
				else
					{
					$val='';
					$filter_yes=1;
					}
				//echo"layout_val=$val<br>";
				}
			elseif(isset($v['DEFAULT_VALUE']) &&
					($v['FILTER_TYPE']!='var' || !(isset($ar[$v['NAME']]) && $v['OPTIONAL_FLAG']==1) )
					)
				{
				$filter_yes=1;
				if(ereg('SYSDATE', $v['DEFAULT_VALUE']))//текущая дата
					{
					$val=str_replace('SYSDATE', db_sysdate(), $v['DEFAULT_VALUE']);
					$sel_date=sel_dual($conn, $val." as val_xml");
					//echo"sel_date=$sel_date<br>";
					$res_date=db_getArray($conn, $sel_date, 2);
					$val_xml=date_format_ar($res_date['VAL_XML']);
					}
				else
					{
					$val=$v['DEFAULT_VALUE'];
					$val_xml=$val;
					}
				if($v['NAME'] && $v['FILTER_TYPE']=='var')//Если это переменная, но применяем значение по умолчанию
					{
					$filter_xml.="<".$v['NAME'].">";
					if(ereg(",", $val_xml))
						{
						$val_xml=explode(",", $val_xml);
						}
					if(is_array($val_xml))
						{
						foreach($val_xml as $k_v=>$v_v)
							$filter_xml.="<ITEM key=\"$k_v\">".$v_v."</ITEM>";
						}
					else
						$filter_xml.=$val_xml;
					$filter_xml.="</".$v['NAME'].">";
					}
				//echo"val=$val, ".$v['DEFAULT_VALUE']."<br>";
				//echo"3<br>";
				}
			elseif($v['OPTIONAL_FLAG'] || $v['FILTER_TYPE']!="var")
				{
				$filter_yes=0;//не учитывать этот фильтр
				//echo"4<br>";
				}
			elseif($v['FILTER_TYPE']=="var")
				{
				//echo"ret false<br>";
				return "";
				}
			/*
			if($v['UNIT_NAME']=='date' && $val!=db_sysdate())
				{
				$val=db_date($val);
				}
			*/
			if($v['NAME']=='date_arch' && $res_layout['CODE_NAME']=='calendar')//если архив - надо определить границы для календарей
				{
				if(strpos($v['WHERE_SQL'], ">")>0)//нижняя граница
					{
					$arch_min=eregi_replace("[a-z0-9_\.]*>=?", "", $v['WHERE_SQL']);
					if($show_err_test)
						echo"arch_min=$arch_min<br>";
					}
				if(strpos($v['WHERE_SQL'], "<")>0)//верхняя граница
					{
					$arch_max=eregi_replace("[a-z0-9_\.]*<=?", "", $v['WHERE_SQL']);
					if($show_err_test)
						echo"arch_max=$arch_max<br>";
					}
				$bind_arch[]=db_for_bind("var_".$v['ID'], $val);
				}
			//echo"val=$val<br>";
			if(!isset($val) && !$v['OPTIONAL_FLAG'] && $v['NAME']!='PRESET_TREE')
				{
				return "";
				}

			if($filter_yes)
				{
				if($v['ID_SQL_IGNORE_FLAG']<>1)//фильтр надо учитывать в count и id_sql
					{
					$sub_sql_ar[$v['LRC_ID']]['SUB_SQL']=addit_where($sub_sql_ar[$v['LRC_ID']]['SUB_SQL'], $v['WHERE_SQL']);
					$sub_sql_ar[$v['LRC_ID']]['filter_yes']=1;

					$bind_count_ar[]=db_for_bind("var_".$v['ID'], $val);
					}

				if(DBASE=="POSTGRESQL" && $res_layout['CODE_NAME']!='calendar' && strpos($val, "'")>0)
					{
					//преобразуем одиночные кавычки в нужное количество
					//echo"level=".$sub_sql_ar[$v['LRC_ID']]['TREE_LEVEL'].", val=$val<br>";
					$bind_xml_ar[]=db_for_bind("var_".$v['ID'], str_replace("'", str_repeat("'", 2*$sub_sql_ar[$v['LRC_ID']]['TREE_LEVEL']), $val));
					//$bind_xml_ar[]=db_for_bind("var_".$v['ID'], $val);
					}
				else
					$bind_xml_ar[]=db_for_bind("var_".$v['ID'], $val);
				//echo"WHERE_SQL=".$v['WHERE_SQL']."<br>";

				//$res_filters[$k]['WHERE_SQL']=$res_filters[$k]['WHERE_SQL'];
				$where_sub[$v['LRC_ID']]=add_text($where_sub[$v['LRC_ID']], $v['WHERE_SQL'], " AND ");
				}
			}
		if($filter_xml)
			$filter_xml="<FILTER_DEF>".$filter_xml."</FILTER_DEF>";
		//echo"\r\nwhere_sub<br>";
		//print_r($where_sub);
		//echo"\r\n<br>";
		//формирование count_sql
		foreach($res_sub as $k=>$v)
			{
			$parent_id=$v['PARENT_ID'];
			//echo"parent_id=$parent_id<br>";
			if($parent_id && $sub_sql_ar[$v['ID']]['filter_yes'])
					{
					$sub_sql_ar[$parent_id]['SUB_SQL']=addit_where($sub_sql_ar[$parent_id]['SUB_SQL'], "EXISTS(".$sub_sql_ar[$v['ID']]['SUB_SQL'].")");
					$sub_sql_ar[$parent_id]['filter_yes']=1;
					}
			else
					{
					$main_id=$v['ID'];
					//echo"main_id=$main_id<br>";
					}
			}
		}
	//echo __LINE__." sub_sql_ar<pre><br>";
	//print_r($sub_sql_ar);
	$count_sql="SELECT count(id) AS c ".$sub_sql_ar[$main_id]['SUB_SQL'];
	//echo"count_sql=$count_sql<br>\r\n";
	$where_pos=strpos($count_sql, "WHERE");
	//echo"where_pos=$where_pos<br>";

	if($where_pos>0)
		{
		$where_add=substr($count_sql, $where_pos+5);
		//echo"where_add=$where_add<br>";
		//echo"id_sql=$id_sql<br><br>";
		$id_sql=str_replace('WHERE_ADD', (strpos($res_layout['ID_SQL'], ' WHERE ')?" AND ":"WHERE").$where_add, $res_layout['ID_SQL']);
		}
	else
		{
		$where_add="";
		$id_sql=str_replace('WHERE_ADD', "", $res_layout['ID_SQL']);
		}
	//echo"where_add=$where_add<br>";

	//echo"\r\nid_sql=$id_sql\r\n<br><br>";
	//подстановка в xml_sql
	$xml_sql=$res_layout['XML_SQL'];
	//echo"\r\n<br>xml_sql=$xml_sql<br>";
	//echo"<pre>sub_sql_ar<br>";
	//print_r($sub_sql_ar);
	foreach($res_sub as $k=>$v)
		{
		$lrc_id=$lrc_er[$v['ID']];
		//echo"lrc_id=$lrc_id, v[id]=".$v['ID']."<br>";
		if ($where_sub[$v['ID']])
			{
			if(DBASE=="POSTGRESQL")
					{
					if(strpos($where_sub[$v['ID']], "'")>0)//преобразуем одиночные кавычки в нужное количество
						{
						//echo"level=".$sub_sql_ar[$v['ID']]['TREE_LEVEL']."<br>";
						$where_sub[$v['ID']]=str_replace("'", str_repeat("'", 2*$sub_sql_ar[$v['ID']]['TREE_LEVEL']), $where_sub[$v['ID']]);
						}
					}
			$xml_sql=eregi_replace("filter_sql_$lrc_id", $where_sub[$v['ID']], $xml_sql);
			}
		else
			{
			$xml_sql=eregi_replace("AND filter_sql_".$lrc_id, "", $xml_sql);
			$xml_sql=eregi_replace("WHERE filter_sql_".$lrc_id, "", $xml_sql);
			}
		}
	$xml_sql=eregi_replace("AND filter_sql_[0-9]+", "", $xml_sql);

	//echo"\r\n<br>".__LINE__." ".$res_layout['NAME']."<br>\r\n<br>count_sql=$count_sql<br><br><pre>\r\n";
	//print_r($bind_count_ar);
	//echo __LINE__." ret_id_sql=$ret_id_sql<br>";
	if(!$var_ar_flag)//если нет фильтров-массивов
		{
		$layout_get_res=layout_get_res($conn, $ar, array("count_sql"=>$count_sql, "bind_count_ar"=>$bind_count_ar, "res_layout"=>$res_layout, "arch_min"=>$arch_min, "arch_max"=>$arch_max, "xml_page"=>$xml_page, "xml_sql"=>$xml_sql, "ret"=>$ret, "id_sql"=>$id_sql, "bind_xml_ar"=>$bind_xml_ar, "layout_id"=>$layout_id, "ret_type"=>$ret_type, "bind_arch"=>$bind_arch, "layer_id"=>$layer_id, "ret_id_sql"=>$ret_id_sql, "show_err_test"=>$show_err_test));
		}
	else
		{
		$layout_get_res=layout_get_res_ar($conn, $ar, array("count_sql"=>$count_sql, "bind_count_ar"=>$bind_count_ar, "res_layout"=>$res_layout, "arch_min"=>$arch_min, "arch_max"=>$arch_max, "xml_page"=>$xml_page, "xml_sql"=>$xml_sql, "ret"=>$ret, "id_sql"=>$id_sql, "bind_xml_ar"=>$bind_xml_ar, "layout_id"=>$layout_id, "ret_type"=>$ret_type, "bind_arch"=>$bind_arch, "layer_id"=>$layer_id, "ret_id_sql"=>$ret_id_sql));
		}
	//echo"<br>".__LINE__." var_ar_flag=$var_ar_flag<br>";
	if($ret_id_sql==1)
		{
		$ret=$layout_get_res;
		}
	else
		{
		$xml_page_tmp=$layout_get_res["xml_page_tmp"];
		$ret=$layout_get_res["ret"];
		}
	//echo __LINE__." layout_get_res=<pre>";
	//print_r($layout_get_res);
	}//конец услвия, что не manual
else//тип отображения - manual
	{
	//праметры
	$xml_sql=$res_layout['XML_SQL'];
	$sel_pars="SELECT lp.*, lpt.code_name AS filter_type, (SELECT unit_name FROM ti_column_types ct WHERE ct.id=lp.column_type_id) AS unit_name"
				." FROM ti_layout_params lp, ti_layout_preset_types lpt"
				." WHERE lp.param_type_id=lpt.id AND lp.layout_id=$layout_id";
	//echo"sel_pars=$sel_pars<br>";
	$res_pars=db_getArray($conn, $sel_pars);
	foreach($res_pars as $k_p=>$v_p)
		{
		if($v_p['INS_VALUE']=='PUBLISH_TYPE')
				{
				//echo"PUBLISH_LIST=".PUBLISH_LIST."<br>";
				$val=PUBLISH_LIST;
				//echo"2<br>";
				}
		elseif($v_p['PARAM_TYPE_ID']==1 || $v_p['INS_VALUE']=="date_arch")
			{
			//$val=$$v_p['INS_VALUE'];
			if($$v_p['INS_VALUE'])
				$val=type_var_prep($$v_p['INS_VALUE'], $v_p['UNIT_NAME']);
			elseif($v_p['INS_VALUE']=="date_arch")
				{
				$val=db_sysdate();
				//$val=type_var_prep($$v_p['INS_VALUE'], $v_p['UNIT_NAME']);
				}

			}
		$bind_xml_ar[]=db_for_bind($v_p['NAME'], $val);
		}
	//echo __LINE__." xml_sql=$xml_sql<br>\r\n<pre>";
	//print_r($bind_xml_ar);

	$xml_res=db_getArray($conn, $xml_sql, 1, array("bind_ar"=>$bind_xml_ar));
	//exit;
	//echo"xml_res<br><pre>";
	//print_r($xml_res);

	foreach($xml_res as $k=>$v)
		{
		$xml_page_tmp.=$v['XML_GEN'];
		}
	$xml_page_tmp="<".$res_layout['NAME']."><LAYOUT_ID>$layout_id</LAYOUT_ID>" .$xml_page_tmp.$pagelist_xml."</".$res_layout['NAME'].">";
	}
//echo __LINE__." layout_time=".(mktime()-$start_layout)."<br>";
if($ret_type==2)
	{
	$ret['xml_page']=$xml_page_tmp;
	return $ret;
	}
else
	return $xml_page_tmp;
}//END layout_xml
//===================================================
//выдает форму поиска и результаты поиска
//===================================================
function search_xml($conn, $ar)
	{
	extract($ar);
	//echo"SEARCH_XML<br>";
	include_once(PATH_INC."func/admin.php");
	//ob_start();
	include_once(PATH_INC."search.php");
	//$out = ob_get_contents();
	//ob_end_clean();
	//echo"out=$out<br>";
	return $ret_xml;
	}
	function newsearch_xml($conn, $ar)
	{
	//print_r($ar);
	extract($ar);
	$ret_xml="";
	//echo"SEARCH_XML<br>";
	include_once(PATH_INC."func/admin.php");
	//ob_start();
	include_once(PATH_INC."search2.php");
	//$out = ob_get_contents();
	//ob_end_clean();
	//echo"out=$out<br>";
	return $ret_xml;
	}
//======================================================
//выдает заголовок для xml - для нормального преобразования в DOM
function xml_header()
	{
	$ret="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
	";
	return $ret;
	}
//======================================================
//добавление ссылок
function add_xml_links($conn, $ar_var, $ar=array())
{
	if($show_err_test)
		echo __LINE__." add_xml_links<br>\r\n";
	extract($ar);
	extract($ar_var);
	//echo struct_xml($xml_page);
	//преобразуем в дерево
	$xmlstr=utf8_encode(convert_cyr_string(xml_header().$xml_page, "w", "i"));
if(!$dom=domxml_open_mem($xmlstr))
	{
	echo __LINE__." Error(add_xml_links) - domxml_open_mem<br>";
	echo struct_xml($xml_page);
	}
//echo "dom=".$dom."<br>/r/n";
//exit();
$layout_str=implode(",", array_keys($layout_ar));
//$sel_link="SELECT id, layout_id, name, xml_name_path FROM ti_layout_link_ref WHERE layer_id=$layer_id";
$sel_link_ar['link']="SELECT id, layout_id, name, xml_name_path, layer_id, is_in_theme, is_layout_level FROM ti_layout_link_ref WHERE layout_id IN ($layout_str)";

//бинарники
$sel_link_ar['bin']="SELECT id, column_id, layout_id, name, xml_name_path, xml_path, download_flag FROM TI_LAYOUT_REF_DOWNLOAD_V WHERE layout_id IN ($layout_str)";
/*if($search)
	$elements=$dom->get_elements_by_tagname("*");
else*/
if($show_err_test)
	{
	echo __LINE__." DOM_XML=".DOM_XML."<br>\r\n";
			ini_set('display_errors', 1);
		ini_set('error_reporting', 7);
	}
if(DOM_XML==1)
	$elements=$dom->get_elements_by_tagname("CLIENT_AREA");
elseif(DOM_XML==2)
	$elements=$dom->getElementsByTagName("CLIENT_AREA");

if($show_err_test)
	{
	if ($elements)
		echo __LINE__." elements YES <br>\r\n";
	else
		echo __LINE__." elements NO <br>\r\n";
	}
//$element=$dom->get_elements_by_tagname("*");
foreach($elements as $element)
	{
	if($show_err_test)
		echo __LINE__." element <br>\r\n";
	break;
	}

if($show_err_test)
	{
	if ($element)
		echo __LINE__." element YES <br>\r\n";
	else
		echo __LINE__." element NO <br>\r\n";
	}
//$element=array_shift($elements);
if(!$element)
	{
	//$element=$elements[0];
	//$element=array_shift($elements);
	}
foreach($sel_link_ar as $type_link=>$sel_link)
{
if($show_err_test)
	echo __LINE__."sel_link($type_link)=$sel_link<br><br>";
$res_link=db_getArray($conn, $sel_link);
foreach($res_link as $k_link=>$v_link)
	{
	if($show_err_test)
		{
		echo"<br>".__LINE__." link_id=".$v_link['ID']."<br>\r\n";
		echo"<pre>";
		print_r($v_link);
		}
	$v_link['XML_NAME_PATH']=ereg_replace("^/", "", $v_link['XML_NAME_PATH']);
	if($show_err_test)
		echo __LINE__."XML_NAME_PATH=".$v_link['XML_NAME_PATH'].", type_link=$type_link<br>";
	$find_el=ti_get_element_by_path($element, $v_link['XML_NAME_PATH'], array("cnt_flag"=>1, "dom_tmp"=>$dom));
	if($show_err_test)
		{
		echo __LINE__."<br>";
		echo"Count=".$find_el["count"]."<br>";
		}
	if($type_link=="link")
			{
			//параметры ссылки
			$res_link_par=array();
			$sel_link_par="SELECT name, value, filter_type FROM TI_LAYOUT_REF_LINKS_PARAMS_SEL WHERE layout_rl_id=".$v_link['ID'];
			if($show_err_test)
				echo __LINE__." sel_link_par=$sel_link_par<br>";
			$res_link_par=db_getArray($conn, $sel_link_par);
			}
	else//бинарники
		{
			//параметры бинарника
			$res_bin_par=array();
			$sel_bin_par="SELECT name, value FROM ti_layout_ref_download_params WHERE layout_rd_id=".$v_link['ID'];
			if($show_err_test)
				echo __LINE__." sel_bin_par=$sel_bin_par<br>";
			$res_bin_par=db_getArray($conn, $sel_bin_par);

		}
	foreach ($find_el["elements"] as $k_ch=>$v_ch)
		{
		//echo __LINE__."<br>";
		//Проверяем, есть ли уже ссылки
		$link=ti_get_element_by_path($v_ch, "./LayerLinks", array("cnt_flag"=>1, "dom_tmp"=>$dom));
		//echo __LINE__."link_count=".$link["count"]."<br>";
		if($link["count"]>0)
			{
			//$link=array_shift($link["elements"]);//$link["elements"][0];
			//echo __LINE__."<br>";
			foreach($link["elements"] as $link)
				break;
			}
		else
			{
			//добавляем новый узел
			//echo __LINE__."<br>";
			if(DOM_XML==1)
				$link = $v_ch->append_child($dom->create_element("LayerLinks"));
			elseif(DOM_XML==2)
				{
				//echo __LINE__."<br>";
				$link = $dom->createElement('LayerLinks');
				$v_ch->appendChild($link);

				//$link = $v_ch->append_child($dom->create_element("LayerLinks"));
				}
			}


		if($type_link=="link")
			{
			//echo"Add link -".$v_link['NAME']." <br>";
			$url="/index.html?layer_id=".$v_link['LAYER_ID'];
			$link_par['layer_id']=$v_link['LAYER_ID'];
			//$link_xml="<LinkParam URL=\"$url\" name=\"".$v_link['NAME']."\">";
			foreach($res_link_par as $k_lp=>$v_lp)
				{
				$filter_type=$v_lp['FILTER_TYPE'];
				if($filter_type=="var")
					{
					$var_name=$v_lp['VALUE'];
					//echo"filter_type=$filter_type, var_name=$var_name<br>";
					if(eregi("^[a-z]+", $var_name))
						$val=$$var_name;
					else
						{
						$val=$var_name;
						}
					//echo"v val=$val<br>";
					}
				elseif($filter_type=="layout")
					{
					//echo"ins=".$v_lp['VALUE']."<br>";
					$find_par=ti_get_element_by_path($v_ch, $v_lp['VALUE'], array("cnt_flag"=>1, "dom_tmp"=>$dom));
					if($find_par["count"]>0)
						{
						foreach($find_par["elements"] as $find_par_1)
							break;
						//$find_par_1=$find_par["elements"][0];
						//echo __LINE__."<br>";
						if(is_object($find_par_1))
							{
							if(DOM_XML==1)
								$val=$find_par_1->get_content();
							elseif(DOM_XML==2)
								$val=$find_par_1->nodeValue;
							}
						else
							$val="";
						}
					//echo __LINE__." val=$val<br>";
					}
				else
					{
					$val="";
					}
				if($val)
					{
					$url=href_prep($url, $v_lp['NAME']."=".$val);
					$link_xml.="<".$v_lp['NAME'].">$val</".$v_lp['NAME'].">";
					$link_par[$v_lp['NAME']]=$val;
					}
				}
			//$link_xml.="</LinkParam>"
			if(!$v_link['IS_IN_THEME'])//без меню
				$url= href_prep($url, "short=1");
			//echo __LINE__."linkurl=$url<br>";
			}
		else//бинарники
			{
			//echo"Bin<br>";
			//echo __LINE__." Bin<br>";
			$link_xml="";
			$url="/images/image_all.html?col_id=".$v_link['COLUMN_ID'];
			$find_par=ti_get_element_by_path($v_ch, $v_link['XML_PATH'], array("dom_tmp"=>$dom));
			//echo $v_link['XML_NAME_PATH'].", xml_path=".$v_link['XML_PATH']."<br>";
			if($v_link['DOWNLOAD_FLAG'])//скачивать картинку
				$url= href_prep($url, "download=1");
			foreach($res_bin_par as $k_lp=>$v_lp)
				{
				$url=href_prep($url, $v_lp['NAME']."=".$v_lp['VALUE']);
				}
			if(DOM_XML==2 || is_object($find_par))
				{
				//$val=$find_par->get_content();
				if(DOM_XML==1)
						$val=$find_par->get_content();
				elseif(DOM_XML==2)
						$val=$find_par->nodeValue;
				if($val)
					$url= href_prep($url, "id=".$val);
				else
					$url="";
				//echo __LINE__." val=$val, url=$url<br>";
				}
			else
				{
				//echo"Not found-".$v_link['XML_PATH']."<br>";
				$val="";
				$url="";
				}

			}
			//echo"url=$url<br>";
		if($url)
			{
			//echo __LINE__."<br>";
			//добавляем ссылку
			if(DOM_XML==1)
				{
				$link2=$link->append_child($dom->create_element("Link"));
				$link2->set_attribute("name", $v_link['NAME']);
			/* Функция create_text_node создаёт текстовый узел. Его добавим как содержимое
			элемента title. Сохранять добавленный элемент в переменную необязательно -
			только если вы хотите с ним после добавления работать.
			надо не забыть перекодировать*/
			//echo"url=".$url."<br>";
			//echo"i-".convert_cyr_string($url, "w", "i")."<br>";
			//echo"utf-".utf8_encode(convert_cyr_string($url, "w", "i"))."<br><br>";
				$link2->append_child($dom->create_text_node(utf8_encode(convert_cyr_string($url, "w", "i"))));
				}
			elseif(DOM_XML==2)
				{
				//echo __LINE__." url=$url<br>";
				$link2 = $dom->createElement('Link', str_replace("&", "&amp;", utf8_encode(convert_cyr_string($url, "w", "i"))) );
				//echo __LINE__."<br>";
				$link3=$link->appendChild($link2);
				//echo __LINE__."<br>";
				$link3->setAttribute("name", $v_link['NAME']);
				if(count($link_par)>0)
					{
				//echo __LINE__."<br>";
					$link2 = $dom->createElement('LinkParam');
				//echo __LINE__."<br>";
					$link3=$link->appendChild($link2);
				//echo __LINE__."<br>";
					$link3->setAttribute("name", $v_link['NAME']);
					$link3->setAttribute("URL", "/index.html");
				//echo __LINE__."<br>";
					foreach($link_par as $k_par=>$v_par)
						{
						//echo "$k_par=>$v_par<br>";
						$param_l=$dom->createElement($k_par, $v_par);
						$param_l2=$link2->appendChild($param_l);
						}
					$link_par=array();
				/* <LinkParam URL="http://www.press.rzd.ru/news/public/press" name="ReleaseCard">
               <STRUCTURE_ID>654</STRUCTURE_ID>
               <layer_id>4069</layer_id>
               <refererVpId>1</refererVpId>
               <refererPageId>704</refererPageId>
               <refererLayerId>4065</refererLayerId>
               <id>76494</id>
            </LinkParam>*/
					//
					}
				}
			//echo __LINE__."<br>";
			}
		}

	}
$res_link=array();
}//foreach($sel_link_ar
if($show_err_test)
	echo __LINE__."<br>\r\n";
if(DOM_XML==1)
	$xml_page=str_replace(trim(xml_header()), "", convert_cyr_string(utf8_decode($dom->dump_mem()), "i", "w"));
elseif(DOM_XML==2)
		$xml_page=str_replace(trim(xml_header()), "", convert_cyr_string(utf8_decode($dom->saveXML()), "i", "w"));
if($show_err_test)
	echo __LINE__."<br>\r\n";
return $xml_page;
}//конец add_xml_links

//======================================================
//получение xml всех отображений слоя по id слоя
function layer_xml($conn, $layer_id, $ar, $ad_arr, $ret_type=1)
{
	//если ret_type=2 - то возвращаем еще и запросы по отображениям
	extract($ar);
	extract($ad_arr);
	global $_SERVER;
	//echo __LINE__."<br><pre>";
	//print_r($_SERVER);
	//print_r($ad_arr);
	$xml_header=xml_header();
$sel_lay_list="SELECT lg.layout_id, lg.sort_order, g.class_name"
					." FROM cham_layer_gather lg, cham_gather g"
					." WHERE lg.layer_id=$layer_id AND lg.gather_id=g.id"
					." ORDER BY lg.sort_order ASC";
if($show_err_test)
	echo __LINE__."sel_lay_list=$sel_lay_list<br><pre>\r\n";
if($our_const_xml)
	$xml_page.="<OUR_CONST>".$our_const_xml."</OUR_CONST>";
$res_lay_list=db_getArray($conn, $sel_lay_list);
//print_r($res_lay_list);

foreach($res_lay_list as $k=>$v)
	{
	if($v['CLASS_NAME']=="standard")
		{
		$layout_id=$v['LAYOUT_ID'];
		if($show_err_test)
			echo __LINE__." layout_id=$layout_id, ret_type=$ret_type<br>\r\n";
		$layout_ar[$layout_id]=$v;
		if($ret_type==2)
			{
			$layout_xml_ar=layout_xml($conn, $layout_id, $ar, $layer_id, $xml_page, $ad_arr, $ret_type);
			$xml_page.=$layout_xml_ar['xml_page'];
			$ret['id_sql'][$layout_id]=$layout_xml_ar['id_sql'];
			}
		else
			$xml_page.=layout_xml($conn, $layout_id, $ar, $layer_id, $xml_page, $ad_arr, $ret_type);
		if($show_err_test)
			echo __LINE__." xml_page=$xml_page<br>\r\n";
		if($show_err_test)
			echo __LINE__." FINISH layout_id=$layout_id<br>\r\n";
		}
	elseif($v['CLASS_NAME']=="search")//поиск
		{
		$xml_page.=search_xml($conn, $ar);
		}
	elseif($v['CLASS_NAME']=="newsearch")//поиск
		{
		$xml_page.=newsearch_xml($conn, $ar);
		}
	}//foreach($res_lay_list as $k=>$v)

//=======================================
//echo ord('№');
$xml_page="<CLIENT_AREA><L_DEFAULT/>".$xml_page."</CLIENT_AREA>";
$xml_page=str_replace(chr(185), '&amp;#8470;', $xml_page);//№
$xml_page=str_replace(chr(150), '&amp;ndash;', $xml_page);//длинное тире
$xml_page=str_replace(chr(133), '&amp;hellip;', $xml_page);//многоточие
if($show_err_test)
	echo"\r\n".__LINE__." xml_page=$xml_page\r\n<br>\r\n\r\n";
//ссылки
$xml_page=add_xml_links($conn, array("xml_page"=>$xml_page, "layout_ar"=>$layout_ar), $ar);
if($show_err_test)
	echo "\r\n\r\n".__LINE__." 3 xml_page=$xml_page<br>\r\n";
if($ret_type==2)
	{
	$ret['xml_page']=$xml_page;
	return $ret;
	}
else
	return $xml_page;
}
//конец layer_xml
//====================
function layer_xsl($conn, $layer_id)
	{
	//echo"<br>layer_id=$layer_id<br><br>";
	$sel_xsl="SELECT v.vfile_path, v.vfile_name FROM cham_viewer v, cham_layer l WHERE l.viewer_id=v.id AND l.id=$layer_id";
	//echo"sel_xsl=$sel_xsl<br>";
	$res_xsl=db_getArray($conn, $sel_xsl, 2);
	//print_r($res_xsl);
	return $res_xsl;
	}
//====================
//подготовка xsl файла для обработки - замена include на сам файл
function xsl_include_prep($file_name, $file_path)
	{
	$file_xsl=XSL_PATH.ereg_replace("^./", "", $file_path)."/".$file_name;
	//echo"file_xsl=$file_xsl<br>";
	$fp=fopen($file_xsl, "r");

	while($xsladd=fread($fp, 100))
		{
		$xslData.=$xsladd;
		}
	fclose($fp);

	//вместо include подставляем содержимое файла
	if(eregi('<xsl:include', $xslData))
			{
			$pos_begin=0;

			while($pos_inc=strpos($xslData, '<xsl:include', $pos_start))
				{
				$xsl_ins="";
				$pos_end=strpos($xslData, '>', $pos_inc);
				$xsladd_file_str=substr($xslData, $pos_inc, $pos_end-$pos_inc+1);
				//echo"\r\nxsladd_file_str=$xsladd_file_str<br>\r\n";
				$del_p=strpos($xsladd_file_str, 'href="');
				$xsladd_file=substr($xsladd_file_str, $del_p+6);
				$del_p=strpos($xsladd_file, '"');
				$xsladd_file=substr($xsladd_file, 0, $del_p);
				$xsl_ins=xsl_include_prep($xsladd_file, $file_path);
				$xsl_ins=substr($xsl_ins, strpos($xsl_ins, "<xsl:template"));
				$xsl_ins=str_replace("</xsl:stylesheet>", "", $xsl_ins);
				//echo"\r\n\r\n xsl_ins=$xsl_ins\r\n\r\n";
				$xslData=str_replace($xsladd_file_str, $xsl_ins, $xslData);
				$pos_start=$pos_inc+1;
				}
			}
	//echo"xslData=$xslData\r\n\r\n\r\n";
	return $xslData;
	}

//===========================================================================
//структурирование xml в красивом виде
function struct_xml($xml_text)
	{
	$xml_ar=explode("<", $xml_text);
	$i=0;
	foreach($xml_ar as $val)
		{
		if($val)
			{
			$prev_end=$end;
			if(eregi("^\/", $val))
				$end=1;
			else
				$end=0;
			//echo"val=$val, end=$end<br>";
			if($end && $prev_end)
				$i--;
			if($i<0)
				$i=0;
			if(!$end || ($end && $prev_end))
				$ret.="\r\n".str_repeat(" ", $i*2);

			$ret.="<".$val;

			if($end && !$prev_end)
				$i--;
			elseif(!$end && !strpos($val, "/>"))
				$i++;
			}
		}
	return $ret;
	}
//============================================================
//РЕЗУЛЬТАТЫ ПОИСКА
//============================================================
function show_search_page($conn, $show_search_page_ar, $res_list_ar, $ar=array())
	{
	extract($ar);
	//$pagelist=pagelist($show_search_page_ar["res_count"], $res_on_page, $show_search_page_ar["page"], "&search_text=".$show_search_page_ar["search_text"]."&nav_id=$nav_id");
	//echo"pagelist=$pagelist<br>";
	if(is_array($res_list_ar))
	{
	foreach($res_list_ar as $k=>$v)
		{
		//echo"$k - ".$v['res_object']['OT_NAME']."<br>";
		$res_list_ar[$k][DATE_MAIN]=date_format_ar($res_list_ar[$k][DATE_MAIN], "", DEF_DATE_FORMAT);
		$other_text="";
		$i=1;
		$search_template="";
		//конец обнуления шаблонов для конкретной записи
		if(is_array($v['text']['other_text']))
			{
			foreach($v['text']['other_text'] as $k_other=>$v_other)
                        {
						if($v_other)
							{
							$res_list_ar[$k]['other_text_ar'][$i]['ot_name']=$k_other;
							$res_list_ar[$k]['other_text_ar'][$i]['ot_text']=$v_other;
							}
						//echo"$k_other - $v_other<br>";
                        if($other_text)
                                $other_text.="<br>";
                        if(is_numeric($k_other))
                                $other_text.=$v_other;
                        else
                                $other_text.=$k_other.": ".$v_other;
                        if($i<count($res_list_ar))
                                $last_flag=0;
                        else
                                $last_flag=1;
                        $i++;
                        }

			}
		echo"$show_search_page_ar<pre>";
		print_r($show_search_page_ar);
		echo"$res_list_ar<pre>";
		print_r($res_list_ar);
		$search_template=$res_list_ar[$k]['SEARCH_TEMPLATE'];
		$res_list_ar[$k]['num']=$k;
		}
	}
	}
//===============================================
//преобразование xml в html
//===============================================
function xml_to_html($xml, $layer_xsl, $server_name)
	{
	if(DEFINED("XML_LOG_FILE"))
		{
		$struct_xml=struct_xml($xml);
		//echo"XML_LOG_FILE=".XML_LOG_FILE."<br>";
		//echo "$struct_xml\r\n";
		//file_log (XML_LOG_FILE, $REQUEST_URI, "w");
		file_log(XML_LOG_FILE, $struct_xml, "w");
		}
	//$xml=str_replace('№', '&#8470;', $xml);

	//echo __LINE__."xml=$xml<br>\r\n\r\n";
	$xmlData = "<?xml version=\"1.0\" encoding=\"Windows-1251\"?>
	".$xml;

	if(ereg("loc.fntr", $server_name) || ereg("loc.admin.fntr", $server_name)
		|| ereg("loc.agency", $server_name)||ereg("loc.admin.agency", $server_name)
		|| ereg("loc.tehnosk", $server_name)||ereg("loc.admin.tehnosk", $server_name)
		|| ereg("loc.ettc", $server_name) || ereg("loc.admin.ettc", $server_name)
		|| ereg("loc.dvgazeta", $server_name)||ereg("loc.admin.dvgazeta", $server_name)
		|| ereg("loc.kasyanov", $server_name)||ereg("loc.admin.kasyanov", $server_name)
		|| ereg("loc.admin.vfps", $server_name)||ereg("loc.vfps", $server_name))//Windows
		{
		//echo __LINE__." Windows<br>";
		//echo __LINE__."xmlData=$xmlData<br>\r\n\r\n";
		//echo  __LINE__."XSL_PATH=".XSL_PATH."<br>";
		$xslData=xsl_include_prep($layer_xsl['VFILE_NAME'], $layer_xsl['VFILE_PATH']);
		//echo __LINE__."xslData=$xslData<br>";
		//приступаем к генерации html
		if(DOM_XML==1)
			{
		$xh = xslt_create();
		$arguments = array(
			'/_xml' => $xmlData,
			'/_xsl' => $xslData
			);
		//echo __LINE__."xslData=$xslData<br>";
		if($ret = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments))
			{
			$ret=trim(str_replace('<?xml version="1.0" encoding="Windows-1251"?>', "", $ret));
			//echo"Ok<br>";
			}
		else
			{
			print ("There was an error that occurred in the XSL transformation...\n");
			print ("\tError number: " . xslt_errno($xh) . "\n");
			print ("\tError string: " . xslt_error($xh) . "\n");
			exit;
			}
			}
		else//(DOM_XML==2)
			{
			//echo __LINE__.'xsl';
			$xslDoc = new DOMDocument();
			$xsl_file=XSL_PATH.ereg_replace("^./", "", $layer_xsl['VFILE_PATH']).$layer_xsl['VFILE_NAME'];
			//echo __LINE__.'xsl = '.$xsl_file;
			$xslDoc->load($xsl_file);
			//echo __LINE__.'xsl';
			$proc = new XSLTProcessor();
			//echo __LINE__.'xsl';
			$proc->importStylesheet($xslDoc);
			//echo __LINE__.'xsl';
			$ret= $proc->transformToXML(domxml_open_mem($xmlData));
			}
		}
	else//Unix
		{
		//echo __LINE__." Unix<br>";

		//echo __LINE__." xmlData=".struct_xml($xmlData)."<br>";
		$filename=rand();
		//echo"filename=$filename<br>";
		$filename="/tmp/".$filename.".xml";
		if(!$file = fopen($filename, "w"))
			{
			echo __LINE__."Невозможно открыть файл ".$filename." для записи xml!!!<br>\r\n$str\r\n<br>";
			}
		else
			{
			fwrite($file, $xmlData);
			fclose ($file);
			$file_xsl=str_replace("//", "/", XSL_PATH.ereg_replace("^./", "", $layer_xsl['VFILE_PATH'])."/".$layer_xsl['VFILE_NAME']);
			//echo  __LINE__." XSL_PATH=".XSL_PATH.", xsltproc $file_xsl $filename<br>";
			ob_start();
			system("xsltproc $file_xsl $filename");
			$ret=ob_get_contents();
			ob_clean();
			$ret=trim(str_replace('<?xml version="1.0" encoding="Windows-1251"?>', "", $ret));
			//echo __LINE__." ret=$ret<br>";
			$del=unlink($filename);
			//echo"del=$del<br>";
			}
		}//Unix
	//echo __LINE__." time =".(mktime()-$start_func)."<br>";
	return $ret;
	}
//конец  xml_to_html
//=================================
//подготовка логического url
function logic_url($ar)
	{
	//echo"<pre>";
	//print_r($ar);
	ksort($ar, SORT_STRING);
	//reset($ar);
	//print_r($ar);
	foreach($ar as $k_va=>$v_va)
		{
		if(var_test($k_va) && $k_va!='layer_id' && $k_va!='nc' && substr($k_va, 0, 2)!="__")
			$logic_url=add_text($logic_url, "$k_va=$v_va", "&");
		}
	return $logic_url;
	}
//=========================
//добавление в кеш
function add_cache($conn, $layer_id, $logic_url, $html_text, $xml, $id_sql_ar)
	{
	//echo"conn=$conn<br><pre>";
	//$sel_test="SELECT id FROM cham_layer WHERE id=$layer_id";
	//$res_test=db_getArray($conn, $sel_test, 2);
	//print_r($res_test);
	db_begin($conn);
	$ins_cache=1;
	$ins_cache=db_insert($conn, "cham_cache", array("name", "html_text", "layer_id"), array($logic_url, $html_text, $layer_id), array("varchar", "text", "int"));
	//echo __LINE__." ins_cache=$ins_cache<br><pre>";
	//print_r($id_sql_ar);
	//вставка части кешей - на основе xml
	if($ins_cache)
		{
		//echo"<pre>$xml";
		//преобразуем в дерево
		//echo __LINE__." ".struct_xml($xml);
		$xmlstr=utf8_encode(convert_cyr_string(xml_header().$xml, "w", "i"));

		if(!$dom=domxml_open_mem($xmlstr))
			{
			echo"Error - domxml_open_mem<br>";
			//echo struct_xml($xml_page);
			}
		else
			{
			//переходим на уровень client_area
			if(DOM_XML==1)
				$elements=$dom->get_elements_by_tagname("CLIENT_AREA");
			elseif(DOM_XML==2)
				{
				$elements=$dom->getElementsByTagName("CLIENT_AREA");
				//echo __LINE__.struct_xml($xml)."<br>";
				}
			foreach($elements as $element)
				break;
			//$element=$elements[0];
			//echo __LINE__.'<br>';
			if(DOM_XML==1)
				{
				$layouts=$element->get_elements_by_tagname("LAYOUT_ID");
				}
			elseif(DOM_XML==2)
				{
				$val_tmp=ti_get_element_by_path($element, ".//LAYOUT_ID", array("cnt_flag"=>1, "dom_tmp"=>$dom));
				$layouts=$val_tmp['elements'];
				//print_r($val_tmp);
				//echo __LINE__." count=".$val_tmp['count']."<br>";
				//$layouts=$element->getElementsByTagName("LAYOUT_ID");
				}
			//echo __LINE__.'<br><pre>';
			//print_r($layouts);
			foreach($layouts as $v_l)
				{
				if(DOM_XML==1)
					{
					//echo"layouts[$k_l]-".$v_l->get_content()."<br>";
					$layout_id=$v_l->get_content();
					//поднимаемся на уровень выше
					$l_up=$v_l->parent_node();
					}
				elseif(DOM_XML==2)
					{
					//echo __LINE__.'<br>';
					//echo __LINE__."layouts[$k_l]-".$v_l->nodeValue()."<br>";
					//echo __LINE__."<hr> Found layout - ".$v_l->nodeValue."<br>";
					$layout_id=$v_l->nodeValue;
					//echo __LINE__.'<br>';
					//поднимаемся на уровень выше
					$l_up=$v_l->parentNode;
					//echo __LINE__.'<br>';
					}
				//echo __LINE__." layout_id=$layout_id<br>";
				if($pre_layout_id==$layout_id)
					$id_sql_index++;
				else
					$id_sql_index=0;
				$pre_layout_id=$layout_id;
				//пролистовка
				$prev_id="";
				$next_id="";
				$last_id="";
				$first_id="";
				if(DOM_XML==1)
					{
					$el_prevs=$l_up->get_elements_by_tagname("PrevPage");
					if(is_array($el_prevs))
						{
						$el_prev=$el_prevs[0];
						if(is_object($el_prev))
							$prev_id=$el_prev->get_attribute("id_link");
						}
					$el_nexts=$l_up->get_elements_by_tagname("NextPage");
					if(is_array($el_nexts))
						{
						$el_next=$el_nexts[0];
						if(is_object($el_next))
							$next_id=$el_next->get_attribute("id_link");
						}
					$el_lasts=$l_up->get_elements_by_tagname("LastPage");
					if(is_array($el_lasts))
						{
						$el_last=$el_lasts[0];
						if(is_object($el_last))
							$last_id=$el_last->get_attribute("id_link");
						}
					$el_firsts=$l_up->get_elements_by_tagname("./PageTurning/FirstPage");
					if(is_array($el_firsts))
						{
						$el_first=$el_firsts[0];
						if(is_object($el_first))
							$first_id=$el_first->get_attribute("id_link");
						}
					}//if(DOM_XML==1)
				elseif(DOM_XML==2)
					{
					//echo __LINE__."<br>";
					//echo"l_up=".$l_up->tagName."<br>";
					$el_prevs=ti_get_element_by_path($l_up, "./PageTurning/PrevPage", array("dom_tmp"=>$dom, "cnt_flag"=>1));
					//echo __LINE__."<br>";
					if(is_array($el_prevs))
						{
						foreach($el_prevs['elements'] as $el_prev)
							{
							$prev_id=$el_prev->getAttribute("id_link");
							break;
							}
						//echo __LINE__."<br>";
						//if(is_object($el_prev))

						//echo __LINE__."<br>";
						}
					$el_nexts=ti_get_element_by_path($l_up, "./PageTurning/NextPage", array("dom_tmp"=>$dom, "cnt_flag"=>1));
					if(is_array($el_nexts))
						{
						foreach($el_nexts['elements'] as $el_next)
							{
							$next_id=$el_next->getAttribute("id_link");
							break;
							}
						}
					$el_lasts=ti_get_element_by_path($l_up, "./PageTurning/LastPage", array("dom_tmp"=>$dom, "cnt_flag"=>1));
					//echo __LINE__." count=".count($el_lasts['count'])."<br>";
					foreach($el_lasts['elements'] as $el_last)
							{
							//echo __LINE__."<br>";
							$last_id=$el_last->getAttribute("id_link");
							break;
							}

					$el_firsts=ti_get_element_by_path($l_up, "./PageTurning/FirstPage", array("dom_tmp"=>$dom, "cnt_flag"=>1));
					if(is_array($el_firsts))
						{
						foreach($el_firsts['elements'] as $el_first)
							{
							$first_id=$el_first->getAttribute("id_link");
							break;
							}
						}
					//echo __LINE__."<br>";
					}//elseif(DOM_XML==2)
				//echo __LINE__.": $prev_id, $next_id, $last_id, $first_id<br>";
				//конец пролистовки
				if(DOM_XML==1)
					{
					$tabs=$l_up->get_elements_by_tagname("TABLE_ID");
					}
				elseif(DOM_XML==2)
					{
					$tabs_tmp=ti_get_element_by_path($l_up, ".//TABLE_ID", array("cnt_flag"=>1, "dom_tmp"=>$dom));
					$tabs=$tabs_tmp['elements'];
					//echo __LINE__." table_count=".$tabs_tmp['count']."<br>";
					}

				foreach($tabs as $k_t=>$v_t)
					{
					if(DOM_XML==1)
						{
						//echo"tab - $k_t-".$v_t->get_content()."<br>";
						$table_id=$v_t->get_content();
						//поднимаемся на уровень выше
						$t_up=$v_t->parent_node();
						$t_up_up=$t_up->parent_node();
						if($t_up_up==$l_up)//таблица - верехний уровень
							{
							//echo"$table_id - UP<BR>";
							$id_sql=$id_sql_ar[$layout_id];
							}
						else
							$id_sql=BASE_NULL;
						}//if(DOM_XML==1)
					elseif(DOM_XML==2)
						{
						//echo __LINE__." Found table - $v_t->nodeValue<br>";
						//echo"tab - $k_t-".$v_t->get_content()."<br>";
						$table_id=$v_t->nodeValue;
						//echo __LINE__."<br>";
						//поднимаемся на уровень выше
						$t_up=$v_t->parentNode;
						$t_up_up=$t_up->parentNode;
						//echo __LINE__."<br>";
						if($t_up_up==$l_up)//таблица - верехний уровень
							{
							//echo"$table_id - UP<BR>";
							$id_sql=$id_sql_ar[$layout_id];
							//echo __LINE__."<br>";
							}
						else
							$id_sql=BASE_NULL;
						}//if(DOM_XML==2)
					if(is_array($id_sql))
						$id_sql=$id_sql[$id_sql_index];
					//echo __LINE__."<br>";
					$el_id=ti_get_element_by_path($t_up, "ID", array("dom_tmp"=>$dom));
					//echo __LINE__."<br>";
					if(is_object($el_id))
						{

						if(DOM_XML==1)
							$rel_id=$el_id->get_content();
						elseif(DOM_XML==2)
							$rel_id=$el_id->nodeValue;
						//echo __LINE__."<br>";
						//echo"id - $rel_id<br>";
						if(!$prev_id && $next_id)
							$prev_id=$rel_id;
						elseif($prev_id && !$next_id)
							$next_id=$rel_id;
						if(!$first_id && $last_id)
							$first_id=$prev_id;
						elseif($first_id && !$last_id)
							$last_id=$next_id;

						$ins_cache_part=db_insert($conn, "cham_cache_part", array("cache_id","table_id", "rel_id", "layout_id", "prev_id", "next_id", "last_id", "first_id", "id_sql"), array($ins_cache, $table_id, $rel_id, $layout_id, $prev_id, $next_id, $last_id, $first_id, $id_sql), array("int", "int", "int", "int", "int", "int", "int", "int", "varchar"));
						}
					}
				}

			}
		}
	db_commit($conn);
	//echo __LINE__.' END add_cache<hr><hr><br>';

	}
?>
<?
/**
* @return void
* @param unknown $conn
* @param unknown $arm_id
* @param unknown $entity_id
* @param unknown $table_id
* @param unknown $field_id
* @param unknown $name
* @param unknown $old_value
* @param unknown $new_value
* @desc Делает запись в лог таблице
*/
function full_log($conn, $arm_id, $entity_id, $name, $table_id)
{
        global $REMOTE_ADDR, $REMOTE_USER;
		$user=$REMOTE_USER?$REMOTE_USER:USER_NAME;


        $ins_log=db_insert($conn, TABLE_PRE."logs", array( "arm_id", "entity_id", "name", "user_id", DATE_MAIN, "ip", "table_id", "login"),
                                                    array( $arm_id, ($entity_id?$entity_id:BASE_NULL), $name, (defined("g_USER_ID")?g_USER_ID:BASE_NULL), db_sysdate(), $REMOTE_ADDR, $table_id, $user),
                                                    array( "int", "int", "varchar", "int", "date", "varchar", "int", "varchar"));//, array("show_ins"=>1));
        //echo"ins_log_q=$ins_log_q<br>";
        if(!$ins_log)
        {
                echo"Внимание: невозможно внести запись в логи.<br>";
                //echo"ins_log_q=$ins_log_q<br>";
        }
}
function showselect($table, $val, $name, $order, $setval="", $addition="", $namesel="", $nullval=0, $about="")
{
        global $conn;
        if(!$order)
                $order=$name;
        if ($about != "")
                $about_str = ", ".$about;
        $sel_q="select $val, $name $about_str from $table order by $order";
        //echo"sel_q=$sel_q<br>";
        $sel=db_query($conn, $sel_q);
        if(!$namesel)
        $namesel=$val;
        echo"<select name=\"$namesel\" $addition>";
        if($nullval)
        optioninp("", "---");
        $res_sel=db_getArray($conn, $sel);
        //while($res=db_fetch_row($sel))
        foreach($res_sel as $k=>$res)
        {
                echo"<option value=\"".$res[strtoupper($val)]."\"";
                if($setval==$res[strtoupper($val)])
                echo" selected";
                echo">".$res[strtoupper($name)];
                if ($about != "")
                        echo " - ".$res[strtoupper($about)];

        }
        echo"</select>";
}
function showselect_ar($ar)
         {
         //echo"SHOWSELECT_AR<br>";
         //echo"<pre>";
         //print_r($ar);
         extract($ar);
         //echo"nullval=$nullval<br>";
		 /*
         echo"def_val_ar=$def_val_ar<br>";
         foreach($def_val_ar as $k=>$v)
                 echo"def_val_ar[$k]=>$v<br>";
         */

         if($type=="mult")
            {
            //echo"mult - $def_sel $id_val";
            $addit.=" multiple";
            if($from=="topicm" && !eregi("size", $addit))
               {
               $addit.=($down_ar['count']<40?' size="'.$down_ar['count'].'"':' style="height:100%;"');
               }
            if($def_sel && $id_val)
               {
               $def_sel.=$id_val;
               $def_res=db_getArray($conn, $def_sel);
               //echo"def_sel=$def_sel<br>";
               foreach($def_res as $k=>$v)
                       {
                       $def_val_ar[$v['ID']]=$v['ID'];
                       }
               }
            /*
            if(count($down_ar)<=17)
                  $addit.=' size="all"';
            else
                $addit.=' size="20"';
            */
            }//конец условия, что мультиселект
		else
			 {
			 if($from=="topic" && !eregi("size", $addit))
               {
               $addit.=(count($down_ar)<40?' size="'.count($down_ar).'"':' size="40" style="height:100%;"');
               }
			 }
         //echo"def_val=$def_val, ".is_numeric($def_val)."<br>";
         if($div)
            div($div);
		 //echo"down_ar=$down_ar<br><pre>";
		 //print_r($down_ar);
		 if(!$no_select || $def_val)
			{
			echo"<select name=\"$name\" $addit>";
			if($nullval)
				{
				optioninp("", "---", "", "", $def_val);
				}
			if($null_sel)
				{
				optioninp(BASE_NULL, "Не определен", "", ($from?" ID=\"Не определен\"":""), ($def_val_ar?(in_array(BASE_NULL,$def_val_ar)?BASE_NULL:""):$def_val));
				}
			if($all_sel)
				{
				optioninp("all", "---");
				}
			foreach($down_ar as $k=>$v)
                 {
                 if($type=="mult" && $def_val_ar)
                    {
                    $def_val=$def_val_ar[$k];
                    }

                 if(is_int($k) || (!$k))//иначе показываются запросы
                    {
                    optioninp($k, eregi_replace("^>>>", "", str_replace("!!!", ">>>", $v)), "", "id=\"$v\"", ((is_numeric($def_val))?$def_val:BASE_NULL));
                    }
				//else
				//	echo "ERROR ".__LINE__."<br>";
				//echo"$k-$v<br>";
                 }
			echo"</select>";
			}
		else
			{
			echo"<select size='3' multiple disabled></select>";
			}
         if($div)
            divend();
         if($or_and)
            {
            //div('id="or_and"');
            br();
            //echo"name=$name<br>";
            $or_and_name=ereg_replace("\[\]", "", $name)."_or_and";
            global $$or_and_name;
            $or_and_val=$$or_and_name;
            //echo"or_and_name=$or_and_name, or_and_val=$or_and_val<br>";
            forminput("radio", $or_and_name, "1", "", (($or_and_val==1)?"":" checked")); echo"Или";
            forminput("radio", $or_and_name, "2", "", (($or_and_val==2)?" checked":""));echo"И";
            //divend();
            }
         }
//================================================
function pagelist($max_count, $ITEMS_ON_PAGE, $page=1, $add_page="", $ar=array())
	{
	extract($ar);
	//view_text_link-вид ссылки, 1-номер страницы
	if(!$view_text_link && DEFINED("PAGELIST_VIEW_TEXT_LINK"))
		{
		$view_text_link=PAGELIST_VIEW_TEXT_LINK;
		}
    $PAGES_ON_PAGE=PAGES_ON_PAGE;
    if(!$ITEMS_ON_PAGE)
		$ITEMS_ON_PAGE=10;
    if(!$page)
		$page=1;
    if(!ereg("&a_id=", $add_page))
        {
            global $a_id;
            if($a_id)
            $add_page.="&a_id=$a_id";
        }
    DEFINE("BEGIN_LIST", (($page-1)*$ITEMS_ON_PAGE));
    DEFINE("END_LIST", ($page*$ITEMS_ON_PAGE));
    $number_page=ceil($max_count/$ITEMS_ON_PAGE);
    //echo"number_page=$number_page<br>";
    if($number_page<=1)
		$ret=0;
    else
            {
            if($PAGES_ON_PAGE>$number_page || $page<$PAGES_ON_PAGE)
				$start_page=1;
            else
				$start_page=floor($page/$PAGES_ON_PAGE)*$PAGES_ON_PAGE+1;
            $end_page=(($start_page+$PAGES_ON_PAGE)>$number_page)?$number_page:($start_page+$PAGES_ON_PAGE-1);
			}
	if($max_count>$ITEMS_ON_PAGE)
		{
		if(DEFINED("PAGELIST_TEMPLATE"))//задан шаблон для пролистовки
			{
			$page_template = & new Template(TPL_PATH.PAGELIST_TEMPLATE);
			//$page_template->AddParam("add_page", $add_page);
			if(!$add_page)
				$add_page="&";
			$page_template->AddParam("url", href_prep(g_URL, $add_page));
			if($page>1)
				{
				$page_template->AddParam("prev_num", $page-1);
				}
			if($page<$number_page)
				{
				$page_template->AddParam("next_num", $page+1);
				$page_template->AddParam("last_num", $number_page);
				}
			for($i_page=$start_page; $i_page<=$end_page; $i_page++)
				{
				$ar_page[$i_page]['page']=$i_page;
				$ar_page[$i_page]['start']=($i_page-1)*$ITEMS_ON_PAGE+1;
				$ar_page[$i_page]['end']=(($ITEMS_ON_PAGE*$i_page)<$max_count)?($ITEMS_ON_PAGE*$i_page):$max_count;
				if($i_page==$page)
					$ar_page[$i_page]['link']=0;
				else
					$ar_page[$i_page]['link']=1;
				}
			$page_template->AddParam("ar_page", $ar_page);
			$ret=$page_template->output();
			}
		else//не задан шаблон для пролистовки
			{
			ob_start();
            $tmp_url=href_prep(g_URL, $add_page);
            if($page>1)
                   {
                   href(href_prep(g_URL."?page=1", $add_page), "&laquo;", 'title="В начало" class="pagenator"');
                   nbsp(2);
                   href(href_prep(g_URL."?page=".($page-1), $add_page), "&lt;", 'title="Предыдущая" class="pagenator"');
                   nbsp(2);
                   }

             for($i_page=$start_page; $i_page<=$end_page; $i_page++)
                 {
                 if($i_page>$start_page)
					 echo " | ";
				if($view_text_link=1)//номер страницы
					 {
					$text_link=$i_page;
					 }
				else
					{
					$text_link=(($i_page-1)*$ITEMS_ON_PAGE+1)."-";
					if(($ITEMS_ON_PAGE*$i_page)<$max_count)
						$text_link.=($ITEMS_ON_PAGE*$i_page);
					else
						$text_link.=$max_count;
					}
				if($i_page!=$page)
					//href($g_url."?page=$i_page".$add_page, $text_link);
					href(href_prep(g_URL."?page=$i_page", $add_page), $text_link, 'class="pagenator"');//.blacki
                 else
					 echo $text_link;
                }
              if($page<$number_page)
                   {
                   nbsp(2);
                   href(href_prep(g_URL."?page=".($page+1), $add_page), "&gt;", 'title="Следующая" class="pagenator"');
                   nbsp(2);
                   href(href_prep(g_URL."?page=$number_page", $add_page), "&raquo;", 'title="В конец" class="pagenator"');
                   }
                $ret = ob_get_contents();
                ob_clean();
			}
		}
	else
		$ret="";
   //echo"ret=$ret<br>";
   return $ret;
}
//================================================
function pagelist_xml($max_count, $num_items, $page=1, $page_links_num="", $ar=array())
	{
	extract($ar);
    if($num_items && $num_items<$max_count)
			{
			if(!$page)
				$page=1;
			global $_SERVER;
			$pagelist_xml.="<NUM_ITEMS>".$num_items."</NUM_ITEMS>";
			//echo"id_sql=$id_sql<br>";
			if($page_links_num && $max_count>($num_items*$page_links_num))
				//есть ограничение на количество пролисотовок на странице и оно будет работать
				{
				$ppage=(ceil($page/$page_links_num)-1)*$page_links_num+1;
				$max_ppage=$ppage+$page_links_num-1;
				}
			else
				{
				$ppage=1;
				$max_ppage=ceil($max_count/$num_items);
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
			$pagelist_xml.="<FirstPage>".htmlspecialchars(href_prep($url_page, "page=1"))."</FirstPage>";
			$pagelist_xml.="<PrevPage>".htmlspecialchars(href_prep($url_page, "page=".($page-1)))."</PrevPage>";
			}
		if($page!=ceil($max_count/$num_items))//если не последняя страница
			{
			$pagelist_xml.="<LastPage>".htmlspecialchars(href_prep($url_page, "page=".ceil($max_count/$num_items)))."</LastPage>";
			$pagelist_xml.="<NextPage>".htmlspecialchars(href_prep($url_page, "page=".($page+1)))."</NextPage>";
			}
		//echo"ppage=$ppage<br>";
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
	return $pagelist_xml;
	}
// ********************************************************************
function show_file ($conn, $col_id = 0, $entity_id, $max_size=0)
{
        if ($col_id == 0)
        {
                $table_name=MULT_TAB;
                $column_name=strtolower(MULT_COL);
        }
        else
        {
                $dbmm_sql="select c.name as column_name, t.name as table_name from ".TABLE_PRE."columns c, ".TABLE_PRE."tables t  where c.id = $col_id and c.table_id = t.id";
                $dbmm_param = db_getArray($conn, $dbmm_sql, 2);
                //echo"dbmm_sql=$dbmm_sql<br>";
                $column_name = $dbmm_param['COLUMN_NAME'];
                $table_name = $dbmm_param['TABLE_NAME'];
        }
        $file_info_sql = "
        select
                ft.name as ".$column_name."_type,
                ".$column_name."_name,
                $column_name,
                ".$column_name."_size,
                ".$column_name."_width,
                ".$column_name."_height
        from
                $table_name tn,
            ".TABLE_PRE."file_type ft
        where
        tn.id = $entity_id and ft.id = tn.".$column_name."_type" ;
        //echo"file_info_sql=$file_info_sql<br>";
        $res = db_getArray($conn, $file_info_sql, 2);
        $file_name = $res[strtoupper($column_name."_name")];
        $file_type = $res[strtoupper($column_name."_type")];
        if (substr($file_type,0,5) == "image")
        {
                $height = $res[strtoupper($column_name."_height")];
                $width = $res[strtoupper($column_name."_width")];
                if ($max_size > 0)
                {
                        if ($height > $max_size || $width > $max_size)
                        {
                                $factor = $height/$width;
                                $width = $max_size;
                                $height = round($factor*$width);
                        }
                }
                echo "<img src='/images/image.html?id=$entity_id&sid=".mktime()."' height='".$height."' width='".$width."'>";
        }
        elseif (strpos($file_type, "x-shockwave-flash"))
        {
            echo "
            <OBJECT
                    classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000'
                    codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0'
                    classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000'  >
                    <PARAM NAME=movie value='images/image.html?id=$entity_id&sid=".mktime()."'>
                    <PARAM NAME=quality VALUE=high>
                    <PARAM NAME='SCALE' VALUE='show all'>
            </OBJECT>";
        }
        else
        {

                href ("/download_file.html?id=$entity_id&sid=".mktime(), $file_name);

        }
}
function my_ucfirst($text)
{
        if(ucfirst('а')=='А')//если функция работает нормально
        $text=ucfirst($text);
        else
        {
                $text1=sem_strtoupper(substr($text, 0, 1));
                $text=$text1.substr($text, 1);
        }
        return $text;
}

function my_strtolower($text)
{
        $text=strtolower($text);
        $text=strtr($text, 'ЙЦУКЕНГШЩЗХФЫВАПРОЛДЖЯЧСМИТЬБЮЭЁ', 'йцукенгшщзхфывапролджячсмитьбюэё');

        return $text;

}

function my_translit($text)
{
    $text=strtr($text, 'АБВГДЕЗИЙКЛМНОПРСТУФЫабвгдезийклмнопрстуфы','ABVGDEZIYKLMNOPRSTUFYabvgdeziyklmnoprstufy');
    $text=str_replace(array('Ё','ё','Ж','ж','Х','х','Ц','ц','Ч','ч','Ш','ш','Щ','щ','Ъ','ъ','ь','Ь','Э','э','Ю','ю','Я','я'),array('YE','ye','ZH','zh','X','x','TS','ts','CH','ch','SH','sh','SHH','shh','`','`','','','JE','je','YU','yu','YA','ya'),$text);
    return $text;

}

//функция добавления в рассылку, add- что нужно включать
function add_post($conn, $table_id, $id, $add, $he_id=array())
{
        $del_q="DELETE FROM post_part WHERE table_id=$table_id AND rel_id=$id AND status=0";
        $del=db_query($conn, $del_q);

        if($add['value'])
        {
			//выбираем рассылки, в которые может включаться этот материал
			$sel_post="SELECT post_id FROM post_entity WHERE table_id=$table_id";
			$res_post=db_getArray($conn, $sel_post);
			foreach($res_post as $k=>$v)
				{
				$ins=db_insert($conn, "post_part",
                           array("id", "table_id", "rel_id", DATE_MAIN, "status", "post_id"),
                           array("", $table_id, $id, db_sysdate(), 0, $v['POST_ID']),
                           array("ID", "int", "int", "date", "int", "int", "int"));
				}
            if(!$ins) die("Can't insert values into post_part.");
        }

        return 1;
}
//запуск из командной строки
function our_exec($file_name, $bin, $log_nor, $log_err, $addit, $wait=0)
{
        $string="$bin $file_name $addit 2>>$log_err >&- <&- >>$log_nor";

        if($wait)
           $string.=" &";
        //echo"string=$string\r\n";
        //echo"output=$output\r\n";
        //echo"return_code=$return_code\r\n";
        $ret=exec($string, $output, $return_code);
        if($return_code)
           $ret_exec=0;
        else
            $ret_exec=1;
		//echo"ret=$ret, output=".print_r($output)."\r\n return_code=$return_code, ret_exec=$ret_exec\r\n";
		return $ret_exec;
}
function check_field ($source_var, $type_var, $field_name, $field_null)
{
         global $err_form_mes;
        //echo "Source_var - $source_var , type - $type_var";br();
        if ($field_null == "f" && $source_var == "")
        {
                table("");
                trtd("", "class='error_msg'");
                echo "Обязательное для заполнения поле '".$field_name."' не заполнено !";
                tdtr();
                tableend();
                return 0;
        }
        else
        {
                if ($type_var == "int" && ($source_var == BASE_TRUE || $source_var == BASE_FALSE))
                {
                        return 1;
                }
                elseif ($type_var == "int" && $source_var == "null")
                {
                        return 1;
                }
                elseif ($type_var == "int")
                {
                        if (is_numeric($source_var) || $source_var=="")
                        {
                                return 1;
                        }
                        else
                        {
                                echo "Поле '".$field_name."' заполнено не верно !";
                                return 0;
                        }
                }
                else
                {
                        return 1;
                }
        }



}
function adm_navigation($conn, $arm_id, $lookup="", $print=0, $ar=array())
{
        extract($ar);
        $arm_name=get_byId($conn, TABLE_PRE."arms", $arm_id);
        echo "<html>\r\n<head>\r\n<title>";
        if(defined("OUTSIDE")) {
            if(defined("USER_TITLE")) $title = USER_TITLE;
            else $title = HOST;
            echo $title;
        }
        else {
            if(defined("ADMIN_TITLE")) $title = ADMIN_TITLE;
            else $title = HOST;
            echo $title."/".$arm_name['NAME'];
        }
        echo "</title>\r\n";
        echo '<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">';
        echo"\r\n";
        global $css_ar;
        foreach ($css_ar as $key_css => $val_css) {
            echo "<link rel='stylesheet' href='".$val_css."'>\r\n";
        }
        global $jscript;
                if(is_array($jscript))
                        foreach($jscript as $k_js=>$v_js)
                jscript($v_js, '');
        jscript('/js/func_form.js', '');
        jscript('/js/editor.js', '');
        jscript('/js/tiny_mce/tiny_mce.js', '');
        echo"\r\n";
        echo "</head>\r\n";
        if($print) body('onload="window.print();" class="print"');
        else body('onload="calendar_mouse_down();"');

        //calendar ------------------------------------------------------
        ?>
        <script language="JavaScript">
        <!--
        // The following script is used to hide the calendar whenever you click the document.
        // When using it you should set the name of popup button or image to "popcal", otherwise the calendar won't show up.
        function calendar_mouse_down(){
        document.onmousedown=function(e){
                var n=!e?self.event.srcElement.name:e.target.name;
                if (document.layers) {
                        with (gfPop) var l=pageX, t=pageY, r=l+clip.width, b=t+clip.height;
                        if (n!="popcal"&&(e.pageX>r||e.pageX<l||e.pageY>b||e.pageY<t)) gfPop.fHideCal();
                        return routeEvent(e);        // must use return here.
                } else if (n!="popcal") gfPop.fHideCal();
        }
        if (document.layers) document.captureEvents(Event.MOUSEDOWN);
        }
        // This is just an example, no guarantee it working in all browsers. You may use your own.
        //-->
        </script>
        <iframe width="174" height="189" name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="/calendar.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; left:-500px; top:0px;">
        </iframe>
        <?
        //------------------------------------------------------ calendar

        table("width='100%' class='AddForm' align='center' valign='top' border='0' height = '100%'");
        trtd('','class="AdminToolBar" nowrap align="left" colspan="2"');
        global $USER_INFO;
        echo $title." <span class='version_number'>(T-3 v. ".VERSION." ) </span> | ".date("d F Y - H : i")." | ".$USER_INFO['FIRST_NAME']." ".$USER_INFO['SECOND_NAME']." ".$USER_INFO['FAMILY'];
        echo '   <a href="/logout.php">Выйти</a>';
        //echo"g_USER_ID=".g_USER_ID."<br>";

        tdtr();
        // Колонка навигации
        if ($lookup == "" && !$print && !$no_nav)
        {
                trtd("","valign='top' align='center' width='".(defined("OUTSIDE")?"30":"20")."%' class='adm_navig_arm'");
                table("width='100%' class='Nav' cellspacing='0' cellpadding='0' border='0'");
                if(defined("OUTSIDE")) show_vneshn_menu($conn, $nav_id);
                else show_admin_menu($conn, $arm_id);
                tableend();
        }
        else
        {
                trtd("","valign='top' align='center' width='0%' class='adm_navig_arm'");
                //nbsp();
        }
        tdtd("valign='top' align='left' CLASS='PageContent'");
}
function adm_navigation_end()
{
        global $conn;
        tdtr();
        trtd('','CLASS="AdminToolBar" noWrap ALIGN="left" colspan="2"');
        global $USER_INFO;
        echo ADMIN_TITLE." <span class='version_number'>(T-3 v. ".VERSION." ) </span> | ".date("d F Y - H : i")." | ".$USER_INFO['FIRST_NAME']." ".$USER_INFO['SECOND_NAME']." ".$USER_INFO['FAMILY'];
        tdtr();
        tableend();
}
//получение параметров-констант из базы
function our_const($conn)
{
        $sel_q="select name, value, show_flag from our_const";
        $res=db_getArray($conn, $sel_q);
	if(is_array($res))
        	foreach($res as $k=>$v)
			{
			define($v['NAME'], $v['VALUE']);
			if($v['SHOW_FLAG'])
				$ret_xml.="<".$v['NAME'].">".htmlspecialchars($v['VALUE'])."</".$v['NAME'].">";
			}
		return $ret_xml;
}
//проверка навигации
function navigation($conn, $nav_id, $script)
{
        //echo "script=$script<br>";
        if(!$nav_id)// && DEFINED("MAIN_ID"))
            {
                //$nav_id=MAIN_ID;
                //$redirect=2;
            if($script!=FIRST_PAGE)//смотрим - может, такой шаблон - только у одной страницы
               {
               $sel_file_q="select n.id from navigation n, shablon s where s.path='".$script."' and s.id=n.shablon_id and n.publish ".PUBLISH_SQL;
               //echo"sel_file_q=$sel_file_q<br>";
               $res_file=db_getArray($conn, $sel_file_q);
               if(count($res_file)==1)
                  {
                  global $nav_id;
                  $nav_id=$res_file[0]['ID'];
                  $noredirect=1;
                  }
               //echo"res_file=$res_file, ".count($res_file).", ".$res_file[0]['ID']."<br>";
               }

            }
        else
            {
                $sel_file_q="select s.path from navigation n, shablon s where n.id=$nav_id and s.id=n.shablon_id and n.publish ".PUBLISH_SQL;
                $res_file=db_getArray($conn, $sel_file_q, 2);
                if($res_file['PATH'])
                   set_ses_var("nav_ses", $nav_id);

            }
        if($res_file['PATH'] && $redirect && $res_file['PATH']!=$script)
           {

                //header("Location:".$res_file['PATH']);
                //exit();
           }
        elseif(!$res_file['PATH'] && $script!=FIRST_PAGE && !$noredirect)
                {
                header("Location:".FIRST_PAGE);
                exit();
                }
}
//запрос информации о картинке
function image_info($conn, $id, $ar=array())
{
	extract($ar);
	//type=1 - запросить тип файла
        if (defined("MULT_COL"))
			$mfile_prefix=MULT_COL;
		else
			$mfile_prefix='FILE';
		if($type)
			$sel_q="SELECT m.name, m.".$mfile_prefix."_width, m.".$mfile_prefix."_height, m.".$mfile_prefix."_size, m.".$mfile_prefix."_name, f.name as file_type FROM multimedia m, ".TABLE_PRE."file_types f where m.id=:id AND m.".$mfile_prefix."_type_id=f.id";
		else
			$sel_q="select name, ".$mfile_prefix."_width, ".$mfile_prefix."_height, ".$mfile_prefix."_size, ".$mfile_prefix."_name from multimedia where id=:id";
		//echo"sel_q=$sel_q, id=$id<br>";
		$bind_ar[]=db_for_bind("id", $id);
        $res=db_getArray($conn, $sel_q, 2, array("bind_ar"=>$bind_ar));
        if(count($res) == 0)
			return false;

        $ret['MFILE_WIDTH']  = $res[$mfile_prefix.'_WIDTH'];
        $ret['MFILE_HEIGHT'] = $res[$mfile_prefix.'_HEIGHT'];
        $ret['MFILE_SIZE']   = $res[$mfile_prefix.'_SIZE'];
        $ret['MFILE_NAME']  = $res[$mfile_prefix.'_NAME'];
		$ret['NAME']  = $res['NAME'];
		$ret['width']=$res[$mfile_prefix.'_WIDTH'];
		$ret['height']=$res[$mfile_prefix.'_HEIGHT'];
		$ret['FILE_TYPE']=$res['FILE_TYPE'];
		//print_r($ret);
        return $ret;
}
//функция прорисовки тега картинки
// с запросом размеров
//stretch - растягивать ли картинку(1), или только ужимать(0)
function viewim_full($conn, $id, $image_width=0, $image_height=0, $ret_view=1, $addit="", $stretch=0, $ar=array())
{
	extract($ar);
	//$flash=1 - есть вероятность, что там флеш
        if(!$image_width || !$image_height || $image_info)
        {
				if($flash)
					{
					$image_inf=image_info($conn, $id, array("type"=>1));
					//echo"<pre>";
					//print_r($image_inf);
					}
				else
					$image_inf=image_info($conn, $id);
                //echo $image_inf['MFILE_WIDTH'].", ".$image_inf['MFILE_HEIGHT'].", $image_width, $image_height<br>";
                if(!$image_width && !$image_height)
					{
                        $image_width=$image_inf['MFILE_WIDTH'];
                        $image_height=$image_inf['MFILE_HEIGHT'];
					}
                else
					{
                        $img_size=lim_img_size($image_inf['MFILE_WIDTH'], $image_inf['MFILE_HEIGHT'], $image_width, $image_height, $stretch);
						//echo"<pre>";
						//print_r($img_size);
                        $image_width=$img_size['width'];
                        $image_height=$img_size['height'];
						//echo"$image_width, $image_height<br>";
					}
                //echo"$pi_width, $pi_height<br>";
        }
        if($image_inf['NAME'] && !ereg("alt=", $addit))
			$addit.='alt="'.for_input(strip_tags($image_inf['NAME'])).'"';
        if($image_width)
			$addit.=' width="'.$image_width.'"';
        if($image_height)
			$addit.=' height="'.$image_height.'"';
		//echo"addit=$addit<br>";
		//echo"file_type=".$image_inf['FILE_TYPE']."<br>";
		if($image_inf['FILE_TYPE'] && strpos($image_inf['FILE_TYPE'], "flash")>0)
			{
			if(defined("TPL_PATH"))
				{
				$flash_template = & new Template(TPL_PATH."/show_flash.tpl");
				$flash_template->AddParam($res_ban);
				$html=$flash_template->output();
				}
			elseif(DEFINED("FLASH_PLAYER_WH") && $image_inf['width'] && $image_inf['height'])
				{
				$html=str_replace("VAR_ID", $id, FLASH_PLAYER_WH);
				$html=str_replace("VAR_WIDTH", $image_inf['width'], $html);
				$html=str_replace("VAR_HEIGHT", $image_inf['height'], $html);
				}
			elseif(DEFINED("FLASH_PLAYER"))
				{
				$html=str_replace("VAR_ID", $id, FLASH_PLAYER);
				}
			else
				{
				//echo"be";
				$html=img_src("/images/image.html?id=".$id, $addit, 1);
				}
			}
		else
			$html=img_src("/images/image.html?id=".$id, $addit, 1);
		//echo"html=$html<br>";
        if($ret_view==1)//нужна только картинка
           return $html;
        else
        {
                $ret['img']=$html;
                $ret['width']=$image_width;
                $ret['height']=$image_height;
                return $ret;
        }
}
//Для картинки - получаем высоту, ширину картинки и ограничения по ним и возвращаем размеры, которые должны быть
//stretch - растягивать ли картинку(1), или только ужимать(0)
function lim_img_size($width, $height, $lim_width=0, $lim_height=0, $stretch=0)
{
        if(!$lim_width)
            $lim_width=$width;
        if(!$lim_height)
            $lim_height=$height;
        //echo"stretch=$stretch<br>";
        //echo"$width, $height, $lim_width, $lim_height, $stretch<br>";
        if($width>$lim_width || $height>$lim_height || ($stretch && ($width!=$lim_width || $height!=$lim_height) ) )
			{
                if($width>$lim_width || $height>$lim_height)
                   $compress=(($width/$lim_width)>($height/$lim_height))?($width/$lim_width):($height/$lim_height);
                elseif(($width<$lim_width || $height<$lim_height) && $stretch)
                        $compress=(($width/$lim_width)<($height/$lim_height))?($width/$lim_width):($height/$lim_height);
                //echo"compress=$compress<br>";
                $ret['width']=floor($width/$compress);
                $ret['height']=floor($height/$compress);
                ///echo"width=".$ret['width'].", height=".$ret['height']."<br>";
			}
        else
			{
                $ret['width']=$width;
                $ret['height']=$height;
			}
		//echo"<pre>";
		//print_r($ret);
        return $ret;
}
//рисуем ссылку на скачивание картинки
function download_image($id, $text, $addit="", $ret=0)
         {
             return href("/images/download_im.html?id=$id", $text, $addit, $ret);
         }
//функция преобразует строку даты определенного формата в массив
function date_format_ar($date, $date_format="", $date_ret_format="")
{
        if(!$date_format)
            $date_format=DATE_FORMAT;
        if($date_ret_format)
           $ret=$date_ret_format;
        $razd="[- :]";
		if($date)
			{
			$date_ar=split($razd, $date);
			$date_format_ar=split($razd, $date_format);
			foreach($date_format_ar as $k=>$v)
				{
                //echo"$k=$v - ".$date_ar[$k]."<br>";
                //if(!$date_ret_format)
                $v=trim($v);
                $ret_ar[$v]=$date_ar[$k];
                //echo"ret_ar[$v]=".$ret_ar[$v]."<br>";
                /*
                else
                {
                $ret=ereg_replace($v, $date_ar[$k], $ret);
                }
                */
				}
			}
		else
			$ret_ar=array();

		if(!$date_ret_format)
				return $ret_ar;
		elseif(count($ret_ar))
				{
                //echo $ret_ar["m"].", ".$ret_ar["d"].", ". $ret_ar["Y"].", ". $ret_ar["H"].", ". $ret_ar["i"].", ". $ret_ar["s"]."<br>";
				$mktime=mktime($ret_ar["H"], $ret_ar["i"], $ret_ar["s"], $ret_ar["m"], $ret_ar["d"], $ret_ar["Y"]);
				if($mktime)
					$ret_str=date($date_ret_format, $mktime);
				else
					$ret_str="";
                return $ret_str;
				}
		else
			return "";
}
//функция получает переменные даты и возращает строку в DATE_FORMAT_TMP
function to_date_format($ar)
{
//echo"<pre>";
//print_r($ar);
extract($ar);
$ret=DATE_FORMAT_TMP;
//echo"ret=$ret<br>";
$ret=str_replace("Y", $year, $ret);
//echo"2 ret=$ret, year=$year<br>";
$ret=str_replace("m", $month, $ret);
$ret=str_replace("d", $day, $ret);
$ret=str_replace("H", $hour?$hour:'00', $ret);
$ret=str_replace("i", $minute?$minute:'00', $ret);
$ret=str_replace("s", '00', $ret);
//echo "$ret<BR>";;
return $ret;
}
//функция получает путь и строку переменных и возвращает рузультат соединения
function href_prep($href, $string)
{
        if(ereg("\?", $href))
        $ans=$href."&".$string;
        else
        $ans=$href."?".$string;
        return $ans;
}
//функция формирует ссылку на объект - по object_id и id объекта
function href_object($conn, $entity_id, $id="", $ret_type=1)
{
        global $SVIZ_NAV_AR;
        //проверяем, есть ли связь с навигацией
		//echo"entity_id=$entity_id<br>";
        if(!count($SVIZ_NAV_AR[$entity_id]))
            sviz_ar($conn, $entity_id);
        //echo"<pre>";
        //print_r($SVIZ_NAV_AR);
        /*
        $sel_path="select s.path, n.id, n.name
        from  ".TABLE_PRE."objects o, shablon s, navigation n
        where o.object_id=$object_id and o.shablon_id=s.id and s.id=n.shablon_id ";
        $res_path=db_getArray($conn, $sel_path);

        if(count($res_path)==1)
        */
        $SHABLON_PATH=(DEFINED("SHABLON_PATH")?SHABLON_PATH:"PATH_HREF");
        if($SVIZ_NAV_AR[$entity_id]['ID'])//только одна рубрика и один шаблон
			{
            $string="nav_id=".$SVIZ_NAV_AR[$entity_id]['ID'].($id?"&ti_id=$id":"");
            $ans=href_prep($SVIZ_NAV_AR[$entity_id]['PATH'], $string);
            $nav_name=$SVIZ_NAV_AR[$entity_id]['NAME'];
			}
        elseif($id)
			{
            if($SVIZ_NAV_AR[$entity_id]['TABLE_SVIZ']==$SVIZ_NAV_AR[$entity_id]['TABLE_MAIN'] && $SVIZ_NAV_AR[$entity_id]['TABLE_SVIZ'])//ссылка на навигацию - в главной таблице
                {
                $sel_path="select n.id, n.name, s.$SHABLON_PATH from ".NAVIGATION." n, shablon s, ".$SVIZ_NAV_AR[$entity_id]['TABLE_MAIN']." m
                                     where m.".NAVIGATION."_id=n.ID and s.id=n.shablon_id and m.id=$id";
                }
            elseif($SVIZ_NAV_AR[$entity_id]['TABLE_SVIZ'])//через вспомогательную таблицу - выберется случайная связь
                {
                        $sel_path="SELECT n.id, t.layer_id"//, n.name, n.template_id, t.$SHABLON_PATH
										." FROM ".NAVIGATION." n, cham_templates t, "
												.$SVIZ_NAV_AR[$entity_id]['TABLE_SVIZ']." sv"
										." WHERE sv.".NAV_LINK_COL."=n.ID and t.id=n.template_id"
												." AND sv.".$SVIZ_NAV_AR[$entity_id]['COLUMN_SVIZ']."=$id"
												." AND n.".REAL_PUBLISH.PUBLISH_SQL;
                }
            //echo"sel_path=$sel_path<br>";
            if($sel_path)
                {
                $res_path=db_getArray($conn, $sel_path);
				foreach($res_path as $k_path=>$v_path)
					{
					//у раздела шаблон - из тех, что для этой сущности указаны
					/*
					if(is_array($SVIZ_NAV_AR[$entity_id]['PATH_AR_SHABLON']) && array_key_exists($v_path['SHABLON_ID'], $SVIZ_NAV_AR[$entity_id]['PATH_AR_SHABLON']))
						{
                        $string="nav_id=".$v_path['ID'].($id?"&ti_id=$id":"");
                        $ans=href_prep($v_path[$SHABLON_PATH], $string);
						$nav_id=$v_path['ID'];
                        $nav_name=$v_path['NAME'];
						$href_path=$v_path[$SHABLON_PATH];
						$yes=1;
						break;
                        }
					*/
					if($SVIZ_NAV_AR[$entity_id]['LAYER_LIST_NAV'][$v_path['ID']])
						{
                        $string ="layer_id=".$SVIZ_NAV_AR[$entity_id]['LAYER_LIST_NAV'][$v_path['ID']] ."&nav_id=".$v_path['ID'].($id?"&id=$id":"");
                        $ans=href_prep(DEF_PATH, $string);
						$nav_id=$v_path['ID'];
                        //$nav_name=$v_path['NAME'];
						$href_path=$v_path[$SHABLON_PATH];
						$yes=1;
						break;
						}
					}

				if(!$yes)//ни у одного раздела шаблоны не подходят - берем первый попавшийся раздел и шаблон по умолчанию(если он есть) или шаблон раздела
					{
					$nav_id=$res_path[0]['ID'];

					$layer_id=$SVIZ_NAV_AR[$entity_id]['LAYER_LIST'][0];
					$href_path="/index.html";
					if($layer_id)
						$href_path=href_prep($href_path, "layer_id=$layer_id");
					if($res_path[0]['ID'])
						$href_path=href_prep($href_path, "nav_id=".$res_path[0]['ID']);
					elseif(count($SVIZ_NAV_AR[$entity_id]['LAYER_LIST_NAV']))
						{
						foreach($SVIZ_NAV_AR[$entity_id]['LAYER_LIST_NAV'] as $k_ll=>$v_ll)
							{
							$href_path=href_prep($href_path, "nav_id=".$k_ll);
							break;
							}
						}

					if($id)
						$href_path=href_prep($href_path, "id=".$id);
                    $ans=href_prep($href_path, $string);
                    //$nav_name=$res_path[0]['NAME'];
					}
                }
        }
        if($ret_type==1)
           return $ans;
        else
            {
            $ans_ar=array("href"=>$ans, "nav_name"=>$nav_name, "nav_id"=>$nav_id);
            //echo"href=".$ans_ar['href'].", nav_name=".$ans_ar['nav_name']."<br>";
            return $ans_ar;
            }
}
//===============================================
//устанвливает, есть ли связь сущноси с навигацией
function sviz_ar($conn, $entity_id)
{
        global $SVIZ_NAV_AR;
		$SHABLON_PATH=(DEFINED("SHABLON_PATH")?SHABLON_PATH:"PATH_HREF");
		$sel_def="SELECT t.layer_id, l.detail_layer_id, n.id, t.$SHABLON_PATH,"
								." (SELECT layer_id FROM ti_layer_entity le"
								." WHERE le.layer_id=t.layer_id AND le.entity_id=$entity_id) as layer_flag,"
								." (SELECT layer_id FROM ti_layer_entity le"
								." WHERE le.layer_id=l.detail_layer_id AND le.entity_id=$entity_id)"
									." as detail_layer_flag"
								." FROM ".NAVIGATION." n, cham_templates t, cham_layer l"
								." WHERE n.template_id=t.id AND l.id=t.layer_id"
								." AND (EXISTS(SELECT layer_id FROM ti_layer_entity le"
								." WHERE le.layer_id=t.layer_id AND le.entity_id=$entity_id)"
								." OR EXISTS(SELECT layer_id FROM ti_layer_entity le"
								." WHERE le.layer_id=l.detail_layer_id AND le.entity_id=$entity_id))";
			//echo"<br>1 sel_def=$sel_def<br><br>";
			$res_def=db_getArray($conn, $sel_def);
			if(count($res_def)==1)//если тольоко один шаблон для сущности
				$SVIZ_NAV_AR[$entity_id]['PATH']=$res_def[0][$SHABLON_PATH];
			elseif(count($res_def)>1)//если несколько шаблонов для сущности
				{
				$SVIZ_NAV_AR[$entity_id]['PATH_AR']=$res_def;
				foreach($res_def as $k_def=>$v_def)
					{
					$SVIZ_NAV_AR[$entity_id]['PATH_AR_SHABLON'][$v_def['ID']]=$v_def[$SHABLON_PATH];
					if($v_def['LAYER_FLAG'])
						{
						$SVIZ_NAV_AR[$entity_id]['LAYER_LIST'][]=$v_def['LAYER_ID'];
						$SVIZ_NAV_AR[$entity_id]['LAYER_LIST_NAV'][$v_def['ID']]=$v_def['LAYER_ID'];
						}
					elseif($v_def['DETAIL_LAYER_FLAG'])
						{
						$SVIZ_NAV_AR[$entity_id]['LAYER_LIST'][]=$v_def['DETAIL_LAYER_ID'];
						$SVIZ_NAV_AR[$entity_id]['LAYER_LIST_NAV'][$v_def['ID']]=$v_def['DETAIL_LAYER_ID'];
						}
					}
				}
			else//нет шаблонов - смотрим ссылки
				{
				$sel_def="SELECT n.id, lrl.layer_id"
						." FROM ti_layout_link_ref lrl, ti_layout l, cham_layer cl, cham_layer_gather lg, "
							.NAVIGATION." n, cham_templates t"
						." WHERE lrl.layout_id=l.id AND l.entity_id=$entity_id"
								." AND lg.layout_id=l.id AND lg.layer_id=cl.id"
								." AND n.template_id=t.id AND t.layer_id=cl.id"
								." AND EXISTS(SELECT layer_id FROM ti_layer_entity le"
												." WHERE le.layer_id=lrl.layer_id AND entity_id=$entity_id)";
				$res_def=db_getArray($conn, $sel_def);
				foreach($res_def as $k_def=>$v_def)
					{
					$SVIZ_NAV_AR[$entity_id]['PATH_AR_SHABLON'][$v_def['ID']]=$v_def[$SHABLON_PATH];
					$SVIZ_NAV_AR[$entity_id]['LAYER_LIST'][]=$v_def['LAYER_ID'];
					$SVIZ_NAV_AR[$entity_id]['LAYER_LIST_NAV'][$v_def['ID']]=$v_def['LAYER_ID'];
					}
				if(!count($SVIZ_NAV_AR[$entity_id]['LAYER_LIST']))
					{
					//то же самое, но список может быть по другой сущности
					$sel_def="SELECT DISTINCT n.id, lrl.layer_id"
						." FROM ti_layout_link_ref lrl, ti_layout l, cham_layer cl, cham_layer_gather lg, "
							.NAVIGATION." n, cham_templates t"
						." WHERE lrl.layout_id=l.id "
								." AND lg.layout_id=l.id AND lg.layer_id=cl.id"
								." AND n.template_id=t.id AND t.layer_id=cl.id"
								." AND EXISTS(SELECT layer_id FROM ti_layer_entity le"
												." WHERE le.layer_id=lrl.layer_id AND entity_id=$entity_id)";
					//echo"sel_def3=$sel_def<br>";
					$res_def=db_getArray($conn, $sel_def);
					foreach($res_def as $k_def=>$v_def)
						{
						$SVIZ_NAV_AR[$entity_id]['PATH_AR_SHABLON'][$v_def['ID']]=$v_def[$SHABLON_PATH];
						$SVIZ_NAV_AR[$entity_id]['LAYER_LIST'][]=$v_def['LAYER_ID'];
						$SVIZ_NAV_AR[$entity_id]['LAYER_LIST_NAV'][$v_def['ID']]=$v_def['LAYER_ID'];
						}
					if(!count($SVIZ_NAV_AR[$entity_id]['LAYER_LIST']))
						{
						$sel_def="SELECT le.layer_id"
                              ." FROM ti_layer_entity le"
                              ." WHERE le.entity_id=$entity_id ";
						$res_def=db_getArray($conn, $sel_def);
						foreach($res_def as $k_def=>$v_def)
							{
							$SVIZ_NAV_AR[$entity_id]['LAYER_LIST'][]=$v_def['LAYER_ID'];
							}
						}
					}

				//echo"<br>2 sel_def=$sel_def<br><br>";
				//print_r($res_def);
				/*
				foreach($res_def as $k_def=>$v_def)
					{
					$SVIZ_NAV_AR[$entity_id]['LAYER_LIST'][]=$v_def['LAYER_ID'];
					}
					*/
				}
			//если не выбраны id таблицы навигации
			if(!$SVIZ_NAV_AR[NAVIGATION]['TABLE_ID'])
				{
                $sel_nav="select t.id as table_id, c.id as column_id
                           from ".TABLE_PRE."tables t, ".TABLE_PRE."columns c
                           where t.id=c.table_id and upper(t.name)='".NAVIGATION."' and upper(c.name)='ID'";
                //echo"sel_nav=$sel_nav<br>";
                $res_nav=db_getArray($conn, $sel_nav, 2);
                $SVIZ_NAV_AR[NAVIGATION]['TABLE_ID']=$res_nav['TABLE_ID'];
                $SVIZ_NAV_AR[NAVIGATION]['COLUMN_ID']=$res_nav['COLUMN_ID'];
				}
			//конец выборки информации по таблице навигации

			$sel_ref="SELECT t.id, t.name, t.main, et.main as et_main
                              FROM ".TABLE_PRE."tables t, ".TABLE_PRE."entity_table et
                              WHERE et.table_id=t.id and et.entity_id=$entity_id
                                    AND t.id IN(SELECT c.table_id FROM ".TABLE_PRE."columns c
                                                     WHERE c.table_id =t.id
                                                       AND (c.ref_column_id=".$SVIZ_NAV_AR[NAVIGATION]['COLUMN_ID']." OR et.main=1)AND c.table_id<>".$SVIZ_NAV_AR[NAVIGATION]['TABLE_ID'].")
                              ORDER BY et.main DESC, t.main ASC";
			//echo"sel_ref=$sel_ref<br>";
			$res_ref=db_getArray($conn, $sel_ref);
			foreach($res_ref as $k=>$v)
                {
                //echo "$k=$v<br>";
                if($v['ET_MAIN'])//связь с навигацией - один ко многим, ссылка - в основной таблице
					{
                        $SVIZ_NAV_AR[$entity_id]['TABLE_MAIN']=$v['NAME'];
                        $SVIZ_NAV_AR[$entity_id]['TABLE_ID']=$v['ID'];
                        if(!$SVIZ_NAV_AR[$entity_id]['TABLE_SVIZ'])
							{
                            $SVIZ_NAV_AR[$entity_id]['TABLE_SVIZ']=$v['NAME'];
							}
					}
                else//связь с навигацией - многие ко многим, через вспомогательную таблицу (она должна быть первой)
					{
                        $SVIZ_NAV_AR[$entity_id]['TABLE_SVIZ']=$v['NAME'];
                        if($SVIZ_NAV_AR[$entity_id]['TABLE_ID'])
                           {
                           $sel_col_sviz="SELECT c1.name FROM ".TABLE_PRE."columns c1, ".TABLE_PRE."columns c2
                                          WHERE c1.table_id=".$v['ID']." AND c1.ref_column_id=c2.id AND c2.table_id=".$SVIZ_NAV_AR[$entity_id]['TABLE_ID'];
                           //echo"sel_col_sviz=$sel_col_sviz<br>";
                           $res_col_sviz=db_getArray($conn, $sel_col_sviz, 2);
                           $SVIZ_NAV_AR[$entity_id]['COLUMN_SVIZ']=$res_col_sviz['NAME'];
                           }
					}
				}
			//echo"<pre>SVIZ_NAV_AR[$entity_id]";
			//print_r($SVIZ_NAV_AR[$entity_id]);
        return 1;
}

//показывает вып меню для выбора месяца
function selmonth($name, $month_ar, $setval, $addition="", $nullval=0)
        {
        select_up($name, $addition);

        if($nullval)
                optioninp("", "---");

        for($i=1; $i<=12; $i++)
                {
                if($i!=$setval)
                        optioninp($i, $month_ar[$i]);
                else
                        optioninp($i, $month_ar[$i], 1);
                }
        select_down();
        }
//показывеает выпадающее меню - от и до заданного значения
function selfromto($from, $to, $setval, $name="", $additional="", $nullval=0)
        {
        if($name)
                select_up($name, $additional);

        if($nullval)
                optioninp("", "---");

        if($from<$to)
                {
                for($from;$from<=$to;$from++)
                        {
                        if($setval!=$from)
                                optioninp($from, $from);
                        else
                                optioninp($from, $from, 1);
                        }
                }
        elseif($from>$to)
                {
                for($from;$to<from;$from--)
                        {
                        if($setval!=$from)
                                optioninp($from, $from);
                        else
                                optioninp($from, $from, 1);

                        }
                }
         else
             {
             optioninp($from, $from);
             }
        if($name)
                select_down();

        }

//устанавливаем переменную cookie
function set_cookie_var($name, $val, $index=""){
  if($index!=""){//если массив
    setcookie($name."[$index]", $val, mktime(1, 1, 1, 1, 1, 2020));
    }
}

//устанавливаем переменную сессии
function set_ses_var($name, $val, $index="")
         {
         global $$name;
         if (session_is_registered($name) && $index=="")
             session_unregister($name);
         if (!(session_is_registered($name)))
            session_register($name);
         if($index!="")//если массив
            {
            if(is_array($$name))
               {
               if(in_array($$name, $index))
                  $$name[$index]=$val;
               else
                  $$name=$$name + array($index=>$val);
               }
            else
                $$name=array($index=>$val);
            //echo"ses - $name=".$$name."-".$$name[$index]."<br>";
            }
         else
             $$name=$val;
		 //echo"$name=$val<br>";
         }
//обработка данных из формы - trim и укорачивание до нужной длины - из админки
function form_string($string, $max_len=0)
         {
         $ret=trim($string);
         if($max_len)
            $ret=substr($ret, 0, $max_len);
         return $ret;
         }
//обработка данных из формы - trim и укорачивание до нужной длины - из внешней части
function form_string_user($string, $max_len=0)
         {
         $ret=trim(strip_tags($string));
         if($max_len)
            $ret=substr($ret, 0, $max_len);
         return $ret;
         }

//возвращаем заданные колонки по id
function get_byId($conn, $table, $id, $columns="name")
         {
         $sel_q="select $columns from $table where id=$id";
         //echo"sel_q=$sel_q<br>";
         $res=db_getArray($conn, $sel_q, 2);
         return $res;
         }
//проверка email
function CheckEmail($email)
        {
        //echo"email=$email<br>";
        $r=eregi("([a-z0-9_-]+)\@([a-z0-9_-]+)\.([a-z0-9_]+)", $email);
        //echo"r=$r<br>";
        return $r;
        }
//выодим значения в поле text - чтобы кавычки не мешали
function for_input($text)
         {
         $ret=ereg_replace('"', '&quot;', $text);
         //echo"text=$text, ret=$ret<br>";
         return $ret;
         }
//округляет байты
function around_bytes($size)
         {
         if($size<1024)
            $att="b";
         if($size>1024)
            {
            $size=$size/1024;
            $att="Kb";
            $size=round($size);
            }
         if($size>1024)
            {
            $size=$size/1024;
            $att="Mb";
            $size=round($size, 2);
            }
         $ret="$size $att";
         return $ret;
         }
/**
 * @return void
 * @param unknown $str
 * @desc Записывает $str в файл заданый в константе SQL_LOG_FILE
*/
function sql_log ($str)
{
        //echo  "Файл - ".SQL_LOG_FILE;

        if (!$file = fopen(SQL_LOG_FILE, "a"))
        {
                echo "Невозможно открыть файл ".SQL_LOG_FILE." для записи логов!!!<br>\r\n$str\r\n<br>";
        }
        else
        {
                fwrite ($file, "-- ".VERSION.", ".date("d-M-Y H:i:s")." IP -  ".$GLOBALS['REMOTE_ADDR']." - user - ".$GLOBALS['REMOTE_USER']." \n");
				fwrite($file, "$str; \n");
                fclose ($file);
        }
}
//определяем, нужно и заносить в логи
function sql_log_yes ($table_name)
         {
		$table_no=array("TI_REF_COLUMNS", "TI_LAYOUT_REF_COLUMNS", "TI_LAYOUT_COLUMNS", "TI_LAYOUT_COLS_PRESET", "CHAM_LAYER_PARAMS", "TI_LAYOUT_REF_LINKS", "TI_LAYOUT_REF_LINKS_PARAMS", "TI_LAYOUT_REF_DOWNLOAD", "TI_LAYOUT_PARAMS", "TI_LAYOUT_LINKS", "TI_LAYOUT_LINKS_PARAMS");
         $table_name=strtoupper($table_name);
         if((eregi(TABLE_PRE, $table_name) || eregi('^cham_', $table_name))
				&& $table_name!=TABLE_PRE."LOGS"
                  && $table_name!=TABLE_PRE."INDEXER_WAIT" && strtoupper($table_name)!=TABLE_PRE."INDEXER_WAIT_BAD"
					  && $table_name!=TABLE_PRE."SEARCH_INDEX" &&

					  $table_name!=TABLE_PRE."SEARCH_WORD" && $table_name!=TABLE_PRE."POST_PART"
				  && !eregi("cache", $table_name) && !array_search($table_name, $table_no))
            {
            return 1;
            }
         else
            return 0;
         }
//обрезает строку до определенной длиныы, но чтобы не резать слова
function string_cut($string, $len)
         {
         //echo"len=$len<br>";
         if(strlen($string)>$len)
            {
            if(eregi("[a-z]", $len))
               $len=LEN_CUT_DEF;
            $ret=substr($string, 0, $len);
            $ret=ereg_replace("[^[[:space:]]]$", "", $ret)."...";
            }
         else
             $ret=$string;
         return $ret;
         }
//считаем, сколько опубликовано
function count_test($conn, $table, $where="", $ar=array())
         {
			extract($ar);
         $sel_count="select count(".$tab_name."id) as c from $table where $tab_name".PUBLISH.PUBLISH_SQL."$where";
         //echo"sel_count=$sel_count<br>";
         $res_count=db_getArray($conn, $sel_count, 2);
         //echo"ret=".$res_count['C']."<br>";
         return $res_count['C'];
         }
//Получаем данные о последней записи
function get_last($conn, $table, $where="", $columns="id, name")
         {
         $sel_last_q="select $columns, ".DATE_MAIN.", ".db_isnull()."(".ORDER_NUM.", ".BIG_ORDER.") as ".ORDER_NUM." from $table where ".PUBLISH.PUBLISH_SQL."$where order by ".ORDER_NUM." ASC, ".DATE_MAIN." DESC, ID DESC";

         $sel_last_q=db_limit($sel_last_q, 0, 1);
         //echo"sel_last_q=$sel_last_q<br>";
         $res_last=db_getArray($conn, $sel_last_q);
         return $res_last;
         }
//замена текста в константах на нужное значение
function const_replace($text, $ar)
         {
         foreach($ar as $k=>$v)
                 {
                 $text=ereg_replace("{".$k."}", $v, $text);
                 }
         return $text;
         }
//добавляем в извещения
function add_post_one($conn, $email, $subject, $text, $name="")
         {
         $ins=db_insert($conn, "post_one", array("name", DATE_MAIN, "email", "subject", "full_text"),
                                           array($name, db_sysdate(), $email, $subject, $text),
                                           array('varchar', 'date', 'varchar', 'varchar', 'varchar'));
         }
//========================================================================
//заменяем строчку типа :var на значение
function var_sql_replace($string, $var_s, $val)
         {
         $str=ereg_replace(":$var_s", $val, $string);
         return $str;
         }
//========================================================================
//получаем по имени Id или готовим insert
function get_or_ins($conn, $ar)
         {
         extract ($ar);
         $sel_q="select id from $table where name='$val'";
         //echo"sel_q=$sel_q<br>";
         $res=db_getArray($conn, $sel_q);
         if(count($res)==1)
            return $res[0]['ID'];
         elseif(!count($res))
                 {
                 $ins=db_insert($conn, $table, array("name"), array($val), array("varchar"));
                 return $ins;
                 }

         }
//ФУНКЦИЯ ИСПРАВЛЯЕТ ЛИШНИЕ ЗАМЕНЫ - &amp и тому подобное послеработы редактора,
//А ТАКЖЕ ОБРЕЗАЕТ ПО ДЛИНЕ
function bad_editor($text, $max_len=0, $reap=0)
        {
        $text=trim($text);
        if($max_len)
                $text=substr($text, 0, $max_len);
        $text=eregi_replace("&amp;", "&", $text);
        if($reap)
            $text=eregi_replace("'+", "''", $text);
        $text=eregi_replace("§", "&sect", $text);
        $text=eregi_replace("http://".g_HOST, "", $text);
        return $text;
        }
//формируем имя sequence для таблицы
function seq_name($table)
         {
         $ret=$table."_id_seq";
         return $ret;
         }
//возвращает максимальную длину элементов
function max_len_in_array($in_ar)
         {
         $ret=0;
                 if(is_array($in_ar))
                        {
                        foreach($in_ar as $k=>$v)
                 {
                 $len=strlen($v);
                 $ret=$ret>$len?$ret:$len;
                 }
                        }
         return $ret;
         }
//преобразует строку, введенную пользователем, в масив слов для поиска
function search_str($text)
         {
         $search_text=substr(trim(strval($text)), 0, 1000);
         $search_text=eregi_replace("[^a-zА-Яа-я0-9-]", " ", $search_text);
         $search_text=ereg_replace(" {1,}", " ", $search_text);
         $search_text=trim($search_text);
         $search_ar=split(" ", $search_text);
         return $search_ar;
         }
//возвращаем сущность для шаблона
function entity_shablon($conn, $shablon_id)
         {
         global $ENT_SHABLON;
         if(!$ENT_SHABLON[$shablon_id])
             {
             $sel_ent="SELECT ENTITY_ID FROM shablon_entity WHERE shablon_id=$shablon_id";
             //echo"sel_ent=$sel_ent<br>";
             $res_ent=db_getArray($conn, $sel_ent, 2);
             if($res_ent['ENTITY_ID'])
                {
                $ENT_SHABLON[$shablon_id]=$res_ent;
                return 1;
                }
            }
         else
             return 1;
         }
//gпринимает название таблицы и колонки, возвращает id колонки
function get_column_id($conn, $table, $column)
         {
         $table=strtoupper(trim($table));
         $column=strtoupper(trim($column));
         $sel_col="SELECT c.id FROM ".TABLE_PRE."tables t, ".TABLE_PRE."columns c
                          WHERE c.table_id=t.id AND upper(t.name)='$table' AND upper(c.name)='$column'";
         //echo"sel_col=$sel_col<br>";
         $res_col=db_getArray($conn, $sel_col, 2);
         return $res_col['ID'];
         }
/*
//проверяет на соответсвие дате из javascript-календаря и возвращает дату в mktime
function date_calendar($date_str)
         {
         $date_str=trim($date_str);
         if(ereg("^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}$", $date_str))
            {
            $date_ar=explode(".", $date_str);
            $date_ret=mktime(0,0,0, $date_ar[1], $date_ar[0], $date_ar[2]);
            return $date_ret;
            }
         else
             {
             return false;
             }
         }
*/
//проверяет на соответсвие дате из javascript-календаря и возвращает дату в виде строки
function date_calendar($date_str)
         {
         $date_str=trim($date_str);
         if(ereg("^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}$", $date_str))
            {
            $date_ar=explode(".", $date_str);
			$date_ret=to_date_format(array("day"=>$date_ar[0], "month"=>$date_ar[1], "year"=>$date_ar[2]));
            return $date_ret;
            }
         else
             {
             return false;
             }
         }
//подцепляет файлы, нужные для templatов
function func_templates()
	{
	require_once(PATH_INC."class/Validator/Validator.php");
	require_once(PATH_INC."class/BaseObject/BaseObject.php");
	require_once(PATH_INC."class/Template/Template.php");
	require_once(PATH_INC."class/SimplePages/SimplePages.php");
	require_once(PATH_INC."class/func_req.php");
	if(!defined("TPL_PATH"))
		DEFINE("TPL_PATH", PATH_INC_HOST."/templates");
	}
//====================================================
//разбор данных о банере
function banner_code($ar)
	{
	extract($ar);
	if($banner_type_id==1)//картинка
            {
            $res_ban['res_body']=viewim_full($conn, $multimedia_id, $max_width, $max_height);
            }
    elseif($banner_type_id==3)//флеш
		{
		if(defined("TPL_PATH"))
			{
			$flash_template = & new Template(TPL_PATH."/show_flash.tpl");
			$flash_template->AddParam($res_ban);
			$res_ban['res_body']=$flash_template->output();
			}
        //echo"res_body=".$res_ban['res_body']."<br>";
        }
    else
        $res_ban['res_body']=$full_text;
    if($banner_url)
		{
        $res_ban['banner_link']="/click.html?ti_id=".$banner_id;
        if(eregi("^http://", $banner_url))
			$ban_addit=' target="_blank"';
        else
            $ban_addit="";
        $res_ban['res_body']=href($res_ban['banner_link'], $res_ban['res_body'], $ban_addit, 1);
        }
	return $res_ban;
	}
//====================================================
//добляем условия через where или and - в зависимости от того, есть ли уже where
function addit_where($text, $addit)
	{
	//echo"text=$text, addit=$addit<br>\r\n";
	if(eregi(" WHERE ", $text))
		{
		$ret=$text." AND ".$addit;
		}
	else
		{
		$ret=$text." WHERE ".$addit;
		}
	return $ret;
	}
//=======================================
//добляем к text add_text через разделитель
function add_text($text, $add_text, $razd="")
	{
	if($text)
		$ret=$text.$razd.$add_text;
	else
		$ret=$add_text;
	return $ret;
	}
//================================
//преобразуем массив в xml
function ar_xml($ins_ar, $nohtml=0)
	{
	foreach($ins_ar as $k=>$v)
		{
		if(eregi("[a-z]", $k) && $v)
			{
			$ret.="<$k>".($nohtml?$v:htmlspecialchars($v))."</$k>";
			}
		}
	return $ret;
	}
//логирование в указанный файл
function file_log ($filename, $str, $write_mode="a")
{

        if (!$file = fopen($filename, $write_mode))
        {
                echo "Невозможно открыть файл ".$filename." для записи логов!!!<br>\r\n$str\r\n<br>";
        }
        else
        {
				//echo"<b>$filename</b><br> $str<br>";
                fwrite ($file, "-- ".VERSION.", ".date("d-M-Y H:i:s")." IP -  ".$GLOBALS['REMOTE_ADDR']." - user - ".$GLOBALS['REMOTE_USER']." \n");
                fwrite($file, "$str\n");
                fclose ($file);
        }
}
//подготовка занчения под определенный тип
function type_var_prep($val, $type_var)
	{
	//echo"val=$val, type_var=$type_var<br>";
	if($type_var=="int")
		$ret=intval($val);
	elseif($type_var=="date")
		{
		if(ereg("[-\./\,]", $val))
			{
			$val=ereg_replace("[-/\,]", ".", $val);
			list($day, $month, $year) = explode ('.', $val, 3);
			//echo"$day, $month, $year<br>";
			$ret=db_date(mktime(0, 0, 0, $month, $day, $year));
			//echo"ret=$ret<br>";
			}
		}
	else
		$ret=$val;
	return $ret;
	}
//проверка, передавать ли переменную в ссылку
function var_test($var_name)
	{
	if($var_name=="PHPSESSID" || ereg("phpbb", $var_name))
		return 0;
	else
		return 1;
	}

	function is_image($filename) {
		$is = @getimagesize($filename);
		if(!$is){//
			return false;
		}elseif(!in_array($is[2], array(1,2,3))){//print_r($is);
			return false;
		}else{//print_r($is);
			return true;
		}
	}
	function scan_dir($dirname,$flag){
		$result = "";
		if ($handle = opendir($dirname)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					$filename = $dirname."/".$file;
					// Если имеем дело с файлом
					if(is_file($filename))
					{
						if($ii=is_image($filename)){
							unlink ($filename);
							if($flag) $result.=$file." Удален<br>";
						}elseif(filesize($filename)==0){
							unlink ($filename);
							if($flag) $result.=$file." Удален<br>";
						}
					}
					// Если перед нами директория, вызываем рекурсивно функцию scan_dir
					if(is_dir($filename))
					{
						if($flag){
							$result.=scan_dir($filename);
						}else scan_dir($filename);
					}
					//flush();
				}
			}
			closedir($handle);
		}
		return $result;
	}

?>
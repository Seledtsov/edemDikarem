<?php
/**
* @return void
* @param unknown $ad_id
* @desc Функция возвращает структуру сущности с id = arm_id
*/
function get_entity_structure ($conn, $arm_id, $arm_column_id=0)
{
$sel_arm_q = "select distinct
     ac.id as arm_column_id,
     ac.column_id,
     ac.name as col_about,
     ac.filter_type_id,
     ac.list_type_id,
     ac.form_type_id,
     ac.sort_order,
     ac.sort_main,
     ac.sort_type_id,
     ac.column_width,
     a.index_type_id,
     a.name as arm_name,
     a.edit_row,
     a.add_row,";
     if(HOST=="agency") $sel_arm_q .= " a.multiadd_row, a.DELETE_CACHED_IMAGES, ";
$sel_arm_q .= "a.del_row,
     a.num_items,
     a.entity_id as object_id,
     aa.arm_type_id
    from
     ".TABLE_PRE."arm_columns ac,
         ".TABLE_PRE."arms a,
         ".TABLE_PRE."arm_additional aa
         ".(DEFINED("g_USER_ID")?", ".TABLE_PRE."arm_user_v auv":" ")."
    WHERE
         a.id = $arm_id AND
     ac.arm_id = a.id AND
         aa.arm_id=a.id".
         (DEFINED("g_USER_ID")?" AND auv.arm_id=a.id AND auv.user_id=".g_USER_ID:"")."
         ".($arm_column_id?" AND ac.id=$arm_column_id":"")."
    order by
     ac.".ORDER_NUM." asc ";
//echo"<!-- sel_arm_q=$sel_arm_q<br> -->";
$res_arm = db_getArray($conn, $sel_arm_q);

foreach($res_arm as $k=>$v)
        {
        if($v['COLUMN_ID'])
           {
        //echo"$k - ".$v['COL_ABOUT']."<br>";
        $sel_col = "select
     t.name as table_name,
     t.id as table_id,
     t.main,
     c.name as col_name,
     c.column_length,
     c.ref_column_id,
     c.id as col_id,
     c.null_value,
     c.default_value,
     c.column_type_id,
     ct.unit_name as col_type,
     e.id as object_id,
     e.name as object_name
    from
     ".TABLE_PRE."column_types ct,
     ".TABLE_PRE."tables t,
     ".TABLE_PRE."columns c,
         ".TABLE_PRE."entity_table et,
         ".TABLE_PRE."entities e
    where
         c.id=".$v['COLUMN_ID']." and
     c.table_id = t.id and
     ct.id = c.column_type_id and
         c.table_id = t.id and
         t.id = et.table_id and
         et.entity_id = e.id
         ".($v['OBJECT_ID']?" and e.id=".$v['OBJECT_ID']:"");
//echo "<br>sel_col - $sel_col<br>";
                  $res_col=db_getArray($conn, $sel_col, 2);
                  }
       else
           $res_col=array();
       $entity_structure[$k]=array_merge($v, $res_col);

        }

        //$entity_structure = db_getArray($conn, $sel_col);
        //запрос префильтров
        $sel_prefilters="SELECT table_id, value, required, column_id
                                FROM  ".TABLE_PRE."arm_prefilters
                                WHERE arm_id=$arm_id";

		//echo"sel_prefilters=$sel_prefilters<br>";
        $res_prefilters=db_getArray($conn, $sel_prefilters);
        $entity_structure['PREFILTERS']=$res_prefilters;
		//echo"<!--<pre>"; print_r($entity_structure); echo '</pre>-->';
        return $entity_structure;

}
/**
* @return unknown
* @param unknown $conn
* @param unknown $entity_structure
* @desc  Если поля является ссылкой то параметры ссылаемой таблицы возвращаются в массиве
*/

function get_entity_relation ($conn, $entity_structure)
{
       for($i=0; $i<count($entity_structure); $i++)
        {
              if(!$i)
                  {
                  global $items_on_page;
                  $items_on_page=$entity_structure[$i]['NUM_ITEMS']?$entity_structure[$i]['NUM_ITEMS']:ITEMS_ON_PAGE;
                  }
              /*
              if($entity_structure[$i]['SORT_MAIN'])
                   {
                   global $sort_by;
                   //if(!$sort_by)
                   //    $sort_by=$entity_structure[$i]['COL_ID']*$entity_structure[$i]['SORT_TYPE_ID'];
                   //echo"sort_by=$sort_by<br>";
                   }
              */
              //echo"<br>sort_main=".$entity_structure[$i]['SORT_MAIN'].", sort_by=$sort_by<br><br>";

                // Если поле является ссылкой то достаем описание того поля на которое ссылаемся
                if($entity_structure[$i]['REF_ID'] != 0)
                {
                        $sel_ref_q="
                        select
                          tc.name as column_name,
                          tt.name as table_name
                        from
                        (
                                select
                                          c.id,
                                            c.name as col_name,
                                c.table_id
                                from
                                      ".TABLE_PRE."columns c
                                where
                                      c.id = ".$entity_structure[$i]['REF_ID']."
                        )
                        as
                          t,
                          ".TABLE_PRE."columns tc,
                          ".TABLE_PRE."tables tt
                        where
                          tc.table_id = t.table_id and
                          tt.id = tc.table_id
                        ";

                        //echo "<br>sel_ref_q=$sel_ref_q<br><br>";
                        $sel_ref=db_query($conn, $sel_ref_q);
                        if($res_ref=db_fetch_row($sel_ref))
                        {
                                $showsel[$i]=$res_ref;
                        }
                }
        }
        return $showsel;
}

/**
* @return void
* @param unknown $conn
* @param unknown $arm_id
* @param unknown $g_url
* @param int $sort_by_search
* @param array $entity_structure
* @param array $showsel
* @desc Вывод формы поиска на экран
*/

function show_filter($conn, $ar)
         {
         global $CONTROL_AR, $SubArms;
         extract($ar);
 		 /*
		 only_id - что опказывать только форму поиска по id
		 $arm_type=="tree" - АРМ-дерево
		 */

         ob_start();
         if(is_array($list_ar))
            {
            table('width="600" cellpadding="2" border="0" class="show_filter"');
            //form(g_URL, "GET");
            form(g_URL,'GET','name="search_form"');
			if(!$only_id)
				{
				thead();
				th('colspan="2"');
				echo"Поиск";
				thend();
				theadend();
				if($sf)
					forminput("hidden", "sf2", 1);
				}
            //form(href_prep($g_url, $SubArms['ADD_STRING']));
            echo $SubArms['HIDDEN'];
            forminput("hidden", "lookup", $lookup);
            forminput("hidden", "lookup_name", $lookup_name);
            $count_red=0;
            }
        foreach($filter_ar as $k=>$v)
                {
				//echo"<pre>";
				//print_r($v);
                /*
                echo"2 $k=$v<br>";
                foreach($v as $k1=>$v1)
                        echo"&nbsp;&nbsp;&nbsp;$k1=$v1<br>";
                */
                //echo $SubArms['COL']['ID']."=".$v['COL_ID']."<br>";
				if(!$only_id || strtoupper($v['COL_NAME'])=='ID')
					{
					if($SubArms['COL']['ID']!=$v['COL_ID'])
						{
						if(is_array($list_ar))
							{
							$count_red++;
							$ctrl_ret=show_control($conn, $v, array("method"=>"search_form","form_name"=>"search_form", "only_id"=>$only_id));
							if($ctrl_ret['whereSQL'])
								$ret['whereSQL'].=($ret['whereSQL']?" and ":"").$ctrl_ret['whereSQL'];
							}
						else
							{
							$ctrl_ret=show_control($conn, $v, array("method"=>"search_form_addit","form_name"=>"search_form", "only_id"=>$only_id));
							}
						}
                    }
                }

        /*if($count_red>1)

        {
        */
        if(is_array($list_ar))
           {
           global $sort_by_search, $search;
		   if(!$only_id  && $arm_type!="tree")
			   {
				trtd("", "width='40%'");
				echo"Сортировать по полю:";
				tdtd('width="60%"');
				select_up("sort_by_search", "style='width:100%'");
				optioninp("", "---");
				foreach($list_ar as $k=>$v)
					{
					if($v['COLUMN_ID'])
						{
						if($CONTROL_AR[$v['LIST_TYPE_ID']]['NAME']!="LookUpListM"
                               && $CONTROL_AR[$v['LIST_TYPE_ID']]['NAME']!="PostIt"
                               && $CONTROL_AR[$v['LIST_TYPE_ID']]['NAME']!="IndexIt"
                               && $CONTROL_AR[$v['LIST_TYPE_ID']]['NAME']!="KidLink"
                               && $CONTROL_AR[$v['LIST_TYPE_ID']]['NAME']!="TreeOrderReculculation"
                               && $CONTROL_AR[$v['LIST_TYPE_ID']]['NAME']!="TreePublishReculculation"
							   )
							{
							if($sort_by_search==$v['COLUMN_ID'] && $search)
								optioninp($v['COLUMN_ID'], $v['COL_ABOUT'], 1);
							else
								optioninp($v['COLUMN_ID'], $v['COL_ABOUT']);
							}
						}
					}
				select_down();
				tdtr();
			   }
           /*}*/
			if($only_id  || $arm_type=="tree")
				tdtd();
			else
			   {
				trtd();td();
			   }
           //forminput("hidden", "a_id", $a_id);
           forminput("hidden", "our_arm_id", $our_arm_id);
           forminput("hidden", "search", 1);
           forminput("submit", "", "  Искать  ");
           nbsp(2);
           forminput("submit", "", "Показать весь список", 'button', "onclick=\"location.replace('".g_URL."?our_arm_id=$our_arm_id".$SubArms['ADD_STRING']."');return false;\"");
		   if($only_id)
			   {
			   nbsp(2);
			   forminput("submit", "sf", "Расширенный поиск");
			   }
           tdtr();
           formend();
           tableend();

           br(2);
           }
        $ret['html']=ob_get_contents();
        ob_end_clean();
        return $ret;
}
//=====================================================================================================
//Показываем список записей
function show_list($conn, $ar)
         {
         extract($ar);
		 /*
		 $arm_type=="tree" - АРМ-дерево
		 */
         ob_start();
         global $addit_string_ar, $SubArms;
         $res_count_q=db_getArray($conn, $sel_count_q, 2);
         //echo"sel_count_q=$sel_count_q<br>";
         $max_on_page=$max_on_page?$max_on_page:ITEMS_ON_PAGE;
         $page=$page?$page:1;
         $column_count=count($entity_info['list_ar']);
         $row_count=($res_count_q['C']>=($max_on_page*$page))?$max_on_page:($res_count_q['C']-($max_on_page*($page-1)));
         //if($res_count_q['C']>$max_on_page)

         //если возникла ошибка при удалении
         if($delete_list_entity)
            {
            global $SEL_SVIZ;
            }
		if($arm_type!="tree")
			$pagelist=pagelist($res_count_q['C'], $max_on_page, $page, addit_string_form(array("addit_string_ar"=>$addit_string_ar, "ret_format"=>"string", "no_val"=>array("page"=>1))) );


         table('class="ListForm"  ');
         form(g_URL,'POST','name="list_form"');//new .17
         if(!$addit_string_ar['print'] && ($entity_info['arm']['DEL_ROW'] || $entity_info['arm']['EDIT_ROW'] || $entity_info['arm']['ADD_ROW']  || $entity_info['arm']['MULTIADD_ROW'] || $entity_info['arm']['DELETE_CACHED_IMAGES']))
            {
            trtd("", "colspan='$column_count'");
			show_list_buttons($entity_info, $addit_string_ar);
            tdtr();
            }
         tableend();
		if($pagelist)
			{
			div('class="pagenatorBlock"');
			echo "$pagelist";
			divend();
			}
         table('class="ListForm" cellpadding="2"');
         //form(g_URL);
         //form(g_URL,'POST','name="list_form"'); //old .16
         $show_list_th_ar=array("row_count"=>$row_count, "addit_string"=>addit_string_form(array("addit_string_ar"=>$addit_string_ar, "ret_format"=>"string", "no_val"=>array("sort_by"=>1) ) ) );
         if($delete_list_entity)
            $show_list_th_ar['delete_list_entity']=$delete_list_entity;
		 if($arm_type!="tree")
			 show_list_th($conn, $entity_info['list_ar'], $show_list_th_ar);
         echo addit_string_form(array("addit_string_ar"=>$addit_string_ar, "ret_format"=>"form_hidden" ));
         if($res_count_q['C'])
            {
            if($res_count_q['C']>$max_on_page && $arm_type!="tree")
               $sel_q=db_limit($sel_q, $max_on_page*($page-1), $max_on_page);
            //echo"sel_q=$sel_q<br>";
            $res_q=db_getArray($conn, $sel_q);
            foreach($res_q as $k=>$v)
                    {
                    if ($k%2 == 0)
                        $trclass = "dark";
                    else
                        $trclass = "light";

					if($arm_type=="tree")
						{

						trtd('', 'class="tree'.$v[TREE_LEVEL].'" colspan="'.$column_count.'"');
						table('border="0"');
						//show_list_th($conn, $entity_info['list_ar'], $show_list_th_ar);
						tr();
						}
					else
						tr("class='$trclass'");
                    $key=$v['ID'];
                    foreach($entity_info['list_ar'] as $k_l=>$v_l)
                            {
                            //$show_control_ar=array("method"=>"list", "val_ar"=>$v, "num_row"=>$k, "lookup"=>$lookup, "lookup_name"=>$lookup_name);
                            $show_control_ar=array("method"=>"list", "val_ar"=>$v, "num_row"=>$key, "lookup"=>$lookup, "lookup_name"=>$lookup_name, "form_name"=>"list_form");
                            if($error_form[$key])
                               {
                               /*
                               echo"error - $k<br>";
                               $show_control_ar['error_form']=$error_form[$k];
                               if($error_form[$k]['err'][$k_l])
                                  $show_control_ar['error_this']=1;
                               */
                               //echo"error - $key<br>";
                               $show_control_ar['error_form']=$error_form[$key];
                               if($error_form[$key]['err'][$k_l])
                                  $show_control_ar['error_this']=1;

                               }
                            if(!$SubArms['COL']['ID'] || $SubArms['COL']['ID']!=$v_l['COL_ID'])
                               show_control($conn, $v_l, $show_control_ar);
                            }//конец пробега по контролам
                    //если возникла ошибка при удалении - показываем, что ссылается на эту запись
                    if($delete_list_entity)
                       {
                       td();
                       if($error_form[$key])//ошибка в этой записи
                          {
                          foreach($SEL_SVIZ[$entity_info['table_id']]['sql'] as $k_sviz=>$v_sviz)
                                  {
                                  $sviz_q=var_sql_replace($v_sviz, "this_id", $key);
                                  //echo"sviz_q=$sviz_q<br>";
                                  $sviz=array();
                                  $sviz=db_getArray($conn, $sviz_q);
                                  foreach($sviz as $k_sv=>$v_sv)
                                          {
                                          if(!$k_sv)
                                              {
                                              if($k_sviz)
                                                  br();
                                              echo $SEL_SVIZ[$entity_info['table_id']]['table_about'][$k_sviz]." (id - ";
                                              }
                                          if($k_sv)
                                             echo", ";
                                          echo $v_sv['ID'];
                                          if($k_sv==(count($sviz)-1))
                                             {
                                             echo")";
                                             }
                                          }
                                  }
                          }
                       tdend();
                       }
					trend();
					if($arm_type=="tree")
						{
						tableend();
						tdtr();
						}


                    }
            }
            //если нет полей, для которых есть поля в форме - рисуем внизу кнопочки
         if(!$entity_info['arm']['COMPLEX'] && $entity_info['arm']['ADD_ROW'] )
             {
                    tr("class='new_list'");
                    foreach($entity_info['list_ar'] as $k_l=>$v_l)
                            {
                            $show_control_ar=array("method"=>"list_new", "val_ar"=>$v, "form_name"=>"list_form");
                            if($error_form['new'])
                               {
                               //echo"error - new<br>";
                               $show_control_ar['error_form']=$error_form['new'];
                               if($error_form['new']['err'][$k_l])
                                  $show_control_ar['error_this']=1;
                               }
                            //show_control($conn, $v_l, array("method"=>"list_new", "val_ar"=>$v));
                            if(!$SubArms['COL']['ID'] || $SubArms['COL']['ID']!=$v_l['COL_ID'])
                                show_control($conn, $v_l, $show_control_ar);
                            }
                    trend();

             }elseif(!$entity_info['arm']['COMPLEX'] && $entity_info['arm']['MULTIADD_ROW'])
             {
                    tr("class='new_list'");
                    foreach($entity_info['list_ar'] as $k_l=>$v_l)
                            {
                            $show_control_ar=array("method"=>"list_new", "val_ar"=>$v, "form_name"=>"list_form");
                            if($error_form['new'])
                               {
                               //echo"error - new<br>";
                               $show_control_ar['error_form']=$error_form['new'];
                               if($error_form['new']['err'][$k_l])
                                  $show_control_ar['error_this']=1;
                               }
                            //show_control($conn, $v_l, array("method"=>"list_new", "val_ar"=>$v));
                            if(!$SubArms['COL']['ID'] || $SubArms['COL']['ID']!=$v_l['COL_ID'])
                                show_control($conn, $v_l, $show_control_ar);
                            }
                    trend();

             }elseif(!$entity_info['arm']['COMPLEX'] && $entity_info['arm']['DELETE_CACHED_IMAGES']){
				  tr("class='new_list'");
                    foreach($entity_info['list_ar'] as $k_l=>$v_l)
                            {
                            $show_control_ar=array("method"=>"list_new", "val_ar"=>$v, "form_name"=>"list_form");
                            if($error_form['new'])
                               {
                               //echo"error - new<br>";
                               $show_control_ar['error_form']=$error_form['new'];
                               if($error_form['new']['err'][$k_l])
                                  $show_control_ar['error_this']=1;
                               }
                            //show_control($conn, $v_l, array("method"=>"list_new", "val_ar"=>$v));
                            if(!$SubArms['COL']['ID'] || $SubArms['COL']['ID']!=$v_l['COL_ID'])
                                show_control($conn, $v_l, $show_control_ar);
                            }
                    trend();
             }//конец строки для ввода записей из списка
         $show_list_th_ar['is']=2;
		 if($arm_type!="tree")
			show_list_th($conn,$entity_info['list_ar'], $show_list_th_ar);
         tableend();

         if($pagelist)
			{
			div('class="pagenatorBlock"');
			echo "$pagelist";
			divend();
			}
         table('class="ListForm" cellpadding="0" ');
         if(!$addit_string_ar['print'] && ($entity_info['arm']['DEL_ROW'] || $entity_info['arm']['EDIT_ROW'] || $entity_info['arm']['ADD_ROW']  || $entity_info['arm']['MULTIADD_ROW'] || $entity_info['arm']['DELETE_CACHED_IMAGES']))
            {
            trtd("", "colspan='$column_count'");
			show_list_buttons($entity_info, $addit_string_ar);
            tdtr();
            }
        formend();
        tableend();

		$ret=ob_get_contents();
        ob_end_clean();
        return $ret;
        }
//рисуем заголовок к форме-списку
function show_list_th($conn, $entity_ar, $ar=array())
         {
         extract($ar);
         global $CONTROL_AR, $sort_by, $addit_string_ar, $SubArms, $show_list_th_yes;
        $show_list_th_yes=1;
         if($show_list_th_yes)
            tr();
         else
             thead();
         foreach($entity_ar as $k=>$v)
                 {
                 if($sort_by==$v['COL_ID'])
                    {
                    $s_b="-".$v['COL_ID'];
                    $arrow_img = "down_arrow.gif";
                    }
                 else
                     {
                     $s_b=$v['COL_ID'];
                     if ($sort_by =="-".$v['COL_ID'])
                         $arrow_img = "up_arrow.gif";
                     else
                         $arrow_img = "";
                     }

                 $control_id=$v['LIST_TYPE_ID'];

                 if(!$CONTROL_AR[$control_id])
                     {
                     $CONTROL_AR[$control_id]=control_info($conn, $control_id);
                     }
                 //echo"control_id=$control_id, ".$CONTROL_AR[$control_id]."<br>";
                 if($CONTROL_AR[$control_id]['params']['VIEW']!="hidden" && $CONTROL_AR[$control_id]['NAME']!="Hidden"
                    && (!$SubArms['COL']['ID'] || $SubArms['COL']['ID']!=$v['COL_ID']))//если не скрытый
                     {
                     if ($v['NULL_VALUE'] == 1  || !$v['COL_ID'])
                         $header_class = "HeaderCell";
                     else
                         $header_class = "HeaderCell_Req";
                     if($CONTROL_AR[$control_id]['NAME']=="Id")
                        $width_col="35";
                     else
                         $width_col=($v['COLUMN_WIDTH']?$v['COLUMN_WIDTH']."%":"auto");
                     if($show_list_th_yes)
                        td('class="'.$header_class.'" width="'.$width_col.'"');
                     else
                        th('class="'.$header_class.'" width="'.$width_col.'"');
                     //echo"col_width=".$v['COLUMN_WIDTH']."<br>";
                     //echo"null_val=".$v['NULL_VALUE'].", $header_class<br>";
                     if($CONTROL_AR[$control_id]['NAME']=="Id")
						 {
						 forminput("checkbox", "markall".$is, '', '', "onclick=\"select_all_entity('markall$is', '$row_count');\" ID='markall$is'");
						 }
                     elseif($CONTROL_AR[$control_id]['NAME']=="LookUpListM"
                                        || $CONTROL_AR[$control_id]['NAME']=="PostIt"
                                        || $CONTROL_AR[$control_id]['NAME']=="IndexIt"
                                        || $CONTROL_AR[$control_id]['NAME']=="KidLink"
										|| $CONTROL_AR[$control_id]['NAME']=="ClearCache")
                        {
                        echo $v['COL_ABOUT'];
                        }
                     else
                         {
                         href(g_URL."?sort_by=$s_b".addit_string_form(array("addit_string_ar"=>$addit_string_ar, "ret_format"=>"string", "no_val"=>array("sort_by"=>"sort_by"))), $v['COL_ABOUT']);
                         if($arrow_img)
                            img_src("/images/$arrow_img");
                         }
                     if($show_list_th_yes)
                        tdend();
                     else
                         thend();
                     }
                 }
         //если были ошибки при удалении
         if($delete_list_entity)
            {
            td('class="HeaderCell"');
            echo"Ссылки";
            tdend();
            }
         if($show_list_th_yes)
            trend();
         else
             theadend();
         $show_list_th_yes=1;
         }
//рисуем кнопки к списку
function show_list_buttons($entity_info, $addit_string_ar)
	{//echo '<!--<pre>'; print_r($entity_info['arm']); echo '</pre>-->';
                table("width='100%' border='0' cellspacing='0' cellpadding='0' class=\"button_table\"");
                trtd('', 'width="0%"');
                    if($entity_info['arm']['EDIT_ROW'] && DEFINED("ARM_PK"))
                    {
                    forminput("submit", "update_list_entity", "Обновить данные", "admin_refresh_button");
                    }
				if($entity_info['arm']['EDIT_ROW'] && DEFINED("ARM_PK") && $entity_info['arm']['ADD_ROW'])
					{
					tdtd();
					nbsp(25);
					tdtd("align='left'");
					}
                if ($entity_info['arm']['ADD_ROW'] == 1)
                    {
                    if (!$entity_info['arm']['COMPLEX']  && DEFINED("ARM_PK"))
                        {
                        forminput("submit", "new_list_entity", "Добавить", "admin_add_button");
                        }
                    elseif ($entity_info['arm']['COMPLEX'])
                       {

                        forminput("button", "", "Добавить", "admin_add_button", "onclick = 'location.href=\"".g_URL."?new=1&".addit_string_form(array("addit_string_ar"=>$addit_string_ar, "ret_format"=>"string"))."\"'");
                        }
                    else nbsp();
                    }
                if( DEFINED("ARM_PK") && ($entity_info['arm']['MULTIADD_ROW']))
					{
					tdtd();
					nbsp(15);
					tdtd("align='left'");
					}
                if ($entity_info['arm']['MULTIADD_ROW'] == 1)
                    {forminput("button", "", "Пакетная загрузка", "admin_add_button", "onclick = 'location.href=\"multi_load.html"."?multi=1&".addit_string_form(array("addit_string_ar"=>$addit_string_ar, "ret_format"=>"string"))."\"'");
                    }
                else nbsp();
    			if( DEFINED("ARM_PK") && ( $entity_info['arm']['DELETE_CACHED_IMAGES']))
					{
					tdtd();
					nbsp(5);
					tdtd("align='left'");
					}
                if ($entity_info['arm']['DELETE_CACHED_IMAGES'] == 1)
                    {forminput("button", "", "Удалить кеш фото", "admin_add_button", "onclick = \"window.open('/delete_cached_images.php','dci','scrollbars=1,top=0,left=0,resizable=1,width=680,height=350'); return false;\"");
                    }
                else nbsp();
                tdtd("align='right' width='100%'");
                if ($entity_info['arm']['DEL_ROW'] == 1  && DEFINED("ARM_PK"))
                    {
                    if(!$del_submit_text) $del_submit_text="Удалить отмеченные записи";
                    forminput("submit", "delete_list_entity", $del_submit_text, "admin_del_button","",0,1);
                    }
				tdtd();
				href(href_prep(g_URL."?".g_QUERY, "print=1"), img_src("/images/print.gif", '', 1), 'target="_blank" onClick="return ow(this.href);"');
                tdtr();
                tableend();
	}
//=================================================================
//рисуем форму редактирования конкретной записи
function show_form($conn, $ar)
         {
         extract($ar);
         //echo"1 error_form=$error_form<br>";
         //echo"sel_q=$sel_q<br>";
         ob_start();
         global $SubArms;
         $res_q=db_getArray($conn, $entity_info['sel_q'], 2);
         form(g_URL, "post", 'enctype="multipart/form-data" name="edit_form"');
         echo addit_string_form(array("addit_string_ar"=>$addit_string_ar, "ret_format"=>"form_hidden" ));
         table('class="show_form"');
         forminput("hidden", "our_ent_id", $our_ent_id);
         foreach($entity_info['form_ar'] as $k_l=>$v_l)
                 {
                 if(!$SubArms['COL']['ID'] || $SubArms['COL']['ID']!=$v_l['COL_ID'])
                    show_control($conn, $v_l, array("method"=>"form_edit", "val_ar"=>$res_q, "error_form"=>$error_form, "string_id"=>$our_ent_id, "form_name"=>"edit_form"));
                 trtd("","height=\"5\" colspan=\"2\"");
                 tdtr();
                 }
         if(($entity_info['arm']['EDIT_ROW'] || ($entity_info['arm']['MULTIADD_ROW'] && $new)||($entity_info['arm']['DELETE_CACHED_IMAGES']) || ($entity_info['arm']['ADD_ROW'] && $new)) && !$addit_string_ar['print_form'])
            {
             trtd("","colspan=\"2\" height=\"10\"");
             tdtr();
             trtd();
             tdtd();
             if($new) forminput("hidden", "new", "1");
             forminput("hidden", "save_form", "1");
             forminput("Submit", "", "Сохранить");
             //forminput("reset", "", "Сбросить");
             tdtr();
            }
         tableend();
         formend();
         $ret=ob_get_contents();
         ob_end_clean();
         return $ret;
         }



function show_all_entity()
{
        global $conn;
        $arms_list = db_select($conn, TABLE_PRE."arms", "id, name, type, file_name");
        foreach ($arms_list as $k => $v)
        {
                href($v['FILE_NAME']."?arm_id=".$v['ID'], $v['NAME']);
                br();
        }
}


//рисуем простую строку формы поиска
function search_form_tr($col_about, $control_html, $ar=array())
         {
		extract($ar);
         trtd('align="left"'.($only_id?"":' width="30%"'));
         //echo $v['COL_ABOUT'].":";
         echo $col_about;
         tdtd(($only_id?"":' width="70%"'));
         echo $control_html;
		 if(!$only_id)
			tdtr();

         }
//получение из базы информации о контроле
function control_info($conn, $control_id)
         {
         $sel_q="select c.addition_params, c.default_value, c.additional_arm_id, ct.name from
                 ".TABLE_PRE."controls c, ".TABLE_PRE."control_types ct where ct.id=c.control_type_id and c.id=$control_id";
         //echo"sel_q=$sel_q<br>";
         $res=db_getArray($conn, $sel_q, 2);
         //echo"addit=".$res['ADDITION_PARAMS']."<br>";
		 if($res['DEFAULT_VALUE'] && ereg("^[\$]", $res['DEFAULT_VALUE']))
			 {
			 //echo"YES";
			 $name_var=str_replace("$", "", $res['DEFAULT_VALUE']);
			 global $$name_var;
			 $res['DEFAULT_VALUE']=$$name_var;
			 }
         if($res['ADDITION_PARAMS'])
            $res['params']=control_params($res['ADDITION_PARAMS']);
		 if($res['NAME']=='Id')
			 {
			 //echo"PK<br>";
			 DEFINE("ARM_PK", 1);//означает, что есть поле первичного ключа
			 }
         return $res;
         }
//=====================================================================
function control_params($string)
         {
         $string_ar=split(RAZD_PARAMS, $string);
         foreach($string_ar as $k=>$v)
                 {
                 $v_ar=split("=", $v);
                 if($v_ar[0])
                    {
                    for($i=1; $i<count($v_ar); $i++)
                        $ret[$v_ar[0]].=($i>1?"=":"").$v_ar[$i];
                    }

                 }
         return $ret;
         }
//=======================================================================
function show_control($conn, $info_ar, $ar)
             {
             extract($ar);
             /*echo"<!--<pre>";
             echo"ar<br>";
             print_r($ar);
             br();
             echo"info_ar<br>";
             print_r($info_ar);
             echo '</pre>-->';*/

             //echo"method=$method<br>";
             //echo"error_form=$error_form<br>";
             global $addit_string, $addit_string_ar, $search, $SubArms, $entity_info, $our_ent_id, $new;
 			 if($method=="save_form" && $our_ent_id && !$string_id)
				$string_id=$our_ent_id;
             //if(!$error_form)
                 $val=$val_ar[strtoupper($info_ar['COL_NAME'])];
             //foreach($val_ar as $k=>$v)
             //        echo"$k=$v<br>";

             global $CONTROL_AR;//информация о контролах
             global $COLUMN_AR;//информация о ссылках на таблицы
             global $SELECT_DOWN_AR;//массивы для постороения выпадающего меню - чтобы не было каждый раз запросов
             if($method=="search_form" || $method=="search_form_addit")
                {
                $control_id=$info_ar['FILTER_TYPE_ID'];
                $control_name=$info_ar['COL_NAME'].$info_ar['ARM_COLUMN_ID']."_search";
                global $$control_name;
                $val=trim($$control_name);
                $addit_string.="&$control_name=$val";
                $addit_string_ar[$control_name]=$val;
                }
             elseif($method=="list_new")
                {
                $control_id=$info_ar['LIST_TYPE_ID'];
                $control_name=$info_ar['COL_NAME'].$info_ar['ARM_COLUMN_ID']."_new";
                $val=$info_ar['DEFAULT_VALUE'];
                //echo"val=$val<br>";
                }
             elseif($method=="list" )
                {
                $control_id=$info_ar['LIST_TYPE_ID'];
                $control_name=$info_ar['COL_NAME'].$info_ar['ARM_COLUMN_ID']."[$num_row]";
                $addit_ctrl=" onchange=\"new_c('mark_$num_row');\";";
                $addit_ctrl_2="new_c('mark_$num_row');";
                }
             elseif($method=="list_prep")
                {
                $control_id=$info_ar['LIST_TYPE_ID'];
                $control_name=$info_ar['COL_NAME'].$info_ar['ARM_COLUMN_ID'];
                }
             elseif($method=="form_edit" || $method=="save_form")
                    {
                    $control_id=$info_ar['FORM_TYPE_ID'];
                    $control_name=$info_ar['COL_NAME'].$info_ar['ARM_COLUMN_ID'];
                    //echo"our_ent_id=$our_ent_id<br>";
                    if($method=="form_edit" && !$string_id)
                       {
                       $val=$info_ar['DEFAULT_VALUE'];
                       }
                    //для того, чтобы сохранять парольв Apache
                    if($method=="save_form" && strtoupper($info_ar['COL_NAME'])=="NAME" && $info_ar['MAIN'])
                       {
                       global $FOR_APACHE_LOGIN;
                       if(!$FOR_APACHE_LOGIN)
                           {
                           global $$control_name;
                           $FOR_APACHE_LOGIN=$$control_name;
                           //echo"FOR_APACHE_LOGIN=$FOR_APACHE_LOGIN<br>";
                           }
                       }
                    }
             if(!$CONTROL_AR[$control_id])
                 {
				 //echo"show_control - $control_id<br>";
                 $CONTROL_AR[$control_id]=control_info($conn, $control_id);
                 }
			//echo"string_id=$string_id, method=$method, control_name=$control_name, value=$value<br>";
				 //echo"<pre>";
				 //print_r($CONTROL_AR[$control_id]);
			//введение новой записи
			if($CONTROL_AR[$control_id]['params']['NEW_VIEW'] && ($method=="list_new" || (($method=="form_edit" || $method=="save_form" || $method=="list_prep") && !$string_id)))
				 {
				$CONTROL_AR[$control_id]['params']['VIEW']=$CONTROL_AR[$control_id]['params']['NEW_VIEW'];
				 }
			//редактирование
			elseif($CONTROL_AR[$control_id]['params']['FORM_EDIT'] && $method=="form_edit"  && $string_id)
				 {
				$CONTROL_AR[$control_id]['NAME']=$CONTROL_AR[$control_id]['params']['FORM_EDIT'];
				 }
			elseif($CONTROL_AR[$control_id]['params']['SAVE_FORM'] && $method=="save_form"  && $string_id)
				 {
				$CONTROL_AR[$control_id]['NAME']=$CONTROL_AR[$control_id]['params']['SAVE_FORM'];
				 }
			elseif(($method=="save_form" || $method=="form_edit") && $CONTROL_AR[$control_id]['params']['OLD_FORM_EDIT'] && $string_id)
					{
					$CONTROL_AR[$control_id]['NAME']=$CONTROL_AR[$control_id]['params']['OLD_FORM_EDIT'];
					}

			if($method=="form_edit" && $addit_string_ar['print_form'])
				 {
				$CONTROL_AR[$control_id]['NAME']='Static';
				 }
			//если идет введение или сохранение новой записи и контрол скрытый
			// и есть значение по умолчанию колонки или контрола - то задается это значение
			if($method=="list_new" ||
				(	($method=="form_edit" ||
					( ($method=="save_form" || $method=="list_prep") && $CONTROL_AR[$control_id]['params']['VIEW']=="hidden"))
					&& !$string_id)	)
				 {
				$val=$info_ar['DEFAULT_VALUE']?$info_ar['DEFAULT_VALUE']:$CONTROL_AR[$control_id]['DEFAULT_VALUE'];
				//echo"val=$val<br>";
				 }
             if($method=="search_form" || $method=="search_form_addit")
                   {
                   $add_control_name="_search";
                   }
             elseif($method=="list_new")
                       {
                       $add_control_name="_new";
                       //$val=array();
                       }
             elseif($method=="list")
                       {
                       $add_control_name="[$num_row]";
                       }
		//echo"string_id=$string_id, method=$method, control_name=$control_name, val=$val<br>";
         //если мультиселекст
         if($CONTROL_AR[$control_id]['NAME']=="LookUpListM" || $CONTROL_AR[$control_id]['NAME']=="TopicM")
                {
                //echo"mult - ".$info_ar['COL_ID']."<br>";
                //$control_name=$info_ar['COL_NAME']."_";
                if($method=="search_form" || $method=="search_form_addit" || $error_form)
                   {
                   global $$control_name;
                   $val=$$control_name;
                   if($method=="search_form" || $method=="search_form_addit")
                      {
                      $addit_string_ar[$control_name]=$val;
                      $or_and_name=$control_name."_or_and";
                      global $$or_and_name;
                      $or_and_val=$$or_and_name;
                      $addit_string_ar[$or_and_name]=$or_and_val;
                      }
                   }
                if($CONTROL_AR[$control_id]['NAME']=="TopicM")// && $from)
                          {
                          $CONTROL_AR[$control_id]['params']['TREE']=1;
                          $CONTROL_AR[$control_id]['params']['FIELDS']="name_path";
                          //echo $addit_string_ar['our_ent_id']."  $method<br>";
                          }

                $get_select_down_ar=array("mult_ref_id"=>$info_ar['COL_ID'], "tree"=>$CONTROL_AR[$control_id]['params']['TREE']);
                if($CONTROL_AR[$control_id]['NAME']=="TopicM")// && !$from)
                   {
                   $get_select_down_ar["TopicM"]=1;
                   if($string_id && $method=="form_edit" && !$from )
                      {
                      if($error_form)
                         $get_select_down_ar["exist_id"]=$$control_name;
                      else
                          $get_select_down_ar["this_id"]=$string_id;
                      //чтобы выбрать только те, что прикреплены
                      }
                   elseif($method=="search_form" || $method=="search_form_addit")
                          $get_select_down_ar["exist_id"]=$val;

                   }
                //echo"col_id=".$info_ar['COL_ID']."<br>";
                if($CONTROL_AR[$control_id]['params']['FIELDS'])
                   {
                   //echo"fields=".$CONTROL_AR[$control_id]['params']['FIELDS']."<br>";
                   $get_select_down_ar['fields']=$CONTROL_AR[$control_id]['params']['FIELDS'];
                   }
                if($CONTROL_AR[$control_id]['params']['EXIST'] && $method=="search_form")
                   $get_select_down_ar["exist"]=$CONTROL_AR[$control_id]['params']['EXIST'];
                if($CONTROL_AR[$control_id]['params']['WHERE'])
                   $get_select_down_ar["params_where"]=$CONTROL_AR[$control_id]['params']['WHERE'];
                //echo"string_id=$string_id<br>";
                if($CONTROL_AR[$control_id]['NAME']=="LookUpListM" ||
                   ($CONTROL_AR[$control_id]['NAME']=="TopicM" && ($string_id || $method=="save_form" || $val || $from )) )
                   {
                   $SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]=get_select_down_ar($conn, $get_select_down_ar);
                   }
                else
                    $SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]=array();
                //echo"cotrol_name=".$CONTROL_AR[$control_id]['NAME']."<br>";
                //echo"<pre>";
                //print_r($SELECT_DOWN_AR);
                //$control_name=$SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]['table'];

                //$control_name.=$add_control_name;
                //$control_name.="[]";
                //echo"control_name=$control_name<br>";
                }//конец условия, что мультеселект
         //если включение в рассылку или индексация или пересчет дерева
         elseif($CONTROL_AR[$control_id]['NAME']=="PostIt"
            || $CONTROL_AR[$control_id]['NAME']=="IndexIt"
            || $CONTROL_AR[$control_id]['NAME']=="TreeOrderReculculation"
            || $CONTROL_AR[$control_id]['NAME']=="TreePublishReculculation"
            || $CONTROL_AR[$control_id]['NAME']=="AlterIt"
            || $CONTROL_AR[$control_id]['NAME']=="ClearCache")
               {
               $control_name_prev=$CONTROL_AR[$control_id]['NAME'];
               $control_name=$control_name_prev.$add_control_name;
               }

             if($error_form && $CONTROL_AR[$control_id]['NAME']!="Static"
                               && $CONTROL_AR[$control_id]['NAME']!="Link"
                               && $CONTROL_AR[$control_id]['NAME']!="image-link"
                               &&  $CONTROL_AR[$control_id]['NAME']!="Id" )
                {
                if($method=="form_edit" || $method=="list_new")
                   {
                   global $$control_name;
                   $val=$$control_name;
                   }
                elseif($method=="list")
                       {
                       $control_name_tmp=$info_ar['COL_NAME'];
                       global $$control_name_tmp;
                       $val_ar_tmp=$$control_name_tmp;
                       $val=$val_ar_tmp[$num_row];
                       //echo"control_name_tmp=$control_name_tmp, control=".$CONTROL_AR[$control_id]['NAME'].", val=$val<br>";
                       }
                }
			//echo"control_name=$control_name<br>";
             //конец условия, что неменяемые поля
             //формирование запросов для поиска
//.blacki ----------------------------------------------------------------------
             //Интервал дат
             if( $search && $method=="search_form" &&
                            ( ($CONTROL_AR[$control_id]['NAME']=="Date" ) || ($CONTROL_AR[$control_id]['NAME']=="DateTime") )
                            && isset($CONTROL_AR[$control_id]['params']['VIEW']) )
                    {
                    if ($val)
                        {
                        list ($date_from,$date_to) = explode(',', $val, 2);
                        $date_from_flag = ($date_from > 1) ? true : false;
                        $date_to_flag   = ($date_to   > 1) ? true : false;
                        //echo "$val = $date_from  // $date_to<br>";
                        }
                    else
                        {
                        //calendar ---------------------------------------------
                        $tmp = $control_name."_from";   global $$tmp;
                        list ($day, $month, $year) = explode ('.', $$tmp, 3);
                        //time
                        if ($CONTROL_AR[$control_id]['NAME']=="DateTime")
                            {
                            $tmp = "time_".$control_name."_from";   global $$tmp;
                            list ($hour, $minute) = explode (':', $$tmp, 2);
                            }
                        else
                            {
                            $hour = 0;
                            $minute = 0;
                            }
                        //--------------------------------------------- calendar
			//$year+=MKTIME_CORR;
                        $date_from=mktime($hour, $minute, 0, $month, $day, $year);
						$date_from=to_date_format(array("hour"=>$hour, "minute"=>$minute, "month"=>$month, "day"=>$day, "year"=>$year));
						//echo __LINE__."date_from =$date_from<br>";
                        //Проверка корректности введенной даты
                        if (
                            (
                               /*date('d-m-Y-H-i',mktime($hour, $minute, 0, $month, $day, $year))==
                               sprintf('%02d-%02d-%4d-%02d-%02d',$day,$month,$year,$hour,$minute) &&*/
                               isset($hour) && isset($minute) && $CONTROL_AR[$control_id]['NAME']=="DateTime"
                            )  ||
                            (
                               /*date('d-m-Y',mktime(0, 0, 0, $month, $day, $year))==sprintf('%02d-%02d-%4d',$day,$month,$year) &&*/
							   isset($day) && isset($month) && isset($year) &&
                               $CONTROL_AR[$control_id]['NAME']=="Date"
                            )
                           )
                             {
                             $date_from_flag=true;
                             }
                           else
                             {
                             $date_from_flag=false;
                             $$tmp="";//читска контрола от мусора
                             }

                        //calendar ---------------------------------------------
                        $tmp = $control_name."_to";   global $$tmp;
                        list ($day, $month, $year) = explode ('.', $$tmp, 3);
                        //time
                        if ($CONTROL_AR[$control_id]['NAME']=="DateTime")
                            {
                            $tmp = "time_".$control_name."_to";
                            global $$tmp;
                            list ($hour, $minute) = explode (':', $$tmp, 2);
                            }
                        else
                            {
                            $hour = 0;
                            $minute = 0;
                            }
                        //--------------------------------------------- calendar
			//$year+=MKTIME_CORR;
                        $date_to=mktime($hour, $minute, 0, $month, $day, $year);
						$date_to=to_date_format(array("hour"=>$hour, "minute"=>$minute, "month"=>$month, "day"=>$day, "year"=>$year));
						//echo __LINE__."date_to =$date_to<br>";
                        //Проверка корректности введенной даты
                        if (
							isset($year) && isset($month) && isset($day) &&(
                            (
                              /* date('d-m-Y-H-i', mktime($hour, $minute, 0, $month, $day, $year))==
                               sprintf('%02d-%02d-%4d-%02d-%02d',$day,$month,$year,$hour,$minute) &&*/
                               isset($hour) && isset($minute) && $CONTROL_AR[$control_id]['NAME']=="DateTime"
                            )  ||
                            (
                               /*date('d-m-Y',mktime(0, 0, 0, $month, $day, $year))==sprintf('%02d-%02d-%4d',$day,$month,$year) &&*/
							   isset($day) && isset($month) && isset($year) &&
                               $CONTROL_AR[$control_id]['NAME']=="Date"
                            ))
                           )
                             {
                             $date_to_flag=true;
                             }
                           else
                             {
                             $date_to_flag=false;
                             $$tmp="";//читска контрола от мусора
							 $date_to="";
                             }

                        }
                    //echo"date_from=$date_from, date_to=$date_to, ".LOWER_DATE_CONST."<br>";
                    //$ret['whereSQL']="(".$info_ar['COL_NAME'].">=".db_date($date_from, $CONTROL_AR[$control_id]['NAME']).") AND
                    //                  (".$info_ar['COL_NAME']."<=".db_date($date_to+(DEFINED("LOWER_DATE_CONST")?LOWER_DATE_CONST:0), $CONTROL_AR[$control_id]['NAME']).")";

                    if ($CONTROL_AR[$control_id]['NAME']=="Date")// если котнрол="интервал дат", то округлять значения до даты
                        {                                        // (т.е. игнорирорвать время)
                        $date_start="date(";
                        $date_end=")";
                        }

                    if ($date_from_flag)
						{
                        //$ret['whereSQL']="(".$info_ar['COL_NAME'].">=".db_date($date_from, $CONTROL_AR[$control_id]['NAME']).")";
                        $ret['whereSQL']="($date_start".$info_ar['COL_NAME']."$date_end>=$date_start".db_date($date_from, $CONTROL_AR[$control_id]['NAME'])."$date_end)";
						//echo __LINE__."whereSQL=".$ret['whereSQL']."<br>";
						}
                    if ($date_to_flag)
                        {
                        $ret['whereSQL'].=($ret['whereSQL'])?" AND ":"";
                        //$ret['whereSQL'].="(".$info_ar['COL_NAME']."<=".db_date($date_to+(DEFINED("LOWER_DATE_CONST")?LOWER_DATE_CONST:0), $CONTROL_AR[$control_id]['NAME']).")";
                        $ret['whereSQL'].="($date_start".$info_ar['COL_NAME']."$date_end<=$date_start".db_date($date_to, $CONTROL_AR[$control_id]['NAME'])."$date_end)";
                        }

                    $addit_string_ar[$control_name]="$date_from,$date_to";//$ret['whereSQL'];
                    if ($ret['whereSQL'])
                        $val="$date_from,$date_to";
                    //echo $ret['whereSQL']."<br>";
                    }
             elseif ($search && $method=="search_form" && ($CONTROL_AR[$control_id]['NAME']=="Date"))
                    {
                    if ($val!="")
                        {
                        //calendar ---------------------------------------------
                        list ($day, $month, $year) = explode ('.', $val, 3);
                        $val=mktime(0, 0, 0, $month, $day, $year);
                        $ret['whereSQL']="date(".$info_ar['COL_NAME'].")=date(".db_date($val).")";
                        $val=date('Y-m-d',$val);
                        //--------------------------------------------- calendar
                        }
                    }
//---------------------------------------------------------------------- .blacki
             elseif(($val!="" || (is_array($val) && count($val))) && $search && $method=="search_form")
                   {
                   //$this_col=$info_ar['tab_string'].".".$info_ar['COL_NAME'];
                   $this_col=$info_ar['TABLE_NAME'].".".$info_ar['COL_NAME'];
                   if($CONTROL_AR[$control_id]['NAME']=="Input")
                      {
                      if(!$info_ar['REF_COLUMN_ID'])
                          $ret['whereSQL']="upper(".$this_col.") like('%'||upper('$val')||'%')";
                      else
                          $ret['whereSQL']="upper(".$info_ar['TABLE_NAME'].".name) like('%'||upper('$val')||'%')";
                      }
                   elseif($CONTROL_AR[$control_id]['NAME']=="LookUpCombo" && $val==BASE_NULL)
                          {
                          $ret['whereSQL']=$this_col." IS NULL";
                          }
                   elseif($CONTROL_AR[$control_id]['NAME']=="LookUpListM"
                          || $CONTROL_AR[$control_id]['NAME']=="TopicM" )//мультиселект
                          {
                          //echo"1 or_and_name=$or_and_name=$or_and_val<br>";
                          if($or_and_val==2)
                             $or_and_SQL=" and ";
                          else
                             $or_and_SQL=" or ";
                          if($CONTROL_AR[$control_id]['NAME']=="TopicM")
                             $val=split(",", $val);
                          foreach($val as $k_mult=>$v_mult)
                                  {
                                  //echo"$k_mult=>$v_mult<br>";
                                  if($v_mult==BASE_NULL)
                                     $ret['whereSQL'].=($ret['whereSQL']?$or_and_SQL:"").$entity_info['table'].".id not in (".$SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]['search_short_q'].")";
                                  else
                                      $ret['whereSQL'].=($ret['whereSQL']?$or_and_SQL:"").$entity_info['table'].".id in (".$SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]['search_q'].$v_mult.")";
                                  }
                          $ret['whereSQL']="(".$ret['whereSQL'].")";
                          //echo"whereSQL=".$ret['whereSQL']."<br>";
                          }
                   else
                       $ret['whereSQL']=$this_col."='$val'";
                   }

             //echo "control_id=$control_id, ".$CONTROL_AR[$control_id]['NAME']."<br>";
             //вывод html
             if($method=="search_form" || $method=="list" || $method=="list_new" || $method=="form_edit")
                {
                ob_start();
                //echo"val=$val, method=$method <br>";
				if($CONTROL_AR[$control_id]['params']['VIEW']=="hidden" && ($method=="list_new" || ($method=="form_edit" && !$string_id) ) && $val)
					{
					;//ничего не показываем, если введение новой записи и есть значение по умолчанию
					}
                elseif($CONTROL_AR[$control_id]['NAME']=="Static")
                   {
                   if(!$info_ar['REF_COLUMN_ID'])
                       {
                       //echo $info_ar['COL_TYPE']."<br>";
                       if($info_ar['COL_TYPE']=="date")
                          {
                          $date_ar=date_format_ar($val);
                          //echo"<pre>";
                          //print_r($date_ar);
                          echo $date_ar["d"]."-".$date_ar["m"]."-".$date_ar["Y"];
                          //if(intval($date_ar["H"]) || intval($date_ar["i"]) || intval($date_ar["s"]))
                          if(!$CONTROL_AR[$control_id]['params']['NO_TIME'])
                             echo " ".$date_ar["H"].":".$date_ar["i"];//.":".$date_ar["s"];
                          //br();
                          //echo $val;
                          }
	                       else{
	                       	   if(!$CONTROL_AR[$control_id]['params']['VALUE']){
                           echo $val;
	                       	   }else{
	                       	   	   $newval = explode("/",$CONTROL_AR[$control_id]['params']['VALUE']);
								   echo $newval[intval($val)];
	                       	   }
	                       }
                       }
                   else //если это ссылочная колонка
                       {
                       if($method=="list")
                          {
                          echo $val_ar[$COLUMN_AR[$info_ar['ARM_COLUMN_ID']]['column_name']];
                          }
                       else
                           {
                           if(!$SELECT_DOWN_AR[$info_ar['REF_COLUMN_ID']][$control_id])
                               {
                               $SELECT_DOWN_AR[$info_ar['REF_COLUMN_ID']][$control_id]=get_select_down_ar($conn, array("ref_id"=>$info_ar['REF_COLUMN_ID'], 'fields'=>$CONTROL_AR[$control_id]['params']['FIELDS']));
                               }
                           echo $SELECT_DOWN_AR[$info_ar['REF_COLUMN_ID']][$control_id][$val];
                           }
                       }
                   }
                   elseif(($CONTROL_AR[$control_id]['NAME']=="Image" || $CONTROL_AR[$control_id]['NAME']=="Download") && ($method=="form_edit" || $method=="list" || $method=="list_new"))
                   {
                       //echo __LINE__." ".$CONTROL_AR[$control_id]['NAME']."<br>";
                       if($method=="form_edit" || $method=="list")
                       {
                           if($val_ar[strtoupper($info_ar['COL_NAME'])."_TYPE_ID"])
                           {
                               $type=get_byId($conn, TABLE_PRE."file_types", $val_ar[strtoupper($info_ar['COL_NAME'])."_TYPE_ID"]);
                               //echo __LINE__." type=$type<br>";
                           }
                           //echo"type=$type, ".$val_ar[strtoupper($info_ar['COL_NAME'])]."<br>";
                           if($val_ar[strtoupper($info_ar['COL_NAME'])])
                           {
                               if(ereg("image", $type['NAME']) && $CONTROL_AR[$control_id]['NAME']=="Image")
                               {
                                   if($CONTROL_AR[$control_id]['params']['VIEW']!="details")
                                   {
                                       echo img_src("/download_any_file.html?our_ent_id=".($string_id?$string_id:$val_ar['ID'])."&col_id=".$info_ar['COL_ID']."&show=1");
                                   }
                                   else
                                   {
                                       href("/download_any_file.html?our_ent_id=".($string_id?$string_id:$val_ar['ID'])."&col_id=".$info_ar['COL_ID']."&show=1", "Просмотреть", 'target="_blank"');
                                       br();
                                       if($val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] && $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"])
                                       {
                                           echo$val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] ."*". $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"];
                                       }
                                       if($val_ar[strtoupper($info_ar['COL_NAME'])."_SIZE"])
                                       {
                                           if($val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] && $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"])
                                               echo", ";
                                           echo around_bytes($val_ar[strtoupper($info_ar['COL_NAME'])."_SIZE"]);
                                           br();
                                       }
                                   }

                               }
                               else
                               {
                                   href("/download_any_file.html?our_ent_id=".($string_id?$string_id:$val_ar['ID'])."&col_id=".$info_ar['COL_ID']."&show=$show", "Скачать файл");
                                   br();
                                   echo around_bytes($val_ar[strtoupper($info_ar['COL_NAME'])."_SIZE"]);
                               }
                           }
                       }
                   }
                   elseif(($CONTROL_AR[$control_id]['NAME']=="preview-image") && ($method=="form_edit" || $method=="list" || $method=="list_new"))
                   {
                       //echo __LINE__." ".$CONTROL_AR[$control_id]['NAME']."<br>";
                       if($method=="form_edit" || $method=="list")
                       {
                           if($val_ar[strtoupper($info_ar['COL_NAME'])."_TYPE_ID"])
                           {
                               $type=get_byId($conn, TABLE_PRE."file_types", $val_ar[strtoupper($info_ar['COL_NAME'])."_TYPE_ID"]);
                               //echo __LINE__." type=$type<br>";
                           }
                           //echo"type=$type, ".$val_ar[strtoupper($info_ar['COL_NAME'])]."<br>";
                           if($val_ar[strtoupper($info_ar['COL_NAME'])])
                           {
                               if(ereg("image", $type['NAME']) && $CONTROL_AR[$control_id]['NAME']=="preview-image")
                               {//echo '<pre>'; print_r($val_ar); print_r( $CONTROL_AR); echo '</pre>';
                                   if($CONTROL_AR[$control_id]['params']['VIEW']!="details")
                                   {
                                       echo img_src("/download_any_file.html?our_ent_id=".($string_id?$string_id:$val_ar['ID'])."&col_id=".$info_ar['COL_ID']."&show=1");
                                   }
                                   else
                                   {
                                   if(!$CONTROL_AR[$control_id]['params']['h']){
                                       echo img_src("/preview.html?our_ent_id=".($string_id?$string_id:$val_ar['ID'])."&col_id=".$info_ar['COL_ID']."&show=1&h=40",' title="'.$val_ar['NAME'].'" alt="'.$val_ar['ALTER_NAME'].'" ');br();
                                   }else{
                                       echo img_src("/preview.html?our_ent_id=".($string_id?$string_id:$val_ar['ID'])."&col_id=".$info_ar['COL_ID']."&show=1&h=".$CONTROL_AR[$control_id]['params']['h'],' title="'.$val_ar['NAME'].'" alt="'.$val_ar['ALTER_NAME'].'" ');br();
                                   }
                                       href("/download_any_file.html?our_ent_id=".($string_id?$string_id:$val_ar['ID'])."&col_id=".$info_ar['COL_ID']."&show=1", $val_ar[strtoupper($info_ar['COL_NAME'])."_NAME"], 'target="_blank"');
                                       br();
                                       if($val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] && $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"])
                                       {
                                           echo $val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] ."*". $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"];
                                       }
                                       if($val_ar[strtoupper($info_ar['COL_NAME'])."_SIZE"])
                                       {
                                           if($val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] && $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"])
                                               echo", ";
                                           echo around_bytes($val_ar[strtoupper($info_ar['COL_NAME'])."_SIZE"]);
                                           br();
                                       }
                                   }
                               }
                               else
                               {
                                   href("/download_any_file.html?our_ent_id=".($string_id?$string_id:$val_ar['ID'])."&col_id=".$info_ar['COL_ID']."&show=$show", "Скачать файл");
                                   br();
                                   echo around_bytes($val_ar[strtoupper($info_ar['COL_NAME'])."_SIZE"]);
                               }
                           }
                       }
                   }
				   elseif($CONTROL_AR[$control_id]['NAME']=="Link")
				   {
					   //echo"lookup=$lookup<br>";
					   $href=g_URL."?"."our_ent_id=".$val_ar['ID'].addit_string_form(array("addit_string_ar"=>$addit_string_ar, "ret_format"=>"string" ));
					   //echo"g_URL=".g_URL."<br>";
					   if($lookup)
					   {
						   //$addit_href=" onclick=\"return select_entity(".$val_ar['ID'].", '$val', '$lookup_name', '');\"";
						   //$addit_td=" class=\"Link\"".$addit_href;
						   //$addit_td=" class=\"Link\"  onclick=\"return select_entity(".$val_ar['ID'].", '".htmlentities ($val, ENT_QUOTES)."', '$lookup_name', '');\"";
						   $addit_td=" class=\"Link\"  onclick=\"return select_entity(".$val_ar['ID'].", '".strip_tags(ereg_replace("['\"]", "", $val))."', '$lookup_name', '');\"";
					   }
					   else
					   {
						   //$addit_td="onclick=\"location.replace('$href');\" onmouseover=\"this.style.cursor='hand';\" class=\"Link\"";
						   $addit_td="class=\"Link\"";
					   }
					   if(!$info_ar['REF_COLUMN_ID'])
						   $href_text=$val;
					   else //если это ссылочная колонка
					   {
						   if($method=="list")
						   {
							   //echo"<pre>";
							   //print_r($info_ar);
							   //$key_ref=;
							   //echo"column_name[$control_id]=".$COLUMN_AR[$control_id]['column_name']."<br>";
							   $href_text=$val_ar[$COLUMN_AR[$info_ar['ARM_COLUMN_ID']]['column_name']];
						   }
						   else
						   {
							   if(!$SELECT_DOWN_AR[$info_ar['REF_COLUMN_ID']][$control_id])
							   {
								   $SELECT_DOWN_AR[$info_ar['REF_COLUMN_ID']][$control_id]=get_select_down_ar($conn, array("ref_id"=>$info_ar['REF_COLUMN_ID']));
							   }
							   $href_text=$SELECT_DOWN_AR[$info_ar['REF_COLUMN_ID']][$control_id][$val];
						   }
					   }
					   href($href, $href_text, $addit_href);
				   }
				   elseif($CONTROL_AR[$control_id]['NAME']=="image-link")
				   {
					   $href=g_URL."?"."our_ent_id=".$val_ar['ID'].addit_string_form(array("addit_string_ar"=>$addit_string_ar, "ret_format"=>"string"));
					   if($lookup)
					   {
					   	   foreach($val_ar as $aikey=>$aival){
				   	   	   if(stripos($aikey,"_WIDTH")>0){$wi = $aival;}
				   	   	   elseif(stripos($aikey,"_HEIGHT")>0){$hi = $aival;}
					   	   }
						   $addit_td=" class=\"Link\"  onclick=\"return select_entity(".$val_ar['ID'].", '".strip_tags(ereg_replace("['\"]", "", $val))."', '$lookup_name', '',".$wi.",".$hi.");\"";
					   }
					   else
					   {
						   $addit_td="class=\"Link\"";
					   }
					   if(!$info_ar['REF_COLUMN_ID'])
						   $href_text=$val;
					   else //если это ссылочная колонка
					   {
						   if($method=="list")
						   {
							   $href_text=$val_ar[$COLUMN_AR[$info_ar['ARM_COLUMN_ID']]['column_name']];
						   }
						   else
						   {
							   if(!$SELECT_DOWN_AR[$info_ar['REF_COLUMN_ID']][$control_id])
							   {
								   $SELECT_DOWN_AR[$info_ar['REF_COLUMN_ID']][$control_id]=get_select_down_ar($conn, array("ref_id"=>$info_ar['REF_COLUMN_ID']));
							   }
							   $href_text=$SELECT_DOWN_AR[$info_ar['REF_COLUMN_ID']][$control_id][$val];
						   }
					   }
					   href($href, $href_text, $addit_href);
				   }
				   elseif(($CONTROL_AR[$control_id]['NAME']=="Input" || $CONTROL_AR[$control_id]['NAME']=="InputInt" )&& $CONTROL_AR[$control_id]['params']['VIEW']=="hidden")
					   forminput("hidden", $control_name, $val, '', $addit_ctrl.($info_ar['COLUMN_LENGTH']?" maxlength=\"".$info_ar['COLUMN_LENGTH']."\" size=\"".$info_ar['COLUMN_LENGTH']."\"":"")." style=\"width:100%\"");
				   elseif($CONTROL_AR[$control_id]['NAME']=="Input")
                       {
                       //определяем ширину контрола
                       $addit_this_ctrl=show_control_width(array("control_type_name"=>$CONTROL_AR[$control_id]['NAME'], "col_length"=>$info_ar['COLUMN_LENGTH'], "method"=>$method, "width"=>$CONTROL_AR[$control_id]['params']['WIDTH']));
                       forminput("text", $control_name, for_input($val), '', $addit_ctrl.($info_ar['COLUMN_LENGTH']?" maxlength=\"".$info_ar['COLUMN_LENGTH']."\" ":"").$addit_this_ctrl);
                       }
                elseif($CONTROL_AR[$control_id]['NAME']=="InputInt")// || $CONTROL_AR[$control_id]['NAME']=="Rating" )
                       {
						forminput("text", $control_name, $val, '', $addit_ctrl.($info_ar['COLUMN_LENGTH']?" maxlength=\"".$info_ar['COLUMN_LENGTH']."\" size=\"".$info_ar['COLUMN_LENGTH']."\"":"").($only_id?"":" style=\"width:100%\""));
                       }
//---------------Date + DateTime (interval)
                   elseif( ( ($CONTROL_AR[$control_id]['NAME']=="Date" ) || ($CONTROL_AR[$control_id]['NAME']=="DateTime") )   &&   $CONTROL_AR[$control_id]['params']['VIEW']=="interval")
                          {
                          //echo __LINE__." val= $val<br>";
                          if ($val)
                              {
                              //date_from, date_to
                              list ($date_from,$date_to) = explode(',', $val, 2);
                              //echo __LINE__." date_from=$date_from | date_to=$date_to <br>";
                              //$date = date('d-m-Y-H-i',$date_from);
                              list ($year, $month, $day, $hour, $minute) = split('[- :]', $date_from, 5);
                              //echo __LINE__." $day, $month, $year, $hour, $minute<br>";
				//$year-=MKTIME_CORR;
                              //exit;
                              }
                          else
                              {
                              //calendar ---------------------------------------------
							  //echo __LINE__." $day, $month, $year, $hour, $minute<br>";
                              $tmp = $control_name."_from";   global $$tmp;
                              list ($day, $month, $year) = explode ('.', $$tmp, 3);
                              //time
                              if ($CONTROL_AR[$control_id]['NAME']=="DateTime")
                              {
                                  $tmp = "time_".$control_name."_from";   global $$tmp;
                                  list ($hour, $minute) = explode (':', $$tmp, 2);
                              }
                              else
                              {
                                  $hour = 0;
                                  $minute = 0;
                              }
                              //--------------------------------------------- calendar
                              }
                          //echo "$month, $day, $year <br>"; exit;
                          if( !(isset($month) && isset($day) && isset($year)) ||
                              ($CONTROL_AR[$control_id]['NAME']=="DateTime" && !(isset($hour) && isset($minute) ) ))
                             {
                             $empty_date_from=true;
                             //$date = date('d-m-Y-H-i',mktime(0, 0, 0, date("n"), 1, date("Y")));
                             //list ($day, $month, $year, $hour, $minute) = explode('-', $date, 5);
                             }

                          echo "с:&nbsp;";
                          $date = $day.(($day&&($month||$year))?".":"").$month.((($day||$month)&&$year)?".":"").$year;
                          forminput("text", $control_name."_from", ($empty_date_from)?"":$date, '', "maxlength='10' size='11' class='plain' ".$addit_ctrl);
						  echo calendar_but($control_name."_from",$method);

                          if($CONTROL_AR[$control_id]['NAME']=="DateTime")//Если, кроме даты, нужно еще и время
                              {
                              nbsp(1);//echo "Время ";
                              $time = $hour.($minute?":":"").$minute;
                              forminput("text", "time_".$control_name."_from", ($empty_date_from)?"":$time, '', "maxlength='5' size='5' class='plain' ".$addit_ctrl);
                              }
                          ######################################################
                          # date_to
                          if ($val)
                              {
                              //$date = date('d-m-Y-H-i',$date_to);
                              list ($year, $month, $day, $month, $hour, $minute) = split('[- :]', $date_to, 5);
				//$year-=MKTIME_CORR;
                              }
                          else
                              {
                              //calendar ---------------------------------------------
                              $tmp = $control_name."_to";   global $$tmp;
                              list ($day, $month, $year) = explode ('.', $$tmp, 3);
                              //time
                              if ($CONTROL_AR[$control_id]['NAME']=="DateTime")
                              {
                                  $tmp = "time_".$control_name."_to";   global $$tmp;
                                  list ($hour, $minute) = explode (':', $$tmp, 2);
                              }
                              else
                              {
                                  $hour = 0;
                                  $minute = 0;
                              }
                              //--------------------------------------------- calendar
                              }
                          //echo "$month, $day, $year <br>";
                          if( !($month AND $day AND $year) OR
                              ($CONTROL_AR[$control_id]['NAME']=="DateTime" AND !(isset($hour) AND isset($minute) ) ))
                             {
                             //$date = date('d-m-Y-H-i',mktime());
                             //list ($day, $month, $year, $hour, $minute) = explode('-', $date, 5);
                             $empty_date_to=true;
                             }

                          echo "&nbsp;&nbsp;по:&nbsp;";
                          $date = $day.(($day&&($month||$year))?".":"").$month.((($day||$month)&&$year)?".":"").$year;
                          forminput("text", $control_name."_to", ($empty_date_to)?"":$date, '', "maxlength='10' size='11' class='plain' ".$addit_ctrl);
						  calendar_but($control_name."_to",$method);
                          if($CONTROL_AR[$control_id]['NAME']=="DateTime")//Если, кроме даты, нужно еще и время
                              {
                              $time = $hour.($minute?":":"").$minute;
                              nbsp(1);//echo "Время ";
                              forminput("text", "time_".$control_name."_to", ($empty_date_to)?"":$time, '', "maxlength='5' size='5' class='plain' ".$addit_ctrl);
                              }

                          }
//---------------Date + DateTime
                elseif( ($CONTROL_AR[$control_id]['NAME']=="Date") || ($CONTROL_AR[$control_id]['NAME']=="DateTime") )
                       {
						//echo __LINE__." 2 <br>";
                       if ($val == "" )
                       {
						   if(!$info_ar['NULL_VALUE'])
						   {
                            $date = date('d-m-Y-H-i',mktime());
                            list ($day, $month, $year, $hour, $minute) = explode('-', $date, 5);
						   }
                       }
                       else
                       {
                           //calendar --------------------------------------
                            if(ereg("\.",$val) && !ereg("\-",$val))
                               {
                               list ($day, $month, $year) = explode ('.', $val, 3);
                               $tmp = "time_".$control_name;
                               global $$tmp;
                               $time = $$tmp; //get time from $_POST[]
                               if (is_array($time))$time = $time[$num_row];
                               list ($hour, $minute) = explode(':', $time, 2);
                               }
                            else
                               {
                           //-------------------------------------- calendar
                               list ($date, $time) = explode(' ', $val,2);
                               list ($year, $month, $day) = explode ('-', $date, 3);
                               list ($hour, $minute, $sec) = explode (':', $time, 3);
                               }
                            //echo __LINE__."$year, $month, $day $hour, $minute";
                       }

                       $control_name1=str_replace(array("[", "]"),array("_", ""),$control_name);
                       $date = $day.(($day&&($month||$year))?".":"").$month.((($day||$month)&&$year)?".":"").$year;
					   //echo __LINE__."addit_ctrl=$addit_ctrl, val=$val<br>";
                       forminput("text", $control_name1, $date, '', "maxlength='10' size='11' class='plain' ".$addit_ctrl);
						calendar_but($control_name1,$method);

                       if($CONTROL_AR[$control_id]['NAME']=="DateTime")//Если, кроме даты, нужно еще и время
                                {
                                $time = $hour.($minute?":":"").$minute;
                                nbsp(1);//echo "Время ";
                                forminput("text", "time_".$control_name1, $time, '', "maxlength='5' size='5' class='plain' ".$addit_ctrl);
                                }
                       }
//---------------Password + HTPassword
                elseif(($CONTROL_AR[$control_id]['NAME']=="Password"
                                   || $CONTROL_AR[$control_id]['NAME']=="HTPassword") && $method=="form_edit")
                       {
                       //echo $addit_ctrl.($info_ar['COLUMN_LENGTH']?" maxlength=\"".$info_ar['COLUMN_LENGTH']."\" size=\"".$info_ar['COLUMN_LENGTH']."\"":"")."<br>";
                       //forminput("password", $control_name."_1", $val, '', " ID='password1' ".$addit_ctrl.($info_ar['COLUMN_LENGTH']?" maxlength=\"".$info_ar['COLUMN_LENGTH']."\" size=\"".$info_ar['COLUMN_LENGTH']."\"":""));

                       //.1 forminput("password", $control_name."_1", $val, '', " ID='password1' ".$addit_ctrl.($info_ar['COLUMN_LENGTH']?" maxlength=\"".$info_ar['COLUMN_LENGTH']."\" ":""));
                       forminput("password", $control_name."_1", '', '', " ID='password1' ".$addit_ctrl.($info_ar['COLUMN_LENGTH']?" maxlength=\"".$info_ar['COLUMN_LENGTH']."\" ":""));
                       br();
                       //echo "Подтвердить:";
                       //forminput("password", $control_name."_2", $val, '', " ID='password2' ".$addit_ctrl.($info_ar['COLUMN_LENGTH']?" maxlength=\"".$info_ar['COLUMN_LENGTH']."\" size=\"".$info_ar['COLUMN_LENGTH']."\"":""));

                       //.1 forminput("password", $control_name."_2", $val, '', " ID='password2' ".$addit_ctrl.($info_ar['COLUMN_LENGTH']?" maxlength=\"".$info_ar['COLUMN_LENGTH']."\" ":""));
                       forminput("password", $control_name."_2", '', '', " ID='password2' ".$addit_ctrl.($info_ar['COLUMN_LENGTH']?" maxlength=\"".$info_ar['COLUMN_LENGTH']."\" ":""));
                       }
//---------------RadioSet
                elseif($CONTROL_AR[$control_id]['NAME']=="RadioSet")
                       {
                       $ref_id=$info_ar['REF_COLUMN_ID'];
                       if($ref_id)
                          {
                          if(!$SELECT_DOWN_AR[$ref_id][$control_id])
                              {
                              $get_select_down_ar=array("ref_id"=>$ref_id);
                              if($CONTROL_AR[$control_id]['params']['FIELDS'])
                                 $get_select_down_ar['fields']=$CONTROL_AR[$control_id]['params']['FIELDS'];
                              $SELECT_DOWN_AR[$ref_id][$control_id]=get_select_down_ar($conn, $get_select_down_ar);
                              }
                           foreach($SELECT_DOWN_AR[$ref_id][$control_id] as $k=>$v)
                               {
                                 if (($val) && ($val==$k))
                                      forminput("radio", $control_name, $k, '', $addit_ctrl." checked");
                                 else
                                      forminput("radio", $control_name, $k, '', $addit_ctrl);
                                 echo $v;
                               }
                          }
                       }
//---------------------------------------------------------------------- .blacki
                elseif($CONTROL_AR[$control_id]['NAME']=="File" || $CONTROL_AR[$control_id]['NAME']=="SaveTemplate")
                       {
                       if($method=="form_edit")
                          {
                          //echo"control_name=$control_name, col_name=".$info_ar['COL_NAME']."<br>";
                          if(!eregi("width", $addit_ctrl))
                              $addit_ctrl.=' style="width:300px;"';

                          forminput("file", $control_name, '', '', $addit_ctrl);
                          br();
                          echo"Сохранить как";
                          br();
                          $file_save_as=$control_name."_name_save";
                          global $$file_save_as;
                          //echo"control_name=$control_name<br>";
						  $file_save_as_field_val=$val_ar[strtoupper($info_ar['COL_NAME'])."_NAME"]? $val_ar[strtoupper($info_ar['COL_NAME'])."_NAME"]:$$file_save_as;
						  if($CONTROL_AR[$control_id]['NAME']=="SaveTemplate" && $val_ar[strtoupper($info_ar['COL_NAME'])."_PATH"])
							  $file_save_as_field_val=$val_ar[strtoupper($info_ar['COL_NAME'])."_PATH"]."/".$file_save_as_field_val;

                          forminput("text", $file_save_as, ($file_save_as_field_val), '', $addit_ctrl);
                          br();
                          if($val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] && $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"])
                             {
                             echo$val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] ."*". $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"];
                             }
                          if($val_ar[strtoupper($info_ar['COL_NAME'])."_SIZE"])
                             {
                             if($val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] && $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"])
                                echo", ";
							echo around_bytes($val_ar[strtoupper($info_ar['COL_NAME'])."_SIZE"]);
                             br();
                             }

                          if($val_ar[strtoupper($info_ar['COL_NAME'])."_TYPE_ID"])
                             {
                             $type=get_byId($conn, TABLE_PRE."file_types", $val_ar[strtoupper($info_ar['COL_NAME'])."_TYPE_ID"]);
                             echo $type['NAME'];
                             br();
                             }
                          if($val_ar[strtoupper($info_ar['COL_NAME'])])
                             {
                             if(ereg("image", $type['NAME']))
                                {
                                $show=1;
                                $addit='target="_blank" onclick="return ow(this.href);"';
                                }
                             else
                                 {
                                 $show=0;
                                 $addit="";
                                 }
                             href("/download_any_file.html?our_ent_id=$string_id&col_id=".$info_ar['COL_ID']."&show=$show", "Просмотреть файл", $addit);
                             //echo $val_ar[strtoupper($control_name)];
                             }
                          }
                       else
                           echo NO_CONTROL_REALIZE;

                       }
                elseif($CONTROL_AR[$control_id]['NAME']=="MultiFile")
                {
                       if($method=="form_edit")
                          {
                          //echo"control_name=$control_name, col_name=".$info_ar['COL_NAME']."<br>";
                          if(!eregi("width", $addit_ctrl))
                              $addit_ctrl.=' style="width:300px;"';

                          forminput("file", $control_name, '', '', $addit_ctrl);
                          br();
                          echo"Сохранить как";
                          br();
                          $file_save_as=$control_name."_name_save";
                          global $$file_save_as;
                          //echo"control_name=$control_name<br>";
                          $file_save_as_field_val=$val_ar[strtoupper($info_ar['COL_NAME'])."_NAME"]? $val_ar[strtoupper($info_ar['COL_NAME'])."_NAME"]:$$file_save_as;
                          if($CONTROL_AR[$control_id]['NAME']=="SaveTemplate" && $val_ar[strtoupper($info_ar['COL_NAME'])."_PATH"])
                              $file_save_as_field_val=$val_ar[strtoupper($info_ar['COL_NAME'])."_PATH"]."/".$file_save_as_field_val;

                          forminput("text", $file_save_as, ($file_save_as_field_val), '', $addit_ctrl);
                          br();
                          if($val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] && $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"])
                             {
                             echo$val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] ."*". $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"];
                             }
                          if($val_ar[strtoupper($info_ar['COL_NAME'])."_SIZE"])
                             {
                             if($val_ar[strtoupper($info_ar['COL_NAME'])."_WIDTH"] && $val_ar[strtoupper($info_ar['COL_NAME'])."_HEIGHT"])
                                echo", ";
                            echo around_bytes($val_ar[strtoupper($info_ar['COL_NAME'])."_SIZE"]);
                             br();
                             }

                          if($val_ar[strtoupper($info_ar['COL_NAME'])."_TYPE_ID"])
                             {
                             $type=get_byId($conn, TABLE_PRE."file_types", $val_ar[strtoupper($info_ar['COL_NAME'])."_TYPE_ID"]);
                             echo $type['NAME'];
                             br();
                             }
                          if($val_ar[strtoupper($info_ar['COL_NAME'])])
                             {
                             if(ereg("image", $type['NAME']))
                                {
                                $show=1;
                                $addit='target="_blank" onclick="return ow(this.href);"';
                                }
                             else
                                 {
                                 $show=0;
                                 $addit="";
                                 }
                            href("/download_any_file.html?our_ent_id=$string_id&col_id=".$info_ar['COL_ID']."&show=$show", "Просмотреть файл", $addit);
                             //img_src("/preview.php?id=$string_id",$addit);
                             //echo $val_ar[strtoupper($control_name)];
                             }
                          }
                       else{
                        //img_src("/preview.php?id=$string_id&h=100",$addit);
                        echo NO_CONTROL_REALIZE;
                       }
                }
                //выпадающий список - выбор одного
                elseif($CONTROL_AR[$control_id]['NAME']=="LookUpCombo"
						|| $CONTROL_AR[$control_id]['NAME']=="LookUpList"
						|| $CONTROL_AR[$control_id]['NAME']=="Topic")
                   {

                   $ref_id=$info_ar['REF_COLUMN_ID'];
                   if($ref_id)
						{
						if(!$SELECT_DOWN_AR[$ref_id][$control_id])
							{
							$get_select_down_ar=array("ref_id"=>$ref_id);
							//echo"params=".$CONTROL_AR[$control_id]['params']['FIELDS']."<br>";
							if($CONTROL_AR[$control_id]['params']['FIELDS'])
								$get_select_down_ar['fields']=$CONTROL_AR[$control_id]['params']['FIELDS'];
			                if($CONTROL_AR[$control_id]['params']['WHERE'])
								{
								$get_select_down_ar["exist_id"]=$val;
						       $get_select_down_ar["params_where"]=$CONTROL_AR[$control_id]['params']['WHERE'];
								}
							if($CONTROL_AR[$control_id]['NAME']=="Topic")
								{
								//echo"val=$val<br>";
								if(!$CONTROL_AR[$control_id]['params']['TREE'])
									$CONTROL_AR[$control_id]['params']['TREE']=1;
								$CONTROL_AR[$control_id]['params']['FIELDS']="name_path";
								$get_select_down_ar["fields"]=$CONTROL_AR[$control_id]['params']['FIELDS'];		$get_select_down_ar[$CONTROL_AR[$control_id]['NAME']]=$CONTROL_AR[$control_id]['NAME'];
								if(!$from && $val)//если не во всплывающем окне
									$get_select_down_ar["exist_id"]=$val;
								}

							if($CONTROL_AR[$control_id]['params']['TREE'])
								$get_select_down_ar["tree"]=$CONTROL_AR[$control_id]['params']['TREE'];
							if($CONTROL_AR[$control_id]['params']['SORT_BY'])
								$get_select_down_ar["sort_by"]=$CONTROL_AR[$control_id]['params']['SORT_BY'];
							if($CONTROL_AR[$control_id]['params']['EXIST'] && $method=="search_form")
								{
								$get_select_down_ar["exist"]=$CONTROL_AR[$control_id]['params']['EXIST'];
								$get_select_down_ar["exist_table_name"]=$info_ar['TABLE_NAME'];//главная таблица
								$get_select_down_ar["exist_col_name"]=$info_ar['COL_NAME'];
								}
							if($CONTROL_AR[$control_id]['NAME']!="Topic" || $val || $from)
								$SELECT_DOWN_AR[$ref_id][$control_id]=get_select_down_ar($conn, $get_select_down_ar);

							}
						if($CONTROL_AR[$control_id]['NAME']=="LookUpCombo"
							|| $CONTROL_AR[$control_id]['NAME']=="LookUpList")
							{
							//определяем ширину контрола
							$max_len_in_array=max_len_in_array($SELECT_DOWN_AR[$ref_id][$control_id]);			$addit_this_ctrl=show_control_width(array("control_type_name"=>$CONTROL_AR[$control_id]['NAME'], "col_length"=>$max_len_in_array, "method"=>$method, "width"=>$CONTROL_AR[$control_id]['params']['WIDTH']));
							//echo"max_len_in_array=$max_len_in_array<br>";

							$showselect=array("down_ar"=>$SELECT_DOWN_AR[$ref_id][$control_id], "name"=>$control_name, "def_val"=>$val, "nullval"=>$info_ar['NULL_VALUE'], "addit"=>$addit_ctrl.$addit_this_ctrl);
							//echo"null_value=".$info_ar['NULL_VALUE']."<br>";
							if($method=="search_form")
								{
								$showselect['nullval']=1;

								if($info_ar['NULL_VALUE'])
									{
									//echo"null_value";
									$showselect['null_sel']=1;
									}
								}
							if($CONTROL_AR[$control_id]['NAME']=="LookUpList")
								{
								if($CONTROL_AR[$control_id]['params']['SIZE'])
									$showselect['addit']=$addit_ctrl.' size="'.$CONTROL_AR[$control_id]['params']['SIZE'].'"';
								elseif(count($SELECT_DOWN_AR[$ref_id][$control_id]))
									$showselect['addit']=$addit_ctrl.' size="'.(count($SELECT_DOWN_AR[$ref_id][$control_id])+ $showselect['null_sel']+$showselect['nullval']).'"';
								}
							showselect_ar($showselect);
							} // конец условия LookUpCombo и  LookUpList
						else//Topic
							{
							//echo"Topic - $val<br>";

							//if(is_array($SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]))
							//  {

							if(!$from)
                               {
                               forminput("hidden", $control_name, $val, '', "ID=\"$control_name\"");//выбранное значение
                               $addit_ctrl.=" disabled ";
                               //определяем ширину контрола
                               $max_len_in_array=max_len_in_array($SELECT_DOWN_AR[$ref_id][$control_id]);
                               //echo"max_len_in_array=$max_len_in_array<br>";
                               $addit_this_ctrl=show_control_width(array("control_type_name"=>$CONTROL_AR[$control_id]['NAME'], "col_length"=>$max_len_in_array, "method"=>$method, "width"=>$CONTROL_AR[$control_id]['params']['WIDTH']));
                               table('border="0" bordercolor="#ff0000" width="100%"');
                               trtd('', 'align="left" '.$addit_this_ctrl);
                               //div('ID="'.$control_name.'_view" align="left"');
                               }
							else
								{
								trtd('height="90%"');
								}

							//echo"$value, $val<pre>";
							//print_r($value);
							//echo"<br>val[".BASE_NULL."]=".$val[BASE_NULL].", ".in_array(BASE_NULL)."<br>";
							$showselect=array("name"=>$control_name."_view[]", "addit"=>$addit_ctrl.'ID="'.$control_name."_view".'"');
							$showselect["id_val"]=($string_id?$string_id:$val_ar['ID']);


							if($method=="search_form")
								{
								if($info_ar['NULL_VALUE'])
									{
									$showselect['null_sel']=1;
									}
							     $showselect['def_val']=$val;
                                 }

							$showselect['conn']=$conn;
							$showselect["down_ar"]=$SELECT_DOWN_AR[$ref_id][$control_id];
							if(!$from)
                               {
                               $showselect['div']='ID="'.$control_name.'_view" align="left"';
                               $showselect['addit'].=$addit_this_ctrl;
                               }
							else
                               $showselect['from']=$from;
							/*
							if($method=="search_form")
                              {
                              if(in_array(BASE_NULL, $val) || $from)
                                 $showselect['null_sel']=1;

                              }
							 */
							//echo"from=$from, count=".count($showselect["down_ar"])."<br>";
							if(!$from && !count($showselect["down_ar"]))
								{
								$showselect['no_select']=1;
								}
							showselect_ar($showselect);

							//}
							if(!$from)
								{
								//divend();
								tdtd('width="100%" align="left" valign="top" style="padding:0px 0px 0px 5px;"');
								href("#", img_src("/images/choose.gif", "alt=\"Выбрать\"", 1), "onclick=\"return ow('/topicm.html?method=$method&our_arm_id=".$addit_string_ar['our_arm_id']."&our_ac_id=".$info_ar['ARM_COLUMN_ID']."&from=topic');\"");
								br();
								href("#", img_src("/images/remove_all.gif", "alt=\"Очистить\"", 1), "onclick=\"set_field('$control_name', ''); set_div('".$control_name."_view', '<select multiple></select>'); return false;\"");
								br();
								tdtr();
								tableend();
								}
							else
								{
								tdtr();
								trtd();
								forminput("button", "select", "Выбрать", '', "onClick=\"return TopicMset(this.form.".$control_name."_view, '$control_name');\"");
								tdtr();
								?>
								<script language="javascript">
								<!--
								TopicMset_begin(<?echo "document.topic.".$control_name."_view, '$control_name'";?>);
								//-->
								</script>
								<?
								}
							}//конец Topic
						}
                   //echo"select_down";
                   }// конец LookUpCombo,  LookUpList и Topic
                elseif($CONTROL_AR[$control_id]['NAME']=="LookUpListM" )//мультиселект
                       {
                      //echo"$control_name=$val<br>";
                       //echo"multiselect";
                       global $$control_name;
                       if($SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id])
                           {
                           $showselect=array("type"=>"mult",  "name"=>$control_name."[]", "addit"=>$addit_ctrl);
                           $showselect["id_val"]=($string_id?$string_id:$val_ar['ID']);
                           if(($method=="list" || $method=="form_edit" ) && !$$control_name )
                              {
                              $showselect["def_sel"]=$SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]['sel_q'];
                              }
                           elseif($method=="search_form" || (($method=="list" || $method=="form_edit" ) && $$control_name ))
                                  {
                                  //echo"sf $control_name=".$$control_name.", count=".count($$control_name)."<br>";
                                  //$showselect['def_val_ar']==$$control_name;
                                                                  if(is_array($$control_name))
                                                                                {
                                                                                foreach($$control_name as $k_def_v=>$v_def_v)
                                                                                        {
                                                                                        $showselect['def_val_ar'][$v_def_v]=$v_def_v;
                                                                                        }
                                                                                }
                                  }
                           $showselect['conn']=$conn;
                           $showselect["down_ar"]=$SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id];
                           //определяем ширину контрола
                           $max_len_in_array=max_len_in_array($SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]);
                           $addit_this_ctrl=show_control_width(array("control_type_name"=>$CONTROL_AR[$control_id]['NAME'], "col_length"=>$max_len_in_array, "method"=>$method, "width"=>$CONTROL_AR[$control_id]['params']['WIDTH']));
                           //echo"addit_this_ctrl=$addit_this_ctrl<br>";
                           $showselect['addit'].=$addit_this_ctrl;
                           if($CONTROL_AR[$control_id]['params']['DISABLED']){
                           	   $doid = explode(',',$CONTROL_AR[$control_id]['params']['DISABLED']);
							   foreach($doid as $oid){
								   unset($showselect["down_ar"][$oid]);
							   }
                           }

                           if($method=="search_form")
                              {
                              $showselect['null_sel']=1;
                              $showselect['or_and']=1;
                              }
                           if($CONTROL_AR[$control_id]['params']['SIZE'])
                            $size=$CONTROL_AR[$control_id]['params']['SIZE'];
                           else
                               {
                               //echo"count=".count($SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id])."<br>";
                               //echo"<pre>";

                               //print_r($SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]);
                               $size=($SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]['count']+ $showselect['null_sel']+$showselect['nullval']);
                               if($size>20)
                                 $size=20;

                               }
                           //echo"size=$size<br>";
                           $showselect['addit'].=' size="'.$size.'"';


                           showselect_ar($showselect);
                           }
                       }// конец LookUpListM
                elseif( $CONTROL_AR[$control_id]['NAME']=="TopicM" )//&& $from)//мультиселект во всплывающем окне
                       {
                      //echo"$control_name=$val<br>";
                       //echo"topicM - from=$from";
                       if(is_array($SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]))
                           {
                           if(!$from)
                               {
                               if($string_id && !$val)
                                  foreach($SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id] as $k_id=>$v_id)
                                       {
                                       if($k_id && is_int($k_id))
                                          $value.=($value?",":"").$k_id;
                                       }
                               else
                                      $value=$val;

                               if(is_array($value))
                                  $set_value=implode(",", $value);
                               else
                                   $set_value=$value;

                               forminput("hidden", $control_name, $set_value, '', "ID=\"$control_name\"");//через разделитель должны быть занесены все текущие значения
                               $addit_ctrl.=" disabled ";
                               //определяем ширину контрола
                               $max_len_in_array=max_len_in_array($SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]);
                               //echo"max_len_in_array=$max_len_in_array<br>";
                               $addit_this_ctrl=show_control_width(array("control_type_name"=>$CONTROL_AR[$control_id]['NAME'], "col_length"=>$max_len_in_array, "method"=>$method, "width"=>$CONTROL_AR[$control_id]['params']['WIDTH']));
                               table('border="0" bordercolor="#ff0000" width="100%"');
                               trtd('', 'align="left" '.$addit_this_ctrl);
                               //div('ID="'.$control_name.'_view" align="left"');
                               }
                           else
                               trtd('height="100%"');
                           //echo"$value, $val<pre>";
                           //print_r($value);
                           //echo"<br>val[".BASE_NULL."]=".$val[BASE_NULL].", ".in_array(BASE_NULL)."<br>";
                           $showselect=array("type"=>"mult",  "name"=>$control_name."_view[]", "addit"=>$addit_ctrl.'ID="'.$control_name."_view".'"');
                           $showselect["id_val"]=($string_id?$string_id:$val_ar['ID']);

                           if($method=="list" || $method=="form_edit" )
                              {
                              //$showselect["def_sel"]=$SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]['sel_q'];
                              }
                           elseif($method=="search_form")
                                  {
                                  //echo"sf $control_name=".$$control_name.", count=".count($$control_name)."<br>";
                                  //$showselect['def_val_ar']==$$control_name;
                                  foreach($val as $k_def_v=>$v_def_v)
                                          {
                                          $showselect['def_val_ar'][$v_def_v]=$v_def_v;
                                          }
                                  }
                           $showselect['conn']=$conn;
                           $showselect["down_ar"]=$SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id];
                           if(!$from)
                               {
                               $showselect['div']='ID="'.$control_name.'_view" align="left"';
                               $showselect['addit'].=$addit_this_ctrl;
                               }
                           else
                               $showselect['from']=$from;
                           if($method=="search_form")
                              {
                              if(in_array(BASE_NULL, $val) || $from)
                                 $showselect['null_sel']=1;
                              if(!$from)
                                  $showselect['or_and']=1;
                              }
                           showselect_ar($showselect);
                           }
                       if(!$from)
                           {
                           //divend();
                           tdtd('width="100%" align="left" valign="top" style="padding:0px 0px 0px 5px;"');
                           href("#", img_src("/images/choose.gif", "alt=\"Выбрать\"", 1), "onclick=\"return ow('/topicm.html?method=$method&our_arm_id=".$addit_string_ar['our_arm_id']."&our_ac_id=".$info_ar['ARM_COLUMN_ID']."');\"");
                           br();
                           href("#", img_src("/images/remove_all.gif", "alt=\"Очистить\"", 1), "onclick=\"set_field('$control_name', ''); set_div('".$control_name."_view', '<select multiple></select>'); return false;\"");
                           br();
                           tdtr();
                           tableend();
                           }
                       else
                           {
                              tdtr();
                              trtd();
                              forminput("button", "select", "Выбрать", '', "onClick=\"return TopicMset(this.form.".$control_name."_view, '$control_name');\"");
                              tdtr();
                              ?>
                              <script language="javascript">
                              <!--
                              TopicMset_begin(<?echo "document.topic.".$control_name."_view, '$control_name'";?>);
                              //-->
                              </script>
                              <?
                              }
                       }// конец TopicM
                elseif($CONTROL_AR[$control_id]['NAME']=="LookUpWindow" && ($method=="search_form" || $method=="form_edit"))//всплывающее окно
                       {
                       $ref_id=$info_ar['REF_COLUMN_ID'];
                       if($ref_id)
                          {
                          $ctrl_name_name=$control_name."_name";
                          forminput("text", $control_name, $val, "", "ID=\"$control_name\" onchange=\"window.document.getElementById('$ctrl_name_name').value=''; LookUpWindowset('$control_name');\"");//id ссылки

                          $arm_info=arm_info($conn,  $CONTROL_AR[$control_id]['ADDITIONAL_ARM_ID']);
                          if($CONTROL_AR[$control_id]['params']['PREF']){
							  $arm_info['PATH_N']=href_prep($arm_info['PATH_N'], $CONTROL_AR[$control_id]['params']['PREF']."=".$our_ent_id);
                          }
                          nbsp();
                          href(href_prep($arm_info['PATH_N'], "lookup=1&lookup_name=".$control_name), img_src("/images/choose2.gif", 'alt="Выбрать" align="middle"', 1), "onclick=\"return ow(this.href);\"");
                          span('ID="'.$control_name.'_view" style="'.($val?"":"display:none;").'"');
                          //nbsp(2);
                          href(href_prep($arm_info['PATH_N'], "lookup=1&lookup_name=".$control_name.($val?"&our_ent_id=$val":"") ), img_src("/images/preview.gif", 'alt="Просмотр" align="middle"', 1), "ID=\"".$control_name."_view_href\" onclick=\"return ow(this.href);\"");
                          spanend();
                          br();
                          if($val)
                             {
                             $tab_ref=info_table($conn, $ref_id);
                             $fields=$CONTROL_AR[$control_id]['params']['FIELDS']?$CONTROL_AR[$control_id]['params']['FIELDS']." as name ":"name";
                             $ctrl_name_val_ar=get_byId($conn, $tab_ref['NAME'], $val, $fields);
                             $ctrl_name_val=$ctrl_name_val_ar['NAME'];
                             }
                          forminput("text", $ctrl_name_name, $ctrl_name_val, "", "ID=\"".$control_name."_name\" disabled");//имя сущности
                          }
                       }//конец LookUpWindow
                elseif($CONTROL_AR[$control_id]['NAME']=="TextArea")
                       {
                       if($method=="form_edit" || $method=="list" || $method=="list_new" )
                          {
                          //определяем ширину контрола
                         if($CONTROL_AR[$control_id]['params']['VIEW']!='hidden')
							{
							$addit_this_ctrl=show_control_width( array("control_type_name"=>$CONTROL_AR[$control_id]['NAME'], "col_length"=>$info_ar['COLUMN_LENGTH'], "method"=>$method, "width"=>$CONTROL_AR[$control_id]['params']['WIDTH']));
							textarea($control_name, $addit_ctrl.$addit_this_ctrl.($CONTROL_AR[$control_id]['params']['ROWS']?'rows="'.$CONTROL_AR[$control_id]['params']['ROWS'].'"':""));
							echo $val;
							textareaend();
							}
                          }
                       else
                           echo NO_CONTROL_REALIZE;
                       }
                elseif($CONTROL_AR[$control_id]['NAME']=="HTML")
                       {
                       if($method=="form_edit")
                          {
                          textarea($control_name, $addit_ctrl.' style="width:90%;height:'.($CONTROL_AR[$control_id]['params']['HEIGTH']?$CONTROL_AR[$control_id]['params']['HEIGTH']:"70").';"');
                          echo $val;
                          textareaend();
                          echo"
                          <script language=\"JavaScript1.2\" defer>
                          <!--
                          var css_site='".HTML_EDITOR_CSS."';
                          var special_style='".HTML_EDITOR_SPECIAL."';
                          var body_style='".HTML_EDITOR_BODY."';
                          editor_generate('$control_name');
                          //-->
                          </script>
                          ";
                          br();
                          }
                       else
                           echo NO_CONTROL_REALIZE;
                       }
                       elseif($CONTROL_AR[$control_id]['NAME']=="MHTML")
                       {
                           if($method=="form_edit")
                           {
                               textarea($control_name, $addit_ctrl.' style="width:100%;height:'.($CONTROL_AR[$control_id]['params']['HEIGTH']?$CONTROL_AR[$control_id]['params']['HEIGTH']:"70").';"');
                               echo $val;
                               textareaend();

                               if(TINYMCE_HTML_EDITOR_JS==='TINYMCE_HTML_EDITOR_JS'){
                                   if(TINYMCE_HTML_EDITOR_CSS=="TINYMCE_HTML_EDITOR_CSS"){
                                       $tinyMCE_css = '/js/tiny_mce/themes/advanced/skins/default/content.css';
                                   }else $tinyMCE_css = TINYMCE_HTML_EDITOR_CSS;
                                   echo '<style>.mceToolbarRow3{display:none;}</style>
                                   <script language="JavaScript1.2" defer>
                                   tinyMCE.init({
                                   mode:"exact",
							       elements: "'.$control_name.'",
                                   mode:"textareas",
                                   theme:"advanced",
                                   language:"ru",
                                   // plugins : "spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
                                   plugins : "table, advlink, preview, emotions, nonbreaking",
                                   // Theme options
                                   theme_advanced_buttons1 : "|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,|,forecolor,backcolor,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,cleanup,code,preview",
                                   theme_advanced_buttons2 : "|,tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,advhr,|",
                                   theme_advanced_toolbar_location : "top",
                                   theme_advanced_toolbar_align : "left",
                                   theme_advanced_statusbar_location : "bottom",
                                   theme_advanced_resizing : true,

                                   // Example content CSS (should be your site CSS)
                                   content_css : "'.$tinyMCE_css.'",
                                   // Drop lists for link/image/media/template dialogs
                                   //template_external_list_url : "js/template_list.js",
                                   //external_link_list_url : "js/link_list.js",
                                   //external_image_list_url : "js/image_list.js",
                                   //media_external_list_url : "js/media_list.js"

                                   });
                                   </script>
                                   ';
                               }else{

                                   if(TINYMCE_HTML_EDITOR_CSS==NULL){
                                       $tinyMCE_css = '/js/tiny_mce/themes/advanced/skins/default/content.css';
                                   }else{ $tinyMCE_css =  TINYMCE_HTML_EDITOR_CSS;
                                  /*  $handle = fopen(TINYMCE_HTML_EDITOR_JS, "r");
                                    $contents = fread($handle, filesize(TINYMCE_HTML_EDITOR_JS));
                                    fclose($handle);*/
                                    echo '<script language="JavaScript1.2">  var tinyMCE_css = "'.$tinyMCE_css.'";
                                    var control_name= "'.$control_name.'"; </script>';
                                    echo '<script type="text/javascript" src="'.TINYMCE_HTML_EDITOR_JS.'"></script>';
                                   }
                               }
                               br();
                           }
                           else
                               echo NO_CONTROL_REALIZE;
                       }
                elseif($CONTROL_AR[$control_id]['NAME']=="Checkbox")
                   {
                   if($method=="search_form")
                      {
                      //echo"val=$val<br>";
                      $showselect=array("name"=>$control_name, "def_val"=>$val, "nullval"=>1);
                      $showselect['down_ar']=array("1"=>"Да", "0"=>"Нет");

                      showselect_ar($showselect);
					  //echo __LINE__."<br>";
                      }
                   else
                       {
                       if($val)
                          $addit_ctrl.=" checked";
                       forminput("checkbox", $control_name, 1, '', $addit_ctrl);
                       }
                   }
                //включение в рассылку
                elseif($CONTROL_AR[$control_id]['NAME']=="PostIt")
                   {
                   if($method=="list" || $method=="list_new" || $method=="form_edit")
                       {
                       forminput("checkbox", $control_name, 1, '', $addit_ctrl);
                       }
                   else
                           echo NO_CONTROL_REALIZE;
                   }
                //индексация
                elseif($CONTROL_AR[$control_id]['NAME']=="IndexIt")
                   {
                   if($method=="list" || $method=="list_new" || $method=="form_edit")
                       {
                       if($CONTROL_AR[$control_id]['params']['VIEW']=="hidden")
                          forminput("hidden", $control_name, 1);
                       else
                          forminput("checkbox", $control_name, 1, '', $addit_ctrl);
                       }
                   else
                           echo NO_CONTROL_REALIZE;
                   }
                //пересчитываемый рейтинг
                elseif($CONTROL_AR[$control_id]['NAME']=="Rating" )
                       {
                       if($CONTROL_AR[$control_id]['params']['MAX_RATING']==1)
                           forminput("checkbox", $control_name, 1, '', $addit_ctrl.($val?" checked":""));
                       else
                           forminput("text", $control_name, $val, '', $addit_ctrl.($info_ar['COLUMN_LENGTH']?" maxlength=\"".$info_ar['COLUMN_LENGTH']."\" size=\"".$info_ar['COLUMN_LENGTH']."\"":""));
                       }
                //дочерние АРМы
                elseif($CONTROL_AR[$control_id]['NAME']=="KidLink" &&
                    ($method=="list" || $method=="list_new" || $method=="form_edit"))
                    {
                        if($method=="list" || ($method=="form_edit" && $string_id))
                        {
                            if(!$CONTROL_AR[$control_id]['LIST'])
                            {
                                $CONTROL_AR[$control_id]['LIST']=get_SubArms($conn, $addit_string_ar['our_arm_id']);
                            }
                            foreach($CONTROL_AR[$control_id]['LIST'] as $k_kid=>$v_kid)
                            {
                                href($v_kid['PATH_N']."&".prep_over_string(($string_id?$string_id:$val_ar['ID']), $addit_string_ar['our_arm_id']), $v_kid['NAME']);
                                br();
                            }
                        }
                    }
                elseif($CONTROL_AR[$control_id]['NAME']=="Id" && $method!="search_form")
                       {
                       if($error_form)
                          {
                          $addit_td='class="error_list"';
                          //echo"ERR<br>";
                          }
                       if($method=="list")
                          {
                          forminput("checkbox", "mark[".$num_row."]", $val,'', 'ID="mark_'.$num_row.'" title="id='.$val.'"');
                          //echo $val;
                          }
                       elseif($method=="list_new")
                              echo"New:";
                       }
                elseif($CONTROL_AR[$control_id]['NAME']=="TreeOrderReculculation" || $CONTROL_AR[$control_id]['NAME']=="TreePublishReculculation")
                       {
                       if($method=="list_new" || $method=="list" || $method=="form_edit")
                          {
                          forminput("hidden", $control_name, 1);
                          }
                       }
                elseif($CONTROL_AR[$control_id]['NAME']=="AlterIt")
                   {
                   $addit_ctrl.=" checked";
                   forminput("checkbox", $control_name, 1, '', $addit_ctrl);
                   }
				elseif($CONTROL_AR[$control_id]['NAME']=="ClearCache")//очистка кеша
					{
					forminput("hidden", $control_name, 1);
					}
                else echo NO_CONTROL_REALIZE;
                $control_html=ob_get_contents();
                ob_end_clean();
                }//конец вывода html


             if($method=="search_form")
                {
				 //echo"only_id=$only_id<br>";
                search_form_tr($info_ar['COL_ABOUT'], $control_html, array("only_id"=>$only_id));
                }
             elseif(($method=="list" ||  $method=="list_new") && $CONTROL_AR[$control_id]['NAME']!="Hidden")
                {
                if($CONTROL_AR[$control_id]['params']['VIEW']!="hidden")
                   {
                   if($error_this)
                      $addit_td='class="error_list"';
                   td($addit_td.($CONTROL_AR[$control_id]['NAME']=="Id"?' width="35"':($info_ar['COLUMN_WIDTH']?'width="'.$info_ar['COLUMN_WIDTH'].'%"':"")));
				   //echo"<pre>";
				   //print_r($info_ar);
                   echo $control_html;
                   tdend();
                   }
                else
                   echo $control_html;
                }
             elseif($method=="form_edit"
                    && !($CONTROL_AR[$control_id]['NAME']=="KidLink" && $method=="form_edit" && !$string_id)  && $CONTROL_AR[$control_id]['NAME']!="Hidden")
                {
                if($CONTROL_AR[$control_id]['NAME']=="Id" || $CONTROL_AR[$control_id]['params']['VIEW']=="hidden")
                   echo $control_html;
                else
                    {

                    if(!$info_ar['NULL_VALUE'] &&
                        !(($CONTROL_AR[$control_id]['NAME']=="HTPassword"
                           || $CONTROL_AR[$control_id]['NAME']=="Password" )
                        && $our_ent_id)
                        && $CONTROL_AR[$control_id]['NAME']!="LookUpListM"
                        && $CONTROL_AR[$control_id]['NAME']!="TopicM")
                       {
						$tdclass=' class="not_null"';
                       }
					 else
						 $tdclass=' class="maybe_null"';
                    trtd('valign="top"', $tdclass);
					span('class="edit_form_colname"');
                    echo $info_ar['COL_ABOUT'];
					spanend();
                    tdtd('class="control_form"');
                    echo $control_html;
                    tdtr();
                    }
                }

             //*************************************************************************
             //обработка данных
             if($method=="list_prep" || $method=="save_form")
                {
                //echo"1 control_name=$control_name<br>";
				//если идет введение или сохранение новой записи и контрол скрытый
				// и есть значение по умолчанию колонки или контрола
				//или же показ статический, а сохранение - по умолчанию (вроде пользователя-редактора в ошибках)
				//- то задается это значение
				//
				//echo"string_id=$string_id, method=$method, control_name=$control_name, value=$value<br>";
				if(( ($method=="save_form" || $method=="list_prep") && $CONTROL_AR[$control_id]['params']['VIEW']=="hidden" && !$string_id)
					||
					($CONTROL_AR[$control_id]['params']['FORM_EDIT']=='Static' && $CONTROL_AR[$control_id]['params']['SAVE_FORM']!='Static'))
					{				$value=$info_ar['DEFAULT_VALUE']?$info_ar['DEFAULT_VALUE']:$CONTROL_AR[$control_id]['DEFAULT_VALUE'];
					//echo"1 value=$value<br>";
					}
			    elseif($method=="save_form")
                   {
                   global $$control_name;
                   $value=$$control_name;
                   //echo"2 $control_name=$value<br>";
                   }
                elseif($method=="list_prep")
                       {
                       if(is_int($num_row))
                          {
                          global $$control_name;
                          $value_name=$$control_name;
                          $value=$value_name[$num_row];
                          //echo"3 value=$value<br>";
                          }
                       elseif($num_row=="new")
                              {
                              $value_name=$control_name."_new";
                              //echo"value_name="
                              global $$value_name;
                              $value=$$value_name;
                              }
                       }
                //echo"2 $control_name=$value<br>";
                if($SubArms['COL']['ID']==$info_ar['COL_ID'] && $SubArms['COL']['ID'])
                   {
                   $value=$SubArms['VAL'];
                   }
                if($CONTROL_AR[$control_id]['NAME']=="Link" || $CONTROL_AR[$control_id]['NAME']=="Static"
                    || $CONTROL_AR[$control_id]['NAME']=="Image"
                    || $CONTROL_AR[$control_id]['NAME']=="preview-image"
                    || $CONTROL_AR[$control_id]['NAME']=="image-link"
                    || $CONTROL_AR[$control_id]['NAME']=="Download"
                    || $CONTROL_AR[$control_id]['NAME']=="KidLink"
                    || $CONTROL_AR[$control_id]['NAME']=="Hidden"
                    )
                   {
                   $ret['val']=0;
                   $ret['no_ins']=1;
                   //$ret['val_base']=$value;
                   //echo "be - ".$CONTROL_AR[$control_id]['NAME']."<br>";
                   }
                elseif($CONTROL_AR[$control_id]['NAME']=="Input" || $CONTROL_AR[$control_id]['NAME']=="TextArea" || $CONTROL_AR[$control_id]['NAME']=="HTML" || $CONTROL_AR[$control_id]['NAME']=="MHTML")
                   {
                   $value=trim((string)$value);
                   //echo"value=$value<br>";
                   if(strlen($value) || $info_ar['NULL_VALUE'])
                       {
                       if($CONTROL_AR[$control_id]['NAME']=="HTML")
                          $value=bad_editor($value);
                       if($info_ar['COLUMN_LENGTH'])
                          $value=substr($value, 0, $info_ar['COLUMN_LENGTH']);
                       $value=str_replace("«", "&laquo;", $value);
                       $value=str_replace("»", "&raquo;", $value);
					   $value=str_replace("©", "&copy;", $value);
                       if(strlen($value))
                          $ret['val']=$value;
                       else
                           $ret['val']=BASE_NULL;
                       //$ret['val_base']="'$value'";
                       }
                   else
                       $ret['err']=1;
                   }
                elseif($CONTROL_AR[$control_id]['NAME']=="InputInt" || $CONTROL_AR[$control_id]['NAME']=="Rating" || $CONTROL_AR[$control_id]['NAME']=="LookUpWindow")
                   {
                   if(isset($value) && is_numeric($value))
                      {
                      $ret['val']=$value;
                      }
                   elseif(!$value && $info_ar['NULL_VALUE'])
                           $ret['val']=BASE_NULL;
                   else
                           {
                           $ret['err']=1;
                           }
                   if($CONTROL_AR[$control_id]['NAME']=="Rating")
                      {
                      $ret[$CONTROL_AR[$control_id]['NAME']]['value']=intval($value);
                      $ret[$CONTROL_AR[$control_id]['NAME']]['params']=$CONTROL_AR[$control_id]['params'];
                      }
                   }
//.blacki ----------------------------------------------------------------------
                elseif( ($CONTROL_AR[$control_id]['NAME']=="Date") || ($CONTROL_AR[$control_id]['NAME']=="DateTime") )
                       {
						//echo"line=".__LINE__." 3 <br>";
						if($value=="db_sysdate")
						   {
							$ret['val']=db_sysdate();
						   }
						else
						   {
						//calendar ---------------------------------------------
                       //--date
                       $control_name1=str_replace(array("[", "]"),array("_", ""),$control_name);
                       if($num_row)
						   $control_name1.="_$num_row";
                       $tmp = $control_name1;
					   global $$tmp;
					   $date = $$tmp;
                       list ($day, $month, $year) = explode('.', $date, 3);
                       //--time

                       //.old $tmp =$control_name; global $$tmp;   $time = $$tmp;
                       $tmp ="time_".$control_name1; global $$tmp;   $time = $$tmp;

                       if (is_array($time))
							$time = $time[$num_row];
                       list ($hour, $minute) = explode(':', $time, 2);
                       //--------------------------------------------- calendar

                       //echo "!!".sprintf('%02s-%02s-%4s',$day,$month,$year)."<br>";
                       //echo "!!".date('d-m-Y-H-i',mktime($hour, $minute, 0, $month, $day, $year));
			//$year+=MKTIME_CORR;
                       if (   //Проверка корректности введенной даты
                              /*date('d-m-Y',mktime(0, 0, 0, $month, $day, $year))!=
                              sprintf('%02d-%02d-%4d',$day,$month,$year)*/
								  ($day || $month || $year) && !($day && $month && $year)
                          )
                           {
                           $ret['err']=1;
                           //echo"error date! ".$month.", ".$day.", ".$year.", mktime=".mktime(0, 0, 0, $month, $day, $year).", ". date('d-m-Y',mktime(0, 0, 0, $month, $day, $year));
                           }
						elseif(!($day && $month && $year))
							   {
								$ret['val']=BASE_NULL;
								//echo __LINE__." val=''<br>";
							   }
                       else//дата введена верно, нужно ли проверяь время?
                           {
                           if($CONTROL_AR[$control_id]['NAME']=="DateTime")//Если, кроме даты, нужно еще и время
                                {
                                //echo "!!".sprintf('%02s-%02s-%4s-%02s-%02s',$day,$month,$year,$hour,$minute)."<br>";
                                //echo "!!".date('d-m-Y-H-i',mktime($hour, $minute, 0, $month, $day, $year));
                                if (   //Проверка корректности введенной даты
                                     /*(
                                       date('d-m-Y-H-i',mktime($hour, $minute, 0, $month, $day, $year))!=
                                       sprintf('%02d-%02d-%4d-%02d-%02d',$day,$month,$year,$hour,$minute)
                                     ) || */!isset($hour) || !isset($minute)
                                   )
                                    {
                                    $ret['err']=1;
                                    //echo date('d-m-Y-H-i',mktime($hour, $minute, 0, $month, $day, $year));br();
                                    //echo sprintf('%02d-%02d-%4d-%02d-%02d',$day,$month,$year,$hour,$minute);
                                    //echo"error time!";
                                    }
                                else
                                    {
                                    //$ret['val']=mktime($hour, $minute, 0, $month, $day, $year);
									$ret['val']=to_date_format(array("hour"=>$hour, "minute"=>$minute, "day"=>$day, "year"=>$year, "month"=>$month));
									//echo __LINE__." val=".$ret['val']."<br>";
                                    //echo "$day/$month/$year  $hour:$minute, mktime=".$ret['val']."<br>";
                                    }
                                }
                           else  //нужна только дата
                                {
                                $ret['val']=mktime(0, 0, 0, $month, $day, $year);
								//echo "$day/$month/$year";
								$ret['val']=to_date_format(array("day"=>$day, "year"=>$year, "month"=>$month));
								//echo __LINE__." val=".$ret['val']."<br>";
								}
                           }
							}//конец условия, что не db_sysdate
                       //exit;
                       }
//---------------Password + HTPassword
                elseif($CONTROL_AR[$control_id]['NAME']=="Password" || $CONTROL_AR[$control_id]['NAME']=="HTPassword")
                {
                       $tmp = $control_name."_1";  global $$tmp;   $pasw1  = $$tmp;
                       $tmp = $control_name."_2";  global $$tmp;   $pasw2  = $$tmp;

                       $pasw1=trim($pasw1);
                       $pasw2=trim($pasw2);

                       if($pasw1!=$pasw2)//если пароль не подтвержден
                          {
                          $ret['err']=1;
                          //echo"ERROR: password1 <> password2!";
                          }
                       else//если пароль подтвержден
                          {
                          //echo"$pasw1 - ".$info_ar['NULL_VALUE']." - $our_ent_id - ".$val_ar['ID']."<br>";
                          if($pasw1 || $info_ar['NULL_VALUE'] || $our_ent_id)
                             {
                             if($info_ar['COLUMN_LENGTH'])
                                $pasw1=substr($pasw1, 0, $info_ar['COLUMN_LENGTH']);

                             if(!$pasw1)
                                {
                                //$ret['val']=md5(BASE_NULL);

                                //$pasw1=BASE_NULL;
                                //echo"Нет пароля - $new<br>";
                                $ret['no_ins']=1;
                                }

                             if ($CONTROL_AR[$control_id]['NAME']=="HTPassword" && $pasw1)
                                   {
                                   if(DEFINED("HTPASSWD_BIN"))
                                      $htpasswd_bin=HTPASSWD_BIN;
                                   elseif(defined("APACHE_BIN"))
                                      {
                                      $htpasswd_bin=APACHE_BIN."htpasswd";
                                      }
                                   else
                                       $htpasswd_bin="htpasswd";
                                   if(DEFINED("PASSFILE"))
									   {
                                      $passfile=PASSFILE;
										//echo"1 passfile=$passfile<br>";
									   }
                                   else
									   {
                                       $passfile=PATH_INC_HOST."/.htpasswd";
										//echo"2 passfile=$passfile<br>";
									   }
                                   global $FOR_APACHE_LOGIN;

                                   $command_string=$htpasswd_bin." -b ".$passfile." ".$FOR_APACHE_LOGIN." ".$pasw1."";

                                   $ex=exec($command_string);
								   //echo"command_string=$command_string, ex=$ex<br>";
                                   }

                             $ret['val']=md5($pasw1);
                             //$ret['val']=$pasw1;
                             }

                          else //if pasw1 is_emty and NOT_NULL enabled
                             $ret['err']=1;
                          }
                          //exit;
                       }
//---------------RadioSet
                elseif($CONTROL_AR[$control_id]['NAME']=="RadioSet")
                   {
                   if(is_numeric($value))
                       {
                       $value=intval($value);
                       $ret['val']=$value;
                       //echo"val=$value";
                       }
                   elseif(!$value && $info_ar['NULL_VALUE'])
                           {
                           $ret['val']=BASE_NULL;
                           }
                   else
                       {
                       $ret['err']=1;
                       }
                   //exit;
                   }
//---------------------------------------------------------------------- .blacki
                elseif($CONTROL_AR[$control_id]['NAME']=="File" || $CONTROL_AR[$control_id]['NAME']=="SaveTemplate")
                       {
						//echo"control_name=$control_name<br>";
                       $file=$control_name."_name";
                       $file_save=$control_name."_name_save";
                       $file_t=$control_name."_type";
                       $file_sa=$control_name."_save_as";

						//почему-то не работает при больших файлах, хотя в $_FILES все есть
                       global $$file, $$file_t, $$file_save, $$file_sa;
						//echo"file_save=$file_save=".$$file_save."<br>";

						global $_FILES;
						/*
						if(is_uploaded_file($_FILES[$control_name]['tmp_name']))
							;//echo"UPLOAD<br>";
						elseif($_FILES[$control_name]['tmp_name'])
							echo"NO UPLOAD<br>";
						*/
						$value=$_FILES[$control_name]['tmp_name'];
						if(!$$file_save)
							$$file_save=$_FILES[$control_name]['name'];
						$$file_t=$_FILES[$control_name]['type'];
					//	$$file=$_FILES[$control_name]['name'];
					//	$$file_save=$_FILES[$control_name]['name'];

						$file_ar[$control_name.'_name']=($$file_save?eregi_replace("[[:alnum:]]{0,}\/", "", $$file_save):$$file);
                       //echo$control_name.'_name='.$file_ar[$control_name.'_name']."<br>";
                       $file_s=$$file_save;

                       //echo"File -  $value, $file_n, save as $file_s<br>";
                       if($value && file_exists($value))
                          {
                          $file_ar['size']=filesize($value);
                          $file_ar['val']=$value;
                          $type= $$file_t;
                          $type_id=get_or_ins($conn, array("table"=>TABLE_PRE."file_types", "val"=>$type));
                          $file_ar['type_id']=$type_id;
							//echo __LINE__."type_id=$type_id<br>";
                          //echo __LINE__." file ($control_name) - $value ($file_ar), size - ".$file_ar['size'].", type - $type ($type_id), ".$file_ar['width']."*".$file_ar['height']."<br>";
						  //echo __LINE__.exif_imagetype($value)."<br>";
						  //ini_set('display_errors', 1);
						  //ini_set('error_reporting', 15);
                          if($img_inf=getimagesize($value))//если это картинка
                             {
                             $file_ar['width']=$img_inf[0];
                             $file_ar['height']=$img_inf[1];
                             }
							 //ini_set('display_errors', 0);
                          //echo __LINE__." file ($control_name) - $value ($file_ar), size - ".$file_ar['size'].", type - $type ($type_id), ".$file_ar['width']."*".$file_ar['height']."<br>";
                          //exit;
						  //echo"<pre>";
						  //print_r($file_ar);
                          }
                       else
                           {
                           $ret['no_ins']=1;
                           }
                       if($CONTROL_AR[$control_id]['NAME']=="SaveTemplate")
                          {
						   //echo"file_save=".$$file_save."<br>";
                          $file_ar['file_sa']= $$file_save;
						  //$file_ar['path_save']=TPL_PATH;

						  $file_ar['path_save']=eregi_replace("\.?/", "./", ereg_replace("/[^/]*$", "", $$file_save));
						  //if($file_ar['path_save']==$file_ar['file_sa'])
						//	$file_ar['path_save']="";
						  //echo"1 path_save=".$file_ar['path_save'].", file_sa=".$file_ar['file_sa']."<br>";
						  if($file_ar['path_save']==$file_ar['file_sa'])
								$file_ar['path_save']="";
						  //echo"2 path_save=".$file_ar['path_save'].", file_sa=".$file_ar['file_sa']."<br>";
                          $ret[$CONTROL_AR[$control_id]['NAME']]=$file_ar;
                          }
                       $ret['file_ar']=$file_ar;
                       }
					   elseif($CONTROL_AR[$control_id]['NAME']=="MultiFile")
					   {
						   //echo"control_name=$control_name<br>";
						   $file=$control_name."_name";
						   $file_save=$control_name."_name_save";
						   $file_t=$control_name."_type";
						   $file_sa=$control_name."_save_as";

						   //почему-то не работает при больших файлах, хотя в $_FILES все есть
						   global $$file, $$file_t, $$file_save, $$file_sa;
						   //echo"file_save=$file_save=".$$file_save."<br>";

						   global $_FILES;
						   /*
						   if(is_uploaded_file($_FILES[$control_name]['tmp_name']))
						   ;//echo"UPLOAD<br>";
						   elseif($_FILES[$control_name]['tmp_name'])
						   echo"NO UPLOAD<br>";
						   */
						   $value=$_FILES[$control_name]['tmp_name'];
						   if(!$$file_save)
							   $$file_save=$_FILES[$control_name]['name'];
						   $$file_t=$_FILES[$control_name]['type'];
						   //    $$file=$_FILES[$control_name]['name'];
						   //    $$file_save=$_FILES[$control_name]['name'];

						   $file_ar[$control_name.'_name']=($$file_save?eregi_replace("[[:alnum:]]{0,}\/", "", $$file_save):$$file);
						   //echo $control_name.'_name='.$file_ar[$control_name.'_name']."<br>";
						   $file_s=$$file_save;

						   //echo"File -  $value, $file_n, save as $file_s<br>";
						   if($value && file_exists($value))
						   {
							   $file_ar['size']=filesize($value);
							   $file_ar['val']=$value;
							   $type= $$file_t;
							   $type_id=get_or_ins($conn, array("table"=>TABLE_PRE."file_types", "val"=>$type));
							   $file_ar['type_id']=$type_id;
							   if($img_inf=getimagesize($value))//если это картинка
							   {
								   $file_ar['width']=$img_inf[0];
								   $file_ar['height']=$img_inf[1];
							   }
							   //ini_set('display_errors', 0);
							   //echo __LINE__." file ($control_name) - $value ($file_ar), size - ".$file_ar['size'].", type - $type ($type_id), ".$file_ar['width']."*".$file_ar['height']."<br>";
							   //exit;
							   //echo"<pre>";
							   //print_r($file_ar);
						   }
						   else
						   {
							   $ret['no_ins']=1;
						   }

						   //$file_ar['file_sa']= $$file_save;
						   //$file_ar['path_save']=eregi_replace("\.?/", "./", ereg_replace("/[^/]*$", "", $$file_save));
						   //$ret[$CONTROL_AR[$control_id]['NAME']]=$file_ar;

						   //$file_ar['file_sa']= $$file_save;
						   //$file_ar['path_save']=TPL_PATH;
						   $fn = $file_ar['file_sa'];
						   //if ( ! eregi("[^A-Za-zА-Яа-я0-9///./~/^/-/_/(/)/{/}'`@#$%_]",$fn)){
						   if (eregi("[^A-Za-zА-Яа-я0-9/-/_'`_]",$fn)){
							   //echo "есть посторонние буквы (FALSE)";
							   $fn = eregi_replace("\s","_" ,$fn);
							   $fn = eregi_replace("[^A-Za-zА-Яа-я0-9///./-/_'`]","X" ,$fn);
						   }
						   //Транслит кирилицы
						   if(ereg("[А-Яа-я]+",$fn)){
							   $fn = my_translit($fn);
						   }
						   //Формируем путь к файлу
						   $fp =IMAGE_PATH.IMAGE_ORIGINAL.$fn;
						   if(file_exists ($fp)){
							   // echo 'Такой файл уже существует.';
							   $fp =IMAGE_PATH.IMAGE_ORIGINAL.str_replace(".",'_'.time().".",$_FILES["file"]["name"]);
						   }//else{
						   $buf = file_get_contents($patch);
						   $wr = file_put_contents ( $fp , $buf, LOCK_EX);
						   if($wr){
							   $file_ar['path_save'] = $fp;
							   unset($file_ar['val']);
						   }
						   //$ret[$CONTROL_AR[$control_id]['NAME']]=$file_ar;
						   $ret['file_ar']=$file_ar;
					   }
                //выпадающий список - выбор одного
                elseif($CONTROL_AR[$control_id]['NAME']=="LookUpCombo" || $CONTROL_AR[$control_id]['NAME']=="LookUpList" || $CONTROL_AR[$control_id]['NAME']=="Topic")
                   {
                   if(is_numeric($value))
                       {
                       $value=intval($value);
                       $ret['val']=$value;
                       }
                   elseif(!$value && $info_ar['NULL_VALUE'])
                           {
                           $ret['val']=BASE_NULL;
                           }
                   else
                       {
                       $ret['err']=1;
                       }
                   //$ret['no_ins']=1;
                   //$ret['val_base']=$value;
                   }
                elseif($CONTROL_AR[$control_id]['NAME']=="LookUpListM" || $CONTROL_AR[$control_id]['NAME']=="TopicM" )//мультиселект
                       {
                       $ret['val']=0;
                       $ret['no_ins']=1;
                       $ret["addit_sql"][]=$SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]['del_q'].":this_val";
                       if($CONTROL_AR[$control_id]['NAME']=="TopicM")
                          $value=split(",", $value);
                       //echo"SELECT_DOWN_AR<br>";
                       //echo"<pre>";
                       //print_r($SELECT_DOWN_AR);
                       //echo"control_name=$control_name, value=$value, col_id=".$info_ar['COL_ID'].", control_id=$control_id".$SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]['ins_q']."<br>";
					   if(is_array($value))
						   {
							foreach($value as $k_v=>$v_v)
                               {
                               if($v_v)
                                  $ret["addit_sql"][]=$SELECT_DOWN_AR[$info_ar['COL_ID']][$control_id]['ins_q']." (:this_val, $v_v)";
                               //echo"$k_v=>$v_v<br>";
                               }
						   }
                       }
                elseif($CONTROL_AR[$control_id]['NAME']=="Checkbox")
                   {
                   $value=trim($value);
                   if($value)
                      $value=1;
                   else
                       $value=0;
                   $ret['val']=$value;
                   //echo"checkbox=$value<br>";
                   //$ret['val_base']=$value;
                   }
                /*
                elseif($CONTROL_AR[$control_id]['NAME']=="PostIt")
                       {
                       //echo"POST - $value<br>";
                       $ret["post"]=intval($value);
                       $ret['val']=0;
                       $ret['no_ins']=1;
                       }
                */
                elseif($CONTROL_AR[$control_id]['NAME']=="IndexIt"
                       || $CONTROL_AR[$control_id]['NAME']=="TreeOrderReculculation"
                       || $CONTROL_AR[$control_id]['NAME']=="TreePublishReculculation"
                       || $CONTROL_AR[$control_id]['NAME']=="PostIt"
                       || $CONTROL_AR[$control_id]['NAME']=="AlterIt"
                       || $CONTROL_AR[$control_id]['NAME']=="ClearCache")
                       {
                       //echo$control_name." - $value<br>";
                       $ret[$CONTROL_AR[$control_id]['NAME']]['value']=intval($value);
                       $ret[$CONTROL_AR[$control_id]['NAME']]['params']=$CONTROL_AR[$control_id]['params'];
                       $ret['val']=0;
                       $ret['no_ins']=1;
                       }
                elseif($CONTROL_AR[$control_id]['NAME']=="Id")
                   {
                   $value=intval($value);
                   $ret['val']=0;
                   $ret['no_ins']=1;
                   //$ret['val_base']=$value;
                   }
                   //echo"val_ret=".$ret['val']."<br>";
                }//конец обработки данных
                //echo "whereSQL=".$ret['whereSQL']."<br>";
             return $ret;
             }

/**
* @return void
* @param unknown $arm_id
* @desc Возвращает массив с описанием связи многие ко многим
*/
function get_relation_many2many ($conn, $arm_id)
{
        $sel_ref_q="
                select
                          tt.name as ish_table_name,
                          t.cross_table_name,
                          t.sviz_name,
                          t.view_type
                from
                (
                        select
                                        ac.name as sviz_name,
                                c.ref_id,
                                        ct.name as view_type,
                                          tab.name as cross_table_name
                        from
                              ".TABLE_PRE."columns c,
                              ".TABLE_PRE."tables tab,
                              ".TABLE_PRE."arm_columns ac,
                                        ".TABLE_PRE."control_type ct
                        where
                                        ac.arm_id= $arm_id and
                                        ac.column_id = c.id and
                                c.table_id = tab.id and
                                        ct.id = ac.view_type and
                                         tab.main = 0
                )
                as
                  t,
                  ".TABLE_PRE."columns tc,
                  ".TABLE_PRE."tables tt
                where
                          tc.id = t.ref_id and
                         tt.id = tc.table_id
                ";
        //echo $sel_ref_q;
        $sviz_structure_q = db_query($conn, $sel_ref_q);
        for ($i = 0; $sviz_structure[$i] = db_fetch_row($sviz_structure_q); $i++)
        {}
        array_pop($sviz_structure);
        return $sviz_structure;
}
/**

* @return void
* @param unknown $conn
* @param unknown $self_id
* @param unknown $arm_id
* @desc Выводит на экран меню навигации админки.
*/
function show_admin_menu ($conn, $arm_id=0)
{

        $sel_nav_q="select distinct a.id, a.name, aa.sort_order, n.id as nav_id, n.name as nav_name, at.path, n.sort_order as n_sort_order
                           from ".TABLE_PRE."adm_navigation n, ".TABLE_PRE."arm_additional aa,
                           ".TABLE_PRE."arms a, ".TABLE_PRE."arm_types at".
                           (DEFINED("g_USER_ID")?", ".TABLE_PRE."arm_user_v auv ":"").
                                " WHERE aa.arm_id=a.id and aa.adm_navigation_id=n.id and aa.arm_type_id=at.id ".
                                (DEFINED("g_USER_ID")?"AND auv.arm_id=a.id and auv.user_id=".g_USER_ID:"")."
                                order by n.sort_order ASC, n.name ASC, aa.sort_order ASC, a.id ASC";
        //echo"sel_nav_q=$sel_nav_q<br>";
        $res_nav=db_getArray($conn, $sel_nav_q);
        foreach($res_nav as $k=>$v)
                {
                if(!$arm_id)
                    {
                    header("Location:".href_prep($v['PATH'], "our_arm_id=".$v['ID']));
                    }
                if($prev_gr!=$v['NAV_ID'])
                   {
                   trtd('','CLASS="Label1"');
                   echo $v['NAV_NAME'];
                   tdtr();
                   $prev_gr=$v['NAV_ID'];
                   }
                if ($v['ID'] == $arm_id )
                       {
                       $class = "Page2Selected";
                       }
                else
                       {
                       $class = "Page2";
                       }
                trtd("","class=$class");
                href(href_prep($v['PATH'], "our_arm_id=".$v['ID']), $v['NAME']);
                tdtr();
                }


}
function edit_table ()
{

}
//на случай, если связь таблицы с самой собой
function cross_table_inf($conn, $table_cross, $table1)
         {
         $sel_col_info="select c.id, c.name from ".TABLE_PRE."columns c, ".TABLE_PRE."tables t
                        where c.table_id=t.id and upper(t.name)=upper('$table_cross') and c.ref_id is not null and upper(c.name)!=upper('".$table1."_id')";
         //echo"sel_col_info=$sel_col_info<br>";
         $res_col_info=db_getArray($conn, $sel_col_info, 2);
         return $res_col_info;
         }
//===============================================================================
//получаем список зависимых АРМов
function get_SubArms($conn, $arm_id)
         {
         $sel_q="SELECT a.id, a.name, at.path
                        FROM ".TABLE_PRE."arms a, ".TABLE_PRE."arm_additional aa,
                                          ".TABLE_PRE."arm_types at, ".TABLE_PRE."arm_arm sa
                        WHERE sa.parent_arm_id=$arm_id AND sa.child_arm_id=a.id AND aa.arm_id=a.id AND aa.arm_type_id=at.id";
         //echo"sel_q=$sel_q<br>";
         $res=db_getArray($conn, $sel_q);
         foreach($res as $k=>$v)
                 $res[$k]['PATH_N']=href_prep($v['PATH'], "our_arm_id=".$v['ID']);
         return $res;
         }
//Для зависимых АРМов- формируем путь обратно и выбирает информацию о ссылающихся колонках
function SubArms_nav($conn, $over_id, $over_arm_id, $entity_structure, $our_arm_id="")
         {
         $count_over_id=count($over_id);
         /*
         if(!$count_over_id && !$over_obj)
             {
             $sel_obj="select object_id from ".TABLE_PRE."objects_tables where table_id=$table_main_id and main=1";
             $res_obj=db_getArray($conn, $sel_obj, 2);
             $over_obj=$res_obj['OBJECT_ID'];
             $ret[over_obj]=$over_obj;
             }
         */
         if($count_over_id)
            {
            foreach($over_id as $k=>$v)
                    {
                    $sel_tab="SELECT distinct t.id as table_id, t.name as table_name, at.path,
                    a.name as arm_name, a.id as arm_id, max(ac.form_type_id) as max_form_type
                              FROM ".TABLE_PRE."tables t,
                                     ".TABLE_PRE."arms a, ".TABLE_PRE."arm_additional aa, ".TABLE_PRE."arm_types at,
                                     ".TABLE_PRE."arm_columns ac, ".TABLE_PRE."columns c
                              WHERE ac.arm_id=a.id AND ac.column_id=c.id AND c.table_id=t.id AND t.main=1
                              AND a.id=aa.arm_id AND at.id=aa.arm_type_id
                              AND a.id=".$over_arm_id[$k]." group by t.id, t.name, at.path, a.name, a.id";
                    //echo"sel_tab=$sel_tab<br>";
                    $res_tab[$k]=db_getArray($conn, $sel_tab, 2);
                    $name_over=get_byId($conn, $res_tab[$k]['TABLE_NAME'], $v);
					if (!$name_over['NAME']) //Если нет колонки name
						{
						$sel_col_name="SELECT c1.name, ac.name as about, min(ac.sort_order) FROM ti_columns c1, ti_arm_columns ac WHERE ac.column_id=c1.id AND ac.arm_id=".$over_arm_id[$k]." AND table_id=".$res_tab[$k]['TABLE_ID']." AND upper(c1.name) like ('%NAME%') AND column_type_id IN(SELECT id FROM ti_column_types WHERE unit_name IN('varchar', 'clob'))";
						$sel_col_name.=" AND NOT EXISTS(SELECT id FROM ti_columns c2 WHERE c2.table_id=c1.table_id AND upper(c2.name)=replace(upper(c1.name), 'NAME', 'WIDTH')) GROUP BY c1.name, ac.name ORDER BY 3 asc, 1 asc";
						//echo __LINE__." sel_col_name=$sel_col_name<br>";
						$res_col_name=db_getArray($conn, $sel_col_name, 1);
						foreach ($res_col_name as $k_col_name=>$v_col_name)
							{
							//print_r($v_col_name);
							if ($name_cols)
								$name_cols.="||' '||";
							$name_cols.= "'<span title=\"".$v_col_name['ABOUT']."\">'||". $v_col_name['NAME']."||'</span>'";
							}
						if ($name_cols)
							{
							//echo __LINE__." name_cols=$name_cols<br>";
							$name_cols.=" as name";
							$name_over=get_byId($conn, $res_tab[$k]['TABLE_NAME'], $v, $name_cols);
							}

						}
                    $res_tab[$k]['NAME']=$name_over['NAME']." (id=".$v.")";
                    $ret_string.="&over_id[$k]=$v&over_arm_id[$k]=".$over_arm_id[$k];
                    $ret_hidden.=forminput("hidden", "over_id[$k]", $over_id[$k], "", "", 1).forminput("hidden", "over_arm_id[$k]", $over_arm_id[$k], "", "", 1);
                    }
            //$res_tab[$k+1]=array("TABLE_ID"=>$entity_structure[0]['TABLE_ID'], "ARM_ID"=>$entity_structure[0]['ARM_ID'], "ARM_NAME"=>$entity_structure[0]['ARM_NAME'], "FILE_NAME"=>g_URL);
            $sel_arm_arm_col="SELECT child_entity_column_id as chec_id FROM ".TABLE_PRE."arm_arm
                                     WHERE parent_arm_id=".$over_arm_id[$k]." AND child_arm_id=$our_arm_id";
            //echo"sel_arm_arm_col=$sel_arm_arm_col<br>";
            $res_arm_arm_col=db_getArray($conn, $sel_arm_arm_col, 2);
            if($res_arm_arm_col['CHEC_ID'])
               {
               $sel_col="select c.id, c.name as name from ".TABLE_PRE."columns c, ".TABLE_PRE."columns c2
                                 where c.ref_column_id=c2.id and c2.table_id=".$res_tab[$k]['TABLE_ID']."
                                         and c.table_id=".$entity_structure[0]['TABLE_ID']."
                                         AND c.id=".$res_arm_arm_col['CHEC_ID'];
               }
            else
                {
                $sel_col="select c.id, c.name as name from ".TABLE_PRE."columns c, ".TABLE_PRE."columns c2
                                 where c.ref_column_id=c2.id and c2.table_id=".$res_tab[$k]['TABLE_ID']." and c.table_id=".$entity_structure[0]['TABLE_ID'];
                }
            //echo"sel_col=$sel_col<br>";
            $res_col=db_getArray($conn, $sel_col, 2);
            $ret['IND']=$res_tab;
            $ret['COL']=$res_col;
            $ret['WHERE']=$entity_structure[0]['TABLE_NAME'].".".$res_col['NAME']."=".$over_id[$k];

            $ret['VAL']=$over_id[$k];
            $ret['ADD_STRING']=$ret_string;
            $ret['HIDDEN']=$ret_hidden;
            }
         else
             $ret=array();
         return $ret;
         }
//подготовка строки для ссылки на зависимый АРМ
function prep_over_string($id, $arm_id)
         {
         global $SubArms;
         //echo"count=".count($SubArms['IND'])."<br>";
         if(!count($SubArms['IND']))
             {
             $ret_string="over_id[1]=$id&over_arm_id[1]=".$arm_id;
             }
         else
             {
             $k=count($SubArms['IND']);
             //echo"k=$k<br>";
             $ret_string=$SubArms['ADD_STRING']."&over_id[".($k+1)."]=$id&over_arm_id[".($k+1)."]=".$arm_id;
             }
         //echo"ret_string=$ret_string<br>";
         return $ret_string;
         }
//обратный путь от зависимого АРМа
function sub_arms_path($sub_arms)
         {
         global $over_id;
		 //echo"<pre>";
		 //print_r($sub_arms);
         if(is_array($sub_arms['IND']))
			foreach($sub_arms['IND'] as $k=>$v)
                 {
                 $href=href_prep($v['PATH'], "our_arm_id=".$v['ARM_ID'].$over_str);
                 if($v['MAX_FORM_TYPE'])//если есть контролы формы
                    {
                    href($href, $v['ARM_NAME']);
                    echo":";
                    href($href."&our_ent_id=".$over_id[$k], $v['NAME']);
                    }
                 else
                     href($href, $v['ARM_NAME'].":".$v['NAME']);
                 $over_str.="&over_id[$k]=".$over_id[$k]."&over_arm_id[$k]=".$v['ARM_ID'];
                 br();
                 }
         }
//Показываем ссылки на зависимые АРМы
function over_nav($sub_arms, $arm_id)
         {

         }
//=======================================================================================================
//Пробегаем по циклу описания АРМ - формируем запрос
function get_for_list($conn, $ar)
             {
             extract($ar);
			 /*
			 $arm_type=="tree" - АРМ-дерево
			 */

             global $CONTROL_AR;
             global $COLUMN_AR;//для информации о ссылках - какая таблица, как называется колонка для значения
			//echo"<pre>";
			//print_r($ar);
             foreach($entity_structure as $k=>$v)
                     {
                     $this_col=$v['TABLE_NAME'].".".$v['COL_NAME']." as ".$v['COL_NAME'];
                     //echo $v['TABLE_NAME']."-".$v['MAIN'].", table_list=$table_list<br>";
                     //echo"this_col=$this_col<br>";
                     $tab_string=$v['TABLE_NAME'];
                     $ac_id=$v['ARM_COLUMN_ID'];
                     if($v['MAIN'])//не кросс-таблица
                        {
                        if($v['REF_COLUMN_ID'] && $v['LIST_TYPE_ID'])
                           {
                           if(!$CONTROL_AR[$v['LIST_TYPE_ID']])
                               {
							   //echo"1 get_for_list - ".$v['LIST_TYPE_ID']."<br>";
                               $CONTROL_AR[$v['LIST_TYPE_ID']]=control_info($conn, $v['LIST_TYPE_ID']);
                               }

                           $tab_ref=get_select_down_ar($conn, array("ref_id"=>$v['REF_COLUMN_ID'], "method"=>"info"));
                           if($tab_ref['NAME'])
                              {
                              $tab_string=$tab_ref['NAME'].$ac_id;
                              $COLUMN_AR[$ac_id]['table_name']=$tab_ref['NAME'];
                              $COLUMN_AR[$ac_id]['column_name']=strtoupper($tab_string."_name");

                              //echo"fields=".$CONTROL_AR[$v['LIST_TYPE_ID']]['params']['FIELDS']."<br>";
                              if($CONTROL_AR[$v['LIST_TYPE_ID']]['params']['FIELDS'])
                                 $fields=ereg_replace("TABLE_NAME", $tab_string, $CONTROL_AR[$v['LIST_TYPE_ID']]['params']['FIELDS']);
                              else
                                  $fields=$tab_string.".name";
                              if(!$v['NULL_VALUE'])
                                  {
                                  //if($tab_ref['NAME'])
                                  $col_list.=($col_list?", ":"").$fields." as ".$tab_string."_name";
                                  $whereSQL.=($whereSQL?" and ":"").$v['TABLE_NAME'].".".$v['COL_NAME']."=".$tab_string.".id";
                                  $table_list.=($table_list?", ":"").$tab_ref['NAME']." ".$tab_string;
                                  $table_list_ar[$tab_ref['NAME']]=$tab_ref['NAME'];
                                  }
                              else
                                     {
                                     $col_list.=($col_list?", ":"")."(select $fields
                                           from ".$tab_ref['NAME']." ".$tab_string." where ".$tab_string.".id=".$v['TABLE_NAME'].".".$v['COL_NAME'].") as ".$tab_string."_name ";
                                     }
                              }
                           }//конец условия, что ссылочная колонка в списке

                        //echo"be whereSQL=$whereSQL<br>";

                        //echo"sort_by=$sort_by, col_id=".$v['COLUMN_ID']."<br>";
                        if($v['SORT_MAIN'] || abs($sort_by)==$v['COLUMN_ID'])
                           {
                           //echo"YES<br>";

                           if(abs($sort_by)==$v['COLUMN_ID'])
                            $index_sort_by=0;
                           elseif($v['SORT_MAIN'])
                                  $index_sort_by=$v['SORT_MAIN'];
                           $sort_by_ar[$index_sort_by]['COL_ID']=$v['COLUMN_ID'];
                           if($v['REF_COLUMN_ID'] && $tab_ref['NAME'])
                              {
                              $sort_by_ar[$index_sort_by]['COL_NAME']=$tab_ref['NAME'].$v['ARM_COLUMN_ID']."_name";
                              //echo"col_name=".$sort_by_ar[$index_sort_by]['COL_NAME']."<br>";
                              }
                           else
                            $sort_by_ar[$index_sort_by]['COL_NAME']=$v['COL_NAME'];

                           $sort_by_ar[$index_sort_by]['SORT_TYPE_ID']=$v['SORT_TYPE_ID'];

                           if(($v['SORT_TYPE_ID']==1 && abs($sort_by)!=$v['COLUMN_ID']) || ( abs($sort_by)==$v['COLUMN_ID'] && $sort_by>0))
                              $sort_by_ar[$index_sort_by]['SORT_TYPE']="ASC";
                           else
                               $sort_by_ar[$index_sort_by]['SORT_TYPE']="DESC";
                           }
                        }//конец условия, что не кросс-таблица

                     //echo"tab_string=$tab_string<br>";
                     $v['tab_string']=$tab_string;
                     if($v['FILTER_TYPE_ID'])
                        {
                        $ret['filter_ar'][]=$v;
                        //echo"filter<br>";
                        }

                     if($v['LIST_TYPE_ID'] && !$complex)
                        {
                        $ret['list_ar'][]=$v;
                        if($v['MAIN'])
                           {
                           if($v['COL_TYPE']=="date")
                              $col_list.=($col_list?", ":"").db_date_char($v['TABLE_NAME'].".".$v['COL_NAME'])." AS ".$v['COL_NAME'];
                           else
                              $col_list.=($col_list?", ":"").$v['TABLE_NAME'].".".$v['COL_NAME'];
                           }
                        if(!$CONTROL_AR[$v['LIST_TYPE_ID']])
                            {
							//echo"2 get_for_list - ".$v['LIST_TYPE_ID']."<br>";
                            $CONTROL_AR[$v['LIST_TYPE_ID']]=control_info($conn, $v['LIST_TYPE_ID']);
                            }
                        if($CONTROL_AR[$v['LIST_TYPE_ID']]['NAME']=="Id")
                           {
                           $ret['table']=$v['TABLE_NAME'];
                           $ret['table_id']=$v['TABLE_ID'];
                           }
                        }
                     if($v['FORM_TYPE_ID'] )
                        {
                        $ret['form_ar'][]=$v;
                        if($v['MAIN'])
                           {
                           if($v['COL_TYPE']=="date")
                              $col_form.=($col_form?", ":"").db_date_char($v['TABLE_NAME'].".".$v['COL_NAME'])." AS ".$v['COL_NAME'];
                           else
                               $col_form.=($col_form?", ":"").$v['TABLE_NAME'].".".$v['COL_NAME'];
                           }
                        $ret['arm']['COMPLEX']=1;
                        if(!$CONTROL_AR[$v['FORM_TYPE_ID']] && $show_form)
                            {
							//echo"3 get_for_list - ".$v['LIST_TYPE_ID']."<br>";
                            $CONTROL_AR[$v['FORM_TYPE_ID']]=control_info($conn, $v['FORM_TYPE_ID']);
                            //echo"form-".$v['FORM_TYPE_ID']." - ".$CONTROL_AR[$v['FORM_TYPE_ID']]['NAME']."<br>";
                            }
                        if($CONTROL_AR[$v['FORM_TYPE_ID']]['NAME']=="Id")
                           {
                           $ret['table']=$v['TABLE_NAME'];
                           $ret['table_id']=$v['TABLE_ID'];
                           }
                        //echo"control_ar[$control_id]=".$CONTROL_AR[$control_id]['NAME']."<br>";
                        if($CONTROL_AR[$v['FORM_TYPE_ID']]['NAME']=="File" || $CONTROL_AR[$v['FORM_TYPE_ID']]['NAME']=="Image"
                        || $CONTROL_AR[$v['FORM_TYPE_ID']]['NAME']=="preview-image" ||  $CONTROL_AR[$v['FORM_TYPE_ID']]['NAME']=="SaveTemplate")
                           {
                           $col_form.=($col_form?", ":"").$v['TABLE_NAME'].".".$v['COL_NAME']."_name,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_size,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_height,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_width,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_type_id" ;
                           if($CONTROL_AR[$v['FORM_TYPE_ID']]['NAME']=="SaveTemplate")
                                $col_form.=", ".$v['TABLE_NAME'].".".$v['COL_NAME']."_path";
                           }
                        if($CONTROL_AR[$v['FORM_TYPE_ID']]['NAME']=="MultiFile")
                           {
                          // echo $col_form = str_replace($v['TABLE_NAME'].".".$v['COL_NAME'].",",$v['TABLE_NAME'].".".$v['COL_NAME']."_name,",$col_form);
                           $col_form.=($col_form?", ":"").$v['TABLE_NAME'].".".$v['COL_NAME']."_name,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_size,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_height,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_width,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_type_id" ;

                           }
                        if($CONTROL_AR[$v['LIST_TYPE_ID']]['NAME']=="Image" || $CONTROL_AR[$v['LIST_TYPE_ID']]['NAME']=="preview-image" )
                               {
                               //echo"Image<br>";
                               $col_list.=($col_list?", ":"").$v['TABLE_NAME'].".".$v['COL_NAME']."_name,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_size,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_height,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_width,
                                                          ".$v['TABLE_NAME'].".".$v['COL_NAME']."_type_id" ;

                               }
                        }
                     //echo"list - ".$v['TABLE_NAME']." - ".$table_list_ar[$v['TABLE_NAME']]."<br>";
                     //echo"col_list=$col_list<br>";
                     if(!$table_list_ar[$v['TABLE_NAME']] && $v['MAIN'])
                         {
                         $table_list.=($table_list?", ":"").$v['TABLE_NAME'];
                         $table_list_ar[$v['TABLE_NAME']]=$v['TABLE_NAME'];
                         }
                     }
             if(!$ret['table'])
                 {
                 $ret['table']=$entity_structure[0]['TABLE_NAME'];
                 $ret['table_id']=$entity_structure[0]['TABLE_ID'];
                 }
             //echo"table=".$ret['table'].", table_id=".$ret['table_id']."<br>";
             //разбираемся с префильтрами
             if(count($entity_structure['PREFILTERS']))
                {
                //echo"prefilters<pre>";
				//print_r($entity_structure['PREFILTERS']);
				//echo"table_id=".$ret['table_id']."<br>";
                $ret_prefilter=prefilters_analize($conn, $ret['table_id'], $ret['table'], $entity_structure['PREFILTERS']);
				//echo $ret_prefilter['whereSQL']."<br>";
                if($ret_prefilter['whereSQL'])
                   {
                   $whereSQL.=($whereSQL?" AND ":"").$ret_prefilter['whereSQL'];
                   }
                }
             //конец получения данных по префильтрам
//echo '<!--<pre>'; var_dump($entity_structure[0]); echo '</pre>-->';
             $ret['table_list']=$table_list;
			 $ret['arm']['MULTIADD_ROW']=$entity_structure[0]['MULTIADD_ROW'];
			 $ret['arm']['DELETE_CACHED_IMAGES']=$entity_structure[0]['DELETE_CACHED_IMAGES'];
             $ret['arm']['ADD_ROW']=$entity_structure[0]['ADD_ROW'];
             $ret['arm']['DEL_ROW']=$entity_structure[0]['DEL_ROW'];
             $ret['arm']['EDIT_ROW']=$entity_structure[0]['EDIT_ROW'];
             $ret['arm']['ARM_TYPE_ID']=$entity_structure[0]['ARM_TYPE_ID'];
             if(!$our_ent_id && !$new)
                 {
                 if(is_array($sort_by_ar))
                    {
                    ksort($sort_by_ar);
                    foreach($sort_by_ar as $k=>$v)
                     {
                     //echo"k=$k<br>";
                     global $sort_by;
                     if(!$sort_by)
                         $sort_by=$v['COL_ID']*$v['SORT_TYPE_ID'];
                     $orderSQL.=($orderSQL?", ":"").$v['COL_NAME']." ".$v['SORT_TYPE'];
                     }
                    }
				if($arm_type=="tree" )// - АРМ-дерево
					{
					if(strpos($col_list, TREE_LEVEL)===false)
						{
						$col_list.=", ".TREE_LEVEL;
						}
					$orderSQL=REAL_ORDER;
					}

                 $ret['sel_q']="SELECT $col_list FROM $table_list";
                 $ret['sel_count_q']="SELECT COUNT(*) AS c FROM $table_list";
                 $ret['where']=$whereSQL;
                 $ret['sort_by_ar']=$sort_by_ar;
                 $ret['orderSQL']=$orderSQL;
                 }
             elseif($our_ent_id)
                 {
                 $ret['sel_q']="select $col_form from ".$entity_structure[0]['TABLE_NAME']." where id=$our_ent_id".($ret_prefilter['whereSQL']?" AND ".$ret_prefilter['whereSQL']:"");
                 //echo("sel_q=".$ret['sel_q']."<br>");
                 }
             //echo("sel_q=".$ret['sel_q']."<br>");
             //echo"main_table=".$ret['table']."(".$ret['table_id'].")<br>";
             return $ret;
             }
//=============================================================================================================
//функция преобразует массив addit_string_ar в строку или набор полей hidden по необходимости
function addit_string_form($ar)
         {
         extract($ar);
         if($ret_format=="string" && $first)
            $ret.=$first;
         foreach($addit_string_ar as $k=>$v)
                 {
                 //echo"k - $k=$v<br>";
                 if(!$no_val[$k])
                    {
                    if(is_array($v))
                       {
                       //echo"k - $k=$v<br>";
                       foreach($v as $k_ar=>$v_ar)
                               {
                               //echo"---- $k_ar=$v_ar<br>";

                               if($ret_format=="string")
                                  $ret.="&".$k."[".$k_ar."]=$v_ar";
                               elseif($ret_format=="form_hidden")
                                      {
                                      $ret.=forminput("hidden", $k."[".$k_ar."]", $v_ar, '', '', 1);
                                      }

                               }
                       }
                    else
                        {
                        if($ret_format=="string")
                           {
                           //echo"k - $k=$v<br>";
                           $ret.="&$k=$v";
                           }
                        elseif($ret_format=="form_hidden")
                           {
                           //echo"k - $k=$v<br>";
                           $ret.=forminput("hidden", $k, $v, '', '', 1);
                           }
                        }
                    }
                 }
         //echo"ret=$ret<br>";
         return $ret;
         }
//=======================================================================================================
//функция обработки добавления из списка
function new_list_entity($conn, $ar)
         {
         extract($ar);
         global $addit_string_ar;
         $prep_ins=prepare_base_control($conn, $info_ar, array("method"=>"list_prep", "num_row"=>"new"));
         if($prep_ins['error'])
            {
            $ret['error']['new']=$prep_ins;
            //echo"ERR 1";
            }
         elseif(is_array($prep_ins))
            {
            $ret['yes'] =db_insert($conn, $info_ar['table'], $prep_ins['col_name'], $prep_ins['val'], $prep_ins['type']);
            if($ret['yes'])
               {
               //.blacki ----------------------------------------------------------------------
               if($our_ent_id)
                  {
                  $this_id=$our_ent_id;
                  $name=UPDATE;// для вызова full_log() //.blacki
                  }
               else
                  {
                  $this_id=$ret['yes'];
                  $name=ADD;// для вызова full_log() //.blacki
                  }
               global $our_arm_id;
               //echo "<br>ARM=".$our_arm_id." ent_id=".$this_id." table=".$info_ar['list_ar'][0]['TABLE_ID']." name=".$name."<br><br>";
               full_log($conn, $our_arm_id, $this_id, $name, $info_ar['list_ar'][0]['TABLE_ID']);
               //---------------------------------------------------------------------- .blacki
               $after_save=after_save($conn, array("prep_ins"=>$prep_ins, "info_ar"=>$info_ar, "this_id"=>$ret['yes'], "new_ins"=>1, "table_name"=>$info_ar['table']));
				if($after_save['error'])
				   {
					//db_rollback($conn);
					$ret['error']['new']=1;
					$ret['yes']=0;
				   }

               }//конец условия, что сохранение прошло нормально
            else
             {
             //db_rollback($conn);
             //echo"Roolback";
             $ret['error']['new']=1;
             }
            }
		if($ret['yes'] && !$ret['error']['new'])
			db_commit($conn);
		else
			db_rollback($conn);
         return $ret;
         }
//=======================================================================================================
//функция обработки обновления из списка
function update_list_entity($conn, $ar)
 {
                 global $our_arm_id;
         extract($ar);
         foreach($info_ar['list_ar'] as $k=>$v)
                 {
                 //echo"$k=".$v['COL_NAME']."<br>";
                 global $$v['COL_NAME'];
                 }
                if(is_array($mark))
                        {
                        foreach($mark as $k=>$v)
                 {
                 //echo"$k=$v<br>";
                 $prep_ins=prepare_base_control($conn, $info_ar, array("method"=>"list_prep", "num_row"=>$k));
                 if($prep_ins['error'])
                    $ret['error'][$k]=$prep_ins;
                 elseif(is_array($prep_ins) && $prep_ins['fields_num'])
                        {
                        $ret['yes']=db_update($conn, $info_ar['table'], $prep_ins['col_name'], $prep_ins['val'], $prep_ins['type'], " id=$v");
                        //echo"yes=".$ret['yes']."<br>";
				 		foreach($prep_ins['type'] as $key=>$tp)
						{
							if($tp=='file' || $tp=='blob'){// echo $tp.' '.$prep_ins['col_name'][$key].' '.$prep_ins['val'][$key].' <br>';
								include(PATH_INC."func.php");
								$dirname = IMAGE_PATH.IMAGE_GALLERY;
								scan_dir($dirname,false);
							}
						}

                        }
                 elseif(is_array($prep_ins) && !$prep_ins['fields_num'])
                        {
                        $ret['yes']=1;
                        }
                 if($ret['yes'])
                    {
					//echo"<pre>";
					//print_r($prep_ins);
                    $after_save=after_save($conn, array("prep_ins"=>$prep_ins, "info_ar"=>$info_ar, "this_id"=>$k));
                    //echo "<br>ARM=".$our_arm_id." ent_id=".$v." table=".$info_ar['list_ar'][0]['TABLE_ID']." name=".'UPDATE'."<br><br>";
                    full_log($conn, $our_arm_id, $v, UPDATE, $info_ar['table_id']);
					if($after_save['error'])
						{
						$ret['error'][$k]=1;
						$ret['yes']=0;
						}
                    }//конец условия, что обновление прошло нормально
                 elseif(!$prep_ins['error'])
                         {
                         $ret['error'][$k]=1;
                         $ret['error_save'][$k]=1;
                         }
				if($ret['yes'] && !$ret['error'][$k])
					db_commit($conn);
				else
					db_rollback($conn);
                 }//конец цикла
                        }
         /*
         if($TreeOrderReculculation)
            {
            TreeOrderReculculation($conn, $info_ar['table']);
            }
         */
         return $ret;
         }
//=======================================================================================================
//функция обработки удаления из списка
function delete_list_entity($conn, $ar)
         {//         echo '<!--<pre>'; var_dump($ar); echo '</pre> -->';
         extract($ar);
         global $our_arm_id, $TreeOrderReculculation, $ERROR, $ERROR_CODE;
         //$test

         foreach($mark as $k=>$v)
                 {
                 db_begin($conn);
                 $del_col_err=0;
                 $del_table_err=0;

                 if(strtoupper($info_ar['table'])==TABLE_PRE."COLUMNS" && $AlterIt[$k])
                       {
                           if(isset($AlterIt[$v])) {
                               $del_col=alter_table_del_col($conn, $v);
                               if(!$del_col)
                                   {
                                   //echo"err";
                                   $del_col_err=1;
                                   }
                           }
                       }
                 elseif(strtoupper($info_ar['table'])==TABLE_PRE."TABLES" && $AlterIt[$k])
                       {
                       $del_table=del_table($conn, $v);
                       //echo"del_table=$del_table ($v)";
                       if(!$del_table)
                           {
                           $del_table_err=1;
                           }
                       }

                 if(!$del_col_err && !$del_table_err)
                     {
                     $del_q="delete from ".$info_ar['table']." where id='$v'";
                     //.blacki ----------------------------------------------------------------------
                     //echo "<br>ARM=".$our_arm_id." ent_id=".$v." table=".$info_ar['list_ar'][0]['TABLE_ID']." name=".'DELETE'."<br><br>";
                     full_log($conn, $our_arm_id, $v, DELETE, $info_ar['table_id']);
                     //---------------------------------------------------------------------- .blacki
                     $del=db_query($conn, $del_q);
                     //echo"del_q=$del_q, $del, $conn<br>";
                     if($del)
                        {
                        if (sql_log_yes($info_ar['table']))
                            {
                            sql_log($del_q);
                            }
				         indexer_wait($conn, "", $v, $info_ar['table_id']);//индексация как части - справочник
						}
                     if($del)
						{
						db_commit($conn);
						$res++;
						}
                     else
                         {
                         db_rollback($conn);
                         $ret['error'][$v]=1;
                         }
                     }
                 else
                     {
                     $ret['error']=1;
                     }

                 }
         if(count($TreeOrderReculculation))
            {
            TreeOrderReculculation($conn, $info_ar['table']);
            }
         if(count($mark)==$res)
            {
            $ret['yes']=1;
            }

         return $ret;
         }
//==========================================================
//сохранение данных из формы
function save_form($conn, $ar)
         {
         global $ERROR, $ERROR_CODE;
         extract($ar);
		 //echo __LINE__."<pre>"; print_r($info_ar);
         $prep_ins=prepare_base_control($conn, $info_ar, array("method"=>"save_form"));
         //echo __LINE__."<pre>";  print_r($prep_ins);
         foreach($prep_ins['col_name'] as $k_prep=>$v_prep)
                 {
                 //echo"prep - $v_prep - ".$prep_ins['val'][$k_prep]." - ".$prep_ins['type'][$k_prep]."<br>";
                 }

         if($prep_ins['error'])
			 {
 			//echo __LINE__."<br><pre>";
            $ret['error']=$prep_ins['err'];
			//print_r($ret['error']);
			 }
         elseif(is_array($prep_ins))
            {
            db_begin($conn);

            if($our_ent_id)
               {
				if($prep_ins['col_name'])//есть колонки, которые надо обновлять
				   {
					$ret['yes']=db_update($conn, $info_ar['table'], $prep_ins['col_name'], $prep_ins['val'], $prep_ins['type'], " id=$our_ent_id");
					foreach($prep_ins['type'] as $key=>$tp)
					{
						if($tp=='file' || $tp=='blob'){// echo $tp.' '.$prep_ins['col_name'][$key].' '.$prep_ins['val'][$key].' <br>';
							include(PATH_INC."func.php");
							$dirname = IMAGE_PATH.IMAGE_GALLERY;
							scan_dir($dirname,false);
						}
					}
				   }
				 else
					 $ret['yes']=1;
               $name=UPDATE;// для вызова full_log() //.blacki

               }
            else
               {
               $ret['yes']=db_insert($conn, $info_ar['table'], $prep_ins['col_name'], $prep_ins['val'], $prep_ins['type'], array("show_ins"=>$show_ins));
               $name=ADD;// для вызова full_log() //.blacki
               $new_ins=1;
               }
            if($ret['yes'])
               {
               if($our_ent_id)
                     $this_id=$our_ent_id;
               else
                   $this_id=$ret['yes'];
               $after_save = after_save($conn, array("prep_ins"=>$prep_ins, "info_ar"=>$info_ar, "this_id"=>$this_id, "new_ins"=>$new_ins, "table_name"=>$info_ar['table']));

               //$after_save=1;
               //echo"after_save=$after_save, conn=$conn<br>";
				if($after_save['error'])
				   {
					//echo"Error<br>";
					//db_rollback($conn);
					$ret['error']=1;
					$ret['yes']=0;
				   }

               }//конец условия, что сохранение прошло нормально
            else
                {
                //db_rollback($conn);
                $ret['error']=1;
                }
//.blacki ----------------------------------------------------------------------
               //echo "<br>SAVE:<br><pre>".var_dump($ar)."</pre><br><br><br>";
               //echo "<br>SAVE:<br><pre>".var_dump($info_ar)."</pre><br><br><br>";
               //echo "<br>ARM=".$our_arm_id." ent_id=".$this_id." table=".$info_ar['list_ar'][0]['TABLE_ID']." name=".$name."<br><br>";
               full_log($conn, $our_arm_id, $this_id, $name, $info_ar['list_ar'][0]['TABLE_ID']);
//---------------------------------------------------------------------- .blacki
            }
		if(!$ret['error'] && $ret['yes'])
			db_commit($conn);
		else
			db_rollback($conn);
         return $ret;
         }
//======================================================================================================
//функция проверки и подготовки данных для сохранения
function prepare_base_control($conn, $info_ar, $ar)
         {
         extract($ar);
		 //echo __LINE__."<pre>prepare_base_control";
		 //print_r($ar);
		 //print_r($info_ar);
         global $TYPE_AR, $SubArms;
         if($method=="list_prep")
            $for_ar=$info_ar['list_ar'];
         elseif($method=="save_form")
            $for_ar=$info_ar['form_ar'];
         $ind_k=0;
         foreach($for_ar as $k=>$v)
         {

                 //echo __LINE__."$k=>$v - ".$v['COL_NAME']." - ".$values_ar[$v['COL_NAME']]."(".$v['COL_ABOUT'].")<br>";
				/*
                 foreach($v as $k1=>$v1)
                         {
                         if(eregi("[a-z]", $k1))
                            echo"&nbsp;&nbsp;$k1=$v1<br>";
                         }
				*/

                 if($v['COLUMN_ID']!=$SubArms['COL']['ID'] || !$SubArms['COL']['ID'])//если не ссылка на родительскую ссылку
                    {
                    //$ret_show_control[$k]=show_control($conn, $v, array("method"=>$method, "value"=>$values_ar[$v['COL_NAME']]));
					/*if($v['COL_NAME']=="mfile")
						{
						echo __LINE__." - $method<br><pre>";
						print_r($v);
						}*/
                    $ret_show_control[$k]=show_control($conn, $v, array("method"=>$method, "num_row"=>$num_row));
					//if($v['COL_NAME']=="mfile")
					//	echo  __LINE__."<br>";
                    }
                 else
                     {
					 //echo"be<br>";
                     $ret_show_control[$k]['no_ins']=1;
                     }
                 if($ret_show_control[$k]['err'])
                    {
                    $ret['err'][$k]=1;
                    $ret['error']=1;
                    }
                 if($ret_show_control[$k]['file_ar'])
                    {
                    foreach($ret_show_control[$k]['file_ar'] as $k_f=>$v_f)
                            {
                            //$ind_k++;
                            //echo"$k_f=>$v_f<br>";
                            }
                    $file_col=$v['COL_NAME'];

                    if($ret_show_control[$k]['file_ar'] && !$ret_show_control[$k]['no_ins'])//если файл закачали
                       {
                       //файл
                       $ind_k++;
                       $ret['col_name'][$ind_k]=$file_col;
                       $ret['val'][$ind_k]=$ret_show_control[$k]['file_ar']['val'];
                       $ret['type'][$ind_k]="blob";
                       //размер
                       $ind_k++;
                       $ret['col_name'][$ind_k]=$file_col."_size";
                       $ret['val'][$ind_k]=($ret_show_control[$k]['file_ar']['size']?$ret_show_control[$k]['file_ar']['size']:BASE_NULL);
                       $ret['type'][$ind_k]="int";
                       //высота
                       $ind_k++;
                       $ret['col_name'][$ind_k]=$file_col."_height";
                       $ret['val'][$ind_k]=($ret_show_control[$k]['file_ar']['height']?$ret_show_control[$k]['file_ar']['height']:BASE_NULL);
                       $ret['type'][$ind_k]="int";
                       //ширина
                       $ind_k++;
                       $ret['col_name'][$ind_k]=$file_col."_width";
                       $ret['val'][$ind_k]=($ret_show_control[$k]['file_ar']['width']?$ret_show_control[$k]['file_ar']['width']:BASE_NULL);
                       $ret['type'][$ind_k]="int";
                       //тип
                       $ind_k++;
                       $ret['col_name'][$ind_k]=$file_col."_type_id";
                       $ret['val'][$ind_k]=$ret_show_control[$k]['file_ar']['type_id'];
                       $ret['type'][$ind_k]="int";

					   //путь, куда класть шаблон
					   //echo"path_save=".$ret_show_control[$k]['file_ar']['path_save']."<br>";
					   if($ret_show_control[$k]['file_ar']['path_save'])
						   {
	                       $ind_k++;
		                   $ret['col_name'][$ind_k]=$file_col."_path";
			               $ret['val'][$ind_k]=$ret_show_control[$k]['file_ar']['path_save'];
				           $ret['type'][$ind_k]="varchar";
						   }
                       }
                    $ind_k++;
                    $ret['col_name'][$ind_k]=$file_col."_name";
                    $ret['val'][$ind_k]=$ret_show_control[$k]['file_ar'][$file_col.$v['ARM_COLUMN_ID'].'_name'];
                    $ret['type'][$ind_k]="varchar";
					//echo __LINE__."<pre>";
					//print_r($ret);
					//exit;
                    }
                 elseif(!$ret_show_control[$k]['no_ins'] && $v['COLUMN_ID'])
                     {
                     $ind_k++;
                     $ret['col_name'][$ind_k]=$v['COL_NAME'];
                     $ret['val'][$ind_k]=$ret_show_control[$k]['val'];
                     //$ret['val_base'][$k]=$ret_show_control[$k]['val_base'];
                     if(!$TYPE_AR[$v['COLUMN_TYPE_ID']])
                         {
                         $TYPE_AR[$v['COLUMN_TYPE_ID']]=get_byId($conn, TABLE_PRE."column_types", $v['COLUMN_TYPE_ID'], "name, unit_name");
                         }
                     $ret['type'][$ind_k]=$TYPE_AR[$v['COLUMN_TYPE_ID']]['UNIT_NAME'];
                     //echo $v['COLUMN_ID']." - ".$ret_show_control[$k]['val']."<br>";
                     }
                 if(isset($ret_show_control[$k]['PostIt']))
                    {
                    $ret['PostIt']=$ret_show_control[$k]['PostIt'];
                    }
                 if(isset($ret_show_control[$k]['AlterIt']))
                    {
                    $ret['AlterIt']=$ret_show_control[$k]['AlterIt'];
                    }
                 if($ret_show_control[$k]['IndexIt']['value'])
                    {
                    //echo"1 Index -".$ret_show_control[$k]['IndexIt']['params']." <br>";
                    $ret['IndexIt']=$ret_show_control[$k]['IndexIt'];
                    }
                 if($ret_show_control[$k]['ClearCache']['value'])
                    {
                    $ret['ClearCache']=$ret_show_control[$k]['ClearCache'];
                    }
                 if(isset($ret_show_control[$k]['TreeOrderReculculation']))
                    {
                    $ret['TreeOrderReculculation']=$ret_show_control[$k]['TreeOrderReculculation'];
                    }
                 if(isset($ret_show_control[$k]['TreePublishReculculation']))
                    {
                    $ret['TreePublishReculculation']=$ret_show_control[$k]['TreePublishReculculation'];
                    }
                 if(isset($ret_show_control[$k]['SaveTemplate']))
                    {
                    //echo"prepare-SaveTemplate<br>";
                    $ret['SaveTemplate'][$k]=$ret_show_control[$k]['SaveTemplate'];
                    }


                 if(isset($ret_show_control[$k]['Rating']))
                    {
                    $ret['Rating'][$k]['col_name']=$v['COL_NAME'];
                    $ret['Rating'][$k]['val']=$ret_show_control[$k]['val'];
                    $ret['Rating'][$k]['params']=$ret_show_control[$k]['Rating']['params'];
                    //echo"params=".$ret['Rating'][$k]['params']['MAX_RATING']."<br>";
                    }

                 if($ret_show_control[$k]['addit_sql'])
                    {
                    foreach($ret_show_control[$k]['addit_sql'] as $k_add=>$v_add)
                            {
                            //echo"$k_add=>$v_add<br>";
                            $ret['addit_sql'][]=$v_add;
                            }
                    }

                 }//конец основного цикла
         if($SubArms['VAL'] && ($method!="list_prep" || $num_row=="new"))
         {
             //echo"be 2<br>";

             $ind_k++;
             $ret['col_name'][$ind_k]=$SubArms['COL']['NAME'];
             $ret['val'][$ind_k]=$SubArms['VAL'];
             $ret['type'][$ind_k]="numeric";
             }
         $ret['fields_num']=$ind_k;
         return $ret;
         }
//======================================================================================
//получаем массив для выпадающего меню
function get_select_down_ar($conn, $ar)
         {
         //echo"<pre>";
         //print_r($ar);
         extract($ar);
         if($ref_id)
            {
            $sel_tab="select c.table_id, t.name from ".TABLE_PRE."columns c, ".TABLE_PRE."tables t
                             WHERE c.id=$ref_id and c.table_id=t.id";
            //echo"sel_tab=$sel_tab<br>";
            $res_tab=db_getArray($conn, $sel_tab, 2);
            if($res_tab)
               {
               if($method=="info")
                  {
                  $ret=$res_tab;
                  }
               else
                   {
                   if($tree)//древовидный справочник
                      {
                      $par_name_col=$res_par['NAME'];
                      //echo"par=$par_name_col<br>";
                      if($exist)
                         {
                         $where_sql=" WHERE id in(select $exist_col_name from $exist_table_name)";
                         if(!$fields)
                             $fields=NAME_PATH;
                         }
                      if($exist_id)
                         $where_sql=" WHERE id in($exist_id)";
                      if($this_id)
						  {
							$where_sql=" WHERE id in(select $exist_col_name from $exist_table_name WHERE $this_col_name =$this_id )";
							//echo"this_id=$this_id, where_sql=$where_sql<br>";
						  }
					//echo"this_id=$this_id, where_sql=$where_sql<br>";
                      if($params_where)
						  {
                          $where_sql.=($where_sql?" AND ":" WHERE ").$params_where;
						  }
                      //if($TopicM)
                      //   $fields="'>>' || name";
                      $sel_col="select id, ".($fields?$fields:"name")." as name, tree_level, ".REAL_ORDER."
                                       FROM ".$res_tab['NAME']."
                                       ".$where_sql."
                                       order by ".REAL_ORDER." ASC";
                      //echo "sel_col=$sel_col<br>";
                      $res_col=db_getArray($conn, $sel_col);
                      foreach($res_col as $k=>$v)
                               {
                               $ret[$v['ID']]="";
                               if($TopicM  || $Topic || $exist)
                                  {
                                  $v_ar=array();
                                  $v_ar=split(RAZD_PARAMS, $v['NAME']);
                                  $v['NAME']="";
                                  foreach($v_ar as $k_t=>$v_t)
                                          {
                                          //echo"$k_t=>$v_t<br>";
                                          if($v_t)
                                             $v['NAME'].=($v['NAME']?" &raquo; ":"").$v_t;
                                          }
                                  }
                               elseif($v['TREE_LEVEL']>1)
                                  $ret[$v['ID']]=nbsp(($v['TREE_LEVEL']-1)*3, 1);
                               $ret[$v['ID']].=$v['NAME'];
                               }
                      }
                   if($tree)
                      {
                      }
                   else
                       {
                       $where_sql=($exist?" WHERE id in(select $exist_col_name from $exist_table_name)":"");
                       if($params_where)
						   {
						 //  echo"this_id=$this_id, exist_id=$exist_id<br>";
   							if(strpos(":this_value", $params_where)===false)
							  {
							  global $our_ent_id;
							  $params_where=str_replace(":this_value", $exist_id?$exist_id:0, $params_where);
							  }
							$where_sql.=($where_sql?" AND ":" WHERE ").$params_where;
						   }
						if($fields)
						   {
							$fields=ereg_replace("TABLE_NAME", $res_tab['NAME'], $fields);
						   }
                       $sel_col="select id, ".($fields?$fields:"name")." as name ".($sort_by?", $sort_by":"")." from ".$res_tab['NAME']."
                                 $where_sql  order by ".($sort_by?"$sort_by, ":"")." name";
                       $res_col=db_getArray($conn, $sel_col);
                       //echo"sel_col=$sel_col<br>";
                       foreach($res_col as $k=>$v)
                               {
                               $ret[$v['ID']]=$v['NAME'];
                               }
                       }
                   }
               }
            else
                echo"Error - sel_tab=$sel_tab<br>";
            }
         elseif($mult_ref_id)
                {
                //echo"mult_ref_id=$mult_ref_id<br>";

                $sel_tab="select t.id, t.name, c2.id as col_id, c2.name as col_name, c2.ref_column_id
                                 from ".TABLE_PRE."columns c, ".TABLE_PRE."tables t, ".TABLE_PRE."columns c2
                                 where c.id=$mult_ref_id and c.table_id=t.id
                                         and  c2.table_id=t.id and c2.ref_column_id is not null";
                //echo"sel_tab=$sel_tab<br>";
                $res_tab=db_getArray($conn, $sel_tab);
                foreach($res_tab as $k=>$v)
                        {
                        if($v['COL_ID']!=$mult_ref_id)
                           {
                           $col_id=$v['COL_NAME'];
                           $ref_col_id=$v['REF_COLUMN_ID'];
                           $ins_col[1]=$v['COL_NAME'];
                           if($exist || $this_id || $exist_id)
                              {
                              $get_select_down_ar["exist"]=$exist;
                              $get_select_down_ar["exist_id"]=$exist_id;
                              $get_select_down_ar["this_id"]=$this_id;
                              $get_select_down_ar["exist_table_name"]=$v['NAME'];
                              $get_select_down_ar["exist_col_name"]=$v['COL_NAME'];
                              }
                           }
                        else//ссылка на предметную таблиицу
                            {
                            $where=" where ".$v['COL_NAME']."=";
                            $ins_col[0]=$v['COL_NAME'];
                            if($this_id)
                               {
                               $get_select_down_ar["this_col_name"]=$v['COL_NAME'];
                               }
                            }
                        }

                $ins_list=$ins_col[0].", ".$ins_col[1];
                //echo"ref_col_id=$ref_col_id<br>";
                $get_select_down_ar["ref_id"]=$ref_col_id;
                //echo"this_id=$this_id<br>";
                $get_select_down_ar["tree"]=$tree;
                $get_select_down_ar["fields"]=$fields;
                if($TopicM)
                   $get_select_down_ar["TopicM"]=1;
                if($params_where)
                   $get_select_down_ar["params_where"]=$params_where;
                $ret=get_select_down_ar($conn, $get_select_down_ar);
                $ret['count']=count($ret);
                    $ret['sel_q']="select $col_id as id from ".$res_tab[0]['NAME'].$where;
                    $ret['del_q']="delete from ".$res_tab[0]['NAME'].$where;
                    $ret['ins_q']="insert into ".$res_tab[0]['NAME']." ($ins_list) values";

                    $ret['search_short_q']="select ".$ins_col[0]." from ".$res_tab[0]['NAME'];
                    $ret['search_q']=$ret['search_short_q']." where ".$ins_col[1]."=";
                    $ret['table']=$res_tab[0]['NAME'];
                    //echo"sel=".$ret['sel_q']."<br>ins=".$ret['ins_q']."<br>del=".$ret['del_q']."<br>search=".$ret['search_q']."<br>";
                    //echo"sel_tab=$sel_tab<br>";

                }//конец мультиселекта
         //echo"ins_q=".$ret['ins_q']."<br>";
         return $ret;

         }
//===============================================================================
//функция возвращает информацию об АРМе - в будущем должна будет еще проверять права
function arm_info($conn, $arm_id)
         {
         $sel_arm_q="SELECT a.name, at.path
                     FROM ".TABLE_PRE."arms a, ".TABLE_PRE."arm_additional aa, ".TABLE_PRE."arm_types at,
                            ".TABLE_PRE."arm_user_v auv
                            WHERE a.id=aa.arm_id AND a.id=$arm_id AND aa.arm_type_id=at.id and auv.arm_id=a.id and auv.user_id=".g_USER_ID;
         //echo"sel_arm_q=$sel_arm_q<br>";
         $res_arm=db_getArray($conn, $sel_arm_q, 2);
         $res_arm['PATH_N']=href_prep($res_arm['PATH'], "our_arm_id=$arm_id");
         return $res_arm;
         }
//получаем id колонки и возвращаем id и имя таблицы
function info_table($conn, $col_id)
         {
         $sel_tab_q="SELECT t.id, t.name FROM ".TABLE_PRE."tables t, ".TABLE_PRE."columns c
                            WHERE c.table_id=t.id and c.id=$col_id";
         $res_tab=db_getArray($conn, $sel_tab_q, 2);
         return $res_tab;
         }
//=================================================================================
//получаем информацию о ссылающихся записях (при удаелнии)
function info_ref($conn, $table_id, $val=0)
         {
         global $SEL_SVIZ;
         if(!isset($SEL_SVIZ[$table_id]))
             {
             $sel_ref_col="SELECT  t.name as table_name, c2.name, t.about
                        FROM ".TABLE_PRE."tables t, ".TABLE_PRE."columns c1, ".TABLE_PRE."columns c2, ".TABLE_PRE."fk_types fk
                        WHERE c1.table_id=$table_id AND c2.ref_column_id=c1.id AND c2.table_id=t.id and c2.fk_type_id=fk.id
                                           AND fk.code_name='restrict'";

             $res_ref=db_getArray($conn, $sel_ref_col);
             //echo"sel_ref_col=$sel_ref_col, $res_ref<br>";
             if(count($res_ref))
                {

                foreach($res_ref as $k=>$v)
                 {
                 $cols_list[$v['TABLE_NAME']].=($cols_list[$v['TABLE_NAME']]?", ":"").$v['NAME'];
                 $where_list[$v['TABLE_NAME']].=($where_list[$v['TABLE_NAME']]?" OR ":" WHERE ").$v['NAME']."=:this_id";
                 //$SEL_SVIZ[$table_id][]="";
                 //echo $v['TABLE_NAME']." - ".$v['NAME']."<br>";
                 $table_about[$v['TABLE_NAME']]=$v['ABOUT'];
                 }
                foreach($cols_list as $k=>$v)
                     {
                     $SEL_SVIZ[$table_id]['sql'][]="SELECT id, $v FROM $k ".$where_list[$k];
                     $SEL_SVIZ[$table_id]['table_about'][]=$table_about[$k];
                     }
                }
             else
                 {
                 $SEL_SVIZ[$table_id]=1;
                 }
             }//конец получения информации по ссылкам на таблицу
         if(is_array($SEL_SVIZ[$table_id]) && $val)
            {
            foreach($SEL_SVIZ[$table_id] as $k=>$v)
                    {
                    echo"$v<br>";
                    }
            }
         }
//изменения по контролам, проводимые после сохранения основных изменений
function after_save($conn, $ar)
         {
         extract($ar);
         global $addit_string_ar, $ERROR, $ERROR_CODE;
         //echo"AFTER_SAVE<br>";
         //echo"<pre>";
         //print_r($prep_ins);
         if(count($prep_ins['addit_sql']))
                   {
                   foreach($prep_ins['addit_sql'] as $k_addit=>$v_addit)
                       {
                       //echo"addit_q=$addit_q, $addit_q_res<br>";
                       $addit_q=var_sql_replace($v_addit, "this_val", $this_id);
                       $addit_q_res=db_query($conn, $addit_q);
                       //echo"addit_q=$addit_q, $addit_q_res<br>";
                       }
                   }

         if(isset($prep_ins['PostIt']))
                  {
                  add_post($conn, $info_ar['table_id'], $this_id, $prep_ins['PostIt']);
                  }
         if($prep_ins['AlterIt']['value'] && $new_ins)
                  {
                    if (strtoupper($table_name) == TABLE_PRE."COLUMNS")
                               {
                               $alter_add=alter_table_add_column($conn, $this_id);
                               //echo"alter_add=$alter_add<br>";
                               if(!$alter_add)
                                   {
                                   $ERROR[]=$ERROR_CODE[1];
                                   //return "error";
                                   //db_rollback($conn);
								   $ret['error']=1;
                                   }
                               }
                    if (strtoupper($table_name) == TABLE_PRE."TABLES")
                               {
                               $table_add=add_table($conn, $this_id);
                               //echo"table_add=$table_add<br>";
                               if(!$table_add)
                                   {
                                   //$ERROR[]=$ERROR_CODE[4];
                                   //db_rollback($conn);
								   $ret['error']=1;
                                   }
                               }
                  }
         if($prep_ins['SaveTemplate'])//нужно сохранять темплайты на диск
            {

            foreach($prep_ins['SaveTemplate'] as $k=>$v)
                    {
                    if(!save_template($v))
                        {
                        $ERROR[]=$ERROR_CODE[6];
                        //echo"error!<br>";
						$ret['error']=1;
                        }
                    }
            }
         if($prep_ins['IndexIt'])
			{
            $indexer_wait=indexer_wait($conn, "", $this_id, $info_ar['table_id']);
            }
         if($prep_ins['ClearCache'])
			{
            $indexer_wait=ClearCache($conn, $info_ar['table_id'], $this_id);
            }

         if(count($prep_ins['Rating']))
                  {
                  //rating($conn, $info_ar['table'], , $v, );
                  foreach($prep_ins['Rating'] as $k_rat=>$v_rat)
                          {
                          //echo"rating - $k_rat - ".$v_rat['col_name']."-".$v_rat['val']."-".$v_rat['params']['MAX_RATING']."<br>";
                          $rating=rating($conn, $info_ar['table'], $v_rat['col_name'], $this_id, $v_rat['val'], $v_rat['params']['MAX_RATING']?$v_rat['params']['MAX_RATING']:MAX_RATING);
						  if(!$rating)
							  {
								$ERROR[]=$ERROR_CODE[7];
							  }
                          }
                  }

         if($prep_ins['TreeOrderReculculation'])
                  {
                  TreeOrderReculculation($conn, $info_ar['table']);
                  }
         if($prep_ins['TreePublishReculculation'])
                  {
                  TreePublishReculculation($conn, $info_ar['table']);
                  }
		return $ret;
         }
//префилтры АРМ по правам
function user_group_filter($conn, $table_id, $column_id="")
	{
	global $get_ref_columns_str_ret, $entity_structure;
	//echo"<pre>";
	//print_r($get_ref_columns_str_ret);
	if($get_ref_columns_str_ret['res_str'][0]['TABLE_ID']=$table_id)//запрос префильтра по главной таблице сущности
		{
		$table_sql=table_sql($conn, array("get_ref_columns_str_ret"=>$get_ref_columns_str_ret, "table_name"=>TABLE_PRE."GROUPS", "column_id"=>$column_id));
		//echo"table_sql=$table_sql<br>";
		return $table_sql;
		}
	else
		return "";
	/*$sel_tab="SELECT DISTINCT er.id, c.id as column_id, upper(c.name) as col_name, ct.unit_name, er.table_id
				FROM  ".TABLE_PRE."columns c, ".TABLE_PRE."column_types ct, ".TABLE_PRE."entity_ref er,  ".TABLE_PRE."tables t
					WHERE ct.id=c.column_type_id AND er.entity_id=".$entity_structure[0]['OBJECT_ID']." AND er.table_id=c.table_id  AND c.table_id=er.table_id AND c.table_id=t.id and upper(t.name)='TI_GROUPS' ORDER BY er.table_id DESC";
					*/
	//echo"sel_tab=$sel_tab<br>";
	}

//анализ превфильтров АРМа и подготовка подзапроса
function prefilters_analize($conn, $table_id, $table, $pref_ar)
         {
         global $our_pref;//массив значений
		 //echo"<pre>";
		 //print_r($pref_ar);
         foreach($pref_ar as $k=>$v)
                 {
                 //echo"$k=".$v['TABLE_ID'].", ".$v['VALUE']."<br>";
                 $tmp="";
				if($v['VALUE']=="USER_GROUP")//префильтр по группе пользователей
						{
						global $group_id_ses;
						if(session_is_registered("group_id_ses"))
							{
							$user_group_filter=user_group_filter($conn, $table_id, $v['COLUMN_ID']);
							$tmp="$table.id IN(".str_replace(":var", " WHERE id IN ($group_id_ses)", $user_group_filter).")";
							}
						elseif($v['REQUIRED'])
							{
							$tmp="$table.id IN(0)";
							}
						}
				else
					 {
					if($table_id==$v['TABLE_ID'])//если фильтр по главной таблице
						 {
						$sel_column_id="SELECT c.id, c.name FROM ti_columns c
                                        WHERE c.table_id=$table_id
                                                 AND c.id=".$v['COLUMN_ID'];

						 }
					else
						 {
						//проверяем, не связь ли один ко многим
						$sel_column_id="SELECT c2.id, c2.name FROM ti_columns c1, ti_columns c2
                                        WHERE c2.ref_column_id=c1.id AND c2.table_id=$table_id
                                                 AND c1.table_id=".$v['TABLE_ID'];
						}
				//echo"sel_column_id=$sel_column_id<br>";
				$res_column_id=db_getArray($conn, $sel_column_id, 2);
                 if($res_column_id['ID'])
                    {
                    if($v['VALUE'] && $v['VALUE']!="USER_GROUP")//если значение предопределено
                       $tmp="$table.".$res_column_id['NAME']." IN (".$v['VALUE'].")";
                    elseif($our_pref[$v['TABLE_ID']])//значения переданы
                       $tmp="$table.".$res_column_id['NAME']." IN (".$our_pref[$v['TABLE_ID']].")";
                    elseif($v['REQUIRED'])//значение обязательное
						{
                        $tmp="$table.".$res_column_id['NAME']." IN (0)";//ничего не должен возвратить
						}
                    else
                        $tmp="";
                    }
                 else//связь многие ко многим
                     {
                     $sel_cross="SELECT t1.name, c1.name as main_col, c2.name as link_col
                                        FROM ti_columns c1, ti_columns c2, ti_columns c3, ti_columns c4,
                                             ti_tables t1, ti_tables t2
                                        WHERE t1.id=c1.table_id AND t2.id=c2.table_id and t1.id=t2.id AND t1.main=0
                                              AND ((c1.ref_column_id=c3.id AND c3.table_id=$table_id)
                                              AND (c2.ref_column_id=c4.id  AND c4.table_id=".$v['TABLE_ID']."))";
                     //echo"sel_cross=$sel_cross<br>";
                     $res_cross=db_getArray($conn, $sel_cross, 2);
                     $tmp=" $table.id IN (SELECT ".$res_cross['MAIN_COL']." FROM ".$res_cross['NAME']." WHERE ".$res_cross['LINK_COL'];
                     if($v['VALUE'])//если значение предопределено
                        $tmp.=" IN (".$v['VALUE']."))";
                     elseif($our_pref[$v['TABLE_ID']])//значения переданы
                        $tmp.=" IN (".$our_pref[$v['TABLE_ID']]."))";
                     elseif($v['REQUIRED'])//значение обязательное
                           $tmp.=" IN (0)";//ничего не должен возвратить
                     else
                         $tmp="";

                     }
                 }
                 if($tmp)
                    $whereSQL.=($whereSQL?" AND ":"").$tmp;
				 }//конец условия, что не по группе пользователя
         //echo"whereSQL=$whereSQL<br>";
         $ret['whereSQL']=$whereSQL;
         return $ret;
         }
//определяем ширину контрола
function show_control_width($ar)
         {
         extract($ar);
         //echo"control_type_name=$control_type_name<br>";
         if($width)
            $addit_this_ctrl=" style=\"width:".$width."px;\"";
         elseif($control_type_name=="InputInt")
                $addit_this_ctrl=" size=\"".($col_length?$col_length:10)."\"";
         elseif($control_type_name=="Input" || $control_type_name=="TextArea")
                {
                //if($col_length && $col_length<MAX_INPUT && ($method=="form_edit" || $method=="search_form"))
                //   $addit_this_ctrl=" size=\"".$col_length."\"";
                //else
                 $addit_this_ctrl=" style=\"width:100%;\"";
                }
         elseif($control_type_name="LookUpCombo" || $control_type_name=="LookUpList"
                                    || $control_type_name=="LookUpListM" || $control_type_name=="TopicM")
                {
                if(($col_length>MAX_INPUT && ($method=="form_edit" || $method=="search_form")) || $method=="list" || $method=="list_new") $addit_this_ctrl=" style=\"width:100%;\"";
                }
         return $addit_this_ctrl;
         }
//сохранение шаблона
function save_template($file_ar)
         {
         //echo"<pre>";
         //print_r($file_ar);
		 //echo"TPL_PATH=".TPL_PATH."<br>";
		 $copy_to=TPL_PATH.ereg_replace("^\.?/", "", $file_ar['file_sa']);
         $res_copy=copy($file_ar['val'], $copy_to);
		 //echo"copy - ".$copy_to."<br>";
         //echo"res_copy=$res_copy<br>";
         if($res_copy) return $res_copy;
         else return false;
         }
//кнопочка для выпадающего календаря
function calendar_but($input_name,$method)
{       if($method=="form_edit")
        {?>
        <a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fPopCalendar(document.<?echo 'edit_form.'.$input_name;?>);return false;" HIDEFOCUS><img name="popcal" align="absmiddle" src="/js/calendar/calbtn.gif" width="34" height="22" border="0" alt=""></a>
        <? }elseif($method=="list"){?>
        <a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fPopCalendar(document.<?echo 'list_form.'.$input_name;?>);return false;" HIDEFOCUS><img name="popcal" align="absmiddle" src="/js/calendar/calbtn.gif" width="34" height="22" border="0" alt=""></a>
        <? }else{?>
        <a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fPopCalendar(document.<?echo $method.'.'.$input_name;?>);return false;" HIDEFOCUS><img name="popcal" align="absmiddle" src="/js/calendar/calbtn.gif" width="34" height="22" border="0" alt=""></a>
        <?
        }
}

?>
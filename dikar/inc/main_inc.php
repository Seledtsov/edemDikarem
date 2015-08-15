<?
if(!DEFINED("IMAGE_FLAG"))
{
//======================================
//Обработка данных из форм
//======================================
if($saveVote)//голосование
	{
	//echo"saveVote=saveVote<br>";
	if(session_is_registered("vote_yes") && $vote_yes[$id])
		{
		header("Location:/index.html?nav_id=$nav_id&no_vote=1");
		exit();
		}
	//провеяем, можно ли голосовать по этому голосованию - по дате и статусу публикации
	$sel_test_vote="SELECT id FROM app_voting WHERE id=$id AND start_date<".db_sysdate()." AND end_date+interval '1 day'>".db_sysdate()." AND ".PUBLISH.PUBLISH_SQL;
	//echo"sel_test_vote=$sel_test_vote<br>";
	$res_test_vote=db_getArray($conn, $sel_test_vote, 2);
	if($res_test_vote['ID'])
		{
		db_begin($conn);
		//echo"<pre>";
		//print_r($answer);
		foreach($answer as $k_q=>$v_q)
			{
			$sel_q="SELECT answer_type_id FROM app_voting_question WHERE  app_voting_id=$id AND id=$k_q";
			$res_q=db_getArray($conn, $sel_q, 2);
			if($res_q['ANSWER_TYPE_ID']==2)//чекбокс - несколько вариантов ответ
				{
				foreach($v_q as $k_a=>$v_a)
					{
					$v_a=intval($v_a);
					$ans_ins[$k_q][]=$v_a;
					$ans_sring=add_text($ans_sring, "answer[$k_q][]=$v_a", "&");
					}
				}
			elseif($res_q['ANSWER_TYPE_ID'])//один вариант ответа
				{
				$v_q=intval($v_q);
				$ans_ins[$k_q]=$v_q;
				$ans_sring=add_text($ans_sring, "answer[$k_q]=$v_q", "&");
				}
			}
		$res_ins=1;
		$ins_res=db_insert($conn, "app_vote_stat", 
			array("date_vote", "remote_address", "remote_address2", "remote_host", "requested_sessid", "res_ins", "vote_id",  "query_string"), 
			array(db_sysdate(), $REMOTE_ADDR, $HTTP_X_FORWARDED_FOR, '', session_id(), $res_ins, $id, $ans_sring),
			array("date", "varchar", "varchar", "varchar", "varchar", "int", "int", "varchar"));
		if(!$ins_res)
			db_rollback($conn);
		else
			{
			foreach($ans_ins as $k_q=>$v_q)
				{
				if(is_array($v_q))
					{
					foreach($v_q as $k_a=>$v_a)
						{
						$ins_ans=db_insert($conn, "app_vote_stat_answer", 
								array("vote_stat_id", "voting_answer_id"),
								array($ins_res, $v_a), array("int", "int"));
						if($res_ins)
							{
							$upd_q="UPDATE app_voting_answer SET amount=".db_isnull()."(amount, 0)+1 WHERE id=$v_a";
							$upd=db_query($conn, $upd_q);
							}
						if(!$ins_ans ||	($res_ins && !$upd))
							db_rollback($conn);
						}
					}
				else
					{
					$ins_ans=db_insert($conn, "app_vote_stat_answer", 
							array("vote_stat_id", "voting_answer_id"),
							array($ins_res, $v_q), array("int", "int"));
					if($res_ins)
						{
						$upd_q="UPDATE app_voting_answer SET amount=".db_isnull()."(amount, 0)+1 WHERE id=$v_q";
						$upd=db_query($conn, $upd_q);
						}
					if(!$ins_ans ||	($res_ins && !$upd))
						db_rollback($conn);				
					}
				}
			$upd_q="UPDATE app_voting SET amount=".db_isnull()."(amount, 0)+1 WHERE id=$id";
			$upd=db_query($conn, $upd_q);	
			if(!$upd)
				db_rollback($conn);			
			else
				{
				db_commit($conn);
				set_ses_var("vote_yes", $id, $id);
				header("Location:/index.html?nav_id=$nav_id&yes_vote=1");
				exit();
				}
			}
		}//конец условия, что можно голосовать
	}//конец голосования
//======================================
if($saveForm && $armId)//сохранить из формы
	{
	$our_arm_id=$armId;
	include_once(PATH_INC."func/func_entity.php");
	$entity_structure = get_entity_structure($conn, $our_arm_id);
	$SubArms=SubArms_nav($conn, $over_id, $over_arm_id, $entity_structure, $our_arm_id);
	$showsel = get_entity_relation($conn, $entity_structure);
	$show_form=1;
	$entity_info_ar=array("complex"=>$complex, "entity_structure"=>$entity_structure, "our_ent_id"=>$our_ent_id, "sort_by"=>$sort_by, "new"=>$new, "show_form"=>$show_form);
	$entity_info=get_for_list($conn, $entity_info_ar);
    $save_form_ar=array("info_ar"=>$entity_info, "our_arm_id"=>$our_arm_id, "our_ent_id"=>$our_ent_id);
    $res=save_form($conn, $save_form_ar);
	if($res['yes'])
		{
		$href=g_URL."?ans=1&nav_id=$nav_id";
		if($layer_id)
			$href.="&layer_id=$layer_id";
		header("Location:$href");
		exit;
		}
	else
		{
		//print_r($res['error']);
        foreach($res['error'] as $k=>$v)
                  {
                  $error_xml.="<ErrorRow>".$entity_info['form_ar'][$k]['ARM_COLUMN_ID']."</ErrorRow>";
                  }
		$error_xml="<ErrorForm>$error_xml</ErrorForm>";
		}
	}//сохранить из формы
//======================================
//получение дерева навигации
/*$sel_nav="SELECT n.id, n.name, n.real_sort_order, n.tree_level, n.parent_id, n.template_id "
			." FROM navigation n LEFT  OUTER JOIN  cham_templates t ON n.template_id=t.id"
			 ." ORDER BY n.real_sort_order ASC";
			 */

global $main_xml;
$sel_nav="SELECT n.id, n.name, n.real_sort_order, n.tree_level, n.parent_id, n.template_id, t.path_href"
			." FROM navigation n LEFT  OUTER JOIN cham_templates t ON t.id=n.template_id "
			." WHERE real_".PUBLISH.PUBLISH_SQL
			 ." ORDER BY n.real_sort_order ASC";

//echo"sel_nav=$sel_nav<br>";
$res_nav=db_getArray($conn, $sel_nav);
//echo"<pre>";
//print_r($res_nav);
global $_SERVER;
global $_REQUEST;
global $_SESSION;
//echo $_SERVER['SCRIPT_NAME'];
if(ereg("tennis.html", $_SERVER['SCRIPT_NAME']))//заставка
	{
	global $_SESSION;
	if(session_is_registered("FIRST_FLASH"))
		header("Location:/index.html");
	else
		{
		//echo"SES_REG";
		$FIRST_FLASH=1;
		session_register("FIRST_FLASH");
		//exit;
		}
	}
if(!$nav_id)
	{
	$nav_id=$_REQUEST['nav_id'];
	}
//echo"nav_id=$nav_id, nav_id_ses=$nav_id_ses<br>";
if(!$nav_id)
	{
	if(ereg("phpBB", $_SERVER['SCRIPT_NAME']))
		{
		if($_SESSION['nav_id_ses']==ID_POST && ereg("index.php", $_SERVER['SCRIPT_NAME']))
			header("Location:/index.html?nav_id=".ID_POST);
		elseif($_SESSION['nav_id_ses']==ID_POST && ereg("profile.php", $_SERVER['SCRIPT_NAME']))
			$nav_id=ID_POST;
		else
			$nav_id=ID_FORUM;
		//echo"userid=$user_id(".$_COOKIE['user_id'].")<br>";
		}
	else
		$nav_id=$res_nav[0]['ID'];
	}
//echo"2 nav_id=$nav_id, nav_id_ses=$nav_id_ses<br>";
//echo $_SERVER['SCRIPT_NAME'].", nav_id=$nav_id<br>\r\n";
if(DEFINED("lang"))
	$main_xml.="<LANG>".lang."</LANG>";
if($nav_id)
	{
	$main_xml.="<NAV_ID>$nav_id</NAV_ID>";
	//echo"script - ".$_SERVER['SCRIPT_NAME']."<br>";
	if($nav_id==ID_FORUM || strpos($_SERVER['SCRIPT_NAME'], "hpBB")>0)
		$main_xml.="<FORUM_FLAG value=\"1\"/>";
	//получаем обратный путь
	$sel_nav_path="SELECT id_path, nav_text, keywords, description"
						." FROM navigation WHERE id=$nav_id AND real_".PUBLISH.PUBLISH_SQL;
	$res_nav_path=db_getArray($conn, $sel_nav_path, 2);
	if(count($res_nav_path)==0)
		{
		header("Location:/index.html");
		}
	else
		{
		$path_ar=explode(RAZD_NAV, $res_nav_path['ID_PATH']);
		$nav_text=$res_nav_path['NAV_TEXT'];
		//echo"<pre>";
		//print_r($path_ar);
		}
	set_ses_var("nav_id_ses", $nav_id);
	}
$main_xml.="<REQUEST>";
foreach($_REQUEST as $k_r=>$v_r)
	{
	if($k_r!="nav_id" && !$_SESSION[$k_r])
		{
		if(is_array($v_r))
			{
			$main_xml.="<$k_r>";
			foreach($v_r as $k_vr=>$v_vr)
				$main_xml.="<ITEM key=\"$k_vr\">".htmlspecialchars($v_vr)."</ITEM>";
			$main_xml.="</$k_r>";
			}
		else
			$main_xml.="<$k_r>".htmlspecialchars($v_r)."</$k_r>";
		}
	if($k_r=="phpbb_sid")// && !$_SESSION['user_id_forum_ses'])
		{
		$sel_user="SELECT s.session_user_id FROM phpbb_sessions s WHERE s.session_id = '".$v_r."'";
		$res_user=db_getArray($conn, $sel_user, 2);
		//$main_xml.="<USER_ID>".$res_user['SESSION_USER_ID']."</USER_ID>";
		set_ses_var("user_id_forum_ses", $res_user['SESSION_USER_ID']);
		}
	}
		
foreach($_SESSION as $k_r=>$v_r)
	{
	if($k_r!="nav_id")
		{
		if(is_array($v_r))
			{
			$main_xml.="<$k_r>";
			foreach($v_r as $k_vr=>$v_vr)
				$main_xml.="<ITEM key=\"$k_vr\">".htmlspecialchars($v_vr)."</ITEM>";
			$main_xml.="</$k_r>";
			}
		else
			$main_xml.="<$k_r>".htmlspecialchars($v_vr)."</$k_r>";

		}
	}
	
$main_xml.="</REQUEST>";
$main_xml.="<NOW_DATE><YEAR>".date("Y")."</YEAR><MONTH>".date("n")."</MONTH><DAY>".date("j")."</DAY></NOW_DATE>";
if(session_is_registered("vote_yes") && count($vote_yes))
	{
	$main_xml.="<VOTE_YES>";
	foreach($vote_yes as $v_v)
		{
		$main_xml.="<VOTE_ID>$v_v</VOTE_ID>";
		}
	$main_xml.="</VOTE_YES>";
	}
if($short)
	$main_xml.="<SHORT>$short</SHORT>";
else
	{
	$main_xml.="<NAVIGATION_LIST>";
	foreach($res_nav as $k=>$v)
		{
		if(!$v['PATH_HREF'])
			{
			$v['PATH_HREF']="/index.html";
			$res_nav[$k]['PATH_HREF']=$v['PATH_HREF'];
			}
		$nav_ar[$v['ID']]=$v;
		$main_xml.="<NAVIGATION>".ar_xml($v);
		if($v['TEMPLATE_ID'])
			{
			$href=$v['PATH_HREF']."?nav_id=".$v['ID'];
			$main_xml.="<LINK>".$href."</LINK>";
			}
		else
			{
			$href="";
			}
		$res_nav[$k]['HREF']=$href;
		if($v['ID']==ID_SEARCH)
			{
			$main_xml.="<SEARCH_ACTION>".$v['PATH_HREF']."</SEARCH_ACTION>";
			}
		elseif($v['ID']==ID_MAP)
			{
			$main_xml.="<MAP_LINK>".$href."</MAP_LINK>";
			}
		$main_xml.="</NAVIGATION>";
		}//foreach($res_nav as $k=>$v)
	$main_xml.="</NAVIGATION_LIST>";
	}
//reset($res_nav);
foreach($res_nav as $k=>$v)
	{
	//для обратного пути
	if($path_ar[$v['TREE_LEVEL']]==$v['ID'] || $nav_id==$v['ID'])
		{
		$path_nav_string.="<ITEM level=\"".$v['TREE_LEVEL']."\"><NAME>".htmlspecialchars($v['NAME'])."</NAME>";
		$href=$v['HREF'];
		if(!$href)
			{
			//echo"k=$k<br>";
			for($i=$k; !$href && $res_nav[$i]['ID']; $i++)
				{
				//echo"i=$i<br>";
				if($res_nav[$i]['TEMPLATE_ID'])
					$href=$res_nav[$i]['PATH_HREF']."?nav_id=".$res_nav[$i]['ID'];
				}
			}
		if($href)
			$path_nav_string.="<LINK>".$href."</LINK>";
		$path_nav_string.="</ITEM>";
		}
	}
$main_xml.="<NAV_PATH>".$path_nav_string."</NAV_PATH>";

if($nav_text)
	$main_xml.="<NAV_TEXT>".htmlspecialchars($nav_text)."</NAV_TEXT>";

if($res_nav_path['KEYWORDS'])
		$main_xml.="<KEYWORDS>".htmlspecialchars($res_nav_path['KEYWORDS'])."</KEYWORDS>";
if($res_nav_path['DESCRIPTION'])
		$main_xml.="<DESCRIPTION>".htmlspecialchars($res_nav_path['DESCRIPTION'])."</DESCRIPTION>";

$res_nav=array();
//echo"main_xml=$main_xml<br>";
//=====================================
if(!$short)
	{
	//получение баннеров
	$main_xml.="<BANNER_LIST>";
	$sel_ban_p="SELECT id FROM banner_places";
	$res_ban_p=db_getArray($conn, $sel_ban_p);
	foreach($res_ban_p as $k_bp=>$v_bp)
		{
		$sel_ban="SELECT id, name, href, banner_type_id, full_text, multimedia_id  FROM banners b "
			."WHERE banner_place_id=:banner_place AND publish_state_id".PUBLISH_SQL
					." AND EXISTS(SELECT banner_id FROM banner_navigation bn"
						." WHERE bn.banner_id=b.id AND bn.navigation_id=:nav_id) ";
		$bind_ar=array();
		$bind_ar[]=db_for_bind("banner_place", $v_bp['ID']);
		$bind_ar[]=db_for_bind("nav_id", $nav_id);
		$res_ban=array();
		$res_ban=db_getArray($conn, $sel_ban, 2, array("bind_ar"=>$bind_ar));
		//echo"<pre>res_ban";
		//print_r($res_ban);
		$banner_text="";
		$main_xml.="<BANNER_".$v_bp['ID'].">";
		$main_xml.=ar_xml($res_ban);
		if($res_ban['BANNER_TYPE_ID']==1 && $res_ban['MULTIMEDIA_ID'])//картинка
			{
			$image=image_info($conn, $res_ban['MULTIMEDIA_ID']);
			//echo"<pre>";
			//print_r($image);
			/*
			$banner_text="<a href=\"/click.html?id=".$res_ban['ID']."\"><img src=\"".IMAGE_LINK."?id=".$res_ban['MULTIMEDIA_ID']."\"/></a>";
			*/
			$main_xml.="<LINK>/click.html?id=".$res_ban['ID']."</LINK>";
			$main_xml.="<IMAGE_INFO>";
			$main_xml.="<WIDTH>".$image['width']."</WIDTH>";
			$main_xml.="<HEIGHT>".$image['height']."</HEIGHT>";
			$main_xml.="<IMAGE>".IMAGE_LINK."?id=".$res_ban['MULTIMEDIA_ID']."</IMAGE>";
			$main_xml.="</IMAGE_INFO>";
			}
		elseif($res_ban['BANNER_TYPE_ID']==2)//текст
			{
			//$banner_text=$res_ban['FULL_TEXT'];
			}
		elseif($res_ban['BANNER_TYPE_ID']==3 && $res_ban['MULTIMEDIA_ID'])//флеш
			{
			if(DEFINED("FLASH_PLAYER"))
				{
				$main_xml.="<FLASH_TEXT>".htmlspecialchars(ereg_replace("VAR_1", $res_ban['MULTIMEDIA_ID'], FLASH_PLAYER))."</FLASH_TEXT>";
				}
			}
		$main_xml.="</BANNER_".$v_bp['ID'].">";
		}
	$res_ban_p=array();
	$res_ban=array();
	$main_xml.="</BANNER_LIST>";
	}
if(DEFINED("CONTACT_TEXT"))
	$main_xml.="<CONTACT_TEXT>".htmlspecialchars(CONTACT_TEXT)."</CONTACT_TEXT>";
if(DEFINED("VERSION_MAIN"))
	$main_xml.="<VERSION_MAIN>".htmlspecialchars(VERSION_MAIN)."</VERSION_MAIN>";
if(!$layer_id && $layer_name)
	{
	$layer_name=strval($layer_name);
	$layer_sel="SELECT id FROM cham_layer WHERE name='$layer_name'";
	//echo"layer_sel=$layer_sel<br>";
	$layer_res=db_getArray($conn, $layer_sel, 2);
	$layer_id=$layer_res['ID'];
	//echo"layer_id=$layer_id<br>";
	}
if(!$layer_id && $nav_id)
	{
	$layer_sel="SELECT layer_id FROM cham_templates WHERE id=".$nav_ar[$nav_id]['TEMPLATE_ID'];
	//echo __LINE__." layer_sel=$layer_sel<br>";
	$layer_res=db_getArray($conn, $layer_sel, 2);
	$layer_id=$layer_res['LAYER_ID'];
	//echo __LINE__." layer_id=$layer_id<br>";
	}
if($error_xml)
	$main_xml.=$error_xml;
//словари
if(lang=="eng")
	$lang_ar=file(TPL_PATH."english.xml");
else
	$lang_ar=file(TPL_PATH."russian.xml");
if(count($lang_ar))
	{
	$main_xml.="<DICT>".implode("", $lang_ar)."</DICT>";
	}
/*if($our_const_xml)
	$main_xml.="<OUR_CONST>".$our_const_xml."</OUR_CONST>";
	*/
}//DEFINED("IMAGE_FLAG");
?>
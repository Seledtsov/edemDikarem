<?
//рассылка с использованием layoutов
define("NO_SES", 1);
DEFINE("NO_AUTH", 1);

include(getenv("g_INC")."conf.php");

echo"\r\n\r\nSTART $SCRIPT_NAME - ".date("d-m-Y H:i:s")."\r\n";
echo"g_INC=".getenv("g_INC")."\r\n";
ignore_user_abort(true);
include(PATH_INC."inc.php");
include_once(PATH_INC."func/func_layout_xml.php");
include_once(PATH_INC."func/post_class.php");
echo"<pre>";
//получаем рассылки, которые сейчас должны пройти
$sel_post="SELECT p.*,pp.value FROM post_text p, post_periods pp WHERE pp.id=p.post_period_id AND p.status=1 AND p.users_groups_id IS NOT NULL AND (p.post_period_id=1 OR (p.post_period_id=2 AND pp.value=".date("H")." AND (now()-interval '24 hour')<p.date_main) OR (p.post_period_id=3 AND pp.value=".date("w")." AND (now()-interval '168 hour')<p.date_main));";
echo "sel_post={$sel_post}\r\n";
$res_post=db_getArray($conn, $sel_post);
$emailar = array();
echo '\r\n\r\n';
//print_r($res_post);
//получаем очкркдь из писем
foreach($res_post as $k=>$v)
{
	//db_begin($conn);
	echo"POST - ".$v['ID']."\r\n";
	$message= $v['INTRO'].'<br/>'.$v['BODY'].'<br/>'.$v['FINISH'];
	$message = str_replace(array("&lt;", "&gt;", '&amp;', '&#039;', '&quot;','&lt;', '&gt;'), array("<", ">",'&','\'','"','<','>'), htmlspecialchars_decode($message, ENT_NOQUOTES));

	echo"message=$message\r\n";

	$ins_mes=db_insert($conn, "post_messages", array("name", "date_main", "post_id", "full_text"), array($v['SUBJECT'], db_sysdate(), $v['ID'], $message));
	//$ins_mes=1;
	echo"MESSAGE_ID=$ins_mes\r\n";
	if($ins_mes)
	{
		//выбираем подписчиков
		if(!is_array($emailar[$v['USERS_GROUPS_ID']])){
			$sel_user = "select u.email from ti_users u, ti_users_groups g where u.id=g.user_id and g.group_id=".$v['USERS_GROUPS_ID'];
			$res_user=db_getArray($conn, $sel_user);
			$emailar[$v['USERS_GROUPS_ID']]  = $res_user;
			unset($res_user);
		}
		if(count($emailar[$v['USERS_GROUPS_ID']])>0){
			$mail=new html_mime_mail();
			$mail->add_html($message);
			$mail->build_message('win');
			foreach($emailar[$v['USERS_GROUPS_ID']] as $v_user)
			{
				//print_r($v_user);
				if(defined("TEST_SERVER_FLAG"))
					$to_mail="marian@oooinex.ru";
				else
					$to_mail=$v_user['EMAIL'];
				if($to_mail){
					$ret=$mail->send(POST_SERVER, $to_mail, MAIL_POST_FROM, $v['SUBJECT']);
					echo"Email-".$to_mail." - $ret\r\n";
				}
			}
		}
		echo $upd=db_update($conn, "post_text", array("status"), array(0), array("int"), "id={$v['ID']}");
	}
	echo"end POST - ".$v['ID']."\r\n\r\n";
}
echo"\r\nEND ".g_URL." - ".date("d-m-Y H:i:s")."\r\n\r\n\r\n";
?>
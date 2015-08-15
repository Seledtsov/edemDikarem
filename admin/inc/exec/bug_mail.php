<?
//отправка писем об ошибках
define("NO_SES", 1);
DEFINE("NO_AUTH", 1);

include(getenv("g_INC")."conf.php");
echo"g_INC=".getenv("g_INC")."\r\n";
ignore_user_abort(true);
$MAX_LIMIT_TIME=86400;
set_time_limit($MAX_LIMIT_TIME);
echo"<pre>";
//$argv = $_SERVER["argv"];
echo"\r\n\r\nSTART $SCRIPT_NAME - ".date("d-m-Y H:i:s")."\r\n";
$start_time=mktime();
include(PATH_INC."inc.php");
include_once(PATH_INC."func/post_class.php");
include_once(PATH_INC."func/func_layout_xml.php");

$sel_layer="SELECT id, name FROM cham_layer WHERE name IN('BugChange', 'BugComment', 'BugNew')";
$res_layer=db_getArray($conn, $sel_layer);
foreach($res_layer as $k=>$v)
	{
	$layer_ar[$v['NAME']]=$v['ID'];
	echo"id=".$v['ID'].", ".$v['NAME']."<br>";
	}

db_disconnect($conn);

function send_mail($conn, $group_id, $message, $subject)
	{
	if($message)
		{
		$sel_mail="SELECT distinct u.email FROM ti_users u, ti_users_groups ug, ti_groups g WHERE (ug.group_id=g.id OR ug.group_id=g.manager_group_id) AND g.id=$group_id AND ug.user_id=u.id AND u.email IS NOT NULL";
		echo"sel_mail=$sel_mail<br>";
		$res_mail=db_getArray($conn, $sel_mail);
		$mail=new html_mime_mail();
		$mail->add_html($message);
		$mail->build_message('win'); // если не "win", то кодиpовка koi8
		echo"$subject<br>$message<br>";
		foreach($res_mail as $k=>$v)
			{
			
			/*$ret=$mail->send(POST_SERVER,
						$v['EMAIL'],
						MAIL_POST_FROM,
						$subject);
						*/
			echo"mail=".$v['EMAIL'].", subject=$subject<br>";
			}
		}
	}



while($MAX_LIMIT_TIME>(mktime()-$start_time + 600))
	{
	$conn=db_connect(DB, DB_USER, DB_PASSWD, DB_HOST);
	//новые ошибки
	$sel_bug_q="SELECT id, name, group_id FROM bugs WHERE send_flag=0";
	$sel_bug=db_getArray($conn, $sel_bug_q);
	//print_r($sel_bug);
	$layer_id=$layer_ar['BugNew'];
	$layer_xsl=layer_xsl($conn, $layer_id);
	//echo"layer_id=$layer_id, layer_xsl=$layer_xsl<br>";
	foreach($sel_bug as $k=>$v)
		{
		$subject="Новая ошибка(".$v['ID']."): ".$v['NAME'];
		$upd=db_update($conn, "bugs", array("send_flag"), array(1), array("int"), "id=".$v['ID']);
		$layer_xml=layer_xml($conn, $layer_id, array("id"=>$v['ID']));
		$xml="<ROOT>".$layer_xml."</ROOT>";
		$message=xml_to_html($xml, $layer_xsl, $SERVER_NAME);
		//echo"message=$message<br>";
		send_mail($conn, $v['GROUP_ID'], $message, $subject);
		}

	//новые комментарии
	$sel_bug_q="SELECT bs.bug_id, b.name, bs.id, b.group_id FROM bugs b, bug_comments bs WHERE bs.send_flag=0 AND bs.bug_id=b.id";
	$sel_bug=db_getArray($conn, $sel_bug_q);
	$layer_id=$layer_ar['BugComment'];
	$layer_xsl=layer_xsl($conn, $layer_id);
	foreach($sel_bug as $k=>$v)
		{
		$subject="Изменения(".$v['BUG_ID']."): ".$v['NAME'];
		$layer_xml=layer_xml($conn, $layer_id, array("comment_id"=>$v['ID']));
		$xml="<ROOT>".$layer_xml."</ROOT>";
		$message=xml_to_html($xml, $layer_xsl, $SERVER_NAME);
		$upd=db_update($conn, "bug_comments", array("send_flag"), array(1), array("int"), "id=".$v['ID']);
		send_mail($conn, $v['GROUP_ID'], $message, $subject);
		}


	//изменения
	$sel_bug_q="SELECT bl.bug_id, b.name, bl.id, b.group_id FROM bugs b, bug_log bl WHERE bl.send_flag=0 AND bl.bug_id=b.id";
	$sel_bug=db_getArray($conn, $sel_bug_q);
	$layer_id=$layer_ar['BugChange'];
	$layer_xsl=layer_xsl($conn, $layer_id);
	foreach($sel_bug as $k=>$v)
		{
		$subject="Изменения(".$v['BUG_ID']."): ".$v['NAME'];
		$layer_xml=layer_xml($conn, $layer_id, array("id"=>$v['ID']));
		$xml="<ROOT>".$layer_xml."</ROOT>";
		echo"<br><br>xml=$xml<br><br>";
		$message=xml_to_html($xml, $layer_xsl, $SERVER_NAME);
		$upd=db_update($conn, "bug_log", array("send_flag"), array(1), array("int"), "id=".$v['ID']);
		send_mail($conn, $v['GROUP_ID'], $message, $subject);
		}

	db_disconnect($conn);
	sleep(600);
	if(eregi("loc.", $SERVER_NAME))
		{
		echo"server_name=$SERVER_NAME<br>";
		break;
		}
	}

?>
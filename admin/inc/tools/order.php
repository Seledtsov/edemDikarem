<?	$user_id=$_SESSION["user_id_ses"];
	$groups = explode(", ",$_SESSION["group_id_ses"]);
	$sel1 = "select *from dictionary_lang_v where name like 'message_%' or name like '%order%'";
	$dict = array();
	$dict_res = db_getArray($conn, $sel1, 1);
	foreach($dict_res as $val){
		$dict[$val["NAME"]] = $val["TEXT"];
	}
	if($user_id){
		if(in_array(14,$groups)){
			//echo mail("marian@oooinex.ru","test","test message",'From:marian@oooinex.ru');
			include_once(PATH_INC."func/post_class.php");
			include_once(PATH_INC."func/func_layout_xml.php");
			global $table_name, $column_name;
			$table_name_m=MULT_TAB;
			$column_name_m=MULT_COL;
			$message=$body1 = $body11 =$body12 = $body2 = "";
			$cid = array();
			//echo $ids;
			if($ids){
				//Формируем список
				$file_info_sql = "select c.*,m.name,m.".$column_name_m."_name, a.name as aname,a.surname from cart c
				left join multimedia_v m on (c.multimedia_id=m.id) join authors_v a on (m.author_id=a.id)
				where c.user_id=$user_id and c.id in($ids) and (c.is_ph is null or c.is_ph!=1)";
				$res=db_getArray($conn, $file_info_sql, 1);
				//print_r($res);
				foreach($res as $val){
					$body1.='<a href="http://'.$SERVER_NAME.'/download.html?s=9&id='.$val["MULTIMEDIA_ID"].'" title="'.$val["MFILE_NAME"].'">'.$val["NAME"].'</a>, '.$val["ANAME"].' '.$val["SURNAME"].' <br/>';
					$cid[]  = $val["ID"];
				}

				$file_info_sql = "select c.*,m.name, a.name as aname,a.surname from cart c
				left join mrubrikator_v m on (c.mrubrikator_id=m.id) join authors_v a on (m.author_id=a.id)
				where c.user_id=$user_id and c.id in($ids) and c.is_ph=1";
				$res=db_getArray($conn, $file_info_sql, 1);//print_r($res);
				foreach($res as $val){
					$body2.='№ '.$val["MRUBRIKATOR_ID"].'  - '.$val["NAME"].', '.$val["ANAME"].' '.$val["SURNAME"].'<br/>';
					$cid[]  = $val["ID"];
				}
				//echo $body1.' '.$body2;
				if(count($cid)>0){
				$update_sql = "update cart set  posted=1,date_edit=now() where id in (".implode(", ",$cid).")";
				$ins_q = db_query($conn, $update_sql);
				}

				unset($file_info_sql);
				$sel1 = "select *from dictionary_lang_v where name like 'order%'";
				$dict_res = db_getArray($conn, $sel1, 1);
				foreach($dict_res as $val){
					$dict[$val["NAME"]] = $val["TEXT"];
				}
				$sel3 = "select *from ti_users where id=".$user_id;
				$user_res = db_getArray($conn, $sel3, 2);
				$user = $user_res["FAMILY"].' '.$user_res["FIRST_NAME"].' '.$user_res["SECOND_NAME"];
				$sel2 = "select u.email from ti_users u, ti_users_groups g where u.id=g.user_id and g.group_id=15";
				$emailar =db_getArray($conn, $sel2, 1);
				//$emailar  = implode(',',$emailar);
				//print_r($dict);
				//$dict["order_photo_mail"].' '.$dict["order_photo2_mail"];
				if($body1){
					$message.=$dict["order_photo_message"];
					$body11 = str_replace("#PHOTO_LIST#", $body1,$dict["order_photo_mail"]);
					$body11 = str_replace('#USER_NAME#',$user,$body11);

					//Письмо  клиенту
					$mail=new html_mime_mail();
					$mail->add_html($body11);
					$mail->build_message('win');
					$ret=$mail->send(POST_SERVER, $user_res["EMAIL"],MAIL_POST_FROM, ' '.$dict["order_photo_subject"]);
					//Письмо  менеджерам
					$user1 = "№".$user_res["ID"].' '.$user.', '.$user_res["COMPANY"];
					$body12 = str_replace("#PHOTO_LIST#", $body1,$dict["order_photo2_mail"]);
					$body12 = str_replace('#USER_NAME#',$user1,$body12);
					$body12 =  str_replace('&amp;',"&",$body12);
					unset($mail);
					$mail=new html_mime_mail();
					$mail->add_html($body12);
					$mail->build_message('win');
					foreach($emailar as $key=>$val){
						$ret=$mail->send(POST_SERVER, $val["EMAIL"],MAIL_POST_FROM, ' '.$dict["order_photo_subject"]);
					}
				}

				if($body2){
					$message.=$dict["order_history_message"];
					$user2 = "№".$user_res["ID"].' '.$user.', '.$user_res["COMPANY"];
					$body2 = str_replace("#PHOTOHISTORY_LIST#",$body2,$dict["order_history_mail"]);
					$body2 = str_replace("#USER_NAME#",$user2,$body2);
					$body2 =  str_replace('&amp;',"&",$body2);
					//Письмо  менеджерам
					$mail=new html_mime_mail();
					$mail->add_html($body2);
					$mail->build_message('win');
					foreach($emailar as $key=>$val){
					$ret=$mail->send(POST_SERVER, $val["EMAIL"],MAIL_POST_FROM, ' '.$dict["order_history_subject"]);
					}
				}
				//Формируем XML
				$os = "select o.id as order_id,  to_char(o.date_edit, 'ddmmyyyy') as date, o.date_edit AS ORDER_DATE, o.is_ph,
				 o.multimedia_id as photo_id, p.name as photo_name, o.mrubrikator_id as photostory_id, ph.name as photostory_name,ph.sold as photostory_sold,
				 o.user_id as client_id, c.family as client_family,c.first_name as client_name,c.second_name as client_second_name, c.name as client_login, c.email as client_email, c.company as client_company, c.edition as client_edition, c.inn as client_inn, c.kpp as client_kpp, c.phone as client_phone, c.country as client_country, c.city as client_city,
				 a.name_ru as author_name, a.surname_ru as author_surname, a.name_en as author_name_en, a.surname_en as author_surname_en, a.id as author_id
				from cart o
				join multimedia p on(o.multimedia_id = p.id)
				join mrubrikator ph on(o.mrubrikator_id = ph.id)
				join ti_users c on(o.user_id = c.id)
				join authors a on(p.author_id = a.id)
				where o.id in($ids) and (o.posted>0 or o.downloads>0)";
				$res=db_getArray($conn, $os, 1);
				foreach($res as $val){
					//print_r($val);
					$file = 'order/'.$val["ORDER_ID"].'_'.$val["DATE"].'.xml';
					//unlink($file);
					if(!file_exists($file)){
						//unlink($file);}else{

	$xml_data = "<CLIENT>
		<CLIENT_ID>{$val['CLIENT_ID']}</CLIENT_ID>
		<CLIENT_FAMILY>{$val['CLIENT_FAMILY']}</CLIENT_FAMILY>
		<CLIENT_NAME>{$val['CLIENT_NAME']}</CLIENT_NAME>
		<CLIENT_SECOND_NAME>{$val['CLIENT_SECOND_NAME']}</CLIENT_SECOND_NAME>
		<CLIENT_LOGIN>{$val['CLIENT_LOGIN']}</CLIENT_LOGIN>
		<CLIENT_EMAIL>{$val['CLIENT_EMAIL']}</CLIENT_EMAIL>
		<CLIENT_PHONE>{$val['CLIENT_PHONE']}</CLIENT_PHONE>
		<CLIENT_COMPANY>{$val['CLIENT_COMPANY']}</CLIENT_COMPANY>
		<CLIENT_EDITION>{$val['CLIENT_EDITION']}</CLIENT_EDITION>
		<CLIENT_INN>{$val['CLIENT_INN']}</CLIENT_INN>
		<CLIENT_KPP>{$val['CLIENT_KPP']}</CLIENT_KPP>
		<CLIENT_COUNTRY>{$val['CLIENT_COUNTRY']}</CLIENT_COUNTRY>
		<CLIENT_CITI>{$val['CLIENT_CITI']}</CLIENT_CITI>
	</CLIENT>
	";
	$xml_data .= "<ORDER>
		<ORDER_ID>{$val['ORDER_ID']}</ORDER_ID>
		<ORDER_DATE>{$val["ORDER_DATE"]}</ORDER_DATE>
	</ORDER>
	";
	$xml_data .= "<AUTHOR>
		<AUTHOR_ID>{$val['AUTHOR_ID']}</AUTHOR_ID>
		<AUTHOR_NAME>{$val["AUTHOR_NAME"]}</AUTHOR_NAME>
		<AUTHOR_SURNAME>{$val["AUTHOR_SURNAME"]}</AUTHOR_SURNAME>
		<AUTHOR_NAME_EN>{$val["AUTHOR_NAME_EN"]}</AUTHOR_NAME_EN>
		<AUTHOR_SURNAME_EN>{$val["AUTHOR_SURNAME_EN"]}</AUTHOR_SURNAME_EN>
	</AUTHOR>
	";
	if($val["IS_PH"]==1){
	$xml_data .= "<PHOTOSTORY>
		<PHOTOSTORY_ID>{$val['PHOTOSTORY_ID']}</PHOTOSTORY_ID>
		<PHOTOSTORY_NAME>{$val["PHOTOSTORY_NAME"]}</PHOTOSTORY_NAME>
		<PHOTOSTORY_SOLD>{$val["PHOTOSTORY_SOLD"]}</PHOTOSTORY_SOLD>
	</PHOTOSTORY>
	";
	}else{
	$xml_data .= "<PHOTO>
		<PHOTOSTORY_ID>{$val['PHOTOSTORY_ID']}</PHOTOSTORY_ID>
		<PHOTOSTORY_NAME>{$val["PHOTOSTORY_NAME"]}</PHOTOSTORY_NAME>
		<PHOTO_ID>{$val['PHOTO_ID']}</PHOTO_ID>
		<PHOTO_NAME>{$val["PHOTO_NAME"]}</PHOTO_NAME>
		<PHOTOSTORY_SOLD>{$val["PHOTOSTORY_SOLD"]}</PHOTOSTORY_SOLD>
	</PHOTO>";
	}
	$xml_data = "<ROOT>
".$xml_data."
</ROOT>";
					$xml_data=str_replace('&amp;', '&', $xml_data);
					//$xml_data=str_replace(chr(185), '&amp;#8470;', $xml_data);//№
					//$xml_data=str_replace(chr(150), '&amp;ndash;', $xml_data);//длинное тире
					//$xml_data=str_replace(chr(133), '&amp;hellip;', $xml_data);//многоточие
					$xml_data= xml_header().$xml_data;
					$xml_data = iconv("CP1251", "UTF-8", $xml_data);
					$fp = fopen($file, 'w');
					fwrite($fp, $xml_data);
					fclose($fp);
				}
				}
				//xml сформирован
				if(!$message){
					print_r($cid);
				}
				echo $message;
			}else{
				echo $dict["message_not_selected"].'<br/>';
			}
		}else{
			echo $dict["message_no_rights"].'<br/>';
		}
	}else{
		echo $dict["message_not_logged"].'<br/>';
	}


?>

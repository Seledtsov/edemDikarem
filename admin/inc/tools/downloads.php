<?
	$user_id=$_SESSION["user_id_ses"];
	$groups = explode(", ",$_SESSION["group_id_ses"]);
    $sel1 = "select *from dictionary_lang_v where (name like 'message_%') or (name like 'order%')";
    $dict = array();
    $dict_res = db_getArray($conn, $sel1, 1);
    foreach($dict_res as $val){
        $dict[$val["NAME"]] = $val["TEXT"];
    }
	if($user_id){
		$bind_ar = array();
        if(in_array(14,$groups)){
		global $table_name, $column_name;
		$table_name=MULT_TAB;
		$column_name=MULT_COL;
		$file_info_sql = "
		select
		ft.name as ".$column_name."_type,
		tn.ID,
		tn.".$column_name."_name,
		tn.$column_name,
		tn.".$column_name."_size,
		tn.".$column_name."_width,
		tn.".$column_name."_height,
		tn.".$column_name."_path,
		tn.MRUBRIKATOR_ID, tn.IN_HIRES
		from
		$table_name tn,
		".TABLE_PRE."file_types ft
		where
		tn.id = $id and
		ft.id = tn.".$column_name."_type_id";
		//echo"file_info_sql=$file_info_sql<br>";
		$bind_ar[]=db_for_bind("id", $id);
		$res=db_getArray($conn, $file_info_sql, 2, array("bind_ar"=>$bind_ar));
		//$user_id=28;
		$cart_flag=false;
		//if($user_id){
		if(count($res)>0 && $res['IN_HIRES']){
			$file_info_sql2 = "select * from cart c where  c.multimedia_id=".$id." and c.user_id = ".$user_id."";
			$res2=db_getArray($conn, $file_info_sql2, 2);
			$IDC=$res2["ID"];
			if(count($res2)>0){
				$cart_flag=true;
			}else{
				$table_name = "cart";

				$columns_list = "multimedia_id, mrubrikator_id,user_id,downloads,date_main,date_edit,posted";
				$val_list = "".$id.",".($res['MRUBRIKATOR_ID']?$res['MRUBRIKATOR_ID']:"NULL").",".$user_id.",1,now(),now(),1";
				$insert_sql = "insert into $table_name ($columns_list) values ($val_list)";
				$ins_q = db_query($conn, $insert_sql);
				if (!$ins_q)
				{
					//echo 'Не удалось получить фотографию '.$res['NAME'].'.';
				}else{
                    $cart_flag=true;
				}
                    //$file_info_sql2 = "select * from cart c where  c.multimedia_id=".$id." and c.user_id = ".$user_id."";
			}
				$file_info_sql2 = "select c.*,m.name,m.".$column_name."_name, a.name as aname,a.surname from cart c
                join multimedia_v m on (c.multimedia_id=m.id) join authors_v a on (m.author_id=a.id)
                where c.user_id=$user_id and c.multimedia_id=".$id." and (c.is_ph is null or c.is_ph!=1)";
                $res2=db_getArray($conn, $file_info_sql2, 2);
                $IDC=$res2["ID"];
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
				where o.id in($IDC) and (o.posted>0 or o.downloads>0)";
				$res0=db_getArray($conn, $os, 1);
				if(count($res0)>0){
				foreach($res0 as $val){
					$file = 'order/'.$val["ORDER_ID"].'_'.$val["DATE"].'.xml';
					//unlink($file);
					if(!file_exists($file)){

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
	                include_once(PATH_INC."func/func_layout_xml.php");
					$xml_data=str_replace(chr(185), '&amp;#8470;', $xml_data);//№
					$xml_data=str_replace(chr(150), '&amp;ndash;', $xml_data);//длинное тире
					$xml_data=str_replace(chr(133), '&amp;hellip;', $xml_data);//многоточие
					$xml_data=str_replace('&amp;', '&', $xml_data);
					$xml_data= xml_header().$xml_data;
					$xml_data = iconv("CP1251", "UTF-8", $xml_data);
					$fp = fopen($file, 'w');
					fwrite($fp, $xml_data);
					fclose($fp);
				}
				}
				//xml сформирован
				}


                include_once(PATH_INC."func/post_class.php");
				$message=$body1 = $body11 =$body12 = $body2 = "";
                $sel3 = "select *from ti_users where id=".$user_id;
				$user_res = db_getArray($conn, $sel3, 2);
				$user = $user_res["FAMILY"].' '.$user_res["FIRST_NAME"].' '.$user_res["SECOND_NAME"];
				$sel2 = "select u.email from ti_users u, ti_users_groups g where u.id=g.user_id and g.group_id=15";
				$emailar =db_getArray($conn, $sel2, 1);
				$body1.='<a href="http://'.$SERVER_NAME.'/download.html?s=9&id='.$res2["MULTIMEDIA_ID"].'" title="'.$res2["MFILE_NAME"].'">'.$res2["NAME"].'</a>, '.$res2["ANAME"].' '.$res2["SURNAME"].' <br/>';

				if($body1){
					$body11 = str_replace("#PHOTO_LIST#", $body1,$dict["order_photo_mail"]);
					$body11 = str_replace('#USER_NAME#',$user,$body11);
					$body11=str_replace('&amp;', '&', $body11);
					//Письмо  клиенту
					$mail=new html_mime_mail();
					$mail->add_html($body11);
					$mail->build_message('win');
					$ret=$mail->send(POST_SERVER, $user_res["EMAIL"],MAIL_POST_FROM, ' '.$dict["order_photo_subject"]);
					$message.=$dict["order_photo_message"];
					//Письмо  менеджерам
					$user1 = "№".$user_res["ID"].' '.$user.', '.$user_res["COMPANY"];
					$body12 = str_replace("#PHOTO_LIST#", $body1,$dict["order_photo2_mail"]);
					$body12 = str_replace('#USER_NAME#',$user1,$body12);
					unset($mail);
					$mail=new html_mime_mail();
					$mail->add_html($body12);
					$mail->build_message('win');
					foreach($emailar as $key=>$val){
						$ret=$mail->send(POST_SERVER, $val["EMAIL"],MAIL_POST_FROM, ' '.$dict["order_photo_subject"]);
					}
				}

			if($cart_flag){
					$file_type = $res[strtoupper($column_name."_type")];
					$file_size = $res[strtoupper($column_name."_size")];
					$lo_oid = $res[strtoupper($column_name)];
					if($lo_oid==NULL){
						$file_path = str_ireplace('/db/src/','/store/hires/',$res[strtoupper($column_name."_path")]);
						$file_paths = explode("/", $file_path);
						$file_paths[3] = 'paNo0_';
						$file_path2 = implode("/", $file_paths);
						if(file_exists($file_path2)){
							$file_img = file_get_contents($file_path);
						}elseif(file_exists($file_path)){
							$file_img = file_get_contents($file_path);
						}
					}else{
						$file_img = db_get_file_data($conn, $lo_oid, $file_size);
					}

					$file=$res[strtoupper($column_name."_name")];
					$file=str_replace(array(" ", "(", ")", "[", "]", "[", "]", "-", "\"", "'"), "_", $file);
					header("Content-type: $file_type");
					header("Content-Disposition: attachment;filename=$file");
					header("Content-Transfer-Encoding: binary");
					header('Pragma: no-cache');
					header('Expires: 0');
					set_time_limit(0);
					echo $file_img;
					set_time_limit(30);
					$update_sql = "update cart set  downloads=downloads+1, posted=1, date_edit=now() where id=".$IDC."";
					$ins_q = db_query($conn, $update_sql);
			}else{
				echo '"'.$res['NAME'].'": '.$dict["message_cart_add_error"].'<br/>';
			}
		}else{
			echo '"'.$res['NAME'].'": '.$dict["message_no_hires"].'<br/>';
		}
	}else{
            echo $dict["message_no_rights"].'<br/>';
        }
    }else{
        echo $dict["message_not_logged"].'<br/>';
    }

?>
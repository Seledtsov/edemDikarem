<?	$user_id=$_SESSION["user_id_ses"];
	$groups = explode(", ",$_SESSION["group_id_ses"]);
		$sel1 = "select *from dictionary_lang_v where name like 'message_%'";
		$dict = array();
		$dict_res = db_getArray($conn, $sel1, 1);
		foreach($dict_res as $val){
			$dict[$val["NAME"]] = $val["TEXT"];
		}
	echo '<div width="500">';
	if($user_id){
		if(in_array(14,$groups)){

			if($ids){
				$file_info_sql = "select l.id from lightbox l where l.id in($ids)";
				$res=db_getArray($conn, $file_info_sql, 1);
				//print_r($res);
				$id_ar=array();
				foreach($res as $val){
					$id_ar[] = $val["ID"];
				}
				if(count($id_ar)>0){
					if(count(explode(", ",$ids))>count($id_ar)){
						echo  $dict["message_photo_not_found"];
					}
					//print_r($id_ar);
					$del = 'delete from lightbox where id in ('.implode(", ",$id_ar).')';
					$ins_q = db_query($conn, $del);
					if (!$ins_q)
					{
						echo $dict["message_not_delete_photos"].'<br/>';
					}else{
						echo  $dict["message_photo_removed"].'<br/>';
					}
					//удаляем пустые лайтбоксы
					$file_info_sql = "select lu.id, count(l.id) as cnt from lightbox_user lu left join lightbox l on (l.lightbox_user_id=lu.id) where lu.user_id=$user_id group by lu.id";
					$res=db_getArray($conn, $file_info_sql, 1);
					$id_ar=array();
					foreach($res as $val){
						if($val["CNT"]==0)
						$id_ar[] = $val["ID"];
					}
					if(count($id_ar)>0){
						$del = 'delete from lightbox_user where id in ('.implode(", ",$id_ar).')';
						$ins_q = db_query($conn, $del);
						if (!$ins_q)
						{
							echo $dict["message_not_delete_lightbox"].'<br/>';
						}else{
							echo  $dict["message_lightbox_removed"].'<br/>';
						}
					}

				}else echo $dict["message_photo_not_found"].'<br/>';

			}elseif($lid){
				$file_info_sql = "select l.id from lightbox l where l.lightbox_user_id=$lid";
				$res=db_getArray($conn, $file_info_sql, 1);
				//print_r($res);
				$id_ar=array();
				foreach($res as $val){
					$id_ar[] = $val["ID"];
				}
				if(count($id_ar)>0){
					$del = 'delete from lightbox where id in ('.implode(", ",$id_ar).')';
					$ins_q = db_query($conn, $del);
				}
				$del = 'delete from lightbox_user where id='.$lid.'';
				$ins_q = db_query($conn, $del);
				if (!$ins_q)
				{
					echo $dict["message_not_delete_lightbox"].'<br/>';
				}else{
					echo  $dict["message_lightbox_removed"].'<br/>';
				}
			}else{
				echo $dict["message_not_selected"].'<br/>';
			}
		}else{
			echo $dict["message_no_rights"].'<br/>';
		}
	}else{
		echo $dict["message_not_logged"].'<br/>';
	}
	echo '</div>';
?>

<?	$user_id=$_SESSION["user_id_ses"];
	$groups = explode(", ",$_SESSION["group_id_ses"]);
		$sel1 = "select *from dictionary_lang_v where name like 'message_%' or (name like 'message_no%')";
		$dict = array();
		$dict_res = db_getArray($conn, $sel1, 1);
		foreach($dict_res as $val){
			$dict[$val["NAME"]] = $val["TEXT"];
		}
	if($user_id){
		if(in_array(14,$groups)){			
			if($ids){
				$file_info_sql = "select l.id from cart l where l.id in($ids) and downloads=0 and posted=0";
				$res=db_getArray($conn, $file_info_sql, 1);
				//print_r($res);
				$id_ar=array();
				foreach($res as $val){
					$id_ar[] = $val["ID"];
				}
				if(count($id_ar)>0){
					if(count(explode(", ",$ids))>count($id_ar)){
						echo  $dict["message_cart_not_found"];
					}
					//print_r($id_ar);
					$del = 'delete from cart where id in ('.implode(", ",$id_ar).')';
					$ins_q = db_query($conn, $del);
					if (!$ins_q)
					{
						echo $dict["message_not_delete_cart"].'<br/>';
					}else{
						echo  $dict["message_cart_removed"].'<br/>';
					}				

				}else echo $dict["message_cart_not_found"].'<br/>';

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

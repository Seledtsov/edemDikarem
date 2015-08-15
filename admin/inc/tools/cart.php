<?	$user_id=$_SESSION["user_id_ses"];
	$groups = explode(", ",$_SESSION["group_id_ses"]);
	$sel1 = "select *from dictionary_lang_v where name like 'message_%'";
	$dict = array();
	$dict_res = db_getArray($conn, $sel1, 1);
	foreach($dict_res as $val){
		$dict[$val["NAME"]] = $val["TEXT"];
	} //print_r($dict) ;
	echo '<div  style="width: 500px;">';
	if($user_id){
		if(in_array(14,$groups)){

			global $table_name, $column_name;
			$table_name_m=MULT_TAB;
			$column_name_m=MULT_COL;
			if($ids){
				foreach(explode(",",$ids) as $id){
					$file_info_sql = "select ft.name as ".$column_name_m."_type, tn.ID, tn.name, tn.".$column_name_m."_name, tn.MRUBRIKATOR_ID, tn.IN_HIRES
					from $table_name_m tn, ".TABLE_PRE."file_types ft where tn.id = ".$id." and ft.id = tn.".$column_name_m."_type_id";
					//echo "file_info_sql=$file_info_sql<br>";
					$res=db_getArray($conn, $file_info_sql, 2);
					//print_r($res);
					if(count($res)>0){$table_name = "cart";
						$file_info_sql2 = "select * from $table_name c where  c.multimedia_id=".$id." and c.user_id = ".$user_id."";
						$res2=db_getArray($conn, $file_info_sql2, 2);
						if(count($res2)>0){
							echo '"'.$res['NAME'].'": '.$dict["message_cart_exist"].'<br/>';
						}else{
							$columns_list = "multimedia_id, mrubrikator_id,user_id,downloads,date_main,date_edit";
							$val_list = "".$id.",".($res['MRUBRIKATOR_ID']?$res['MRUBRIKATOR_ID']:"NULL").",".$user_id.",0,now(),now()";
							$insert_sql = "insert into $table_name ($columns_list) values ($val_list)";
							$ins_q = db_query($conn, $insert_sql);
							if (!$ins_q)
							{
								echo '"'.$res['NAME'].'": '.$dict["message_cart_add_error"].'<br/>';
							}else{
								echo '"'.$res['NAME'].'": '.$dict["message_cart_add"].'<br/>';
							}
						}
					}else{
						echo '"¹'.$id.'": '.$dict["message_cart_not_exist"].'<br/>';
					}
				}
			}elseif($phs){
				foreach(explode(",",$phs) as $id){
					$file_info_sql = "select tn.ID, tn.name, tn.multimedia_id from mrubrikator tn where tn.id = ".$id." and tn.publish_state_id=2";
					//echo "file_info_sql=$file_info_sql<br>";
					$res=db_getArray($conn, $file_info_sql, 2);
					//print_r($res);
					if(count($res)>0){
						$table_name = "cart";
						$file_info_sql2 = "select * from $table_name c where  c.multimedia_id=".($res['MULTIMEDIA_ID']?$res['MULTIMEDIA_ID']:"NULL")." and c.mrubrikator_id=$id and c.user_id = ".$user_id." and c.is_ph=1";
						$res2=db_getArray($conn, $file_info_sql2, 2);
						//print_r($res2);
						if(count($res2)>0){
							echo '"'.$res['NAME'].'": '.$dict["message_cart_ph_exist"].'<br/>';

						}else{
							$columns_list = "multimedia_id, mrubrikator_id,user_id,downloads,date_main,date_edit,is_ph";
							$val_list = "".($res['MULTIMEDIA_ID']?$res['MULTIMEDIA_ID']:"NULL").",".$id.",".$user_id.",0,now(),now(),1";
							$insert_sql = "insert into $table_name ($columns_list) values ($val_list)";
							$ins_q = db_query($conn, $insert_sql);
							if (!$ins_q)
							{

								echo '"'.$res['NAME'].'": '.$dict["message_cart_ph_add_error"].'<br/>';
							}else{
								echo '"'.$res['NAME'].'": '.$dict["message_cart_ph_add"].'<br/>';
							}
						}
					}else{
						echo '"¹'.$id.'": '.$dict["message_cart_ph_not_exist"].'<br/>';
					}
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

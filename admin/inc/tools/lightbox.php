<?header('Content-type: text/html; charset=windows-1251');
	$user_id=$_SESSION["user_id_ses"];
	$groups = explode(", ",$_SESSION["group_id_ses"]);
	$sel1 = "select *from dictionary_lang_v where (name like 'message_lightbox_%') or (name like 'message_no%')";
	$dict = array();
	$dict_res = db_getArray($conn, $sel1, 1);
	foreach($dict_res as $val){
		$dict[$val["NAME"]] = $val["TEXT"];
	}
	//print_r($dict);
	if($user_id){
		if(in_array(14,$groups)){
			if(!$lightbox_user_id && !$lbname){
				$select_lb = "select * from lightbox_user where user_id = ".$user_id."";
				$res0=db_getArray($conn, $select_lb, 1);
				$form = '<!DOCTYPE html>
				<html>
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
				<script>$(\'form#select_lightbox\').bind(\'submit\', function(event){
				var ids = $("#ids").val();
				var lightbox_user_id = $("#lightbox_user_id option:selected").val();
				var lbname = $("#lbname").val();
				event.preventDefault();
				var data = "http://'.$_SERVER["HTTP_HOST"].'/lightbox.html?ids="+ids+"&lightbox_user_id="+lightbox_user_id+"&lbname="+lbname+"";
				$.fancybox({\'href\':data,\'type\': \'ajax\'});
				});</script></head>';
				$form .= '<body>
				<form method="post" action="/lightbox.html" id="select_lightbox" accept-charset="windows-1251">
				<div id="lightbox_result">'.$dict["message_lightbox_select"].'</div>
				<input name="ids" id="ids" type="hidden" value="'.$ids.'" >
				<select id="lightbox_user_id" name="lightbox_user_id">';
				if(count($res0)<10)  $form .= '<option value="" selected>'.$dict["message_lightbox_new"].'</option>';
				foreach($res0 as $row){
					$form .= '<option value="'.$row["ID"].'">'.$row["NAME"].'</option>';
				}
				$form .= '</select>';
				if(count($res0)<10) $form .= ' '.$dict["message_lightbox_or"].' <input id="lbname" name="lbname" type="text" placeholder="'.$dict["message_lightbox_name"].'" value="" > ';
				echo $form .= '<input type="submit" name="submit" id="submit" value="OK" /></form></body></html>';
			}else{
				echo '<div width="500">';
				if($lbname){
					$str=getenv('HTTP_USER_AGENT');
					if (eregi("Opera[/ ]([0-9a-z\.]*)",$str,$pocket)){
						//echo "Opera ".$pocket[1];
						$lbname =  iconv( "UTF-8", "windows-1251//TRANSLIT", $lbname);
					}
					//$lbname =  iconv( "UTF-8", "windows-1251//TRANSLIT", $lbname);
					$insert_sql = "insert into lightbox_user (name, user_id) values ('$lbname',$user_id)";
					$ins_q = db_query($conn, $insert_sql);
					if (!$ins_q)
					{
						echo $dict["message_lightbox_create_error"].'<br/>';
					}else{
						echo $dict["message_lightbox_create"]." \"$lbname\".<br/>";
						$insert_sql = "select id as lightbox_user_id from lightbox_user where name='$lbname' ORDER BY id desc";
						$lightbox_user = db_getVar($conn, $insert_sql,2);
						unset($lightbox_user); unset($insert_sql);
					}
					unset($insert_sql);
					unset($ins_q);
				}else{

				}
				//echo print_r($res0);

				global $table_name, $column_name;
				$table_name_m=MULT_TAB;
				$column_name_m=MULT_COL;
				foreach(explode(",",$ids) as $id){
					$file_info_sql = "select ft.name as ".$column_name_m."_type, tn.ID, tn.name, tn.".$column_name_m."_name, tn.MRUBRIKATOR_ID, tn.IN_HIRES
					from $table_name_m tn, ".TABLE_PRE."file_types ft where tn.id = ".$id." and ft.id = tn.".$column_name_m."_type_id";
					//echo "file_info_sql=$file_info_sql<br>";
					$res=db_getArray($conn, $file_info_sql, 2);
					//print_r($res);
					if(count($res)>0){$table_name = "lightbox";
						$file_info_sql2 = "select * from $table_name c where  c.multimedia_id=".$id." and c.user_id = ".$user_id." and lightbox_user_id=$lightbox_user_id";
						$res2=db_getArray($conn, $file_info_sql2, 2);
						if(count($res2)>0){
							echo '"'.$res['NAME'].'": '.$dict["message_lightbox_exist"].' .<br/>';
						}else{
							$columns_list = "multimedia_id, mrubrikator_id,user_id,date,lightbox_user_id";
							$val_list = "".$id.",".($res['MRUBRIKATOR_ID']?$res['MRUBRIKATOR_ID']:"NULL").",".$user_id.",now(),$lightbox_user_id";
							$insert_sql = "insert into $table_name ($columns_list) values ($val_list)";
							$ins_q = db_query($conn, $insert_sql);
							if (!$ins_q)
							{
								echo '"'.$res['NAME'].'": '.$dict["message_lightbox_add_error"].'<br/>';
							}else{
								echo '"'.$res['NAME'].'": '.$dict["message_lightbox_add"].'<br/>';
							}
						}
					}else{
						echo '"'.$res['NAME'].'": '.$dict["message_lightbox_unreg"].'<br/>';
					}
				}
				echo '</div>';}
		}else{echo '<div width="500">';
			echo $dict["message_no_rights"].'<br/>';echo '</div>';
		}
	}else{echo '<div width="500">';
		echo $dict["message_not_logged"].'<br/>';echo '</div>';
	}


?>
<?
	//$file = 'gallery/'.$col_id.'_'.$id.'_'.($s?$s:'w'.$w.'_h'.$h).'.jpg';
	$file_ex=array('gif', 'jpg', 'jpeg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'tiff', 'jpc', 'jp2', 'jpf', 'jb2', 'swc', 'aiff', 'wbmp', 'xbm' );
	$file_exists = false;
	foreach($file_ex as $ex){
		$file = 'gallery/'.$col_id.'_'.$id.'_'.$s.'.'.$ex;
		if(file_exists($file)){
			//$file_exists=true;
			break;
		 	//unlink($file);
		}
	}
	if(!$file_exists){
		//echo 'Файл не существует, создаем его';
		$sel_tab_col="SELECT t.name AS table_name, c.name AS column_name FROM ti_tables t, ti_columns c WHERE c.id=$col_id AND c.table_id=t.id";
		$res_tab_col=db_getArray($conn, $sel_tab_col, 2);
		$table_name=$res_tab_col['TABLE_NAME'];
		$column_name=$res_tab_col['COLUMN_NAME'];
		$file_info_sql = "SELECT
		ft.name as ".$column_name."_type,
		tn.".$column_name."_name,
		tn.$column_name,
		tn.".$column_name."_size,
		tn.".$column_name."_width,
		tn.".$column_name."_height
		from
		$table_name tn,
		".TABLE_PRE."file_types ft
		where
		tn.id = :id and
		ft.id = tn.".$column_name."_type_id
		";

		$bind_ar[]=db_for_bind("id", $id);
		$res=db_getArray($conn, $file_info_sql, 2, array("bind_ar"=>$bind_ar));
		$file_type = $res[strtoupper($column_name."_type")];
		$file_size = $res[strtoupper($column_name."_size")];
		$lo_oid = $res[strtoupper($column_name)];
		$ex = strrchr(strtolower($res[strtoupper($column_name."_name")]),'.');

		$file = 'gallery/'.$col_id.'_'.$id.'_'.$s.''.$ex;
		$file_info_sql2 = "SELECT s.id, s.width, s.height, s.unit, s.watermark from image_size s where  s.id = $s";
		$res2=db_getArray($conn, $file_info_sql2);

		if($res2){
			$res2 = $res2[0];
		}else{
			$res2["WIDTH"] = $res[strtoupper($column_name."_WIDTH")];
			$res2["HEIGHT"] = $res[strtoupper($column_name."_HEIGHT")];
			$res2["WATERMARK"] =1;
		}

		$file_img = db_get_file_data($conn, $lo_oid, $file_size);
		$wi =$res2["WIDTH"];
		$hi = $res2["HEIGHT"];
		$q = 100;
		$src = $file_img;
		$w_src = $res[strtoupper($column_name."_WIDTH")];
		$h_src = $res[strtoupper($column_name."_HEIGHT")];
		if($wi>0 || $hi>0 ){
			if($wi>0 && $hi>0 ){
				if($w_src/$wi>$h_src/$hi){
					$w_dest = $wi;
					$ratio = $w_src/$wi;
					$h_dest = round($h_src/$ratio);
				}elseif($w_src/$wi<$h_src/$hi){
					$ratio = $h_src/$hi;
					$w_dest = round($w_src/$ratio);
					$h_dest = $hi;
				}else{
					$w_dest = $wi;
					$h_dest = $hi;
				}
			}elseif( $wi>0){
				$ratio = $w_src/$wi;
				$w_dest = round($w_src/$ratio);
				$h_dest = round($h_src/$ratio);
			}elseif( $hi>0){
				$ratio = $h_src/$hi;
				$w_dest = round($w_src/$ratio);
				$h_dest = round($h_src/$ratio);
			}else{
				$w_dest = $w_src;
				$h_dest = $h_src;
			}
			//echo $file_type.' '.$file;
			$fimg = imagecreatefromstring($file_img);
			$dest = @ImageCreateTrueColor ($w_dest,$h_dest);
			if(substr_count($file_type,"png")){
				imagealphablending($dest, false);
				imagesavealpha($dest,true);
				$transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
				imagefilledrectangle($dest, 0, 0, $w_dest,$h_dest, $transparent);
			}
			imagecopyresampled($dest, $fimg, 0, 0, 0, 0, $w_dest, $h_dest, $w_src, $h_src);
			imagedestroy($fimg);
		}else{
			$dest = imagecreatefromstring($file_img);
			$w_dest = $res[strtoupper($column_name."_WIDTH")];
			$h_dest = $res[strtoupper($column_name."_HEIGHT")];
		}
		if($res2["WATERMARK"]){
			if(substr_count($file_type,"png")){
               imageAlphaBlending($dest, true);
			}
			if(WATERMARK){
				$wm = @imagecreatefrompng(WATERMARK);
				$ww =imagesx($wm);
				$wh =imagesy($wm);
				if($w_dest>$h_dest){
				$nh=round($h_dest*3/5);
				$nw = round($nh*$ww/$wh);
				}else{
				$nw=round($w_dest*3/5);
				$nh = round($nw*$wh/$ww);
				}
				imagecopyresampled ($dest, $wm, round(($w_dest-$nw)/2), round(($h_dest-$nh)/2), 0, 0, $nw, $nh, $ww, $wh);
				imagedestroy($wm);
			}
		}

		if(substr_count($file_type,"gif")) imagegif($dest,$file);
        elseif(substr_count($file_type,"jpeg")) imagejpeg($dest,$file);
        elseif(substr_count($file_type,"png")) imagepng($dest,$file);
        elseif(substr_count($file_type,"bmp")) imagewbmp ($dest,$file);
		$old = umask(0);
		chmod($file, 0664);
		umask($old);
		imagedestroy($dest);
		if(substr_count($file_type,"gif")){
			$im = @imagecreatefromgif ($file);
			header("Content-type: ".$file_type);
			imagegif($im);
		}elseif(substr_count($file_type,"jpeg")){
			$im = @imagecreatefromjpeg ($file);
			header("Content-type: ".$file_type);
			imagejpeg($im);
        }elseif(substr_count($file_type,"png")){
			$im = @imagecreatefrompng ($file);
			header("Content-type: ".$file_type);
			imagesavealpha($im,true);
			imagepng($im);
        }elseif(substr_count($file_type,"bmp")){
			$im = @imagecreatefromwbmp ($file);
			header("Content-type: ".$file_type);
			imagewbmp($im);
        }
		imagedestroy($im);exit();
	}else{
		$file_type = image_type_to_mime_type(exif_imagetype ($file));
		if($ex=='gif'){
			$im = @imagecreatefromgif ($file);
			header("Content-type: ".$file_type);
			imagegif($im);
		}elseif($ex=='jpg'){
			$im = @imagecreatefromjpeg ($file);
			header("Content-type: ".$file_type);
			imagejpeg($im);
		}elseif($ex=='jpeg'){
			$im = @imagecreatefromjpeg ($file);
			header("Content-type: ".$file_type);
			imagejpeg($im);
		}elseif($ex=='png'){
			$im = @imagecreatefrompng ($file);
			header("Content-type: ".$file_type);
			imagepng($im);
		}elseif($ex=='bmp'){
			$im = @imagecreatefromwbmp ($file);
			header("Content-type: ".$file_type);
			imagewbmp($im);
		}
		if($im){imagedestroy($im); exit();}
	}
?>
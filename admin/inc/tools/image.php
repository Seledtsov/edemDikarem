<?

    $table_name=MULT_TAB;
    $column_name=MULT_COL;
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
    //echo"file_info_sql=$file_info_sql<br>";
    $bind_ar[]=db_for_bind("id", $id);
    $res=db_getArray($conn, $file_info_sql, 2, array("bind_ar"=>$bind_ar));

    $file_type = $res[strtoupper($column_name."_type")];

    $file_size = $res[strtoupper($column_name."_size")];

    $lo_oid = $res[strtoupper($column_name)];

    //echo"lo_oid=$lo_oid<br>";

    $file_img = db_get_file_data($conn, $lo_oid, $file_size);

    //если задана ширина ресайзим картину
    $wi = intval($_REQUEST["w"]);
    $hi = intval($_REQUEST["h"]);
    if($wi>0 || $hi>0 ){//var_dump($res);
        $q = 100;
        $src = $file_img; 
        $w_src = $res["MFILE_WIDTH"]; 
        $h_src = $res["MFILE_HEIGHT"];
        //определение размера
        if($wi>0 && $hi>0 ){
            $w_dest = $wi; 
            $h_dest = $hi;
        }elseif( $wi>0){
            // вычисление пропорций 
            $ratio = $w_src/$wi; 
            $w_dest = round($w_src/$ratio); 
            $h_dest = round($h_src/$ratio); 
        }elseif( $hi>0){
            // вычисление пропорций 
            $ratio = $h_src/$hi; 
            $w_dest = round($w_src/$ratio); 
            $h_dest = round($h_src/$ratio); 
        }else{
            $w_dest = $w_src; 
            $h_dest = $h_src;
        }
        if($w_dest!=$w_src && $h_dest!=$h_src){
            // Создание пустой картинки 
            $fimg = imagecreatefromstring($file_img);
            // Преобразование размера
            $dest = imagecreatetruecolor($w_dest,$h_dest);
            imagecopyresized($dest, $fimg, 0, 0, 0, 0, $w_dest, $h_dest, $w_src, $h_src);
            imagecopyresampled($dest, $fimg, 0, 0, 0, 0, $w_dest, $h_dest, $w_src, $h_src);
        }else{
            $dest = imagecreatefromstring($file_img);
        }
        //Вывод картинки
        $file_size  = getimagesize ($dest);
        //echo '<pre>'; print_r($arsize);
        header("Cache-Control: public, max-age=604800, must-revalidate");
        Header("Pragma: public");
        header("Content-type: {$res['MFILE_TYPE']};");
        $file=$res[strtoupper($column_name."_name")];
       // http_send_content_disposition($file, true)
        //Header("Content-Disposition: inline, filename=".$file);
        header("Content-Length: $file_size");
        if(substr_count($res['MFILE_TYPE'],"gif")) imagegif($dest);
        elseif(substr_count($res['MFILE_TYPE'],"jpeg")) imagejpeg($dest);
        elseif(substr_count($res['MFILE_TYPE'],"png")) imagepng($dest);
        elseif(substr_count($res['MFILE_TYPE'],"bmp")) imagewbmp ($dest);
        imagedestroy($dest);

    }else{

        Header("Pragma: no-cache");

        header("Accept-Ranges: bytes");

        header("Content-Length: $file_size");

        if($download)
        {
            $file=$res[strtoupper($column_name."_name")];
            Header( "Content-type: application/force-download");
            header("Content-Disposition: attachment; filename=".$file);
        }
        else
            Header( "Content-type: ".$file_type);

        echo $file_img;
    }
?>
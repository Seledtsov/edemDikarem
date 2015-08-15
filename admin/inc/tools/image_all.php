<?

    //$table_name=MULT_TAB;
    //$column_name=MULT_COL;
    //phpinfo();
    $sel_tab_col="SELECT t.name AS table_name, c.name AS column_name FROM ti_tables t, ti_columns c WHERE c.id=$col_id AND c.table_id=t.id";
    //echo"sel_tab_col=$sel_tab_col<br>";
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
    //echo"file_info_sql=$file_info_sql<br>";
    $bind_ar[]=db_for_bind("id", $id);
    $res=db_getArray($conn, $file_info_sql, 2, array("bind_ar"=>$bind_ar));

    $file_type = $res[strtoupper($column_name."_type")];

    $file_size = $res[strtoupper($column_name."_size")];

    $lo_oid = $res[strtoupper($column_name)];

    //echo"lo_oid=$lo_oid<br>";
    $file=$res[strtoupper($column_name."_name")];
    $file=str_replace(array(" ", "(", ")", "[", "]", "[", "]", "-", "\"", "'"), "_", $file);
    //var_dump($res);
    //Header("Pragma: no-cache");
    //header("Accept-Ranges: bytes");*/
    $wi = intval($_REQUEST["w"]);
    $hi = intval($_REQUEST["h"]);
    if($wi>0 || $hi>0 ){
        $q = 100;
        $src = $file_img;
        $w_src = $res["IMG_WIDTH"];
        $h_src = $res["IMG_HEIGHT"];
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
       // echo "{$w_dest} = {$w_src} {$h_dest} = {$h_src}";
        $file_img = db_get_file_data($conn, $lo_oid, $file_size);
        $fimg = imagecreatefromstring($file_img);
        if($w_dest!=$w_src && $h_dest!=$h_src){
            // Преобразование размера
            $dest = imagecreatetruecolor($w_dest,$h_dest);
            imagecopyresized($dest, $fimg, 0, 0, 0, 0, $w_dest, $h_dest, $w_src, $h_src);
            imagecopyresampled($dest, $fimg, 0, 0, 0, 0, $w_dest, $h_dest, $w_src, $h_src);
        }else{
            $dest = $fimg;
        }
        //Вывод картинки
        //echo $res['IMG_TYPE'];
        header("Cache-Control: public, must-revalidate");
        Header("Pragma: public");
        header("Content-type: {$res['IMG_TYPE']};");
        //imagejpeg($fimg);
        if(substr_count($res['IMG_TYPE'],"gif")) imagegif($dest);
        elseif(substr_count($res['IMG_TYPE'],"jpeg")) imagejpeg($dest);
        elseif(substr_count($res['IMG_TYPE'],"png")) imagepng($dest);
        elseif(substr_count($res['IMG_TYPE'],"bmp")) imagewbmp ($dest);
        imagedestroy($dest);
    }else{

        //echo"file_type=$file_type<br>";
        header("Cache-Control: public, must-revalidate");
        Header("Pragma: public");
        if($download)
        {
            header("Content-Disposition: attachment; filename=\"$file\"");
            header("Content-Length: $file_size");
            Header("Content-type: application/octet-stream; name=\"$file\"");
        }
        else
        {
            header("Content-Disposition: inline; filename=\"$file\"");
            header("Content-Length: $file_size");
            Header("Content-type: $file_type; name=\"$file\"");
        }
        header("Content-Transfer-Encoding: binary\n");
        //echo $file_img;
        db_output_file_data ($conn, $lo_oid);
    }

?>
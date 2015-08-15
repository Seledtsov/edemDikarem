<?
global $table_name, $column_name;

$table_name=MULT_TAB;
$column_name=MULT_COL;

$file_info_sql = "
                 select
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

//$res=db_getArray($conn, $file_info_sql, 2);

$file_type = $res[strtoupper($column_name."_type")];

$file_size = $res[strtoupper($column_name."_size")];

$lo_oid = $res[strtoupper($column_name)];

//echo"lo_oid=$lo_oid<br>";

$file_img = db_get_file_data($conn, $lo_oid, $file_size);

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
   {
   /*
   Header("Cache-Control: no-cache, must-revalidate");
   header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");                 // Date in the past
   header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");    // always modified
   header("Content-Disposition: inline; filename=" . $filename);
   header("Content-Transfer-Encoding: binary");
   */

   Header( "Content-type: ".$file_type);
   }



echo $file_img;

?>
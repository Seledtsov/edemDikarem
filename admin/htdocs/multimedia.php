<?
    if(getenv("g_INC"))
        include_once(getenv("g_INC")."conf.php");
    else
        include_once($_SERVER['DOCUMENT_ROOT']."/inc/conf.php");
    include(PATH_INC."inc.php");
    $table_name=MULT_TAB;
    $column_name=MULT_COL;

    $mrubrikator_info_sql = "SELECT mr.id, mr.name, mr.parent_id from mrubrikator mr Order By parent_id DESC, id ASC";
    $rubriks=db_getArray($conn, $mrubrikator_info_sql);
    
    if($_REQUEST["rubrika_id"]==NULL){
       $rubrika_id = $rubriks[0]["ID"];
    }else{
       $rubrika_id = intval($_REQUEST["rubrika_id"]);
    }
    //echo '<pre>';    print_r($rubriks);
    $menu="";
    foreach($rubriks as $rubrika){
        $menu .= ' <a href = "/multimedia.php?rubrika_id='.$rubrika["ID"].'">'.$rubrika["NAME"].'</a>';
    }
    echo $menu;
    
    $file_info_sql = "SELECT
    ft.name as ".$column_name."_type,
    tn.id, tn.name,
    tn.".$column_name."_name,
    tn.$column_name,
    tn.".$column_name."_size,
    tn.".$column_name."_width,
    tn.".$column_name."_height,
    mm.mrubrikator_id
    from $table_name tn
    left join multimedia_mrubrikator mm ON(mm.multimedia_id = tn.id)
    join  ".TABLE_PRE."file_types ft ON (ft.id = tn.".$column_name."_type_id)";
    if($rubrika_id>0){
        $file_info_sql .= " WHERE mm.mrubrikator_id=$rubrika_id";
    }
    $imgs = "";

    $results = db_getArray($conn, $file_info_sql);
    foreach($results as $result){
    $imgs .= '<img align="left" src="'.IMAGE_LINK.'?id='.$result["ID"].'&h=100"  title="'.$result["NAME"].'" alt="'.$result["NAME"].'" >';
    }
    echo $imgs;
   /* echo '<pre>';
    print_r($resul2t);*/
?>
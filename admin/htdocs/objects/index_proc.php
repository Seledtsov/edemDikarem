<?
//файл индексации
include(getenv("g_INC")."conf.php");

ignore_user_abort(true);
set_time_limit(36000);
/*
$argv = $_SERVER["argv"];


echo"\r\n\r\nSTART $SCRIPT_NAME - ".date("d-m-Y H:i:s")."\r\n";
$start_time=mktime();
*/
include(PATH_INC."inc.php");
include_once(PATH_INC."func/func_indexer".DBASE_ATTR.".php");
if($id && $obj_id && del_indexer($conn, $id, $obj_id))
{

$sel_rating_q="select r.rating, c.column_id, upper(c.name) as col_name, c.ref_id, t.table_id, upper(t.name) as name, ot.main
                      from ".TABLE_PRE."search_rating r, ".TABLE_PRE."columns c, ".TABLE_PRE."tables t, ".TABLE_PRE."objects_tables ot
                      where ot.object_id=$obj_id and ot.table_id=t.table_id and c.column_id=r.column_id and c.table_id=t.table_id";
echo"sel_rating_q=$sel_rating_q<br>";
$res_rating=db_getArray($conn, $sel_rating_q);

foreach($res_rating as $k=>$v)
        {
        //if(!$column_list[$v['NAME']])
        //    $column_list[$v['NAME']]="id";
        //$column_list[$v['NAME']].=",".$v['COL_NAME'];
        //$rating[$v['NAME']][$v['COL_NAME']]=$v['RATING'];

        if(!$tables[$v['TABLE_ID']]['COLUMN_LIST'])
            $tables[$v['TABLE_ID']]['COLUMN_LIST']="ID";
        $tables[$v['TABLE_ID']]['MAIN']=$v['MAIN'];
        $tables[$v['TABLE_ID']]['COLUMN_LIST'].=",".$v['COL_NAME'];
        $tables[$v['TABLE_ID']]['TABLE_NAME']=$v['NAME'];
        $tables[$v['TABLE_ID']][$v['COL_NAME']]['RATING']=$v['RATING'];
        $tables[$v['TABLE_ID']][$v['COL_NAME']]['ID']=$v['COLUMN_ID'];
        $tables[$v['TABLE_ID']][$v['COL_NAME']]['REF_ID']=$v['REF_ID'];
        }
foreach($tables as $k=>$v)
        {
        if($v['MAIN'])
           {
           $v['COLUMN_LIST'].=", ".PUBLISH.", ".DATE_MAIN;
           }
        $sel_ind_q="select ".$v['COLUMN_LIST']." from ".$v['TABLE_NAME']." where id=$id";
        echo"sel_ind_q=$sel_ind_q<br>";

        $res_ind=array();
        $res_ind=db_getArray($conn,$sel_ind_q, 2);
        if(!$res_ind[PUBLISH])
           {
           echo"publish=".$res_ind[PUBLISH]."\r\n";
           exit();
           }
        foreach($res_ind as $k1=>$v1)
                {
                if($k1!="ID"  && $k1!=PUBLISH && $k1!=DATE_MAIN)
                   {
                   echo"$k1=$v1<br>";
                   if(!is_numeric($k1))
                      {
                      if($v[$k1]['REF_ID'] && $v1 && $v1==intval($v1))
                         {
                         $sel_ref_col_q="select t.name from ".TABLE_PRE."tables t, ".TABLE_PRE."columns c where c.table_id=t.table_id and c.column_id=".$v[$k1]['REF_ID'];
                         //echo"sel_ref_col_q=$sel_ref_col_q<br>";
                         $res_ref_col=db_getArray($conn, $sel_ref_col_q, 2);
                         $sel_ref_text="select name from ".$res_ref_col['NAME']." where id=$v1";
                         //echo"sel_ref_text=$sel_ref_text<BR>";
                         $res_ref_text=db_getArray($conn, $sel_ref_text, 2);
                         $section_id=$v1;
                         $v1=$res_ref_text['NAME'];
                         }
                      else
                          {
                          $section_id=0;
                          }
                      //echo"$k1=$v1<br>";
                      $v1=strip_tags($v1);
                      if($v1)
                         {

                         $ind_id=db_insert($conn, TABLE_PRE."search_index",
                            array(0=>"ID", "object_id", "column_id", "rel_id", "date_pub", "date_index", "publish", "full_text", "len", "section_id", "rating"),
                            array(0=>"", $obj_id, $v[$k1]['ID'], $res_ind['ID'], $res_ind[DATE_MAIN], db_sysdate(), $res_ind[PUBLISH], $v1, strlen($v1), $section_id, $v[$k1]['RATING']),
                            array(0=>'ID', 'int', 'int', 'int', 'date', 'date', 'int', 'text', 'int', 'int', 'int'));

                         pre_indexer($conn, $ind_id);
                         $ind_ar[]=$ind_id;
                         }
                      }
                   }//конец условия, что не ID
                }//конц обрабтки конкретной таблицы
        }//конец обработки всех таблиц
index_operation($conn, $id, $obj_id);
full_rating($conn, $id, $obj_id);
}

?>
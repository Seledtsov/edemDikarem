<?
if($bt_do_new)
    {
    $ans=1;
    foreach($res_col as $k_val=>$v_val)
             {
             if($v_val['COLUMN_NAME'])
             {
             $col_name=$v_val['COLUMN_NAME']."_new";
             $col_name_up=$col_name."_up";
             if(!$$col_name && $$col_name_up)
                 $col_name=$col_name_up;
             if($pk_name!=$v_val['COLUMN_NAME'] || $$col_name)//$v_val['REDUCT'])
                 {
                 //echo $v_val['COLUMN_NAME']."=".$col_name_ar[$k]."<br>";
                 if($col_list)
                     $col_list.=", ";
                 if($val_list)
                     $val_list.=", ";

                 $col_list.=$v_val['COLUMN_NAME'];
                 $val_list.="'".$$col_name."'";
                 }
             elseif($v_val['SEQ_NAME'])
                    {
                 if($col_list)
                     $col_list.=", ";
                 if($val_list)
                     $val_list.=", ";

                 $col_list.=$v_val['COLUMN_NAME'];
                 $val_list.=$v_val['SEQ_NAME'].".nextval";

                    }
             else//if($v_val['SEQ_NAME'])
                 {
                 if($col_list)
                     $col_list.=", ";
                 if($val_list)
                     $val_list.=", ";

                 $col_list.=$v_val['COLUMN_NAME'];
                 $sel_max_q="select (NVL(max(".$v_val['COLUMN_NAME']."), 0)+1) as max from $table_name";
                 //echo"sel_max_q=$sel_max_q<br>";
                 $sel_max=db_query($conn, $sel_max_q);
                 $res_max=db_fetch_row($sel_max);
                 $val_list.=$res_max['MAX'];

                 }
             }
             }
    $upd_q="insert into $table_name($col_list) values($val_list)";
    //echo"upd_q=$upd_q<br>";
    $upd=db_query($conn, $upd_q);
    if($upd)
        {
        full_log($conn, $g_uid, $ARM_ID, DO_INSERT, $v, 0);
        }
    else
        {
        $ans=-1;
        echo"upd_q=$upd_q<br>";
        }

    }
elseif($bt_do_upd)
    {
    $ans=1;
    foreach($mark as $k=>$v)
             {
             $setSQL="";
             foreach($res_col as $k_val=>$v_val)
                      {
                      if($v_val['REDUCT'])
                          {
                          $col_name_ar=$$v_val['COLUMN_NAME'];
                          //echo $v_val['COLUMN_NAME']."=".$col_name_ar[$k]."<br>";
                          if($setSQL)
                              $setSQL.=", ";
                          $setSQL.=$v_val['COLUMN_NAME']."='".$col_name_ar[$k]."'";
                          }
                      }

             $upd_q="update $table_name set $setSQL where $pk_name=$v";
             $upd=db_query($conn, $upd_q);
             if($upd)
                 {
                 full_log($conn, $g_uid, $ARM_ID, DO_UPDATE, $v, 0);
                 }
             else
                 {
                 $ans=-1;
                 echo"upd_q=$upd_q - $ans<br>";
                 }
             //echo"$k=$v<br>";

             }
    }
elseif($bt_do_del)
        {
        $ans=1;
        foreach($mark as $k=>$v)
             {
             //echo"$k=$v<br>";
             $upd_q="delete from $table_name where $pk_name=$v";
             //echo"upd_q=$upd_q<br>";
             $upd=db_query($conn, $upd_q);
             if($upd)
                 full_log($conn, $g_uid, $ARM_ID, DO_DELETE, $v, 0);
             else
                 {
                 $ans=-1;
                 echo"upd_q=$upd_q<br>";
                 }
             }

        }
?>
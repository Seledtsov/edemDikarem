<?
function db_connect($database, $user, $password, $host="")
        {
        global $SCRIPT_NAME;
        //if(eregi("test_er", $SCRIPT_NAME))
         //       echo"be\r\n";

        $ret = @OCILogon($user, $password, $database);
		//echo"ret=$ret<br>";
        if(eregi("test_er", $SCRIPT_NAME))
                echo"ret=$ret\r\n";
        if(!$ret)
                {
                //catch_exception();
                //echo"$database, $user, $password";
                ;
                }
        db_check_errors($php_errormsg);
        if(eregi("test_er", $SCRIPT_NAME))
                echo"php_errormsg=$php_errormsg\r\n";
        if(eregi("test_er", $SCRIPT_NAME))
                echo"db_connect end\r\n";

        return $ret;
        }

function db_disconnect($conn)
{
    $ret = OCILogoff($conn);
    db_check_errors($php_errormsg);
    return $ret;
}

function db_check_errors($errormsg)
         {
         global $db_error_code, $db_error_msg, $db_error_source;
         if (ereg( '^([^:]*): (...-.....): (.*)', $errormsg, &$data))
             {
             list($foo, $function, $db_error_code, $db_error_msg) = $data;
             $db_error_msg =  "$function: $db_error_msg";
             $db_error_source =  "[Oracle][PHP][OCI8]";
             }
         elseif (ereg( '^([^:]*): (.*)', $errormsg, &$data))
                {
                list($foo, $function, $db_error_msg) = $data;
                $db_error_msg =  "$function: $db_error_msg";
                $db_error_code = 0;
                $db_error_source =  "[PHP][OCI8][db-oci8]";
                }
         else
             {
             $db_error_msg = $errormsg;
             $db_error_code = 0;
             $db_error_source =  "[PHP][OCI8][db-oci8]";
             }
         }
//-----------------------------------------------------
//для обычных запросов
function db_query($conn, $query)
{
    //echo"1 db_query- $query<br>";
    $stmt = OCIParse($conn, $query);
    db_check_errors($php_errormsg);
    if (!$stmt) {
    return false;
    }
    //echo"2 db_query- $query<br>";
    if (OCIExecute($stmt, OCI_DEFAULT))
        {
        return $stmt;
        }
    else
        {
        echo "Error-$query<br>";
        }

    db_check_errors($php_errormsg." ".$query);
    OCIFreeStatement($stmt);
   return false;
}



/*
 * Function: db_fetch_row
 * Arguments: $stmt (int)   - result identifier
 * Description: Returns an array containing data from a fetched row.
 * Returns:   false - error
 *          (array) - returned row, first column at index 0
 */

//-----------------------------------------------------------
//получение результатов обычных запросов
function db_fetch_row($stmt, $pg_row=0, $ar_view=1)
{
    $cols = OCIFetchInto($stmt, $row, OCI_ASSOC);
//        echo ,"$cols,$row[0]\n";
    if (!$cols) {
    db_check_errors($php_errormsg);
    return false;
    }
    foreach($row as $k=>$v)
            {
            if(is_object($v))
               $row[$k]=$v->load();
            }
    return $row;
}

function db_select($conn, $table_name, $columns, $conditions="")
         {
         $sel_q="select $columns from $table_name";
         $stmt = OCIParse($conn, $sel_q);
         db_check_errors($php_errormsg);
         if (!$stmt)
               {
               return false;
               }
         if (@OCIExecute($stmt, OCI_DEFAULT))
               {
               for($i=0; $cols = @OCIFetchInto($stmt, &$row, OCI_ASSOC); $i++)
                      {
                      foreach($row as $k=>$v)
                               {
                               if(is_object($v))
                                  $row[$k]=$v->load();
                               }
                      $res[$i]=$row;
                      }

               if (!$i)

                     {

                     //echo"err-fetch";

                     db_check_errors($php_errormsg);

                     return false;

                     }

               return $res;

               }

         db_check_errors($php_errormsg);

         @OCIFreeStatement($stmt);

         return false;

         }

//------------------------------------------------------------------------------
//функция принимает запрос и возвращает массив результатов
function db_getArray($conn, $sel_q, $ar=1, $extr_ar=array())
         {
		extract($extr_ar);
		/*
		to_lower - возврат массива с названиями колонок в нижнем регистре
		*/
		$res_ar=array();
		if(!$parse)//что не отпарсенный запрос
			{
			$stmt = @OCIParse($conn, $sel_q);
			db_check_errors($php_errormsg);
			if (!$stmt)
              {
              return false;
              }
			}
		else
			$stmt=$sel_q;
		if($bind_ar)//надо биндить запрос
				{
				//echo"sel_q=$sel_q, stmt=$stmt, conn=$conn<br>";
				foreach($bind_ar as $k_b=>$v_b)
					{
					if(strpos($sel_q, $v_b['name'])>0 || $parse)
						OCIBindByName($stmt, $v_b['name'], &$v_b['val'], $v_b['length']);
					//echo"$stmt, name=".$v_b['name'].", val=&".$v_b['val'].", len=".$v_b['length']."<br>";
					}
				}

         if (@OCIExecute($stmt, OCI_DEFAULT))
             {
             for($i=0; $res=db_fetch_row($stmt, $i); $i++)
                 {
				 if($to_lower)
					{
					foreach($res as $k=>$v)
						$res[strtolower($k)]=$v;
					}
                 if($ar==1)
                    {
                    $res_ar[$i]=$res;
                    }
                 elseif(!$i)
                         {
                         $res_ar=$res;
                         }
                 }
             }
		if(!$parse)
			@OCIFreeStatement($stmt);

		return $res_ar;
        }

//-----------------------------------------------------------------------------------------------------
//функция принимает запрос и преобразует результаты первой строки в виде глобальных переменных,
//например $res['NAME'] - это $name

function db_getVar($conn, $sel_q)
         {
        $stmt = @OCIParse($conn, $sel_q);
         db_check_errors($php_errormsg);
         if (!$stmt)
              {
              return false;
              }
         if (@OCIExecute($stmt, OCI_DEFAULT))
                {
                     if($res=db_fetch_row($stmt))
                            {
                            foreach($res as $k=>$v)
                                    {
                                    //echo"$k=$v<br>";
                                    if(is_object($v))
                                        {
                                        $v=$v->load();
                                        }
                                    //echo"&nbsp;&nbsp;$k=$v<br>";
                                    $GLOBALS[strtolower($k)]=$v;
                                    }
                            @OCIFreeStatement($sel);
                            return 1;
                            }
                }
         else
             return 0;
         }


function db_limit($sel_q, $start_row, $count_row)
         {
         //echo "start = $start_row, count=$count_row<br>";
         if($start_row==0)
            $sel_ret="select * from ($sel_q) where rownum<=".($start_row+$count_row);
         else
            $sel_ret="SELECT * FROM (SELECT lim.*, rownum as r FROM ($sel_q) lim)
                               WHERE r<=".($start_row+$count_row)." AND r>$start_row" ;
         //echo"sel_ret=$sel_ret<br>";
         return $sel_ret;
         }
/**
 * @return void
 * @param unknown $conn
 * @param unknown $table_name
 * @param unknown $columns_name
 * @param unknown $columns_value
 * @param unknown $columns_type
 * @param unknown $conditions
 * @desc Функция изменяет строку в таблицу table_name. В массиве $columns_name
 передаются названия полей, в $columns_value - значения, в $columns_type - типы полей.
 Индексация у этих массивов, естественно, должна совпадать.
*/


function db_update ($conn, $table_name, $columns_name, $columns_value, $columns_type, $conditions, $ar=array())
{
		extract($ar);
        /*
        foreach ($columns_name as $k1=>$v1)
        {
        echo $columns_name[$k1]." - ".$columns_value[$k1]." - ".$columns_type[$k1];
        br();
        }
        */
        $ind_lob=0;
		$clob_ar=array();
        foreach ($columns_name as $key => $val)
                 {
                 //echo"$val=".$columns_type[$key]."-".$columns_value[$key]."<br>";
                 if ($set_list && intval($columns_value[$key]) != -2)
                     {
                     $set_list.= ", ";
                     }
                //echo"set_list=$set_list<br>";
                $columns_list.= $val;

                if((string)$columns_value[$key]==BASE_NULL)
                    {
                    //echo"NULL<br>";
                    $set_list.=$columns_name[$key]."=".BASE_NULL;
                    }
                elseif ($columns_type[$key] == "date")
                       {
                       //echo"DATE<br>";
                       //$set_list.= $columns_name[$key]." = TO_DATE('".date('Y-m-d H:i', $columns_value[$key])."', 'YYYY-MM-DD HH24:MI')";
                       //echo $set_list;
					   //if($show_ins)
						//	   echo"columns_value[$key]=".$columns_value[$key]."<br>";
					   if($columns_value[$key]!=db_sysdate())
							$set_list.= $columns_name[$key]." = TO_DATE('".date('Y-m-d H:i', $columns_value[$key])."', 'YYYY-MM-DD HH24:MI')";
                        else
                            $set_list.= $columns_name[$key]." = ".$columns_value[$key];
                       }
                elseif($columns_type[$key] == "file" || $columns_type[$key] == "blob" )
                       {
                       //echo"BLOB<br>";
                       $ind_lob++;
                       $lob_val[$ind_lob]=$columns_value[$key];
                       $clob_ar[$ind_lob]= OCINewDescriptor($conn, OCI_D_LOB);
                       $clob_type_ar[$ind_lob]="blob";
                       $set_list.= $columns_name[$key]." =EMPTY_BLOB() ";
                       $ret_var_sql.=($ret_var_sql?", ":"").$columns_name[$key];
                       $ret_val_sql.=($ret_val_sql?", ":"")." :var_$ind_lob";
                       }
                // Если это поле - тип загружаемого файла
                elseif($columns_type[$key] == "varchar" && $columns_name[$key] == $file_variable."_type")
                       {
                       //echo"VARCHAR<br>";
                       // Проверяем есть ли такой тип в справочнике типов, если нет то добавляем.
                       $check_type_sql = "select id as id_file_type, name as name_file_type from ".TABLE_PRE."file_type where name = '".$columns_value[$key]."'";
                       db_getVar($conn, $check_type_sql);
                       if ($GLOBALS['name_file_type'])
                           {
                           $set_list.= $columns_name[$key]." = ".$GLOBALS['id_file_type']."";
                           }
                        else
                            {
                            $inserted_type_id = db_insert($conn, TABLE_PRE."file_type", array(0=>"name", "id"),array(0=>$columns_value[$key], ''), array(0=>"varchar", 'ID'));
                            $set_list.= $columns_name[$key]." = $inserted_type_id";
                            }
                        }
                elseif($columns_type[$key] == "varchar")
                       {
                       //echo"VARCHAR - 2<br>";
                       $set_list.= $columns_name[$key]." = '".db_string_prep($columns_value[$key])."'";
                       }
                elseif($columns_type[$key] == "clob" || $columns_type[$key] == "text")
                       {
                       //echo"CLOB<br>";
                       $ind_lob++;
                       $lob_val[$ind_lob]=$columns_value[$key];
                       $clob_ar[$ind_lob]= OCINewDescriptor($conn, OCI_D_LOB);
                       $clob_type_ar[$ind_lob]="clob";
                       $set_list.= $columns_name[$key]." = EMPTY_CLOB() ";
                       $ret_var_sql.=($ret_var_sql?", ":"").$columns_name[$key];
                       $ret_val_sql.=($ret_val_sql?", ":"")." :var_$ind_lob";
                       }
                else
                    {
                    //echo"OTHER<br>";
                    $set_list.= $columns_name[$key]." = '".$columns_value[$key]."'";
                    }
                }
        $update_sql = "update $table_name set $set_list where $conditions".($ret_var_sql?" RETURNING $ret_var_sql INTO $ret_val_sql":"");
//		if($show_ins)
//			echo"update_sql=$update_sql\r\n<br>\r\n";
        if (sql_log_yes($table_name))
            {
            sql_log($update_sql);
            }
        $upd_q = OCIParse($conn, $update_sql);
		
        foreach($clob_ar as $k_lob=>$v_lob)
                 {
                 OCIBindByName ($upd_q, ":var_$k_lob", &$v_lob, -1, ($clob_type_ar[$k_lob]=="blob"?SQLT_BLOB:OCI_B_CLOB));
                 }
        if(OCIExecute($upd_q, OCI_DEFAULT))
           {
           foreach($clob_ar as $k_lob=>$v_lob)
                 {
                 //echo"$k_lob - $v_lob - ".$lob_val[$k_lob]."<br>";
                 if($clob_type_ar[$k_lob]=="blob")
                     $v_lob->savefile($lob_val[$k_lob]);
                 else
                     $v_lob->save($lob_val[$k_lob]);

                 }
           //OCICommit($conn);
           OCIFreeStatement($upd_q);
           foreach($clob_ar as $k_lob=>$v_lob)
                 {
                 OCIFreeDesc($v_lob);
                 }
           return 1;
           }
        else
            {
            db_exception($update_sql);
            return 0;
            }
}

/**
 * @return void
 * @param unknown $conn
 * @param unknown $table_name
 * @param unknown $columns_name
 * @param unknown $columns_value
 * @param unknown $columns_type
 * @desc Функция вставляет строку в таблицу table_name. В массиве $columns_name
 передаются названия полей, в $columns_value - значения, в $columns_type - типы полей.
 Индексация у этих массивов, естественно, должна совпадать.
*/
function db_insert ($conn, $table_name, $columns_name, $columns_value, $columns_type, $addit="")
{
        global $ERROR, $ERROR_CODE;
         if(is_array($addit))
            {
            extract($addit);
            }
		$clob_ar=array();
        if(!$no_id)
            {
                // Получить ID вставляемой записи
                        $seq_name=$table_name."_id_seq";
                        $sel_next_val_q="select $seq_name.nextval as id FROM dual";
                        //echo"sel_next_val_q=$sel_next_val_q<br>";
                        $res_next_val=db_getArray($conn, $sel_next_val_q, 2);
                        if($res_next_val['ID'])
                           {
                                     $columns_list.='id';
                                     $val_list.=$res_next_val['ID'];
                                 $ret_id=$res_next_val['ID'];
                            }
            }
        foreach ($columns_name as $key => $val)
                 {
                 //echo"<br><br>1 - column_name=".$val.", type=".$columns_type[$key].", val=".$columns_value[$key]."<br>";
                //echo"$key=$val<br>";
                if ($columns_list && $columns_type[$key] != "ID")
                    {
                    $columns_list.= ", ";
                    $val_list.=", ";
                    }
                if ($columns_type[$key] == "ID")
                    {
                       /* //$val_list.= "";
                        $seq_name=$table_name."_".$val."_seq";
                        $sel_next_val_q="select nextval('$seq_name') as id";
                        //echo"sel_next_val_q=$sel_next_val_q<br>";
                        $res_next_val=db_getArray($conn, $sel_next_val_q, 2);
                        //echo "res_next_val=$res_next_val<br>";
                        if($res_next_val['ID'])
                        {
                                $columns_list.=$val;
                                $val_list.=$res_next_val['ID'];
                                $ret_id=$res_next_val['ID'];
                        }
                                                */
                       }
                else
                    {
                    //echo "val = $val";
                    $columns_list.=$val;
                    if((string)$columns_value[$key]==BASE_NULL)
                            {
                            $val_list.=BASE_NULL;
                            }
                    elseif($columns_type[$key] == "date" )
                        {
                        if($columns_value[$key]!=db_sysdate())
                           $val_list.= "TO_DATE('".date('Y-m-d H:i:00', $columns_value[$key])."', 'YYYY-MM-DD HH24:MI:SS')";
                        else
                            $val_list.=  $columns_value[$key];

                        }
                        /*elseif ($val == "arm_id" && $columns_value[$key] == 10)
                        {

                        }*/
                        elseif($columns_type[$key] == "blob")
                               {
                               //echo "Обновляем файл!";
                               // Сохранить файл
                               $ind_lob++;
                               $lob_val[$ind_lob]=$columns_value[$key];
                               $clob_ar[$ind_lob]= OCINewDescriptor($conn, OCI_D_LOB);
                               $clob_type_ar[$ind_lob]="blob";
                               $val_list.="EMPTY_BLOB() ";
                               $ret_var_sql.=($ret_var_sql?", ":"").$columns_name[$key];
                               $ret_val_sql.=($ret_val_sql?", ":"")." :var_$ind_lob";
                               }
                        // Если это поле - тип загружаемого файла
                        elseif ($columns_type[$key] == "varchar" && $columns_name[$key] == $file_variable."_type")
                        {

                                //echo  "Проверяем есть ли такой тип в справочнике типов, если нет то добавляем.";
                                $check_type_sql = "select id as id_file_type, name as name_file_type from ".TABLE_PRE."file_type where name = '".$columns_value[$key]."'";
                                $res = db_getArray($conn, $check_type_sql, 2);
                                if ($res['NAME_FILE_TYPE'])
                                {
                                        $val_list.= "'".$res['ID_FILE_TYPE']."'";
                                        //$set_list.= $columns_name[$key]." = ".$GLOBALS['id_file_type']."";
                                }
                                else
                                {
                                        $inserted_type_id = db_insert($conn, TABLE_PRE."file_type", array(0=>"name", "id"),array(0=>$columns_value[$key], ''), array(0=>"varchar", 'ID'));
                                        $val_list.= "'".$inserted_type_id."'";
                                }
                        }
                        elseif ($columns_type[$key] == "int" && (string)$columns_value[$key]==BASE_TRUE)
                        {
//echo"1.1 - column_name=".$val.", type=".$columns_type[$key].", val=".$columns_value[$key]."<br>";
                                       $val_list.="1";
//echo"1.1 - column_name=".$val.", type=".$columns_type[$key].", val=".$columns_value[$key]."<br>";
                        }
                        elseif ($columns_type[$key] == "int" && (string)$columns_value[$key]==BASE_FALSE)
                        {
                                       $val_list.="0";
                        }
                        elseif ($columns_type[$key] == "int" && (string)$columns_value[$key]=="")
                                {
                                //$val_list.= "'0'";
//echo"2 - column_name=".$val.", type=".$columns_type[$key].", val=".$columns_value[$key]."<br>";
                                $val_list.="null";
                                }
                        elseif ($columns_type[$key] == "int" && (string)$columns_value[$key]=="null")
                                {
//echo"3 - column_name=".$val.", type=".$columns_type[$key].", val=".$columns_value[$key]."<br>";
                                $val_list.="null";
                                }
                        elseif ($columns_type[$key] == "varchar")// || $columns_type[$key] == "text")
                                {
                                $val_list.= "'".db_string_prep($columns_value[$key])."'";
                                }
                        elseif ($columns_type[$key] == "clob" || $columns_type[$key] == "text")
                                {
                                $ind_lob++;
                                $lob_val[$ind_lob]=$columns_value[$key];
                                $clob_ar[$ind_lob]= OCINewDescriptor($conn, OCI_D_LOB);
                                $clob_type_ar[$ind_lob]="clob";
                                $val_list.="EMPTY_CLOB() ";
                                $ret_var_sql.=($ret_var_sql?", ":"").$columns_name[$key];
                                $ret_val_sql.=($ret_val_sql?", ":"")." :var_$ind_lob";
                                }
                        else
                        {
//echo"5 - column_name=".$val.", type=".$columns_type[$key].", val=".$columns_value[$key]."<br>";
                                $val_list.= "'".$columns_value[$key]."'";
                        }
                }
        }
        $insert_sql = "insert into $table_name ($columns_list) values ($val_list)".($ret_var_sql?" RETURNING $ret_var_sql INTO $ret_val_sql":"");
        if($show_ins)
           echo"insert_sql=$insert_sql\r\n<br>\r\n";
        if (sql_log_yes($table_name))
            {
            sql_log($insert_sql);
            }
        $ins_q = @OCIParse($conn, $insert_sql);
		
        foreach($clob_ar as $k_lob=>$v_lob)
                 {
                 $bind=OCIBindByName ($ins_q, ":var_$k_lob", &$v_lob, -1, ($clob_type_ar[$k_lob]=="blob"?SQLT_BLOB:OCI_B_CLOB) );
                 //echo"bind=$bind<br>";
                 }
        if(OCIExecute($ins_q, OCI_DEFAULT))
           {
           foreach($clob_ar as $k_lob=>$v_lob)
                 {
                 //echo"$k_lob - $v_lob - ".$lob_val[$k_lob]."-".$clob_type_ar[$k_lob]."<br>";
                 if($clob_type_ar[$k_lob]=="blob")
                     $save=$v_lob->savefile($lob_val[$k_lob]);
                 else
                     $save=$v_lob->save($lob_val[$k_lob]);
                 //echo"save=$save<br>";
                 }
           OCIFreeStatement($ins_q);



           foreach($clob_ar as $k_lob=>$v_lob)
                 {
                 OCIFreeDesc($v_lob);
                 }
           //OCICommit($conn);
           if($no_id || !$ret_id)
              {
              $ret=1;
              }
           else
            $ret=$ret_id;
           //echo"ret=$ret<br>";
           return $ret;
           }
        else
            {
            db_exception($insert_sql);
            return 0;
            }
}

/**

 * @return void

 * @param unknown $par

 * @desc Выводит на экран сообщение о том, что произошла ошибка БД

*/

function exception ($par)
{
}

//функция возвращает функцию времени базы
function db_sysdate()
         {
         return "SYSDATE";
         }
//функция возвращает строку для запроса последовательности
function db_nextval($seq_name)
{
        $ret="$seq_name.nextval";
        return $ret;
}
//функция возвращает строку для запроса текущего значения последовательности
function db_currval($seq_name)
{
        $ret="$seq_name.currval";
        return $ret;
}
/**

 * @return unknown

 * @param unknown $conn

 * @param unknown $file_link

 * @param unknown $file_size

 * @desc Получает бинарный код файла по ссылке и размеру файла

*/

function db_get_file_data ($conn, $file_link, $file_size=0)
{
return $file_link;
}
//функция преобразует дату - mktime в формат запроса, понимаегого базой
function db_date($date_mk, $date_f="", $znak="")
         {
		//echo"db_date - $date_mk, ".date('Y-m-d H:i:00', $date_mk)."\r\n";
         if($date_f=="Date")
             $ret="TO_DATE('".date('Y-m-d', $date_mk)."', 'YYYY-MM-DD')";
         else
             $ret="TO_DATE('".date('Y-m-d H:i:00', $date_mk)."', 'YYYY-MM-DD HH24:MI:SS')";
         if($znak=="<")
            {
            $ret.="+1";
            }
		//echo"ret=$ret\r\n";
         return $ret;
         }

//возвращает имя функции - nvl, COALESCE

function db_isnull()
         {
         return "NVL";
         }
//начало транзакции
function db_begin($conn)
         {
         //$res=db_query($conn, "begin;");
         //echo"begin<br>";

         }
function db_commit($conn)
         {
         //$res=db_query($conn, "commit;");
         //echo"commit <br>";
         OCICommit($conn);
         }
function db_rollback($conn)
         {
         //$res=db_query($conn, "rollback;");
         //echo"rollback<br>";
		OCIRollback($conn);
         }
//формирует правильный запрос для получения даты
function db_date_char($col, $date_format="")
         {
        if(!$date_format)
            $date_format=DATE_FORMAT_SEL;
		elseif($date_format=="Date")
			$date_format="YYYY-MM-DD";
         $ret="TO_CHAR($col, '".$date_format."')";
         return $ret;
         }
function db_procedure_execute($conn, $string)
         {
         $proc_q="begin $string; end;";
         $res=db_query($conn, $proc_q);
         if(!$res)
             echo"Error - proc_q=$proc_q, $res<br>";
         //else
         //    echo"Ok - proc_q=$proc_q, $res<br>";
         return $res;
         }

//заменяет, при необходимости, спецсимволы
function db_string_prep($text)
         {
         $ret=str_replace("'", "''", $text);
         return $ret;
         }
//===================================================================
//парсим запрос
function db_parse($conn, $sel)
         {
         $res = OCIParse($conn, $sel);
         return $res;
         }
//---------------------------------------------------
//принимает указатель после parse, проводит подстановку значений переменных (аналог OCIBindByName)
function db_BindByName($query_prep, $ph_name, $var, $length=-1, $type="", $addarray=array())
        {
        if(!$type)
                $ans=OCIBindByName($query_prep, $ph_name, &$var, $length);
        else
                $ans=OCIBindByName($query_prep, $ph_name, &$var, $length, $type);
        return $ans;
        }
//----------------------------------------------------------
function db_execute($stmt, $data=array())
        {
        global $db_oci8_pieces;
        while (list($i, $value) = each($data))
                {
                $db_oci8_pieces[$stmt][$i] = $data[$i];
                }
        $ret = OCIExecute($stmt, OCI_DEFAULT);
        if (!$ret)
                {
                db_check_errors($php_errormsg);
                return false;
                }
        return true;
        }

//функция возвращает правильную запись функции CASE (PostgresQL) или DECODE (Oracle)
//col - что сравниваем, ar - массив - сравниваемое значение=>возвращаемое значение, $other - возвращаемое значение для остальных случаев
function db_case($col, $ar, $over="", $addit_ar=array())
	{
	extract($addit_ar);
	//если int_ret - возвращать как цифры
    $ret="DECODE($col";
	if($int_ret)
		{
        foreach($ar as $k=>$v)
                 $ret.=", '$k', $v";
        if(isset($over) && strval($over)!="")
            {
            if(strval($over)!="COLUMN")
               $ret.=", $over";
            else
               $ret.=", $col";
            }
		}
	else
		{
        foreach($ar as $k=>$v)
                 $ret.=", '$k', '$v'";
        if(isset($over) && strval($over)!="")
            {
            if(strval($over)!="COLUMN")
               $ret.=", '$over'";
            else
               $ret.=", $col";
            }
		}
    $ret .= ")";
    return $ret;
	}
//подготовка outer join
function db_outer_join($col_list, $tables, $join_on, $join_type, $where="", $order="")
         {

         foreach($tables as $k=>$v)
                 {
				//echo"$k - $v, ".$join_on[$k-1]."<br>";
                 if(!$join_on[$k-1])
                     $table_list.=($table_list?", ":" ").$v;
                 //elseif($join_on[$k] )
                 //       {
                 //       $table_list.=" $k $v";
                  //      }
                 if($join_on[$k])//если нужно делать join
                    {
                    $table_list.=" ".$join_type[$k]." OUTER JOIN ".$tables[$k+1]." ON ".$join_on[$k];
                    }
                 }
         $sel="SELECT $col_list FROM $table_list".($where?" WHERE ".$where:"").($order?" ORDER BY ".$order:"");
         return $sel;
         }
/**
* @return void
* @param unknown $par
* @desc Выводит на экран сообщение о том, что произошла ошибка БД
*/
function db_exception ($par)
{
        //echo "Error!!!";
        //echo $par;

}
//функция получает два запроса и возвращает один - union
function db_union($sel_q1, $sel_q2)
{
        $ret_q="($sel_q1) union all ($sel_q2)";
        return $ret_q;
}
//подготавливает число для вычитания/прибавления к дате
function db_oper_min($min)
         {
         $ret="$min/(24*60)";
         return $ret;
         }
//для удобства формирования бинда
function db_for_bind($name, $val, $length="")
	{
	$ret=array("name"=>":".$name, "val"=>$val, "length"=>($length?$length:strlen($val)));
	return $ret;
	}
//функция создания запроса без таблицы (констант, даты и т.п.)
function sel_dual($conn, $vars)
	{
	$sel_q="SELECT $vars FROM dual";
	return $sel_q;
	}
?>
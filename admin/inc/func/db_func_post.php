<?
// Функция возвращает идентификатор коннекта к БД
function db_connect($database, $user, $password, $host)
{
        if($ret = pg_connect("host=$host dbname=$database user=$user password=$password"))
			{
			//echo"ret=$ret<br>(host=$host dbname=$database user=$user password=$password)<br>";
			return $ret;
			}
		else
			{
			ignore_user_abort(true);
			$string="sudo -u postgres /t3/admin/inc/exec/pg_restart.sh >>/tmp/pg_restart.sh.log 2>>/tmp/pg_restart.sh.err";
			//$string="sudo -u postgres /tmp/test_grant_pg.sh >>/tmp/pg_restart.sh.log 2>>/tmp/pg_restart.sh.err";
			$ret=exec($string, $output, $return_code);
			//$output=shell_exec($string);
			if(!$return_code)
				{
				//$ret = pg_connect("host=$host dbname=$database user=$user password=$password");
				//return $ret;
				//global $REQUEST_URI;
				//header("Location: $REQUEST_URI");
				}
			else
				{
				echo"string=$string<br><pre>output=".print_r($output)."<br>return_code=$return_code<br>";
				}
			}
}
// Возвращает последнюю ошибку от сервера БД если параметр пустой

function db_check_errors($errormsg="")
{
        if ($errormsg == "")
        echo pg_errormessage($errormsg);
}

//-----------------------------------------------------
//для обычных запросов
function db_query($conn, $query)
{
        $start_time=mktime();
        $stmt = @pg_query($conn, $query);
        //echo $stmt;
        //echo"query=$query, conn=$conn, stmt=$stmt<br>";
        if(defined("SQL_TIME_MAX") && (mktime()-$start_time)>=SQL_TIME_MAX)
            echo"<br>ATTENTION! query=$query - ".(mktime()-$start_time)."<br><br>";
        if($stmt)
                return $stmt;
        else
        {
                db_exception($query);
                return 0;
        }
}
//-----------------------------------------------------------
//получение результатов обычных запросов
function db_fetch_row($stmt, $row=0, $val=2)
{
//error_reporting  (!E_WARNING);
        if($ar_view==1)
        $ar_view_var=="PGSQL_ASSOC";
        else
        $ar_view_var=="PGSQL_BOTH";

        $cols = pg_fetch_array($stmt);
        foreach($cols as $k=>$v)
                {
                //echo "$k = $v <br>";
                if(is_object($v))
                   $cols[$k]=$v->load();
                $ret[strtoupper($k)]=$cols[$k];
                }
        return $ret;
}
function db_select($conn, $table_name, $columns, $conditions="", $addit="")
{
        if ($conditions)
        $conditions= " where ".$conditions;
        $sel_q="select  $columns from $table_name $conditions $addit";
        //echo $sel_q;
        $stmt = @pg_exec($conn, $sel_q);
        if (!$stmt)
        {
                db_exception($sel_q);
                return false;
        }
        $num_rows = pg_num_rows($stmt);
        for($i=0; $i<$num_rows; $i++)
        {
                $cols = pg_fetch_array($stmt, $i, PGSQL_ASSOC);
                foreach($cols as $k=>$v)
                {
                        if(is_object($v))
                        $cols[$k]=$v->load();
                        $cols[strtoupper($k)] = $cols[$k];
                }
                $res[$i]=$cols;
        }
        return $res;
}

//------------------------------------------------------------------------------
//функция принимает запрос и возвращает массив результатов
// $object_arr - массив колонок имеющих тип OID (файл)
function db_getArray($conn, $sel_q, $ar=1, $extr_ar=array())
         {
		extract($extr_ar);
		//если ret_sql=1 - возвращать приготовленный sql
		$res_ar=array();
         //echo __LINE__." sel_q=$sel_q<br>";
		if($bind_ar)//надо биндить запрос
				{
				//print_r($bind_ar);
				//echo"sel_q=$sel_q<br>";
				foreach($bind_ar as $k_b=>$v_b)
					{
					if(is_array($v_b['val']))
						{
						//echo __LINE__." ". $v_b['name'].", ".$v_b['val']."<br>";
						if(!isset($val_ar[$k_b]))
							{
							foreach($v_b['val'] as $key=>$val)
								{
								$res_tmp=db_getArray($conn, $sel_q, 1, array_merge($extr_ar, array("val_ar"=> array($k_b=>$key))));
								$res=array_merge($res, $res_tmp);
								//echo __LINE__."<pre>";
								//print_r(array("val_ar"=> array($k_b=>$key)));
								//echo __LINE__."res_tmp=<pre>";
								//print_r($res_tmp);
								//echo __LINE__."res=<pre>";
								//print_r($res);
								}
							}
						else
							{
							//echo __LINE__." val=".$v_b['val'][$val_ar[$k_b]]."<br>";
							$sel_q=str_replace($v_b['name'], $v_b['val'][$val_ar[$k_b]], $sel_q);
							}
						}
					else
						$sel_q=str_replace($v_b['name'], $v_b['val'], $sel_q);

					}//foreach($bind_ar as $k_b=>$v_b)
				//if(is_array($val_ar))
					//echo __LINE__." sel_q=$sel_q<br>";
				}
		if($ret_sql==1)
			return $sel_q;
		if($sel_q)
			{
			$stmt=@pg_exec($conn, $sel_q);
			if (!$stmt)
				{
				//echo"Error($conn) - sel_q=$sel_q<br>";
				return false;
				}
			else
				{
				for($i=0; $res=db_fetch_row($stmt); $i++)
					{
					if($ar==1)
						$res_ar[$i]=$res;
					elseif($i==0)
                       return $res;
					}
				}
			}
		if($res)
			{
			return $res;
			}
         return $res_ar;
        }
//-----------------------------------------------------------------------------------------------------
//функция принимает запрос и преобразует результаты первой строки в виде глобальных переменных,
//например $res['NAME'] - это $name
function db_getVar($conn, $sel_q)
{
        $stmt = @pg_exec($conn, $sel_q);
        if (!$stmt)
             {
             return false;
             }
        else
            {
                if($res=db_fetch_row($stmt))
                {
                        foreach($res as $k=>$v)
                        {
                                if(is_object($v))
                                {
                                        $v=$v->load();
                                }
                                //echo "$k -> $v";
                                $GLOBALS[strtolower($k)]=$v;
                        }
                        return 1;
                }
            }
}
//
function db_disconnect($conn)
{
        $ret = pg_close($conn);
        return $ret;

}
function db_limit($sel_q, $start_row, $count_row)
{
        $sel_ret="$sel_q LIMIT $count_row OFFSET $start_row";
        return $sel_ret;
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

        if(!$no_id)
            {
                // Получить ID вставляемой записи
                        $seq_name=$table_name."_id_seq";
                        $sel_next_val_q="select nextval('$seq_name') as id";
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
                    elseif  ($columns_type[$key] == "date" )
                        {
                        if($columns_value[$key]!=db_sysdate())
							{
							//echo"date_save=".date('Y-m-d H:i:00', $columns_value[$key])."<br>";
							//$val_list.= "'".(date("Y", $columns_value[$key])).date('-m-d H:i:00', $columns_value[$key])."'";
							$val_list.="'".$columns_value[$key]."'";
							}
                        else
                            $val_list.=  $columns_value[$key];

                        }
                        /*elseif ($val == "arm_id" && $columns_value[$key] == 10)
                        {

                        }*/
                    elseif ($columns_type[$key] == "file" || $columns_type[$key] == "blob" )
                        {
                                //echo "Обновляем файл!";
                                // Сохранить файл
								//flush("insert - file - ".$columns_value[$key]."<br>");
								/*
                                $fp = fopen($columns_value[$key], "r");
                                //$buffer = fread($fp, filesize($columns_value[$key]));
                                //fclose($fp);
								//echo"fp=$fp<br>";
								//echo"buffer=$buffer<br>";
								//flush(__LINE__."buffer len=".length($buffer)."<br>");
                                // --------- CREATE - INSERT OID ---
                                pg_exec($conn, "begin");
                                $oid = pg_lo_create($conn);
                                //echo "OID = $oid, conn=$conn<br>";
                                $handle = pg_loopen ($conn, $oid, "w");
								//echo"handle=$handle<br>";
                                //pg_lowrite ($handle, $buffer);
								pg_lowrite ($handle, fread($fp, filesize($columns_value[$key])));
								//echo"pg_lowrite<br>";
                                pg_loclose ($handle);
								//echo"pg_loclose<br>";
                                pg_exec($conn, "commit");
								fclose($fp);*/
                                //echo $columns_name[$key];
								pg_query($conn, "begin");
								$oid = pg_lo_import($conn, $columns_value[$key]);
								pg_query($conn, "commit");
								//echo "OID = $oid, conn=$conn<br>";
                                $val_list.= $oid;
                                //echo "OID = $oid<br>";
                                $file_variable = $columns_name[$key];
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
					elseif ($columns_type[$key] == "varchar" || $columns_type[$key] == "text" || $columns_type[$key] == "clob")
                        {
                                $val_list.= "'".db_string_prep($columns_value[$key])."'";
								//echo"val_list=$val_list<br>\r\n";
                        }

					else
                        {
//echo"5 - column_name=".$val.", type=".$columns_type[$key].", val=".$columns_value[$key]."<br>";
                                $val_list.= "'".$columns_value[$key]."'";
                        }
                }
        }
        $insert_sql = "insert into $table_name ($columns_list) values ($val_list)";

        $ins_q = db_query($conn, $insert_sql);
        if($show_ins)
           echo"$insert_sql, $ins_q\r\n<br>";
        //echo pg_last_error();
        //echo pg_errormessage($insert_sql);
        //echo $ins_q;
        if (!$ins_q)
				{
				db_exception( "$insert_sql - $ins_q");
                return 0;
				}
        else
            {
            if(sql_log_yes($table_name))// && strtoupper($table_name)!=TABLE_PRE."LOGS" && strtoupper($table_name)!=TABLE_PRE."LOGS")
                {
                sql_log("insert into $table_name ($columns_list) values ($val_list)");
                }
        if($no_id || !$ret_id)
           $ret=1;
        else
            $ret=$ret_id;
        //echo"ret=$ret<br>";
        return $ret;
        }

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

function db_update ($conn, $table_name, $columns_name, $columns_value, $columns_type, $conditions)
{
	//echo "db_update:";
        /*
        foreach ($columns_name as $k1=>$v1)
        {
        echo $columns_name[$k1]." - ".$columns_value[$k1]." - ".$columns_type[$k1];
        br();
        }
        */

        foreach ($columns_name as $key => $val)
                 {
                 if ($set_list && $columns_value[$key] != "-2")
                     {
                     $set_list.= ", ";
                     }
                $columns_list.= $val;
                if((string)$columns_value[$key]==BASE_NULL)
                            {
                            $set_list.=$columns_name[$key]."=".BASE_NULL;
                            }
                elseif  ($columns_type[$key] == "date")
                         {
/*                       if($columns_value[$key]!=db_sysdate())
                                               $set_list.= $columns_name[$key]." = '".date('Y-m-d H:i:00', $columns_value[$key])."'";
                                else
                                        $set_list.=  $columns_name[$key]." = '".$columns_value[$key]."'"; */
                        if($columns_value[$key]!=db_sysdate())
							{
							//echo"date_save=".(date("Y")).date('m-d H:i:00', $columns_value[$key])."<br>";
							/*
							$set_list.= $columns_name[$key]." = '".(date("Y", $columns_value[$key])).date('-m-d H:i:00', $columns_value[$key])."'";
							*/
							if(strpos(' '.$columns_value[$key], 'timestamp')>0)
								$set_list.= $columns_name[$key]." = ".$columns_value[$key]."";
							else
								$set_list.= $columns_name[$key]." = '".$columns_value[$key]."'";
							}
                        else
                            $set_list.= $columns_name[$key]." = ". $columns_value[$key];

                        //$set_list.= $columns_name[$key]." = '".date('Y-m-d H:i:00', $columns_value[$key])."'";
                        //echo $set_list;
                        }
                elseif ($columns_type[$key] == "file" || $columns_type[$key] == "blob" )
                {
                        db_getVar($conn,"select ".$columns_name[$key]." from $table_name where $conditions");
                        $old_file_oid = $GLOBALS[strtolower($columns_name[$key])];
                        if ($columns_value[$key] == -2)
                        {
                                //echo "Обновить имя файла !";
                        }
                        elseif ($columns_value[$key] == -1)
                        {
                                //echo "Открепить файл !";
                                if ($old_file_oid)
                                {
                                        pg_exec($conn, "begin");
                                        pg_lo_unlink($conn, $old_file_oid);
                                        pg_exec($conn, "commit");
                                        $set_list.= $columns_name[$key]." = 0";
                                }
                        }
                        else
                        {
                                if ($old_file_oid)
                                {
                                        pg_exec($conn, "begin");
                                        pg_lo_unlink($conn, $old_file_oid);
                                        pg_exec($conn, "commit");
                                }
                                //echo "Обновляем файл!";
                                // Сохранить файл
								/*
                                $fp = fopen($columns_value[$key], "r");
                                $buffer = fread($fp, filesize($columns_value[$key]));
                                fclose($fp);
                                // --------- CREATE - INSERT OID ---
                                pg_exec($conn, "begin");
                                $oid = pg_locreate($conn);
                                $handle = pg_loopen ($conn, $oid, "w");
                                pg_lowrite ($handle, $buffer);
                                pg_loclose ($handle);
                                pg_exec($conn, "commit");
                                //echo $columns_name[$key];
								*/
								pg_query($conn, "begin");
								$oid = pg_lo_import($conn, $columns_value[$key]);
								pg_query($conn, "commit");
                                $set_list.= $columns_name[$key]." = ".$oid."";
                                //echo "OID = $oid";
                                $file_variable = $columns_name[$key];
                        }
                }
                // Если это поле - тип загружаемого файла
                elseif ($columns_type[$key] == "varchar" && $columns_name[$key] == $file_variable."_type")
                {

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
                /*
                elseif ($columns_type[$key] == "int" && $columns_value[$key]=="")
                {
                        //$set_list.= $columns_name[$key]." = '0'";
                        $set_list.= $columns_name[$key]." = null";//Иначе нельзя добавить статику с пустым разделом навигации
                }
                elseif ($columns_type[$key] == "int" && $columns_value[$key]=="null")
                {
                        $set_list.= $columns_name[$key]." = null";
                }
                */
                elseif ($columns_type[$key] == "varchar" || $columns_type[$key] == "text" || $columns_type[$key] == "clob")
                {

                        $set_list.= $columns_name[$key]." = '".db_string_prep($columns_value[$key])."'";
                }
                else
                {

                        $set_list.= $columns_name[$key]." = '".$columns_value[$key]."'";
                }

        }
        $update_sql = "update $table_name set $set_list where $conditions";
        if (sql_log_yes($table_name))
            {
            sql_log($update_sql);
            }
        $upd_q = db_query($conn, $update_sql);
//        echo "update_sql = $update_sql - $upd_q\r\n<br>";
        if (!$upd_q)
        {
                db_exception($update_sql);
                return 0;
        }
        else
        {
//                if ($table_name == "ti_columns")
  //                      echo $update_sql;
                return  $upd_q;
        }
}


/**
* @return void
* @param unknown $par
* @desc Выводит на экран сообщение о том, что произошла ошибка БД
*/
function db_exception ($par)
{
        //echo "Error!!!";
        echo $par;

}
//функция получает два запроса и возвращает один - union
function db_union($sel_q1, $sel_q2)
{
        $ret_q="($sel_q1) union all ($sel_q2)";
        return $ret_q;
}
//функция возвращает функцию времени базы
function db_sysdate()
{
        return "now()";
}
//функция возвращает строку для запроса последовательности
function db_nextval($seq_name)
{
        $ret="nextval('$seq_name')";
        return $ret;
}
//функция возвращает строку для запроса текущего значения последовательности
function db_currval($seq_name)
{
        $ret="currval('$seq_name')";
        return $ret;
}
//функция возвращает следущее значение последовательности
function db_next_seq($conn, $seq_name)
{
        $sel_q="select nextval('$seq_name') as n";
        $res=db_getArray($conn, $sel_q, 2 );
        return $res['N'];
}
/**
* @return unknown
* @param unknown $conn
* @param unknown $file_link
* @param unknown $file_size
* @desc Получает бинарный код файла по ссылке и размеру файла
*/
function db_get_file_data ($conn, $lo_oid, $file_size=0)
{
        //echo"$lo_oid, $file_size<br>";
		if($file_size>0)
			ini_set('memory_limit', '100M');
        settype($lo_oid, "integer");
        pg_exec($conn, "begin");
        $handle_lo = pg_lo_open($conn,$lo_oid,"r") or die("<h1>Error.. can't get handle</h1>");
        $file_img = pg_lo_read($handle_lo,$file_size) or die("<h1>Error, can't read large object.</h1>");
        pg_exec($conn, "commit");
		pg_lo_close($handle_lo);
        return $file_img;
}
//выводим сразу файл в output
function db_output_file_data ($conn, $lo_oid)
{
   pg_query($conn, "begin");
   $handle = pg_lo_open($conn, $lo_oid, "r");
   pg_lo_read_all($handle);
   pg_query($conn, "commit");
}
/*
//функция преобразует дату - mktime в формат запроса, понимаегого базой
function db_date($date_mk, $date_f="")
         {
         $ret="timestamp '".(date('Y', $date_mk)).date('-m-d H:i:00', $date_mk)."'";
         //echo"date_mk=$date_mk, ret=$ret<br>";
         return $ret;
         }
		 */
//функция преобразует дату  в формат запроса, понимаегого базой
function db_date($date, $date_f="")
         {
		if(ereg("^[0-9]{5,}$", $date))
			{
			$ret="timestamp '".(date('Y', $date)).date('-m-d H:i:00', $date)."'";
			//echo"date_mk - ".$date."<br>";
			}
		else
			{
			$ret="timestamp '".$date."'";
			//echo"date<br>";
			}
         //echo"date_mk=$date_mk, ret=$ret<br>";
         return $ret;
         }

//функция преобразует дату - mktime в формат запроса, понимаегого базой - для препарации
function db_date_prep($var_name)
         {
         $ret="timestamp '".$var_name."'";
         //echo"date_mk=$date_mk, ret=$ret<br>";
         return $ret;
         }

//возвращает имя функции - nvl, COALESCE
function db_isnull()
         {
         return "COALESCE";
         }
//подготавливает число для вычитания/прибавления к дате
function db_oper_min($min)
         {
         $ret="interval '$min minute'";
         return $ret;
         }
//заменяет, при необходимости, спецсимволы
function db_string_prep($text)
         {
         //$ret=ereg_replace("'", "''", $text);
         $ret=addslashes($text);
         return $ret;
         }
//операции с датой - округление
function db_trunc($val, $column)
         {
         $ret="date_trunc('$val', $column)";
         return $ret;
         }
//функция возвращает правильную запись функции CASE (PostgresQL) или DECODE (Oracle)
//col - что сравниваем, ar - массив - сравниваемое значение=>возвращаемое значение, $other - возвращаемое значение для остальных случаев

function db_case($col, $ar, $over="", $addit_ar=array())
	{
	extract($addit_ar);
	//если int_ret - возвращать как цифры
    $ret="CASE";
	if($int_ret)
		{
	    foreach($ar as $k=>$v)
			$ret.=" WHEN $col='$k' THEN $v";
        if(isset($over) && strval($over)!="")
            {
            if(strval($over)!="COLUMN")
               $ret.=" ELSE $over";
            else
               $ret.=" ELSE $col";
            }
		}
	else
		{
	    foreach($ar as $k=>$v)
			$ret.=" WHEN $col='$k' THEN '$v'";
        if(isset($over) && strval($over)!="")
            {
            if(strval($over)!="COLUMN")
               $ret.=" ELSE '$over'";
            else
               $ret.=" ELSE $col";
            }
		}
	$ret.=" END ";
    return $ret;
    }
//начало транзакции
function db_begin($conn)
         {
		//sql_log("begin");
         $res=db_query($conn, "begin;");
         //echo"begin<br>";
         }
function db_commit($conn)
         {
		//sql_log("commit");
         $res=db_query($conn, "commit;");
         //echo"commit - $res<br>";
         }
function db_rollback($conn)
         {
         $res=db_query($conn, "rollback;");
         //echo"rollback - $res<br>";
         }
//формирует правильный запрос для получения даты
function db_date_char($col, $date_format="")
         {
         $ret=$col;
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
//для удобства формирования бинда (от Oracle)
function db_for_bind($name, $val, $length="")
	{
	$ret=array("name"=>":".$name, "val"=>$val, "length"=>($length?$length:strlen($val)));
	return $ret;
	}
//функция создания запроса без таблицы (констант, даты и т.п.)
function sel_dual($conn, $vars)
	{
	$sel_q="SELECT $vars";
	return $sel_q;
	}
function low_user()
{
		$get_prior=" ps ax  | grep 'pst_low' |grep 'S '";
		$exec_prior=shell_exec($get_prior);
		//echo($exec_prior);
		$prior_ar=explode("\n", $exec_prior);
		//print_r($prior_ar);
		foreach($prior_ar as $prior_str)
		  {
			if(strpos($prior_str, " postgres"))
			  {
				$prior_str=trim($prior_str);
				$prior_s_ar=split("[ ]+", $prior_str);
				//print_r($prior_s_ar);
				exec("renice 19 -p ".$prior_s_ar[0]);
			  }
		  }
}
?>

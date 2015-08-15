<?
// Функции изменения структуры БД
// Получаеи информацию о колонке по id
function target_column_info ($conn, $id)
{
                $target_column_info_sql = "
                select
                        c.name as colname,
                        t.name as tabname,
                        ct.name as type_name
                from
                        ".TABLE_PRE."columns c,
                        ".TABLE_PRE."tables t,
                        ".TABLE_PRE."column_type ct
                where
                        c.id = $id and
                        c.table_id = t.id and
                        ct.id = c.type_id
                ";
                //echo $target_column_info_sql;
                $target_column_info = db_getArray($conn, $target_column_info_sql,2);
                return $target_column_info;
}
//===========================================================================================
//Добавляем колонку в таблицу
function alter_table_add_column ($conn, $column_id)
{
        //echo"alter_table_add_column<br>";
        //return 1;
        $add_column_sql=array();
        $sel_info="SELECT c.name, c.about, c.column_length, c.null_value, c.default_value, c.ref_column_id, c.fk_type_id,
                          t.id as table_id, t.name as table_name, t.main, t.seq_name, ct.name as column_type
                          FROM ".TABLE_PRE."columns c, ".TABLE_PRE."tables t, ".TABLE_PRE."column_types ct
                          WHERE c.table_id=t.id and c.column_type_id=ct.id and c.id=$column_id ";
        //echo"sel_info=$sel_info<br>";
        $res_info=db_getArray($conn, $sel_info, 2);
        $add_column_sql[0]="ALTER TABLE ".$res_info['TABLE_NAME']." add ".$res_info['NAME']." ".$res_info['COLUMN_TYPE'];
        if($res_info['COLUMN_LENGTH'])
               $add_column_sql[0].= "(".$res_info['COLUMN_LENGTH'].(eregi("char", $res_info['COLUMN_TYPE'])?"char":"").")";


        //если превичный ключ - создаем sequence
        if (strtoupper($res_info['NAME']) == "ID")
        {
                $seq_name=($res_info['SEQ_NAME']?$res_info['SEQ_NAME']:create_name($res_info['TABLE_NAME'], "_ID_SEQ"));
                if(!$res_info['SEQ_NAME'])
                    {
                    $upd_seq_q="UPDATE ".TABLE_PRE."tables set seq_name='$seq_name' where id=".$res_info['TABLE_ID'];
                    $upd_seq=db_query($conn, $upd_seq_q);
                    if(!$upd_seq)
                        {
                        //echo"upd_seq_q=$upd_seq_q<br>";
                        db_rollback($conn);
                        return 0;
                        }
                    $add_column_sql[]="CREATE SEQUENCE $seq_name";
                    }
                $add_column_sql[]=create_trigger_for_pk($res_info['TABLE_NAME']);
                $add_pk_sql= "ALTER TABLE ".$res_info['TABLE_NAME']." ADD CONSTRAINT ".create_name($res_info['TABLE_NAME'], "_pk")." PRIMARY KEY (id)";
        }
        else
            {
            if ($res_info['DEFAULT_VALUE'])
                 {
                 $add_column_sql[]="ALTER TABLE ".$res_info['TABLE_NAME']."  ALTER COLUMN ".$res_info['NAME']." SET DEFAULT ".$res_info['DEFAULT_VALUE'];
                 }
            //echo $add_column_sql;
            }
        //$add_column_sql.=$add_column_sql_add;
        //если не нулевое значение
        if (!$res_info['NULL_VALUE'])
             {
             if(isset($res_info['DEFAULT_VALUE']))
                $add_column_sql[]="UPDATE ".$res_info['TABLE_NAME']." SET ".$res_info['NAME']." = '".$res_info['DEFAULT_VALUE']."'";
             $add_column_sql[]="ALTER TABLE ".$res_info['TABLE_NAME']." MODIFY ( ".$res_info['NAME']." NOT NULL)";
             }

        if ($res_info['ABOUT'])
                   {
                   $add_column_sql[]="COMMENT ON COLUMN ".$res_info['TABLE_NAME'].".".$res_info['NAME']." IS '".$res_info['ABOUT']."'";
                   }
        //echo"2 alter_table_add_column<br>";
        // Связь Вторичный ключ
        if ($res_info['REF_COLUMN_ID'])
            {
            if($res_info['FK_TYPE_ID'])
               {
               $ref_type=get_byId($conn, TABLE_PRE."fk_types", $res_info['FK_TYPE_ID'], "code_name");
               }
            //echo"3 alter_table_add_column<br>";
            $ref_sel="SELECT c.NAME, t.NAME as table_name FROM ".TABLE_PRE."columns c, ".TABLE_PRE."tables t
                               WHERE c.table_id=t.id AND c.id=".$res_info['REF_COLUMN_ID'];
            $ref_res=db_getArray($conn, $ref_sel, 2);
            $add_col_ref="ALTER TABLE ".$res_info['TABLE_NAME']." ADD CONSTRAINT ".create_fk_name($res_info['TABLE_NAME'], $res_info['NAME'])." FOREIGN KEY (".$res_info['NAME'].") REFERENCES ".$ref_res['TABLE_NAME']." (".$ref_res['NAME'].") ";
            if($ref_type['CODE_NAME']=="cascade")//если надо удалять каскадно
             $add_col_ref.=" ON DELETE CASCADE";
            elseif($ref_type['CODE_NAME']=="set_null")
             $add_col_ref.=" ON DELETE SET NULL";
            $add_column_sql[]=$add_col_ref;
            $add_column_sql[]="CREATE INDEX ".create_ind_name($res_info['TABLE_NAME'], $res_info['NAME'])." ON  ".$res_info['TABLE_NAME']."(".$res_info['NAME'].")";
            }
        //echo"3 alter_table_add_column<br>";
//        echo "$add_column_sql<br>";
//        exit;
          if($add_pk_sql)
             $add_column_sql[]=$add_pk_sql;
        $res_count=0;
        foreach($add_column_sql as $k=>$v)
                {
                //echo"$k=$v<br>";
                //sql_log($v);
                $res=db_query($conn, $v);
                //echo"res=$res<br>";
                //echo"4 alter_table_add_column<br>";
                sql_log($v);
                if($res)
                    $res_count++;
				else
					break;
                }
       //echo"res_count=$res_count<br>";
        //echo"alter_table_add_column<br>";
        if($res_count<count($add_column_sql))
            {
			if($res_count)//колонка добавилась
				{
				$drop_col_q="ALTER TABLE ".$res_info['TABLE_NAME']." DROP ".$res_info['NAME'];
				$drop_col=db_query($conn, $drop_col_q);
				//alter_table_del_col ($conn, $column_id);
				}
			$del_q="delete from ".TABLE_PRE."columns WHERE id=$column_id";
			$del=db_query($conn, $del_q);
			db_commit($conn);

            return 0;
            }
        else
            return 1;
}
//Удаление колонки
function alter_table_del_col ($conn, $column_id)
{
        global $ERROR, $ERROR_CODE;
        $sel_info="SELECT c.name, c.about, c.column_length, c.null_value, c.default_value, c.ref_column_id, t.name as table_name, t.main, ct.name as column_type
                          FROM ".TABLE_PRE."columns c, ".TABLE_PRE."tables t, ".TABLE_PRE."column_types ct
                          WHERE c.table_id=t.id and c.column_type_id=ct.id and c.id=$column_id";
        //echo"sel_info=$sel_info<br>";
        $res_info=db_getArray($conn, $sel_info, 2);

        $delete_column_sql[]="ALTER TABLE ".$res_info['TABLE_NAME']." DROP (".$res_info['NAME'].")";

        if(strtoupper($res_info['NAME']) == "ID")
                {
                $delete_column_sql[]="drop sequence ".$res_info['TABLE_NAME']."_id_seq;";
                }
        $del_count=0;
        foreach($delete_column_sql as $k=>$v)
                {
                sql_log($v);
                $del=db_query($conn, $v);
                if($del)
                   $del_count++;
                }
        //echo $delete_column_sql;
        if($del_count>=count($delete_column_sql))
           {
           return 1;
           }
        else
            {
            $ERROR[]=$ERROR_CODE[3]." (".$res_info['TABLE_NAME'].".".$res_info['NAME'].")";
            db_rollback($conn);
            return 0;
            }

}
//==============================================================================================
function add_table($conn, $table_id)
{
        global $ERROR, $ERROR_CODE;
        $sel_info="SELECT name, about from ".TABLE_PRE."tables where id=$table_id";
        //echo"sel_info=$sel_info<br>";
        $res_info=db_getArray($conn, $sel_info, 2);
        $create_table = "CREATE TABLE ".$res_info['NAME']."(tab_id number)";
        //echo "$create_table<br>";
        sql_log($create_table);
        $add_table=db_query($conn, $create_table);
        $comment_table_q="COMMENT ON TABLE ".$res_info['NAME']." IS '".$res_info['ABOUT']."'";
        $comment_table=db_query($conn, $comment_table_q);
        //echo"$create_table, <br>$add_table<br>";
        if($add_table)
           {
           $sel_type="select id from ".TABLE_PRE."column_types where unit_name='int'";
           $res_type=db_getArray($conn, $sel_type, 2);
           $ins_id=db_insert($conn, TABLE_PRE."columns", array( 'name', 'table_id', 'column_type_id', 'about'),
                                              array( 'id', $table_id, $res_type['ID'], "Id таблицы ".$res_info['NAME'], $res_info['NAME']),
                                              array('varchar', 'int', 'int', 'varchar'));
           //echo"ins_id=$ins_id<br>";
           $alter_ins_id=alter_table_add_column($conn, $ins_id);
           }
        if($add_table && $ins_id && $alter_ins_id)
           {
           $del_test_col_q="ALTER TABLE ".$res_info['NAME']." DROP (tab_id)";
           $del_test_col=db_query($conn, $del_test_col_q);
		   //echo"del_test_col_q=$del_test_col_q, $del_test_col<br>";
           return 1;
           }
        else
            {
            $ERROR[]=$ERROR_CODE[4]." (".$res_info['NAME'].")";
            db_rollback($conn);
            $del_tab_q="delete from ".TABLE_PRE."tables where id=$table_id";
            $del_tab=db_query($conn, $del_tab_q);
            //echo"rallback<br>";
            return 0;
            }


}
//удаление таблицы
function del_table($conn, $table_id)
{
global $ERROR, $ERROR_CODE;
        $table_id=intval($table_id);
        $table_name=get_byID($conn, TABLE_PRE."tables", $table_id, 'name, seq_name');
        //echo"table_name=".$table_name['NAME']."<br>";
        $del_column_q="DELETE FROM ".TABLE_PRE."columns WHERE table_id=$table_id";
        $del_column=db_query($conn, $del_column_q);
        if($del_column)
           {
           $drop_table[]="drop table ".$table_name['NAME'];
           if($table_name['SEQ_NAME'])
              $drop_table[]="drop sequence ".$table_name['SEQ_NAME'];
           $drop_count=0;
           foreach($drop_table as $k=>$v)
                   {
                   sql_log($v);
                   $drop=db_query($conn, $v);
                   if($drop)
                      $drop_count++;
                   }
           //echo"drop_table=$drop_table, $drop<br>";
           }
        if($drop_count>=count($drop_table))
           return 1;
        else
            {
            $ERROR[]=$ERROR_CODE[5]." (".$table_name['NAME'].")";
            db_rollback($conn);
            return 0;
            }
}
//функция формирует команду создания триггера для заполнения id
function create_trigger_for_pk($table_name)
         {
         $trigger_name=create_name($table_name, "_ID_TR");
         $seq_name=create_name($table_name, "_ID_SEQ");
         $ret="CREATE OR REPLACE TRIGGER ".$trigger_name."\n";
         $ret.=" BEFORE INSERT\n";
         $ret.=" ON ".$table_name."\n";
         $ret.=" FOR EACH ROW\n";
         $ret.="DECLARE\n";
         $ret.="  next_seq number;\n";
         $ret.="BEGIN\n";
         $ret.="IF :new.id IS NULL THEN\n";
         $ret.="    SELECT ".$seq_name.".nextval INTO next_seq FROM dual;\n";
         $ret.="    :new.id:=next_seq;\n";
         $ret.="ELSE\n";
         $ret.="    next_seq:=1;\n";
         $ret.="    WHILE next_seq<:new.id LOOP\n";
         $ret.="    SELECT  ".$seq_name.".nextval INTO next_seq FROM dual;\n";
         $ret.="    END LOOP;\n";
         $ret.="END IF;\n";
         $ret.="END ".$trigger_name.";\n\n";

         return $ret;
         }
function create_name($table_name, $addit)
         {
         //echo"1 create_name - $table_name, $addit<br>";
         if(strlen($table_name.$addit)>29)
            {
            $table_name=substr($table_name, 0, (29-strlen($addit)));
            }
         //echo"2 create_name - $table_name, $addit<br>";
         return $table_name.$addit;
         }
//функция формирует имя для FK
function create_fk_name($table, $column)
         {
         if(strlen($table.$column)>25)
            {
            $table=substr($table, 0, (25-strlen($column)));
            }
         $ret=$table."__".$column."_fk";
         return $ret;
         }
function create_ind_name($table, $column)
         {
         if(strlen($table.$column)>25)
            {
            $table=substr($table, 0, (25-strlen($column)));
            }
         $ret=$table."__".$column."_i";
         return $ret;
         }

?>
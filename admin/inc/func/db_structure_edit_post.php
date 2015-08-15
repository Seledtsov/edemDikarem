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
        //return 1;
        $sel_info="SELECT c.name, c.about, c.column_length, c.null_value, c.default_value, c.ref_column_id, c.fk_type_id,
                          t.id as table_id, t.name as table_name, t.main, t.seq_name, ct.name as column_type
                          FROM ".TABLE_PRE."columns c, ".TABLE_PRE."tables t, ".TABLE_PRE."column_types ct
                          WHERE c.table_id=t.id and c.column_type_id=ct.id and c.id=$column_id ";
        //echo"sel_info=$sel_info<br>";
        $res_info=db_getArray($conn, $sel_info, 2);
        $add_column_sql="ALTER TABLE ".$res_info['TABLE_NAME']." add column ".$res_info['NAME']." ".$res_info['COLUMN_TYPE'];
        if($res_info['COLUMN_LENGTH'])
               $add_column_sql .= "(".$res_info['COLUMN_LENGTH'].")";
        $add_column_sql.= ";";

        //если превичный ключ - создаем sequence
        if (strtoupper($res_info['NAME']) == "ID")
        {
                $seq_name=($res_info['SEQ_NAME']?$res_info['SEQ_NAME']:($res_info['TABLE_NAME']."_id_seq"));
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
                    $add_column_sql.="\r\n CREATE SEQUENCE $seq_name;";
                    }
                $add_column_sql_add="\r\n ALTER TABLE ".$res_info['TABLE_NAME']." ALTER COLUMN ".$res_info['NAME']." SET DEFAULT nextval('$seq_name'::text);";
                $add_pk_sql = "\r\n ALTER TABLE ".$res_info['TABLE_NAME']." ADD CONSTRAINT ".$res_info['TABLE_NAME']."_pk PRIMARY KEY (id)";
        }
        else
            {
            if ($res_info['DEFAULT_VALUE'])
                 {
                 $add_column_sql.="\r\n ALTER TABLE ".$res_info['TABLE_NAME']."  ALTER COLUMN ".$res_info['NAME']." SET DEFAULT ".$res_info['DEFAULT_VALUE'].";";
                 }
            }
        $add_column_sql.=$add_column_sql_add;
        //если не нулевое значение
        if (!$res_info['NULL_VALUE'])
             {
             if($res_info['DEFAULT_VALUE'])
                $add_column_sql.="\r\n UPDATE ".$res_info['TABLE_NAME']." SET ".$res_info['NAME']." = '".$res_info['DEFAULT_VALUE']."';";
             $add_column_sql.="\r\n ALTER TABLE ".$res_info['TABLE_NAME']." ALTER COLUMN ".$res_info['NAME']." SET NOT NULL;";
             }

        if ($res_info['ABOUT'])
                   {
                   $add_column_sql.="\r\n COMMENT ON COLUMN ".$res_info['TABLE_NAME'].".".$res_info['NAME']." IS '".$res_info['ABOUT']."';";
                   }
        // Связь Вторичный ключ
        if ($res_info['REF_COLUMN_ID'])
            {
            if($res_info['FK_TYPE_ID'])
               {
               $ref_type=get_byId($conn, TABLE_PRE."fk_types", $res_info['FK_TYPE_ID'], "code_name");
               }

            $ref_sel="SELECT c.NAME, t.NAME as table_name FROM ".TABLE_PRE."columns c, ".TABLE_PRE."tables t
                               WHERE c.table_id=t.id AND c.id=".$res_info['REF_COLUMN_ID'];
            $ref_res=db_getArray($conn, $ref_sel, 2);
            $add_column_sql.="\r\n ALTER TABLE ".$res_info['TABLE_NAME']." ADD CONSTRAINT ".$res_info['TABLE_NAME']."_".$res_info['NAME']."_fk FOREIGN KEY (".$res_info['NAME'].") REFERENCES ".$ref_res['TABLE_NAME']." (".$ref_res['NAME'].") ";
            if($ref_type['CODE_NAME']=="cascade")//если надо удалять каскадно
             $add_column_sql.="ON UPDATE CASCADE ON DELETE CASCADE;";
            elseif($ref_type['CODE_NAME']=="set_null")
             $add_column_sql.="ON UPDATE RESTRICT ON DELETE SET NULL;";
            else
             $add_column_sql.="ON UPDATE RESTRICT ON DELETE RESTRICT;";
            }
//        echo "$add_column_sql<br>";
//        exit;
		//если колонка типа oid - надо создать треггер на удаление
		if($res_info['COLUMN_TYPE']=="oid")
			{			
			$triiger_oid_sql="\r\n CREATE OR REPLACE FUNCTION ".$res_info['TABLE_NAME']."__".$res_info['NAME']."_tf()
  RETURNS \"trigger\" AS
\$BODY\$
DECLARE
--чистим колонку ".$res_info['NAME']." перед удалением
BEGIN
IF OLD.".$res_info['NAME']." IS NOT NULL THEN
	DELETE FROM pg_largeobject WHERE loid=OLD.".$res_info['NAME'].";
END IF;
RETURN OLD;
END\$BODY\$
  LANGUAGE 'plpgsql' VOLATILE;
CREATE TRIGGER  ".$res_info['TABLE_NAME']."__".$res_info['NAME']."_tr  
  BEFORE DELETE
  ON ".$res_info['TABLE_NAME']."
  FOR EACH ROW
  EXECUTE PROCEDURE ".$res_info['TABLE_NAME']."__".$res_info['NAME']."_tf();
";
$add_column_sql.=$triiger_oid_sql;
			}
        $add_column_sql.=$add_pk_sql;
        sql_log($add_column_sql);
        $res=db_query($conn, $add_column_sql);
       //echo"res=$res<br>";
        if(!$res)
            {
            db_rollback($conn);
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

        $delete_column_sql="ALTER TABLE ".$res_info['TABLE_NAME']." DROP COLUMN ".$res_info['NAME'].";";

        if(strtoupper($res_info['NAME']) == "ID")
                {
                $delete_column_sql.="\r\n drop sequence ".$res_info['TABLE_NAME']."_id_seq;";
                }
		//если колонка типа oid - надо удалить треггер на удаление и триггерную функцию
		if($res_info['COLUMN_TYPE']=="oid")
			{
			$delete_column_sql="DROP TRIGGER ".$res_info['TABLE_NAME']."__".$res_info['NAME']."_tr ON ".$res_info['TABLE_NAME'].";\r\n DROP FUNCTION ".$res_info['TABLE_NAME']."__".$res_info['NAME']."_tf();\r\n".$delete_column_sql;
			}
        sql_log($delete_column_sql);
        $del=db_query($conn, $delete_column_sql);
        //echo $delete_column_sql;
        if($del)
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
        $create_table = "CREATE TABLE ".$res_info['NAME']."();
        COMMENT ON TABLE ".$res_info['NAME']." IS '".$res_info['ABOUT']."';";
        //echo "$create_table<br>";
        sql_log($create_table);
        $add_table=db_query($conn, $create_table);
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
           return 1;
           }
        else
            {
            $ERROR[]=$ERROR_CODE[4]." (".$res_info['NAME'].")";
            db_rollback($conn);
            $del_tab_q="delete from ".TABLE_PRE."tables where id=$table_id";
            $del_tab=db_query($conn, $del_tab_q);
            echo"rallback<br>";
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
           $drop_table="drop table ".$table_name['NAME'].";";
           if($table_name['SEQ_NAME'])
              $drop_table.="\r\n drop sequence ".$table_name['SEQ_NAME'].";";
           sql_log($drop_table);
           $drop=db_query($conn, $drop_table);
           //echo"drop_table=$drop_table, $drop<br>";
           }
        if($drop)
           return 1;
        else
            {
            $ERROR[]=$ERROR_CODE[5]." (".$table_name['NAME'].")";
            db_rollback($conn);
            return 0;
            }
}
?>
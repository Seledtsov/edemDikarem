<?
//удаляет из таблиц индексации результаты индексации данной сущности
function del_indexer($conn, $id, $obj_id)
         {
         $sel_q="select ".TABLE_PRE."SEARCH_DEL_INDEX_PR($id, $obj_id)";
         $sel=db_query($conn, $sel_q);
         if(!$sel)
             echo"$sel_q\r\n";
         return $sel;
         }
//удаляем езультат подсчета релевантности
function del_relev($conn, $id, $obj_id)
         {
         $sel_q="select ".TABLE_PRE."DEL_RELEV_PR($id, $obj_id)";
         $sel=db_query($conn, $sel_q);
         if(!$sel)
             echo"$sel_q\r\n";
         return $sel;
         }
//функция вызова процедуры разбивки по словам
function pre_indexer($conn, $ind_id)
         {
         $sel_q="select ".TABLE_PRE."SEARCH_WORD_PR($ind_id)";
         $sel=db_query($conn, $sel_q);
         if(!$sel)
             echo"$sel_q\r\n";
         return $sel;
         }
//функция расчетов по индексации - кроме основной релевантности
function index_operation($conn, $id, $obj_id)
         {
         $sel_q="select ".TABLE_PRE."SEARCH_OPER_PR($id, $obj_id)";
         $sel=db_query($conn, $sel_q);
         if(!$sel)
             echo"$sel_q\r\n";
         return $sel;
         }
//функция расчета полной релевантности
function full_rating($conn, $id, $obj_id)
         {
         $sel_q="select ".TABLE_PRE."SEARCH_FULL_PR($id, $obj_id)";
         $sel=db_query($conn, $sel_q);
         if(!$sel)
             echo"$sel_q\r\n";

         return $sel;
         }


?>
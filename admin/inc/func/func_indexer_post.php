<?
//������� �� ������ ���������� ���������� ���������� ������ ��������
function del_indexer($conn, $id, $obj_id)
         {
         $sel_q="select ".TABLE_PRE."SEARCH_DEL_INDEX_PR($id, $obj_id)";
         $sel=db_query($conn, $sel_q);
         if(!$sel)
             echo"$sel_q\r\n";
         return $sel;
         }
//������� �������� �������� �������������
function del_relev($conn, $id, $obj_id)
         {
         $sel_q="select ".TABLE_PRE."DEL_RELEV_PR($id, $obj_id)";
         $sel=db_query($conn, $sel_q);
         if(!$sel)
             echo"$sel_q\r\n";
         return $sel;
         }
//������� ������ ��������� �������� �� ������
function pre_indexer($conn, $ind_id)
         {
         $sel_q="select ".TABLE_PRE."SEARCH_WORD_PR($ind_id)";
         $sel=db_query($conn, $sel_q);
         if(!$sel)
             echo"$sel_q\r\n";
         return $sel;
         }
//������� �������� �� ���������� - ����� �������� �������������
function index_operation($conn, $id, $obj_id)
         {
         $sel_q="select ".TABLE_PRE."SEARCH_OPER_PR($id, $obj_id)";
         $sel=db_query($conn, $sel_q);
         if(!$sel)
             echo"$sel_q\r\n";
         return $sel;
         }
//������� ������� ������ �������������
function full_rating($conn, $id, $obj_id)
         {
         $sel_q="select ".TABLE_PRE."SEARCH_FULL_PR($id, $obj_id)";
         $sel=db_query($conn, $sel_q);
         if(!$sel)
             echo"$sel_q\r\n";

         return $sel;
         }


?>
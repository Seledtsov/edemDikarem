<?
//������� ���������� ����������, ����������� ��� PostgreSQL
//--------------------------------------------------------------------------------------------
//1- ���� ������������� �� ������, 2 - ����  ������������� �� ����� ��������������,
//4 - ������ ������������� �� �����, 8 - ������ ������������� �� ������
//������� ���������� � ������� �� ����������
function indexer_wait($conn, $obj_id, $id, $table_id="", $main_rel_id=BASE_NULL, $rating=1)
         {
         $sel_q="select ".TABLE_PRE."INDEXER_WAIT($id, ".($obj_id?$obj_id:BASE_NULL).", $rating, ".($table_id?$table_id:BASE_NULL).", $main_rel_id)";
         //echo"sel_q=$sel_q\r\n";
         $sel=db_query($conn, $sel_q);
         return $sel;
         }
//���������� ����� - �������
function indexer_wait_part($conn, $tab_id, $id, $rating=1)
         {
         //$sel_q="select ".TABLE_PRE."INDEXER_WAIT_PART($id, $tab_id, $rating)";
         //echo"sel_q=$sel_q\r\n";
         //$sel=db_query($conn, $sel_q);
         return $sel;
         }
//������� ��������� ������
function TreeOrderReculculation($conn, $table)
         {
         $sel_q="select ".TABLE_PRE."TreeOrderReculculation('$table', 1, 0, null, '', '".RAZD_PARAMS."', '')";
         $sel=db_query($conn, $sel_q);
         //echo"sel_q=$sel_q, $sel\r\n";
         return $sel;
         }
//�������� ��������
function rating($conn, $table, $column, $id, $rating, $max_rating=MAX_RATING)
         {
         $sel_q="select ".TABLE_PRE."Rating('$table', '$column', $id, $rating, ".$max_rating.")";
         
         $sel=db_query($conn, $sel_q);
		 //echo"sel_q=$sel_q, $sel\r\n";
         return $sel;
         }

//������� ��������� ������� ���������� ������
function TreePublishReculculation($conn, $table)
         {
         $sel_q="SELECT ".TABLE_PRE."TreePublishReculculation('$table', ".BASE_NULL.", ".BASE_NULL.")";
         $sel=db_query($conn, $sel_q);
		 //echo"sel_q=$sel_q, $sel\r\n";
         return $sel;
         }
//������� ���������� � ������� ������� ����
function ClearCache($conn, $tab_id, $id)
         {
         $sel_q="select ".TABLE_PRE."cache__add_line($tab_id, $id)";
         //echo"sel_q=$sel_q\r\n";
         $sel=db_query($conn, $sel_q);
         return $sel;
         }
//������� ������� ����
function ClearCache_real($conn, $tab_id, $id)
         {
         $sel_q="select ".TABLE_PRE."cache__change_string($tab_id, $id)";
         //echo"sel_q=$sel_q\r\n";
         $sel=db_query($conn, $sel_q);
         return $sel;
         }
?>
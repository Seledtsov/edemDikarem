<?
//������� �� ������ ���������� ���������� ���������� ������ ��������
function del_indexer($conn, $id, $obj_id)
         {
         $proc=TABLE_PRE."SEARCH_DEL_INDEX_PR($id, $obj_id)";
         $res=db_procedure_execute($conn, $proc);
         return $res;
         }
//������� �������� �������� �������������
function del_relev($conn, $id, $obj_id)
         {
         $proc=TABLE_PRE."DEL_RELEV_PR($id, $obj_id)";
         $res=db_procedure_execute($conn, $proc);
         return $res;
         }
//������� ������ ��������� �������� �� ������
function pre_indexer($conn, $ind_id)
         {
         $proc=TABLE_PRE."SEARCH_WORD_PR($ind_id)";
         $res=db_procedure_execute($conn, $proc);
         return $res;
         }
//������� �������� �� ���������� - ����� �������� �������������
function index_operation($conn, $id, $obj_id)
         {
         $proc=TABLE_PRE."SEARCH_OPER_PR($id, $obj_id)";
         $res=db_procedure_execute($conn, $proc);
         return $res;
         }
//������� ������� ������ �������������
function full_rating($conn, $id, $obj_id)
         {
         $proc=TABLE_PRE."SEARCH_FULL_PR($id, $obj_id)";
         $res=db_procedure_execute($conn, $proc);
         return $res;
         }
//������� �������� �� ������
function index_phrase($conn, $id)
	{
         $proc=TABLE_PRE."SEARCH_INDEXER_PACK.PHRASE_PR($id)";
         $res=db_procedure_execute($conn, $proc);
         return $res;

	}


?>
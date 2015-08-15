<?
//функции различного назначения, специфичные для PostgreSQL
//--------------------------------------------------------------------------------------------
//1- явно индексировать из списка, 2 - явно  индексировать из формы редактирования,
//4 - скрыто индексировать из формы, 8 - скрыто индексировать из списка
//функция добавления в очередь на индексацию
function indexer_wait($conn, $obj_id, $id, $table_id="", $main_rel_id=BASE_NULL, $rating=1)
         {
         $proc=TABLE_PRE."INDEXER_WAIT_PR($id, ".($obj_id?$obj_id:BASE_NULL).", $rating, ".($table_id?$table_id:BASE_NULL).", $main_rel_id)";
         $res=db_procedure_execute($conn, $proc);
         return $res;
         }
//индексация части - таблицы
function indexer_wait_part($conn, $tab_id, $id, $rating=1)
         {
         echo"indexer_wait_part<br>";
         return 1;
         //$sel_q="select ".TABLE_PRE."INDEXER_WAIT_PART($id, $tab_id, $rating)";
         //echo"sel_q=$sel_q\r\n";
         //$sel=db_query($conn, $sel_q);
         return $sel;
         }
//функция пересчета дерева
function TreeOrderReculculation($conn, $table)
         {
         $proc=TABLE_PRE."TreeOrderReculculation('$table', '".RAZD_PARAMS."')";
         $res=db_procedure_execute($conn, $proc);
         return $res;
         }
//пересчет рейтинга
function rating($conn, $table, $column, $id, $rating, $max_rating=MAX_RATING)
         {
         $proc=TABLE_PRE."Rating('$table', '$column', $id, $rating, ".$max_rating.")";
		 //echo"rating proc - $proc<br>";
         $res=db_procedure_execute($conn, $proc);
         return $res;
         }

//функция пересчета статуса публикации дерева
function TreePublishReculculation($conn, $table)
         {
         $proc=TABLE_PRE."TreePublishReculculation('$table')";
         $res=db_procedure_execute($conn, $proc);
         //echo"$proc - $res<br>";
         return $res;
         }
?>
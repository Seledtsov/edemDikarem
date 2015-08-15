#!/bin/bash
#снимаем с разработческого сервера дампы нужных таблиц
#выполнять надо под postgres
export BASE="kasyanov"
export POSTGRES_BIN="/usr/local/pgsql/bin"
export SAVE_PATH="/t3/arch/base"

$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_layout -f $SAVE_PATH/$BASE/ti_layout.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_ref_columns  -f $SAVE_PATH/$BASE/ti_ref_columns.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_search_templates -f $SAVE_PATH/$BASE/ti_search_templates.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_layout_ref_columns -f $SAVE_PATH/$BASE/ti_layout_ref_columns.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_layout_columns -f $SAVE_PATH/$BASE/ti_layout_columns.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_layout_cols_preset -f $SAVE_PATH/$BASE/ti_layout_cols_preset.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t cham_layer_gather -f $SAVE_PATH/$BASE/cham_layer_gather.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t cham_layer_params -f $SAVE_PATH/$BASE/cham_layer_params.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_layout_ref_links -f $SAVE_PATH/$BASE/ti_layout_ref_links.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_layout_ref_links_params -f $SAVE_PATH/$BASE/ti_layout_ref_links_params.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_layout_ref_download -f $SAVE_PATH/$BASE/ti_layout_ref_download.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_layout_params -f $SAVE_PATH/$BASE/ti_layout_params.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_layout_links -f $SAVE_PATH/$BASE/ti_layout_links.backup
$POSTGRES_BIN/pg_dump $BASE -U pst -a -O -x -D -F p -t ti_layout_links_params -f $SAVE_PATH/$BASE/ti_layout_links_params.backup



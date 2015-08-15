#!/bin/bash
#Под postrges!!!
export BASE="tehnosk2"
#export POSTGRES_BIN="/usr/bin"
export POSTGRES_BIN="/usr/local/pgsql/bin"
export LOG_PATH="/t3/logs"
export SQL_PATH="/t3/sql/tehnosk"
export VERTION="1.0.014"
echo START
date
$POSTGRES_BIN/psql -U pst -d $BASE -f $SQL_PATH/drop_tr.sql >$LOG_PATH/drop_tr.log 2>$LOG_PATH/drop_tr.err 
$POSTGRES_BIN/psql -U pst -d $BASE -f $SQL_PATH/update_$VERTION.sql >$LOG_PATH/update_$VERTION.log 2>$LOG_PATH/update_$VERTION.err 
$POSTGRES_BIN/psql -U pst -d $BASE -f $SQL_PATH/meta.sql >$LOG_PATH/meta.log 2>$LOG_PATH/meta.err 
echo END
date

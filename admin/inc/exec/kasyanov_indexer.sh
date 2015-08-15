#!/bin/bash
export g_INC="/t3/admin/inc/"
export g_INC_exec="/t3/admin/inc/exec"
export CRON_LOG_PATH="/t3/logs"
export PHP_BIN="/usr/bin/php"
echo $CRON_LOG_PATH

set `date  +"%u"`
nice --adjustment=19 $PHP_BIN $g_INC_exec/indexer.php host=kasyanov 1>$CRON_LOG_PATH/indexer.${1}.log 2>>$CRON_LOG_PATH/indexer.err &
nice --adjustment=19 $PHP_BIN $g_INC_exec/indexer.php host=kasyanov lang=eng 1>$CRON_LOG_PATH/indexer_eng.${1}.log 2>>$CRON_LOG_PATH/indexer_eng.err &


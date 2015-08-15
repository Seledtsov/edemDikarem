#!/bin/bash
export g_INC="/t3/admin/inc/"
export g_INC_exec="/t3/admin/inc/exec"
export CRON_LOG_PATH="/t3/logs"
export PHP_BIN="/usr/bin/php"
export PATH_INC_HOST="/t3/fntr/inc"
echo $CRON_LOG_PATH

set `date  +"%u"`

nice --adjustment=12 $PHP_BIN $g_INC_exec/clear_cache.php host=fntr 1>$CRON_LOG_PATH/clear_cache.${1}.log 2>>$CRON_LOG_PATH/clear_cache.err &
nice --adjustment=13 $PHP_BIN $g_INC_exec/clear_cache.php host=fntr lang=eng 1>$CRON_LOG_PATH/clear_cache_eng.${1}.log 2>>$CRON_LOG_PATH/clear_cache_eng.err &

#!/bin/bash
export g_INC="/t3/admin/inc/"
export g_INC_exec="/t3/admin/inc/exec"
export CRON_LOG_PATH="/tmp"
export PHP_BIN="/usr/bin/php"
export PATH_INC_HOST="/t3/fntr/inc"
echo $CRON_LOG_PATH
$PHP_BIN $g_INC_exec/post_send.php host=fntr 1>>$CRON_LOG_PATH/post.log 2>>$CRON_LOG_PATH/post_err.log &

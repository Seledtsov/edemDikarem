#!/bin/bash
export g_INC="/var/www/admin/inc/"
export g_INC_exec="/var/www/admin/inc/exec"
export CRON_LOG_PATH="/tmp"
export PHP_BIN="/usr/bin/php"
export PATH_INC_HOST="/home/client45/01www/agency/inc"
echo $CRON_LOG_PATH
$PHP_BIN $g_INC_exec/post_send.php host=agency 1>>$CRON_LOG_PATH/post.log 2>>$CRON_LOG_PATH/post_err.log &

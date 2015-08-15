#!/bin/bash
if [ -e /tmp/pg_start.flag ]; then
	exit
else
date>/tmp/pg_start.flag
/usr/bin/pg_ctl -D /var/lib/pgsql stop
/usr/bin/pg_ctl -D /var/pgsql stop
/usr/bin/pg_ctl -D /var/pgsql -l /tmp/postgres.log start
rm  /tmp/pg_start.flag
echo END
date
fi
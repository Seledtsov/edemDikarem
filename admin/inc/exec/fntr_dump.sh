#!/bin/bash
set `date  +"%u"`
export DMP_DIR="/t3/arch/base"
rm -f $DMP_DIR/to_delete/*
mv $DMP_DIR/yesterday/* $DMP_DIR/to_delete/
mv $DMP_DIR/today/* $DMP_DIR/yesterday/

#rm -f $DMP_DIR/yesterday/*
#rm -f $DMP_DIR/to_delete/*

/usr/bin/pg_dump fntr -U pst -x -i -O -F t  | gzip -c>$DMP_DIR/today/fntr${1}.dmp.gz
/usr/bin/pg_dump fntr_eng -U pst -x -i -O -F t  | gzip -c>$DMP_DIR/today/fntr_eng${1}.dmp.gz

          
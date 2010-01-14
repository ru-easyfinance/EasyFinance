#! /usr/local/bin/bash

dumpdir=/www/easyfinance.ru/sql_dump
dumpfile=$dumpdir/easyfinance-$(date +%d_%m_%Y-%H_%M).sql

remote_host=hm.podzone.net
remote_user=gorlum
remote_pass=Cfdtkjdcrbq

# Making dump directory (if needed)
mkdir -p $dumpdir

# Processed database dump
/usr/local/bin/mysqldump -ueasyfinance -pM4pfokPYfJ9ZfDyx easyfinance --no-create-db --no-create-info --complete-insert > $dumpfile 
gzip $dumpfile
rm -rf $dumpfile
# echo ""
# Copying to internal server
# rsync -r /www/easyfinance.ru/sql_dump $remote_user@$remote_host:/www


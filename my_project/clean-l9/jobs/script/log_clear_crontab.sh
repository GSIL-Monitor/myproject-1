#!/bin/bash
# Author:  xp
#
# Notes: log 日志定时删除
#       定时删除  保存3天    路径    /mnt/www/upload/clean-log/collapse/2018/12
#       定时删除  保存2天   路径   /mnt/www/clean/jobs/swoole/var/2018/12

year=$(date +%Y)

month=$(date +%m)

day=$(date +%d)

logdir=/mnt/www/upload/clean-log/collapse


if [ $day -gt 04 ]
then

cd ${logdir}
# 删除上一年的日志
rm -rf `ls | grep -v "^${year}$"`
# 删除上个月的日志
cd ${year}
rm -rf `ls | grep -v "^${month}$"`
# 删除几天前的日志
cd ${month}
# rm -rf `ls | grep -v "^${day}$" | grep -v "^${day}-1$" | grep -v "^${day}-2$" | grep -v "^${day}-3$"`
# find  -mtime +3 -name "*" -exec rm -rf {} \;
# find . -type d -name 'date+[0-9][0-9]' -a ! -mtime -3

tt=`date -d -3day +%d`
for file in ./*
do
if test -d $file
then
name=`basename $file`
if [ $name -lt $tt ]
then
rm -rf ./${name}
echo ./${name}
fi
fi
done
fi


swooledir=/mnt/www/clean/jobs/swoole/var


if [ $day -gt 03 ]
then

cd ${swooledir}
# 删除上一年的日志
rm -rf `ls | grep -v "^${year}$"`
# 删除上个月的日志
cd ${year}
rm -rf `ls | grep -v "^${month}$"`
# 删除几天前的日志
cd ${month}
# rm -rf `ls | grep -v "^${day}$" | grep -v "^${day}-1$" | grep -v "^${day}-2$"`
# find  -mtime +2 -name "*" -exec rm -rf {} \;
# find . -type d -name '*' -a ! -mtime -2

tt1=`date -d -2day +%d`
for file in ./*
do
if test -d $file
then
name=`basename $file`
if [ $name -lt $tt1 ]
then
rm -rf ./${name}
echo ./${name}
fi
fi
done
fi
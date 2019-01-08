#!/bin/bash
# Author:  xp
#
# Notes: 监控磁盘使用率     >80%   邮件报警

# 1. 获取硬盘使用率

# SERVER_IP=`ifconfig|grep 192.168.1|awk -F":" '{print $2}'|cut -d" " -f1`
ROOT_DISK=`/bin/df -h|grep /dev/vda1|awk -F" " '{print $5}'|cut -d"%" -f1`
HOME_DISK=`/bin/df -h|grep /dev/vdb|awk -F" " '{print $5}'|cut -d"%" -f1`

# 2. 发送邮件

if [ $ROOT_DISK -ge 30 ];then
#/usr/local/bin/sendEmail -f xiaogouzhineng@moomv.com -t xp@ldrobot.com -s smtp.qiye.163.com -u " web服务器，根磁盘/当前利用率预警" -o message-content-type=html -o message-charset=utf8 -xu xiaogouzhineng@moomv.com -xp gL6jx25yrusaqTLb -m "web服务器，根磁盘‘/’当前利用率的百分值为 $ROOT_DISK %，超过预警阀值，请知悉!"
#/usr/local/bin/sendEmail -f xiaogouzhineng@moomv.com -t wf@ldrobot.com -s smtp.qiye.163.com -u " web服务器，根磁盘/当前利用率预警" -o message-content-type=html -o message-charset=utf8 -xu xiaogouzhineng@moomv.com -xp gL6jx25yrusaqTLb -m "web服务器，根磁盘‘/’当前利用率的百分值为 $ROOT_DISK %，超过预警阀值，请知悉!"
curl https://www.xiaogou111.com/api/rootDisk?disk=$ROOT_DISK
else
echo "The ROOT_DISK of $SERVER_IP-$HOSTNAME is Enough to use" >> /tmp/test.log
fi

sleep 5

if [ $HOME_DISK -ge 80 ];then
#/usr/local/bin/sendEmail -f xiaogouzhineng@moomv.com -t xp@ldrobot.com -s smtp.qiye.163.com -u " web服务器，项目磁盘‘/mnt’当前利用率预警" -o message-content-type=html -o message-charset=utf8 -xu xiaogouzhineng@moomv.com -xp gL6jx25yrusaqTLb -m "web服务器，项目磁盘‘/mnt’当前利用率的百分值为 $ROOT_DISK %，超过预警阀值，请知悉!"
#/usr/local/bin/sendEmail -f xiaogouzhineng@moomv.com -t wf@ldrobot.com -s smtp.qiye.163.com -u " web服务器，项目磁盘‘/mnt’当前利用率预警" -o message-content-type=html -o message-charset=utf8 -xu xiaogouzhineng@moomv.com -xp gL6jx25yrusaqTLb -m "web服务器，项目磁盘‘/mnt’当前利用率的百分值为 $ROOT_DISK %，超过预警阀值，请知悉!"
curl https://www.xiaogou111.com/api/homeDisk?disk=$HOME_DISK
else
echo "The ROOT_DISK of $SERVER_IP-$HOSTNAME is Enough to use" >> /tmp/test.log
fi

#APP接口监控
curl https://www.xiaogou111.com/api/appMonitor
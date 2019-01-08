user="root"
dbhost="127.0.0.1"
dbname="clean"
password="im639()"
date_a="$(date +"%Y%m%d%H%M%S")"
date_y="$(date +"%Y")"
date_m="$(date +"%m")"
target_dir="/var/www/mysql-back/$dbname/$date_y/$date_m"
if [ ! -d $target_dir ];then
mkdir -p $target_dir;
fi
mysqldump -h$dbhost -u$user  $dbname > "$target_dir/$dbname-$date_a.sql"

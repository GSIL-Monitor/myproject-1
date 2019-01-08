1、服务器必须安装 zip unzip 
安装方法 sudo apt install zip unzip 或 sudo yum install zip unzip

2、检查服务器目录及权限是否与配置文件保持一致
如配置文件使用/mnt/publish/backup和/mnt/publish/temp，那么服务要存在对应的目录，没有就手动建立并置权限为755

3、ssh配置与项目名称保持一致
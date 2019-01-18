## 使用说明

#### PHP环境搭建(docker+nginx+mysql+php+redis+mongo)

###### 镜像下载

```docker
docker pull dnmp_php70
docker pull nginx:1.15.7-alpine
docker pull mysql:8.0.13
docker pull redis:5.0.3-alpine
docker pull mongo:latest
```

###### docker-compose 运行环境

```
docker-compose up -d
```

###### 服务器端口

```
服务器需要开放端口
http   80
https  443
redis  6379
mysql  3306
mongo  27017
scoket 9501 和 9502  

注：需开放端口与 docker-compose.yml 配置文件设置对应
docker-compose.yml 文件中所有端口配置可在 .env 设置
```

#### 配置

###### 目录结构

```
/
├── conf                    配置文件目录
│   ├── conf.d              Nginx用户站点配置目录
│   |	├── certs           HTTPS证书目录
│   ├── nginx.conf          Nginx默认配置文件
│   ├── mysql.cnf           MySQL用户配置文件
│   ├── redis.cnf           redis配置文件
│   ├── php-fpm.conf        PHP-FPM配置文件（部分会覆盖php.ini配置）
│   └── php.ini             PHP默认配置文件
├── Dockerfile              PHP镜像构建文件
├── extensions              PHP扩展源码包
├── log                     Nginx日志目录
├── mysql                   MySQL数据目录
├── redis                   redis数据目录
├── mongo                   mongo数据目录
├── mnt                     mnt数据目录(need 可写权限)
├── www                     PHP代码目录
└── source.list             Debian源文件
```

###### docker-compose配置

```
.env文件为docker-compose参数设置文件
可根据需求调整相应参数值
```

###### nginx站点配置

```
1. 在conf.d目录下创建站点 conf 配置
2. 如果使用https协议，则将证书放在certs目录
3. websocket ssl conf 配置
4. ssl 证书放在certs目录
```

###### redis密码配置

```
1. 在 redis.conf 文件中设置 requirepass 参数来给 redis 添加密码
2. 在 redis.conf 文件中设置 bind 0.0.0.0 以确保远程可访问redis server
```

###### MySQL数据

```
将数据库所有数据放入mysql目录
```

###### 项目源码

```
将项目源码放入www目录

# 项目相关配置
## 数据库配置
在 /项目目录/app/config/parameters.yml 文件中设置数据库配置
## 邮箱配置
在 /项目目录/app/config/parameters.yml 文件中设置邮箱配置
## redis配置
在 /项目目录/app/config/parameters.yml 文件中设置redis配置
## 缓存文件配置
在 /项目目录/ 目录下，创建var目录，并提供可写(w)权限
```

###### 权限配置

```
/mnt目录需要可写(w)权限
```

###### 脚本、定时任务

```
# swoole 运行脚本

在dnmp目录运行
docker exec -it dnmp_php70_1 /bin/bash
进入php服务器，然后运行
php /var/www/html/clean/jobs/swoole/SweepingSwooleServer.php
启动swoole

# 日志清理脚本

在服务器 crontab -e 添加定时任务

```




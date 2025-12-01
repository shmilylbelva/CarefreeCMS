# CMS 系统部署指南

## 📚 目录

- [系统要求](#系统要求)
- [环境检查](#环境检查)
- [安装步骤](#安装步骤)
- [配置指南](#配置指南)
- [启动服务](#启动服务)
- [域名配置](#域名配置)
- [SSL/HTTPS](#ssltls-配置)
- [监控和维护](#监控和维护)
- [升级指南](#升级指南)
- [故障恢复](#故障恢复)

---

## 系统要求

### 服务器要求

| 组件 | 最低版本 | 推荐版本 |
|------|---------|---------|
| PHP | 8.0 | 8.1+ |
| MySQL | 5.7 | 8.0+ |
| Redis | 5.0 | 6.0+ |
| Nginx/Apache | 1.14 | 1.18+ |

### 服务器配置

| 指标 | 最低 | 推荐 |
|------|------|------|
| CPU | 1 核 | 2 核+ |
| 内存 | 2 GB | 4 GB+ |
| 硬盘 | 10 GB | 50 GB+ |
| 带宽 | 1 Mbps | 5 Mbps+ |

### 必需的 PHP 扩展

```
curl, fileinfo, gd, json, mbstring, openssl,
pdo, pdo_mysql, ctype, tokenizer, xml
```

### 操作系统

- Linux（推荐：CentOS 7+、Ubuntu 18.04+）
- macOS（用于开发）
- Windows Server（不推荐生产环境）

---

## 环境检查

### 1. 检查 PHP 版本

```bash
php -v
# 输出示例：
# PHP 8.1.0 (cli) (built: Nov 24 2021 07:32:23) ( NTS )
```

### 2. 检查 PHP 扩展

```bash
php -m
# 或检查特定扩展
php -i | grep pdo
php -i | grep mbstring
```

### 3. 检查 MySQL 连接

```bash
mysql -u root -p -e "SELECT VERSION();"
# 输出示例：
# +-----------+
# | VERSION() |
# +-----------+
# | 8.0.28    |
# +-----------+
```

### 4. 检查 Redis 连接

```bash
redis-cli ping
# 输出：PONG
```

### 5. 检查文件权限

```bash
# 检查写入权限
ls -la /var/www/html
# drwxrwxr-x  user  group

# 设置正确权限
chmod -R 755 /var/www/cms
chmod -R 777 /var/www/cms/backend/runtime
chmod -R 777 /var/www/cms/backend/public
```

---

## 安装步骤

### 第一步：下载项目

#### 方式一：Git 克隆

```bash
cd /var/www
git clone https://github.com/your-org/cms.git
cd cms
git checkout v1.2.0  # 检出指定版本
```

#### 方式二：下载压缩包

```bash
cd /var/www
wget https://github.com/your-org/cms/releases/download/v1.2.0/cms-1.2.0.tar.gz
tar -xzf cms-1.2.0.tar.gz
mv cms-1.2.0 cms
cd cms
```

### 第二步：安装后端依赖

```bash
cd backend

# 安装 Composer 依赖
composer install --no-dev --optimize-autoloader

# 如果 Composer 未安装，先安装 Composer
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

### 第三步：配置环境变量

```bash
# 复制环境配置模板
cp .env.example .env

# 编辑 .env 文件，配置数据库和其他信息
nano .env
```

**必需配置项**：

```env
# 应用
APP_NAME=CMS
APP_ENV=production
APP_DEBUG=false
APP_KEY=your-app-key-here

# 数据库
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cms_database
DB_USERNAME=cms_user
DB_PASSWORD=strong_password

# Redis（可选）
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

# JWT
JWT_SECRET=your-jwt-secret-key
```

### 第四步：生成应用 KEY

```bash
# 生成 APP_KEY
php think key:generate

# 生成 JWT_SECRET（如果为空）
php think jwt:secret
```

### 第五步：创建和初始化数据库

```bash
# 使用提供的 SQL 文件创建数据库
mysql -u root -p < docs/database_design.sql

# 或使用 ThinkPHP 迁移
php think migrate

# 导入 Seeder 数据（可选）
php think seeder:run
```

### 第六步：安装前端依赖

```bash
cd ../backend

# 安装 npm 依赖
npm install

# 构建前端
npm run build

# 构建后的文件在 dist/ 目录
```

### 第七步：配置 Web 服务器

#### Nginx 配置示例

```nginx
server {
    listen 80;
    server_name api.example.com;

    # 重定向到 HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.example.com;

    # SSL 证书配置
    ssl_certificate /etc/ssl/certs/api.example.com.crt;
    ssl_certificate_key /etc/ssl/private/api.example.com.key;

    # 日志文件
    access_log /var/log/nginx/cms-api-access.log;
    error_log /var/log/nginx/cms-api-error.log;

    # 根目录
    root /var/www/cms/backend/public;

    # 字符集
    charset utf-8;

    # Gzip 压缩
    gzip on;
    gzip_types text/plain application/json;

    # API 入口
    location / {
        if (-f $request_filename) {
            break;
        }
        if (-d $request_filename) {
            break;
        }
        rewrite ^(.*)$ /index.php?s=$1 last;
    }

    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }
}

# 前端应用
server {
    listen 443 ssl http2;
    server_name admin.example.com;

    ssl_certificate /etc/ssl/certs/admin.example.com.crt;
    ssl_certificate_key /etc/ssl/private/admin.example.com.key;

    root /var/www/cms/frontend/dist;

    access_log /var/log/nginx/cms-admin-access.log;
    error_log /var/log/nginx/cms-admin-error.log;

    # SPA 应用路由
    location / {
        try_files $uri $uri/ /index.html;
    }

    # 静态资源缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

#### Apache 配置示例

```apache
<VirtualHost *:443>
    ServerName api.example.com
    DocumentRoot /var/www/cms/backend/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/api.example.com.crt
    SSLCertificateKeyFile /etc/ssl/private/api.example.com.key

    <Directory /var/www/cms/backend/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ /index.php?s=$1 [L]
        </IfModule>
    </Directory>

    LogLevel warn
    ErrorLog /var/log/apache2/cms-error.log
    CustomLog /var/log/apache2/cms-access.log combined
</VirtualHost>
```

### 第八步：设置文件权限

```bash
cd /var/www/cms

# 设置所有者
sudo chown -R www-data:www-data .

# 设置目录权限
sudo chmod 755 backend/app backend/config backend/public
sudo chmod 755 frontend/dist

# 设置写入权限
sudo chmod 777 backend/runtime
sudo chmod 777 backend/public/upload
sudo chmod 777 frontend/dist
```

---

## 配置指南

### PHP 配置优化

编辑 `/etc/php/8.1/fpm/php.ini`：

```ini
; 文件上传限制
upload_max_filesize = 10M
post_max_size = 10M

; 执行时间
max_execution_time = 300
max_input_time = 300

; 内存
memory_limit = 256M

; 禁用危险函数
disable_functions = exec,passthru,shell_exec,system,proc_open

; 时区
date.timezone = Asia/Shanghai

; 错误处理
error_reporting = E_ALL & ~E_DEPRECATED
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
```

### MySQL 配置优化

编辑 `/etc/mysql/mysql.conf.d/mysqld.cnf`：

```ini
[mysqld]
# 连接设置
max_connections = 1000
max_allowed_packet = 256M

# 缓存设置
query_cache_size = 64M
query_cache_type = 1

# InnoDB 设置
innodb_buffer_pool_size = 1G
innodb_file_per_table = ON

# 字符集
character_set_server = utf8mb4
collation_server = utf8mb4_unicode_ci
```

### Redis 配置优化

编辑 `/etc/redis/redis.conf`：

```conf
# 最大内存
maxmemory 512mb
maxmemory-policy allkeys-lru

# AOF 持久化
appendonly yes
appendfsync everysec

# 持久化文件
dir /var/lib/redis
```

---

## 启动服务

### 启动 PHP-FPM

```bash
# 启动 PHP-FPM 服务
sudo systemctl start php8.1-fpm

# 设置开机自启
sudo systemctl enable php8.1-fpm

# 查看状态
sudo systemctl status php8.1-fpm
```

### 启动 Nginx

```bash
# 启动 Nginx
sudo systemctl start nginx

# 重启 Nginx（修改配置后）
sudo systemctl restart nginx

# 查看状态
sudo systemctl status nginx

# 测试配置
sudo nginx -t
```

### 启动 MySQL

```bash
# 启动 MySQL
sudo systemctl start mysql

# 设置开机自启
sudo systemctl enable mysql
```

### 启动 Redis

```bash
# 启动 Redis
sudo systemctl start redis-server

# 设置开机自启
sudo systemctl enable redis-server
```

### 验证部署

```bash
# 测试 API 端点
curl -I https://api.example.com/backend/system/info

# 输出应该包含：
# HTTP/2 200

# 测试前端访问
curl -I https://admin.example.com/
```

---

## 域名配置

### DNS 配置

在域名服务商处配置 DNS 记录：

```
主机记录    类型    值
-------     ----    -----
api         A       服务器 IP 地址
admin       A       服务器 IP 地址
www         CNAME   example.com
```

### 验证 DNS

```bash
# 查询 DNS 记录
nslookup api.example.com
dig api.example.com

# 应该看到服务器 IP 地址
```

---

## SSL/TLS 配置

### 使用 Let's Encrypt 免费证书

#### 1. 安装 Certbot

```bash
sudo apt-get install certbot python3-certbot-nginx
```

#### 2. 申请证书

```bash
# 申请证书
sudo certbot certonly --webroot \
  -w /var/www/cms/backend/public \
  -d api.example.com \
  -d admin.example.com

# 或使用 Nginx 插件
sudo certbot --nginx \
  -d api.example.com \
  -d admin.example.com
```

#### 3. 配置自动续期

```bash
# 编辑 crontab
sudo crontab -e

# 添加以下行（每月检查一次证书）
0 3 1 * * certbot renew --quiet
```

### 导入自购证书

```bash
# 复制证书文件
sudo cp your-cert.crt /etc/ssl/certs/
sudo cp your-key.key /etc/ssl/private/

# 修改权限
sudo chmod 644 /etc/ssl/certs/your-cert.crt
sudo chmod 600 /etc/ssl/private/your-key.key
```

---

## 监控和维护

### 日志监控

```bash
# 查看 API 日志
tail -f /var/www/cms/backend/runtime/log/2025-10-24.log

# 查看 Nginx 日志
tail -f /var/log/nginx/cms-api-error.log
tail -f /var/log/nginx/cms-api-access.log

# 查看 MySQL 日志
tail -f /var/log/mysql/error.log
```

### 性能监控

```bash
# 查看 CPU 和内存使用
top

# 查看磁盘使用
df -h
du -sh /var/www/cms/*

# 查看网络连接
netstat -tulpn | grep LISTEN
```

### 备份策略

#### 数据库备份

```bash
#!/bin/bash
# 每天备份数据库
BACKUP_DIR=/var/backups/cms
DB_NAME=cms_database
DB_USER=cms_user
DB_PASS=your_password

mkdir -p $BACKUP_DIR

# 完整备份
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/$(date +%Y%m%d_%H%M%S).sql.gz

# 只保留 7 天的备份
find $BACKUP_DIR -type f -mtime +7 -delete
```

#### 文件备份

```bash
# 备份上传的文件
tar -czf /var/backups/cms-files-$(date +%Y%m%d).tar.gz \
  /var/www/cms/backend/public/upload

# 备份配置文件
tar -czf /var/backups/cms-config-$(date +%Y%m%d).tar.gz \
  /var/www/cms/backend/.env \
  /etc/nginx/sites-available/ \
  /etc/php/8.1/fpm/
```

---

## 升级指南

### 升级前准备

```bash
# 1. 备份数据库和文件
mysqldump -u root -p cms_database | gzip > cms_backup.sql.gz

# 2. 停止 Web 服务
sudo systemctl stop nginx

# 3. 备份项目目录
tar -czf cms_backup.tar.gz /var/www/cms
```

### 升级步骤

```bash
# 1. 下载新版本
cd /var/www/cms
git fetch origin
git checkout v1.2.1

# 2. 更新 PHP 依赖
cd backend
composer install --no-dev --optimize-autoloader

# 3. 运行数据库迁移（如有新的迁移文件）
php think migrate

# 4. 构建前端
cd ../backend
npm install
npm run build

# 5. 清除缓存
cd ../api
php think cache:clear

# 6. 启动服务
sudo systemctl start nginx
```

### 升级问题排查

```bash
# 检查 PHP 依赖
composer install --dry-run

# 检查数据库连接
php think tinker

# 查看错误日志
tail -f backend/runtime/log/*
```

---

## 故障恢复

### 无法访问 API

#### 1. 检查 Nginx 状态

```bash
sudo systemctl status nginx
sudo nginx -t

# 查看错误日志
tail -f /var/log/nginx/cms-api-error.log
```

#### 2. 检查 PHP-FPM

```bash
sudo systemctl status php8.1-fpm
ps aux | grep php-fpm

# 查看 PHP 日志
tail -f /var/log/php/error.log
```

#### 3. 检查数据库连接

```bash
# 测试 MySQL 连接
mysql -u cms_user -p -h 127.0.0.1 -e "USE cms_database; SELECT 1;"

# 查看 .env 配置
cat backend/.env | grep DB_
```

### 数据库问题

#### 恢复数据库备份

```bash
# 恢复备份
mysql -u root -p cms_database < cms_backup.sql
```

#### 修复表

```bash
mysql -u root -p
> USE cms_database;
> CHECK TABLE articles;
> REPAIR TABLE articles;
```

### 磁盘空间满

```bash
# 查看大文件
find /var/www/cms -type f -size +100M

# 清理日志
find /var/www/cms/backend/runtime/log -type f -mtime +30 -delete

# 清理缓存
rm -rf /var/www/cms/backend/runtime/cache/*
```

### 内存溢出

```bash
# 增加 PHP 内存限制
vi /etc/php/8.1/fpm/php.ini
# memory_limit = 512M

# 重启 PHP-FPM
sudo systemctl restart php8.1-fpm
```

---

## 检查清单

部署完成前，请检查以下项目：

- [ ] 服务器环境满足要求
- [ ] 数据库创建并初始化
- [ ] 环境变量 .env 配置正确
- [ ] 文件权限设置正确
- [ ] Web 服务器配置正确
- [ ] SSL 证书已配置
- [ ] 可以访问 API 端点
- [ ] 可以访问前端应用
- [ ] 日志监控正常工作
- [ ] 备份策略已部署
- [ ] 管理员账户可以登录

---

## 相关文档

- [开发指南](./DEVELOPER_GUIDE.md)
- [API 文档](./API_DOCUMENTATION.md)
- [故障排查](./TROUBLESHOOTING.md)
- [安全扫描](./SECURITY_SCANNING.md)

---

**部署指南版本**: 1.0.0
**最后更新**: 2025-10-24
**CMS 版本**: 1.2.0

# CMS系统部署文档

## 目录

1. [系统要求](#系统要求)
2. [环境准备](#环境准备)
3. [后端部署](#后端部署)
4. [前端部署](#前端部署)
5. [数据库配置](#数据库配置)
6. [Nginx配置](#nginx配置)
7. [生产环境优化](#生产环境优化)
8. [常见问题](#常见问题)

---

## 系统要求

### 后端要求
- **PHP**: >= 8.0
- **MySQL**: >= 5.7 或 >= 8.0
- **Redis**: >= 5.0 (可选，用于缓存)
- **Composer**: >= 2.0
- **扩展**:
  - PDO PHP Extension
  - OpenSSL PHP Extension
  - Mbstring PHP Extension
  - JSON PHP Extension
  - Redis PHP Extension (可选)
  - GD 或 Imagick Extension (图片处理)

### 前端要求
- **Node.js**: >= 16.0
- **npm**: >= 8.0 或 **pnpm**: >= 7.0

### 服务器要求
- **操作系统**: Linux (推荐 Ubuntu 20.04+, CentOS 7+)
- **内存**: 最低 2GB (推荐 4GB+)
- **磁盘**: 最低 20GB (根据数据量调整)
- **网络**: 公网IP (如需外网访问)

---

## 环境准备

### 1. 安装PHP 8.0

#### Ubuntu/Debian
```bash
sudo apt update
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.0-fpm php8.0-cli php8.0-mysql php8.0-redis \
    php8.0-mbstring php8.0-xml php8.0-curl php8.0-gd php8.0-zip
```

#### CentOS/RHEL
```bash
sudo yum install epel-release
sudo yum install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
sudo yum module reset php
sudo yum module install php:remi-8.0
sudo yum install php php-fpm php-mysqlnd php-redis php-mbstring \
    php-xml php-curl php-gd php-zip
```

### 2. 安装MySQL

```bash
# Ubuntu/Debian
sudo apt install mysql-server

# CentOS/RHEL
sudo yum install mysql-server

# 启动MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# 安全配置
sudo mysql_secure_installation
```

### 3. 安装Redis (可选)

```bash
# Ubuntu/Debian
sudo apt install redis-server

# CentOS/RHEL
sudo yum install redis

# 启动Redis
sudo systemctl start redis
sudo systemctl enable redis
```

### 4. 安装Nginx

```bash
# Ubuntu/Debian
sudo apt install nginx

# CentOS/RHEL
sudo yum install nginx

# 启动Nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 5. 安装Composer

```bash
# 下载Composer
curl -sS https://getcomposer.org/installer | php

# 移动到全局路径
sudo mv composer.phar /usr/local/bin/composer

# 验证安装
composer --version
```

### 6. 安装Node.js和npm

```bash
# 使用NodeSource仓库
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# 或使用nvm（推荐）
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
nvm install 18
nvm use 18

# 验证安装
node --version
npm --version
```

---

## 后端部署

### 1. 克隆代码

```bash
cd /var/www
sudo git clone <repository-url> cms
cd cms/backend
```

### 2. 安装依赖

```bash
composer install --no-dev --optimize-autoloader
```

### 3. 配置环境变量

```bash
# 复制环境配置文件
cp .env.example .env

# 编辑配置
nano .env
```

**重要配置项**:
```ini
# 应用配置
APP_DEBUG = false  # 生产环境必须设置为false
APP_TRACE = false

# 数据库配置
DB_TYPE = mysql
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_NAME = cms_database
DB_USER = cms_user
DB_PASS = <强密码>

# Redis配置（如果使用）
REDIS_HOST = 127.0.0.1
REDIS_PORT = 6379
REDIS_PASSWORD = <强密码>

# JWT密钥（必须更换为强随机密钥）
JWT_SECRET = <生成的强随机密钥>

# 文件上传
UPLOAD_MAX_SIZE = 10485760  # 10MB
UPLOAD_ALLOWED_EXT = jpg,jpeg,png,gif,pdf,doc,docx

# 缓存配置
CACHE_DRIVER = redis  # 或 file
```

**生成强随机密钥**:
```bash
# 生成JWT密钥
openssl rand -base64 32

# 生成数据库密码
openssl rand -base64 24
```

### 4. 数据库初始化

```bash
# 创建数据库
mysql -u root -p

CREATE DATABASE cms_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cms_user'@'localhost' IDENTIFIED BY '<强密码>';
GRANT ALL PRIVILEGES ON cms_database.* TO 'cms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# 导入数据库结构
mysql -u cms_user -p cms_database < database/cms.sql
```

### 5. 设置文件权限

```bash
# 设置所有者
sudo chown -R www-data:www-data /var/www/cms

# 设置目录权限
sudo find /var/www/cms -type d -exec chmod 755 {} \;

# 设置文件权限
sudo find /var/www/cms -type f -exec chmod 644 {} \;

# 设置运行时目录可写
sudo chmod -R 775 /var/www/cms/backend/runtime
sudo chmod -R 775 /var/www/cms/backend/public/uploads
```

### 6. 配置PHP-FPM

编辑 `/etc/php/8.0/fpm/pool.d/www.conf`:

```ini
user = www-data
group = www-data

listen = /run/php/php8.0-fpm.sock

listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

重启PHP-FPM:
```bash
sudo systemctl restart php8.0-fpm
```

---

## 前端部署

### 1. 进入前端目录

```bash
cd /var/www/cms/frontend
```

### 2. 安装依赖

```bash
npm install --production
# 或使用pnpm（更快）
pnpm install --production
```

### 3. 配置环境变量

```bash
# 复制环境配置
cp .env.production.example .env.production

# 编辑配置
nano .env.production
```

配置示例:
```ini
VITE_API_BASE_URL=https://api.yourdomain.com
VITE_APP_TITLE=CMS管理系统
```

### 4. 构建生产版本

```bash
npm run build
# 或
pnpm build

# 构建输出到 dist/ 目录
```

### 5. 部署静态文件

```bash
# 复制构建文件到Nginx目录
sudo cp -r dist/* /var/www/cms/frontend/dist/

# 设置权限
sudo chown -R www-data:www-data /var/www/cms/frontend/dist
```

---

## Nginx配置

### 1. 创建配置文件

创建 `/etc/nginx/sites-available/cms`:

```nginx
# 后端API配置
server {
    listen 80;
    server_name api.yourdomain.com;

    root /var/www/cms/backend/public;
    index index.php;

    # 日志配置
    access_log /var/log/nginx/cms-api-access.log;
    error_log /var/log/nginx/cms-api-error.log;

    # 最大上传大小
    client_max_body_size 10M;

    # 隐藏Nginx版本
    server_tokens off;

    # 安全头
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;

        # PHP配置
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_read_timeout 300;
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }

    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}

# 前端配置
server {
    listen 80;
    server_name www.yourdomain.com yourdomain.com;

    root /var/www/cms/frontend/dist;
    index index.html;

    # 日志配置
    access_log /var/log/nginx/cms-frontend-access.log;
    error_log /var/log/nginx/cms-frontend-error.log;

    # 安全头
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip压缩
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
    gzip_vary on;
    gzip_min_length 1000;
    gzip_comp_level 6;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # 静态资源缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }
}
```

### 2. 启用配置

```bash
# 创建符号链接
sudo ln -s /etc/nginx/sites-available/cms /etc/nginx/sites-enabled/

# 测试配置
sudo nginx -t

# 重载Nginx
sudo systemctl reload nginx
```

### 3. 配置SSL (HTTPS)

使用Let's Encrypt免费证书:

```bash
# 安装Certbot
sudo apt install certbot python3-certbot-nginx

# 获取证书
sudo certbot --nginx -d api.yourdomain.com -d www.yourdomain.com -d yourdomain.com

# 自动续期
sudo certbot renew --dry-run
```

---

## 生产环境优化

### 1. PHP优化

编辑 `/etc/php/8.0/fpm/php.ini`:

```ini
; 性能优化
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  # 生产环境关闭

; 安全配置
expose_php=Off
display_errors=Off
log_errors=On
error_log=/var/log/php/error.log

; 上传限制
upload_max_filesize=10M
post_max_size=10M
max_execution_time=60
memory_limit=256M
```

### 2. MySQL优化

编辑 `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
# 性能优化
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# 连接优化
max_connections = 200
thread_cache_size = 16

# 查询缓存
query_cache_type = 1
query_cache_size = 64M

# 慢查询日志
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2
```

重启MySQL:
```bash
sudo systemctl restart mysql
```

### 3. Redis优化

编辑 `/etc/redis/redis.conf`:

```ini
# 内存限制
maxmemory 512mb
maxmemory-policy allkeys-lru

# 持久化
save 900 1
save 300 10
save 60 10000

# 安全
requirepass <强密码>
bind 127.0.0.1

# 性能
tcp-backlog 511
timeout 300
```

重启Redis:
```bash
sudo systemctl restart redis
```

### 4. 应用缓存预热

```bash
cd /var/www/cms/backend
php think cache:warmup
```

### 5. 设置定时任务

编辑crontab:
```bash
sudo crontab -e -u www-data
```

添加:
```cron
# 每小时预热缓存
0 * * * * cd /var/www/cms/backend && php think cache:warmup

# 每天清理过期日志
0 2 * * * cd /var/www/cms/backend && php think log:clear --days=30

# 每周数据库备份
0 3 * * 0 mysqldump -u cms_user -p<密码> cms_database | gzip > /var/backups/cms_$(date +\%Y\%m\%d).sql.gz
```

---

## 常见问题

### 1. 500 Internal Server Error

**检查项**:
- PHP错误日志: `tail -f /var/log/php/error.log`
- Nginx错误日志: `tail -f /var/log/nginx/cms-api-error.log`
- 文件权限是否正确
- `.env` 配置是否正确

### 2. 数据库连接失败

**检查项**:
```bash
# 测试数据库连接
mysql -u cms_user -p cms_database

# 检查MySQL是否运行
sudo systemctl status mysql

# 检查端口
sudo netstat -tlnp | grep 3306
```

### 3. Redis连接失败

**检查项**:
```bash
# 测试Redis连接
redis-cli ping

# 检查Redis是否运行
sudo systemctl status redis

# 检查配置
redis-cli info
```

### 4. 文件上传失败

**检查项**:
- 目录权限: `ls -la /var/www/cms/backend/public/uploads`
- PHP配置: `upload_max_filesize`, `post_max_size`
- Nginx配置: `client_max_body_size`

### 5. 前端无法访问后端API

**检查项**:
- CORS配置是否正确
- API地址配置是否正确
- Nginx反向代理配置
- 防火墙规则

---

## 监控和维护

### 1. 日志监控

```bash
# 实时查看访问日志
tail -f /var/log/nginx/cms-api-access.log

# 查看错误日志
tail -f /var/log/nginx/cms-api-error.log
tail -f /var/log/php/error.log
```

### 2. 性能监控

使用工具:
- **New Relic**: APM监控
- **Datadog**: 全栈监控
- **htop**: 系统资源监控
- **mytop**: MySQL监控

### 3. 数据库备份

```bash
# 手动备份
mysqldump -u cms_user -p cms_database | gzip > cms_backup_$(date +%Y%m%d).sql.gz

# 恢复备份
gunzip < cms_backup_20250126.sql.gz | mysql -u cms_user -p cms_database
```

### 4. 更新部署

```bash
# 拉取最新代码
cd /var/www/cms
sudo -u www-data git pull

# 更新后端依赖
cd backend
sudo -u www-data composer install --no-dev --optimize-autoloader

# 清理缓存
php think clear:cache

# 更新前端
cd ../frontend
npm install
npm run build
sudo cp -r dist/* /var/www/cms/frontend/dist/

# 重启服务
sudo systemctl reload php8.0-fpm
sudo systemctl reload nginx
```

---

## 安全建议

1. **定期更新系统和软件包**
```bash
sudo apt update && sudo apt upgrade
```

2. **配置防火墙**
```bash
sudo ufw allow 22/tcp  # SSH
sudo ufw allow 80/tcp  # HTTP
sudo ufw allow 443/tcp # HTTPS
sudo ufw enable
```

3. **禁用不必要的PHP函数**
编辑 `php.ini`:
```ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
```

4. **定期检查日志**
- 检查异常登录尝试
- 监控慢查询
- 查看错误日志

5. **数据库安全**
- 使用强密码
- 限制远程访问
- 定期备份
- 使用最小权限原则

---

**最后更新**: 2025-11-26
**维护者**: CMS项目团队

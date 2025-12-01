# CMS 系统故障排查指南

## 📚 目录

- [常见问题解决](#常见问题解决)
- [性能问题](#性能问题)
- [数据库问题](#数据库问题)
- [身份认证问题](#身份认证问题)
- [文件上传问题](#文件上传问题)
- [缓存问题](#缓存问题)
- [日志分析](#日志分析)
- [调试技巧](#调试技巧)

---

## 常见问题解决

### 问题 1: 无法连接到 API

#### 症状
- 浏览器显示 "无法访问此网站"
- API 请求超时

#### 解决方案

**第一步：检查服务是否运行**

```bash
# 检查 Nginx
sudo systemctl status nginx

# 检查 PHP-FPM
sudo systemctl status php8.1-fpm

# 检查 MySQL
sudo systemctl status mysql
```

如果服务未运行，启动它们：

```bash
sudo systemctl start nginx
sudo systemctl start php8.1-fpm
sudo systemctl start mysql
```

**第二步：检查端口是否开放**

```bash
# 检查 Nginx 监听的端口
sudo netstat -tulpn | grep nginx

# 检查 PHP-FPM
sudo netstat -tulpn | grep php

# 输出应该显示：
# tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN
# tcp        0      0 0.0.0.0:443             0.0.0.0:*               LISTEN
```

**第三步：检查防火墙设置**

```bash
# 查看防火墙规则
sudo ufw status

# 允许 HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw reload
```

**第四步：测试本地连接**

```bash
# 测试 localhost
curl -v http://127.0.0.1/backend/system/info

# 测试域名
curl -v https://api.example.com/backend/system/info
```

### 问题 2: 502 Bad Gateway 错误

#### 症状
- 访问页面显示 "502 Bad Gateway"
- Nginx 错误日志显示 "connect() failed"

#### 解决方案

**原因通常是 PHP-FPM 问题**

```bash
# 检查 PHP-FPM 状态
sudo systemctl status php8.1-fpm

# 查看 PHP-FPM 进程
ps aux | grep php-fpm

# 重启 PHP-FPM
sudo systemctl restart php8.1-fpm
```

**检查 PHP 配置**

```bash
# 查看 Nginx 配置中的 fastcgi_pass
grep -n "fastcgi_pass" /etc/nginx/sites-available/default

# 确保 PHP 套接字或 IP:PORT 正确
# 常见配置：
# fastcgi_pass 127.0.0.1:9000;
# 或
# fastcgi_pass unix:/run/php/php8.1-fpm.sock;
```

**增加 PHP-FPM 工作进程**

```bash
# 编辑 PHP-FPM 配置
sudo vi /etc/php/8.1/fpm/pool.d/www.conf

# 修改以下参数
pm = dynamic
pm.max_children = 50      # 最大进程数
pm.start_servers = 10     # 启动时的进程数
pm.min_spare_servers = 5  # 最小空闲进程数
pm.max_spare_servers = 20 # 最大空闲进程数

# 重启 PHP-FPM
sudo systemctl restart php8.1-fpm
```

### 问题 3: 500 Internal Server Error

#### 症状
- 页面显示 "500 Internal Server Error"
- 错误日志显示 "PHP Fatal error"

#### 解决方案

**第一步：查看错误日志**

```bash
# 查看 PHP 错误日志
tail -f /var/log/php/error.log

# 查看 API 项目日志
tail -f /var/www/cms/backend/runtime/log/2025-10-24.log

# 查看 Nginx 错误日志
tail -f /var/log/nginx/cms-api-error.log
```

**第二步：启用调试模式（仅开发环境）**

```bash
# 编辑 .env 文件
vi /var/www/cms/backend/.env

# 临时启用调试
APP_DEBUG=true

# 重新访问，查看详细错误信息
# 完成调试后关闭
APP_DEBUG=false
```

**第三步：检查常见原因**

```bash
# 权限问题
ls -la /var/www/cms/backend/runtime/
chmod -R 777 /var/www/cms/backend/runtime/

# 配置文件问题
cat /var/www/cms/backend/.env | grep DB_

# 缺少依赖
composer install
```

### 问题 4: 数据库连接失败

#### 症状
- 错误："SQLSTATE[HY000] [2002] Connection refused"
- 登录页无法加载

#### 解决方案

**第一步：检查 MySQL 服务**

```bash
# 检查 MySQL 运行状态
sudo systemctl status mysql

# 如果未运行，启动它
sudo systemctl start mysql
```

**第二步：测试数据库连接**

```bash
# 直接连接数据库
mysql -h 127.0.0.1 -u cms_user -p

# 如果失败，检查用户和密码是否正确
mysql -h 127.0.0.1 -u root -p -e "SELECT user, host FROM mysql.user;"
```

**第三步：检查 .env 配置**

```bash
# 查看数据库配置
cat /var/www/cms/backend/.env | grep DB_

# 确保以下值正确：
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=cms_database
# DB_USERNAME=cms_user
# DB_PASSWORD=your_password
```

**第四步：检查防火墙**

```bash
# 如果 MySQL 不在本地，检查防火墙
sudo ufw allow from any to any port 3306

# 检查 MySQL 绑定地址
grep "bind-address" /etc/mysql/mysql.conf.d/mysqld.cnf
```

---

## 性能问题

### 问题 5: 页面加载缓慢

#### 症状
- API 响应时间 > 1 秒
- 前端加载缓慢

#### 解决方案

**第一步：分析 API 响应时间**

```bash
# 使用 curl 测试
time curl https://api.example.com/backend/article/list

# 查看响应时间
# real    0m2.345s  <- 如果超过 1 秒，说明有问题
```

**第二步：检查数据库查询**

```bash
# 启用 MySQL 慢查询日志
vi /etc/mysql/mysql.conf.d/mysqld.cnf

long_query_time = 1
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log

# 重启 MySQL
sudo systemctl restart mysql

# 分析慢查询
cat /var/log/mysql/slow-query.log | tail -20
```

**第三步：使用缓存**

```bash
# 确保 Redis 运行
sudo systemctl status redis-server

# 检查 .env 中的缓存配置
cat /var/www/cms/backend/.env | grep CACHE

# 应该看到：
# CACHE_DRIVER=redis
```

**第四步：启用 Gzip 压缩**

```bash
# Nginx 配置
vi /etc/nginx/nginx.conf

gzip on;
gzip_types text/plain application/json text/css text/javascript;
gzip_min_length 1024;

# 重启 Nginx
sudo systemctl restart nginx
```

### 问题 6: 内存占用过高

#### 症状
- PHP-FPM 内存占用 > 500 MB
- MySQL 内存占用 > 1 GB
- 服务经常重启

#### 解决方案

**第一步：查看内存使用**

```bash
# 查看进程内存占用
ps aux --sort=-%mem | head -10

# 查看内存统计
free -h
```

**第二步：优化 PHP 内存设置**

```bash
# 编辑 PHP 配置
vi /etc/php/8.1/fpm/php.ini

memory_limit = 256M  # 减少内存限制
max_execution_time = 60  # 减少执行时间

# 重启 PHP-FPM
sudo systemctl restart php8.1-fpm
```

**第三步：优化 MySQL 内存设置**

```bash
# 编辑 MySQL 配置
vi /etc/mysql/mysql.conf.d/mysqld.cnf

# 减少缓冲区大小
innodb_buffer_pool_size = 512M
query_cache_size = 32M

# 重启 MySQL
sudo systemctl restart mysql
```

**第四步：检查并优化代码**

```bash
# 查看最耗内存的 API 调用
grep "Allowed memory" /var/www/cms/backend/runtime/log/*

# 分析代码中的大数据处理
grep -r "->get()" /var/www/cms/backend/app/service --include="*.php"
```

### 问题 7: CPU 占用过高

#### 症状
- CPU 占用率 > 80%
- 响应变慢

#### 解决方案

**第一步：识别占用 CPU 的进程**

```bash
# 实时查看 CPU 占用
top

# 按 CPU 排序
ps aux --sort=-%cpu | head -10
```

**第二步：分析 PHP 进程**

```bash
# 如果 PHP 占用 CPU 过高，可能是某个请求阻塞
# 查看 PHP 进程数
ps aux | grep php-fpm | wc -l

# 看是否有僵尸进程
ps aux | grep defunct
```

**第三步：优化数据库查询**

```bash
# 启用 MySQL 查询日志
vi /etc/mysql/mysql.conf.d/mysqld.cnf

general_log = 1
general_log_file = /var/log/mysql/queries.log

# 运行一段时间后分析查询
tail -100 /var/log/mysql/queries.log | grep -i "SELECT"
```

---

## 数据库问题

### 问题 8: 数据库表损坏

#### 症状
- 错误："Table is marked as crashed"
- 查询返回不完整的数据

#### 解决方案

**第一步：检查表状态**

```bash
mysql -u root -p
> USE cms_database;
> CHECK TABLE articles;
```

**第二步：修复表**

```bash
mysql -u root -p
> USE cms_database;
> REPAIR TABLE articles;

# 或使用命令行工具
sudo mysqlcheck -u root -p --auto-repair cms_database
```

### 问题 9: 磁盘空间不足

#### 症状
- 错误："No space left on device"
- 无法保存新数据

#### 解决方案

**第一步：检查磁盘空间**

```bash
# 查看磁盘使用
df -h

# 查看各目录大小
du -sh /var/www/cms/*
du -sh /var/log/*
```

**第二步：清理不需要的文件**

```bash
# 删除旧日志
sudo find /var/log -type f -mtime +30 -delete

# 删除缓存
rm -rf /var/www/cms/backend/runtime/cache/*

# 清理 MySQL 二进制日志（谨慎！）
sudo mysql -u root -p -e "PURGE BINARY LOGS BEFORE DATE_SUB(NOW(), INTERVAL 7 DAY);"
```

**第三步：扩展存储**

```bash
# 如果是虚拟机，扩展虚拟磁盘后执行
sudo lvresize -L +50G /dev/vg0/root
sudo resize2fs /dev/vg0/root
```

---

## 身份认证问题

### 问题 10: 无法登录

#### 症状
- 登录页显示"用户名或密码错误"
- 登录后自动退出

#### 解决方案

**第一步：检查数据库用户**

```bash
mysql -u root -p
> USE cms_database;
> SELECT id, username, password FROM admin_users LIMIT 5;
```

**第二步：重置管理员密码**

```bash
# 生成新密码哈希（假设新密码是 admin123）
php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"

# 输出: $2y$10$...

# 更新数据库
mysql -u root -p
> USE cms_database;
> UPDATE admin_users SET password='$2y$10$...' WHERE username='admin';
```

**第三步：检查 JWT 配置**

```bash
# 检查 .env 中的 JWT 配置
cat /var/www/cms/backend/.env | grep JWT

# 确保 JWT_SECRET 已设置
# JWT_SECRET=your-secret-key

# 如果未设置，执行
cd /var/www/cms/api
php think jwt:secret
```

### 问题 11: JWT Token 过期

#### 症状
- API 返回 401 Unauthorized
- 前端显示"请重新登录"

#### 解决方案

**原因**：Token 有效期已过期

```bash
# 查看 .env 中的 Token 过期时间
cat /var/www/cms/backend/.env | grep JWT_EXPIRATION

# 默认 24 小时，如需修改：
JWT_EXPIRATION=86400  # 秒数

# 重新启动服务
php think serve --host 127.0.0.1 --port 8000
```

**前端需要实现 Token 刷新**：

```javascript
// 监听 401 响应
axios.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401) {
      // 尝试刷新 token
      const newToken = await refreshToken()
      if (newToken) {
        // 重试原请求
        return axios(error.config)
      }
      // 刷新失败，跳转到登录页
      router.push('/login')
    }
    return Promise.reject(error)
  }
)
```

---

## 文件上传问题

### 问题 12: 文件上传失败

#### 症状
- 上传文件时显示"上传失败"
- 错误："The file is too large"

#### 解决方案

**第一步：检查文件大小限制**

```bash
# 查看 PHP 配置
grep -i "upload_max_filesize\|post_max_size" /etc/php/8.1/fpm/php.ini

# 应该看到类似：
# upload_max_filesize = 10M
# post_max_size = 10M

# 如果需要增加限制
sudo vi /etc/php/8.1/fpm/php.ini
upload_max_filesize = 100M
post_max_size = 100M

# 重启 PHP-FPM
sudo systemctl restart php8.1-fpm
```

**第二步：检查上传目录权限**

```bash
# 检查权限
ls -la /var/www/cms/backend/public/upload

# 确保有写权限
sudo chmod 777 /var/www/cms/backend/public/upload
```

**第三步：检查磁盘空间**

```bash
# 查看剩余空间
df -h /var/www/cms/backend/public/upload
```

### 问题 13: 上传的文件无法访问

#### 症状
- 文件上传成功，但访问返回 404
- 编辑器中的图片显示 ❌

#### 解决方案

**第一步：检查文件路径**

```bash
# 查看文件是否存在
ls -la /var/www/cms/backend/public/upload/

# 检查数据库中记录的路径
mysql -u root -p
> USE cms_database;
> SELECT * FROM media LIMIT 1\G
```

**第二步：检查 Nginx 配置**

```bash
# 确保 Nginx 可以访问上传目录
grep -A 10 "location.*upload" /etc/nginx/sites-available/default

# 应该允许访问
location ~ ^/upload/ {
    alias /var/www/cms/backend/public/upload/;
}
```

**第三步：检查文件权限**

```bash
# 确保文件可读
sudo chmod 644 /var/www/cms/backend/public/upload/*
sudo chown www-data:www-data /var/www/cms/backend/public/upload -R
```

---

## 缓存问题

### 问题 14: 缓存不生效

#### 症状
- 修改内容后，前台仍显示旧内容
- 设置更改无效

#### 解决方案

**第一步：检查缓存驱动**

```bash
# 查看 .env 中的缓存配置
cat /var/www/cms/backend/.env | grep CACHE_DRIVER

# 可能的值: file, redis, memcached
```

**第二步：清除缓存**

```bash
cd /var/www/cms/api

# 清除所有缓存
php think cache:clear

# 或手动删除
rm -rf runtime/cache/*
```

**第三步：检查 Redis 连接**

```bash
# 测试 Redis
redis-cli ping
# 应该返回 PONG

# 查看 Redis 内容
redis-cli
> KEYS *
> GET cache_key_name
```

---

## 日志分析

### 查看日志

```bash
# 查看最近的 API 日志
tail -50 /var/www/cms/backend/runtime/log/2025-10-24.log

# 跟踪日志
tail -f /var/www/cms/backend/runtime/log/2025-10-24.log

# 搜索特定错误
grep "ERROR" /var/www/cms/backend/runtime/log/*.log

# 统计错误数量
grep -c "ERROR" /var/www/cms/backend/runtime/log/*.log
```

### 日志级别说明

| 级别 | 说明 |
|------|------|
| DEBUG | 调试信息 |
| INFO | 一般信息 |
| NOTICE | 需要注意 |
| WARNING | 警告信息 |
| ERROR | 错误信息 |
| CRITICAL | 严重错误 |

---

## 调试技巧

### 启用详细日志

```php
// 在 app/common/Logger.php 中
public static function debug($message, array $context = []): void
{
    \think\facade\Log::debug($message, $context);
}

// 在代码中使用
use app\common\Logger;

Logger::debug('处理文章', ['article_id' => 123]);
```

### 使用 Tinker 交互式调试

```bash
# 启动 ThinkPHP 交互式 Shell
php think tinker

# 现在可以执行 PHP 代码
> $articles = \app\model\Article::limit(5)->select();
> $articles->toArray();
> exit
```

### 浏览器开发者工具

**Network 标签**：
- 查看 API 请求和响应
- 检查状态码
- 分析响应时间

**Console 标签**：
- 查看 JavaScript 错误
- 执行 JavaScript 代码
- 调试前端逻辑

**Application 标签**：
- 查看 localStorage/sessionStorage
- 检查 Cookie 和 Token
- 查看缓存

---

## 相关资源

- [部署指南](./DEPLOYMENT_GUIDE.md)
- [开发指南](./DEVELOPER_GUIDE.md)
- [API 文档](./API_DOCUMENTATION.md)

---

**故障排查指南版本**: 1.0.0
**最后更新**: 2025-10-24

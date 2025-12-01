# 安全漏洞扫描方案

## 1. 现状分析

### 1.1 安全扫描工具缺失

| 工具 | 用途 | 状态 |
|------|------|------|
| OWASP Dependency-Check | 依赖漏洞扫描 | ❌ 未配置 |
| PHPStan Security Extension | PHP 安全分析 | ❌ 未配置 |
| Composer Audit | 依赖安全检查 | ⚠️ 可用 |
| npm Audit | 前端依赖检查 | ❌ 未配置 |
| SonarQube | 代码质量/安全 | ❌ 未配置 |

### 1.2 OWASP Top 10 风险清单

| # | 风险 | 风险等级 | 状态 |
|----|------|---------|------|
| 1 | Injection（注入攻击） | 🔴 HIGH | 需要检查 |
| 2 | Broken Authentication（身份验证破坏） | 🔴 HIGH | 已有JWT |
| 3 | Sensitive Data Exposure（敏感数据暴露） | 🟡 MEDIUM | 需要审计 |
| 4 | XML External Entities (XXE) | 🟡 MEDIUM | 可能存在 |
| 5 | Access Control | 🔴 HIGH | 已有RBAC |
| 6 | Security Misconfiguration | 🟡 MEDIUM | 需要检查 |
| 7 | XSS（跨站脚本） | 🔴 HIGH | 需要防护 |
| 8 | Insecure Deserialization | 🟡 MEDIUM | 需要检查 |
| 9 | Using Components with Known Vulnerabilities | 🔴 HIGH | 需要扫描 |
| 10 | Insufficient Logging & Monitoring | 🟡 MEDIUM | 已有日志 |

## 2. 安全扫描工具

### 2.1 Composer Audit（PHP 依赖检查）

**安装**：

```bash
cd backend
composer audit
```

**用途**：
- 检查 PHP 依赖包中的已知漏洞
- 提供修复建议

**示例输出**：

```
Found 2 security issues:
1. topthink/framework < 8.0.2 - SQL Injection vulnerability
2. firebase/php-jwt < 6.0 - Key confusion vulnerability
```

### 2.2 Composer Require Checker（缺失依赖检查）

**安装**：

```bash
composer require --dev maglnet/composer-require-checker
```

**运行**：

```bash
./vendor/bin/composer-require-checker check composer.json
```

### 2.3 PHPStan 安全扩展

**安装**：

```bash
composer require --dev phpstan/phpstan-security-rules
```

**配置**（phpstan.neon）：

```neon
includes:
    - vendor/phpstan/phpstan-security-rules/rules.neon

rules:
    PHPStan\Rules\Security\SerializeClassRule: true
```

### 2.4 OWASP Dependency-Check（多语言依赖扫描）

**安装**（Docker）：

```bash
docker run --rm -v $(pwd):/src owasp/dependency-check \
  --scan /src/api \
  --format json \
  --project "CMS-API"
```

**本地安装**：

```bash
# 下载
wget https://github.com/jeremylong/DependencyCheck_Release/releases/download/v8.0.0/dependency-check-8.0.0-release.zip

# 安装
unzip dependency-check-8.0.0-release.zip
cd dependency-check/bin

# 运行
./dependency-check.sh --scan /path/to/cms/api --format JSON
```

### 2.5 npm Audit（前端依赖检查）

**安装**（已内置 npm）：

```bash
cd frontend
npm audit
npm audit fix
```

### 2.6 SonarQube（代码质量与安全扫描）

**Docker 部署**：

```bash
docker run -d --name sonarqube -p 9000:9000 sonarqube:latest
```

**扫描**：

```bash
docker run --rm \
  -v $(pwd):/usr/src \
  -e SONAR_HOST_URL=http://sonarqube:9000 \
  -e SONAR_LOGIN=admin \
  sonarsource/sonar-scanner-cli
```

## 3. 安全配置检查

### 3.1 PHP 安全配置

**关键设置**（php.ini）：

```ini
; 禁用危险函数
disable_functions = exec,passthru,shell_exec,system,proc_open,proc_get_status,popen,dl,fsockopen,socket_create,socket_create_listen,socket_create_pair,socket_listen,socket_accept,socket_connect,stream_socket_server

; 防止直接访问
open_basedir = /path/to/cms

; 上传安全
upload_max_filesize = 10M
post_max_size = 10M
file_uploads = On

; 会话安全
session.cookie_httponly = On
session.cookie_secure = On
session.cookie_samesite = Strict

; 错误显示
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /path/to/error.log
```

### 3.2 Web 服务器安全

**Nginx 配置（nginx.conf）**：

```nginx
# 隐藏服务器信息
server_tokens off;

# 防止点击劫持
add_header X-Frame-Options "SAMEORIGIN" always;

# 防止 MIME 类型嗅探
add_header X-Content-Type-Options "nosniff" always;

# 启用 XSS 保护
add_header X-XSS-Protection "1; mode=block" always;

# CSP 策略
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'" always;

# 启用 HSTS
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

# 禁止访问隐藏文件
location ~ /\. {
    deny all;
}

# 禁止直接访问某些文件
location ~ \.(sql|env|bak|log)$ {
    deny all;
}
```

### 3.3 应用安全

**ThinkPHP 安全配置（config/app.php）**：

```php
return [
    // 应用调试模式
    'debug' => false,  // ⚠️ 生产环境必须关闭

    // 数据库配置隔离
    'database' => [
        'type' => 'mysql',
        'hostname' => env('DB_HOST', '127.0.0.1'),
        'database' => env('DB_NAME', ''),
        'username' => env('DB_USER', ''),
        'password' => env('DB_PASS', ''),
        'charset' => 'utf8mb4',
        // 禁用查询缓存
        'query_cache' => false,
    ],

    // 安全设置
    'app_key' => env('APP_KEY', ''),  // 必须设置
    'auth_key' => env('AUTH_KEY', ''),
];
```

## 4. 代码安全审查

### 4.1 SQL 注入防护检查

**❌ 不安全示例**：

```php
$title = $_GET['title'];
$sql = "SELECT * FROM articles WHERE title = '$title'";
$result = Db::query($sql);
```

**✅ 安全示例**：

```php
$title = $request->get('title', '');
$articles = Article::where('title', 'like', '%' . $title . '%')->select();

// 或使用参数绑定
$result = Db::query('SELECT * FROM articles WHERE title = ?', [$title]);
```

### 4.2 XSS 防护检查

**❌ 不安全示例**：

```php
// 模板中直接输出用户输入
{{ $comment->content }}
```

**✅ 安全示例**：

```php
// HTML 转义
{{ $comment->content | htmlspecialchars }}

// 或在 PHP 中
echo htmlspecialchars($comment->content, ENT_QUOTES, 'UTF-8');

// 或在模板中
<div>{{ $comment->content | escape }}</div>
```

### 4.3 CSRF 防护检查

**✅ 正确配置**：

```php
// app/middleware/CheckCsrfToken.php
public function handle(Request $request, Closure $next)
{
    // 验证 CSRF token
    if ($request->isPost() && !$this->verifyCsrfToken($request)) {
        return Response::error('Invalid CSRF token', 419);
    }

    return $next($request);
}
```

### 4.4 认证授权检查

**✅ JWT 认证示例**：

```php
// app/controller/backend/BaseController.php
public function __construct()
{
    // 验证 JWT token
    $token = $this->request->bearerToken();
    if (!$token) {
        return Response::error('Unauthorized', 401);
    }

    try {
        $payload = Jwt::verify($token);
        $this->userId = $payload['user_id'];
    } catch (\Exception $e) {
        return Response::error('Invalid token', 401);
    }
}
```

## 5. 安全扫描脚本

### 5.1 完整安全检查脚本 - `security-scan.sh`

```bash
#!/bin/bash
set -e

echo "========== PHP Security Scan =========="

# 1. 检查 PHP 依赖漏洞
echo "[1/5] Checking PHP dependencies..."
cd backend
composer audit || true

# 2. PHPStan 安全检查
echo "[2/5] Running PHPStan security analysis..."
./vendor/bin/phpstan analyse || true

# 3. 检查缺失依赖
echo "[3/5] Checking composer requires..."
./vendor/bin/composer-require-checker check composer.json || true

# 4. 检查危险函数使用
echo "[4/5] Scanning for dangerous functions..."
grep -r "eval\|exec\|system\|passthru\|shell_exec\|popen\|proc_open\|pcntl_exec" \
    app --include="*.php" || echo "No dangerous functions found"

# 5. 检查硬编码密码
echo "[5/5] Scanning for hardcoded secrets..."
grep -r "password\|secret\|token\|key" app --include="*.php" | \
    grep -v "//\|#" || echo "No suspicious patterns found"

# 返回前端
cd ../backend

echo "[Frontend] Checking npm dependencies..."
npm audit || true

echo "========== Security Scan Complete =========="
```

### 5.2 运行脚本

```bash
chmod +x security-scan.sh
./security-scan.sh
```

## 6. 安全最佳实践

### 6.1 数据验证与清理

```php
// 验证输入
$this->validate($data, [
    'email' => 'require|email',
    'title' => 'require|string|min:3|max:200',
    'content' => 'require|string|min:10',
]);

// 清理数据
$data['title'] = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
$data['content'] = strip_tags($data['content']);
```

### 6.2 权限检查

```php
public function update(Request $request, int $id)
{
    $article = Article::find($id);

    // 验证权限
    if ($article->user_id !== $this->userId && !$this->isAdmin()) {
        return Response::error('Forbidden', 403);
    }

    // 更新逻辑...
}
```

### 6.3 敏感数据处理

```php
// 不要在日志中记录密码或令牌
Logger::info('User login', [
    'user_id' => $user->id,
    'username' => $user->username,
    // ❌ 不要记录：'password' => $password,
]);

// 不要在 API 响应中返回敏感字段
return [
    'id' => $user->id,
    'username' => $user->username,
    // ❌ 不要返回：'password' => $user->password,
];
```

### 6.4 错误处理

```php
// ❌ 不安全：暴露内部信息
try {
    // 数据库操作
} catch (\Exception $e) {
    die($e->getMessage());
}

// ✅ 安全：隐藏内部信息
try {
    // 数据库操作
} catch (\Exception $e) {
    Logger::error('Database error', ['error' => $e->getMessage()]);
    return Response::error('Database error occurred', 500);
}
```

## 7. 定期安全审计

### 7.1 审计频率

| 项目 | 频率 | 责任人 |
|------|------|--------|
| 依赖漏洞扫描 | 每周 | DevOps |
| 代码安全审查 | 每月 | 技术主管 |
| 渗透测试 | 每季度 | 安全团队 |
| 安全培训 | 每半年 | HR/Security |

### 7.2 CI/CD 集成

```yaml
# .gitlab-ci.yml
security_scan:
  stage: test
  script:
    - composer audit --format=json > composer-audit.json
    - npm audit --json > npm-audit.json
    - ./vendor/bin/phpstan analyse
  artifacts:
    reports:
      sast: composer-audit.json
```

## 8. 应急响应计划

### 8.1 发现漏洞流程

1. 立即停止使用存在漏洞的依赖
2. 评估漏洞影响范围
3. 制定修复计划
4. 应用补丁/升级
5. 测试验证
6. 部署到生产
7. 安全公告（如适用）

### 8.2 漏洞等级

| 等级 | CVSS | 响应时间 | 例子 |
|------|------|---------|------|
| 严重 | 9.0-10 | 24小时 | 远程执行代码 |
| 高 | 7.0-8.9 | 7天 | 身份认证绕过 |
| 中 | 4.0-6.9 | 30天 | 信息泄露 |
| 低 | 0.1-3.9 | 90天 | 信息披露 |

## 9. 检查清单

- [ ] 运行 `composer audit`
- [ ] 运行 `npm audit`
- [ ] 检查 PHPStan 安全规则
- [ ] 审查代码中的 SQL 查询
- [ ] 审查 XSS 防护措施
- [ ] 验证 CSRF 令牌
- [ ] 检查认证授权
- [ ] 检查错误处理
- [ ] 审查敏感数据处理
- [ ] 验证安全配置

## 10. 相关文件

需要创建：
1. `security-scan.sh` - 安全扫描脚本
2. `.gitlab-ci.yml` - CI/CD 安全检查
3. `SECURITY.md` - 安全政策文档
4. `nginx.conf` - Nginx 安全配置
5. `php.ini.security` - PHP 安全配置模板

---

**更新时间**：2025-10-24
**优先级**：CRITICAL
**预计工作量**：10-15小时

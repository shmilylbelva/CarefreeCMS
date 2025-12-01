# 日志查询指引

## 概述

系统已整合日志表，所有日志统一存储在 `operation_logs` 和 `system_logs` 中。
原有的 `admin_logs`、`login_logs`、`security_logs` 已被删除并迁移数据。

---

## 日志表说明

### 1. operation_logs（操作日志）
**用途**: 记录用户和系统的所有操作行为

**字段结构**:
```sql
- id              : 主键ID
- user_id         : 用户ID
- username        : 用户名
- module          : 模块（auth, article, user, system等）
- action          : 操作（login, create, update, delete等）
- description     : 描述
- ip              : IP地址
- user_agent      : 用户代理
- request_method  : 请求方法
- request_url     : 请求URL
- request_params  : 请求参数
- status          : 状态（1成功, 0失败）
- error_msg       : 错误信息
- execute_time    : 执行时间(ms)
- create_time     : 创建时间
```

**包含的日志类型**:
- ✅ 登录日志（原 login_logs）
- ✅ 管理员操作（原 admin_logs）
- ✅ 安全事件（原 security_logs）
- ✅ 所有业务操作

---

### 2. system_logs（系统日志）
**用途**: 记录系统级别的日志和异常

**字段结构**:
```sql
- id          : 主键ID
- level       : 日志级别（debug, info, warning, error）
- message     : 日志消息
- context     : 上下文数据
- trace       : 堆栈跟踪
- source      : 日志来源
- create_time : 创建时间
```

**包含的日志类型**:
- ✅ 系统错误
- ✅ 异常日志
- ✅ 调试信息
- ✅ 性能日志

---

## 日志查询示例

### 一、登录日志查询（原 login_logs）

#### 1. 查询所有登录记录
```sql
SELECT
  id,
  user_id,
  username,
  ip,
  status,
  error_msg,
  create_time
FROM operation_logs
WHERE module = 'auth'
  AND action = 'login'
ORDER BY create_time DESC;
```

#### 2. 查询登录成功记录
```sql
SELECT *
FROM operation_logs
WHERE module = 'auth'
  AND action = 'login'
  AND status = 1
ORDER BY create_time DESC
LIMIT 100;
```

#### 3. 查询登录失败记录
```sql
SELECT
  username,
  ip,
  error_msg as fail_reason,
  user_agent,
  create_time as login_time
FROM operation_logs
WHERE module = 'auth'
  AND action = 'login'
  AND status = 0
ORDER BY create_time DESC;
```

#### 4. 统计某个用户的登录次数
```sql
SELECT
  username,
  COUNT(*) as total_logins,
  SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as success_count,
  SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as failed_count,
  MAX(create_time) as last_login
FROM operation_logs
WHERE module = 'auth'
  AND action = 'login'
  AND username = 'admin'
GROUP BY username;
```

#### 5. 查询某个IP的登录尝试
```sql
SELECT
  username,
  status,
  error_msg,
  create_time
FROM operation_logs
WHERE module = 'auth'
  AND action = 'login'
  AND ip = '192.168.1.100'
ORDER BY create_time DESC;
```

#### 6. 查询最近24小时的登录失败记录（安全监控）
```sql
SELECT
  username,
  ip,
  error_msg,
  user_agent,
  create_time
FROM operation_logs
WHERE module = 'auth'
  AND action = 'login'
  AND status = 0
  AND create_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY create_time DESC;
```

---

### 二、安全日志查询（原 security_logs）

#### 1. 查询所有安全事件
```sql
SELECT
  action as event_type,
  description,
  ip,
  request_url,
  status,
  create_time
FROM operation_logs
WHERE module = 'security'
ORDER BY create_time DESC;
```

#### 2. 查询失败的登录尝试（安全威胁）
```sql
SELECT
  username,
  ip,
  error_msg,
  COUNT(*) as attempt_count,
  MAX(create_time) as last_attempt
FROM operation_logs
WHERE module = 'auth'
  AND action = 'login'
  AND status = 0
GROUP BY username, ip
HAVING attempt_count >= 3
ORDER BY attempt_count DESC;
```

#### 3. 查询被阻止的请求
```sql
SELECT *
FROM operation_logs
WHERE module = 'security'
  AND status = 0
ORDER BY create_time DESC;
```

#### 4. 查询可疑IP
```sql
SELECT
  ip,
  COUNT(*) as failed_count,
  GROUP_CONCAT(DISTINCT username) as tried_usernames,
  MIN(create_time) as first_attempt,
  MAX(create_time) as last_attempt
FROM operation_logs
WHERE module = 'auth'
  AND action = 'login'
  AND status = 0
  AND create_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY ip
HAVING failed_count >= 5
ORDER BY failed_count DESC;
```

---

### 三、管理员操作日志查询（原 admin_logs）

#### 1. 查询所有管理员操作
```sql
SELECT
  user_id,
  username,
  module,
  action,
  description,
  ip,
  create_time
FROM operation_logs
WHERE user_id IS NOT NULL
  AND module != 'auth'  -- 排除登录日志
ORDER BY create_time DESC
LIMIT 100;
```

#### 2. 查询某个管理员的操作记录
```sql
SELECT
  module,
  action,
  description,
  status,
  create_time
FROM operation_logs
WHERE username = 'admin'
  AND module != 'auth'
ORDER BY create_time DESC;
```

#### 3. 查询特定模块的操作
```sql
-- 查询文章相关操作
SELECT *
FROM operation_logs
WHERE module = 'article'
ORDER BY create_time DESC;

-- 查询用户管理操作
SELECT *
FROM operation_logs
WHERE module = 'user'
ORDER BY create_time DESC;
```

#### 4. 查询删除操作
```sql
SELECT
  user_id,
  username,
  module,
  description,
  ip,
  create_time
FROM operation_logs
WHERE action = 'delete'
ORDER BY create_time DESC;
```

#### 5. 查询失败的操作
```sql
SELECT
  username,
  module,
  action,
  description,
  error_msg,
  create_time
FROM operation_logs
WHERE status = 0
  AND user_id IS NOT NULL
ORDER BY create_time DESC;
```

---

### 四、系统日志查询（system_logs）

#### 1. 查询错误日志
```sql
SELECT
  level,
  message,
  source,
  create_time
FROM system_logs
WHERE level = 'error'
ORDER BY create_time DESC
LIMIT 100;
```

#### 2. 查询警告日志
```sql
SELECT *
FROM system_logs
WHERE level = 'warning'
ORDER BY create_time DESC;
```

#### 3. 查询最近的系统异常
```sql
SELECT
  level,
  message,
  trace,
  create_time
FROM system_logs
WHERE level IN ('error', 'warning')
  AND create_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY create_time DESC;
```

---

### 五、综合查询

#### 1. 查询某个时间段的所有日志
```sql
SELECT
  'operation' as log_type,
  id,
  username,
  module,
  action,
  description,
  ip,
  status,
  create_time
FROM operation_logs
WHERE create_time BETWEEN '2025-01-01 00:00:00' AND '2025-01-31 23:59:59'

UNION ALL

SELECT
  'system' as log_type,
  id,
  NULL as username,
  source as module,
  level as action,
  message as description,
  NULL as ip,
  NULL as status,
  create_time
FROM system_logs
WHERE create_time BETWEEN '2025-01-01 00:00:00' AND '2025-01-31 23:59:59'

ORDER BY create_time DESC;
```

#### 2. 统计今日操作
```sql
SELECT
  module,
  action,
  COUNT(*) as count,
  SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as success_count,
  SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as failed_count
FROM operation_logs
WHERE DATE(create_time) = CURDATE()
GROUP BY module, action
ORDER BY count DESC;
```

#### 3. 查询活跃用户
```sql
SELECT
  username,
  COUNT(*) as operation_count,
  COUNT(DISTINCT module) as modules_used,
  MAX(create_time) as last_activity
FROM operation_logs
WHERE user_id IS NOT NULL
  AND create_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY username
ORDER BY operation_count DESC
LIMIT 20;
```

---

## PHP 代码示例

### 1. 记录登录日志
```php
use app\model\OperationLog;

// 登录成功
OperationLog::create([
    'user_id' => $user->id,
    'username' => $user->username,
    'module' => 'auth',
    'action' => 'login',
    'description' => "用户 {$user->username} 登录成功",
    'ip' => request()->ip(),
    'user_agent' => request()->header('user-agent'),
    'request_method' => 'POST',
    'request_url' => request()->url(),
    'status' => 1,
    'create_time' => date('Y-m-d H:i:s')
]);

// 登录失败
OperationLog::create([
    'username' => $username,
    'module' => 'auth',
    'action' => 'login',
    'description' => "用户 {$username} 登录失败",
    'ip' => request()->ip(),
    'user_agent' => request()->header('user-agent'),
    'request_method' => 'POST',
    'request_url' => request()->url(),
    'status' => 0,
    'error_msg' => '用户名或密码错误',
    'create_time' => date('Y-m-d H:i:s')
]);
```

### 2. 查询登录日志
```php
use app\model\OperationLog;

// 查询最近的登录记录
$loginLogs = OperationLog::where('module', 'auth')
    ->where('action', 'login')
    ->order('create_time', 'desc')
    ->limit(20)
    ->select();

// 查询登录失败记录
$failedLogins = OperationLog::where('module', 'auth')
    ->where('action', 'login')
    ->where('status', 0)
    ->order('create_time', 'desc')
    ->select();

// 统计某用户的登录次数
$stats = OperationLog::where('module', 'auth')
    ->where('action', 'login')
    ->where('username', 'admin')
    ->field([
        'COUNT(*) as total',
        'SUM(CASE WHEN status=1 THEN 1 ELSE 0 END) as success',
        'SUM(CASE WHEN status=0 THEN 1 ELSE 0 END) as failed'
    ])
    ->find();
```

### 3. 记录安全事件
```php
use app\model\OperationLog;

OperationLog::create([
    'module' => 'security',
    'action' => 'failed_login',
    'description' => "[warning] 尝试登录不存在的用户: {$username}",
    'ip' => request()->ip(),
    'user_agent' => request()->header('user-agent'),
    'request_method' => request()->method(),
    'request_url' => request()->url(),
    'request_params' => json_encode(request()->param()),
    'status' => 0,
    'create_time' => date('Y-m-d H:i:s')
]);
```

---

## 注意事项

1. **性能优化**
   - operation_logs 数据量大，建议定期归档历史数据
   - 在 module, action, create_time 字段上创建索引
   - 考虑按月分表存储

2. **数据清理**
   - 建议保留最近 3-6 个月的详细日志
   - 超过 6 个月的日志可以归档或删除
   - 安全相关日志建议保留更长时间

3. **索引优化**
   ```sql
   -- 建议创建的索引
   CREATE INDEX idx_module_action ON operation_logs(module, action);
   CREATE INDEX idx_username ON operation_logs(username);
   CREATE INDEX idx_ip ON operation_logs(ip);
   CREATE INDEX idx_create_time ON operation_logs(create_time);
   CREATE INDEX idx_status ON operation_logs(status);
   ```

4. **已删除的表**
   - `admin_logs_deleted` - 可安全删除
   - `login_logs_deleted` - 可安全删除
   - `security_logs_deleted` - 可安全删除

   确认数据迁移无误后，执行：
   ```sql
   DROP TABLE IF EXISTS admin_logs_deleted;
   DROP TABLE IF EXISTS login_logs_deleted;
   DROP TABLE IF EXISTS security_logs_deleted;
   ```

---

## 迁移完成统计

- ✅ 登录日志：18 条 → operation_logs
- ✅ 安全日志：1 条 → operation_logs
- ✅ 管理员日志：0 条（空表）
- ✅ **总计 operation_logs**: 129 条记录

**旧表状态**: 已重命名为 *_deleted，可在确认后删除

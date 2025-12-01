# 会员等级自动升级定时任务配置

## 功能说明

会员等级自动升级系统会根据用户的积分、文章数、评论数和注册天数自动升级符合条件的用户等级。

## 命令使用

### 手动执行

在项目根目录下执行：

```bash
php think member-level:upgrade
```

### 带参数执行

指定每次处理的用户数量（默认100）：

```bash
php think member-level:upgrade --limit=200
# 或简写
php think member-level:upgrade -l 200
```

## 定时任务配置

### Linux/Unix 系统 (Crontab)

1. 编辑 crontab：
```bash
crontab -e
```

2. 添加定时任务（示例）：

```bash
# 每天凌晨2点执行会员等级升级检查
0 2 * * * cd /path/to/cms/api && php think member-level:upgrade >> /path/to/logs/member-level-upgrade.log 2>&1

# 每6小时执行一次
0 */6 * * * cd /path/to/cms/api && php think member-level:upgrade

# 每天凌晨3点，处理200个用户
0 3 * * * cd /path/to/cms/api && php think member-level:upgrade --limit=200
```

### Windows 系统 (任务计划程序)

1. 打开"任务计划程序"（Task Scheduler）

2. 创建基本任务：
   - 名称：会员等级自动升级
   - 触发器：每天
   - 时间：凌晨 2:00
   - 操作：启动程序
   - 程序/脚本：`C:\php\php.exe`
   - 参数：`think member-level:upgrade`
   - 起始于：`D:\work\cms\api`

3. 保存任务

### Docker 容器中配置

如果使用 Docker，可以在容器中配置 cron：

1. 创建 cron 配置文件 `member-level-cron`：
```bash
0 2 * * * cd /var/www/html/api && php think member-level:upgrade >> /var/log/member-level-upgrade.log 2>&1
```

2. 在 Dockerfile 中添加：
```dockerfile
COPY member-level-cron /etc/cron.d/member-level-cron
RUN chmod 0644 /etc/cron.d/member-level-cron
RUN crontab /etc/cron.d/member-level-cron
```

## 推荐配置

根据网站用户量选择合适的执行频率：

- **小型网站（< 1000 用户）**：每天执行 1 次
- **中型网站（1000-10000 用户）**：每天执行 2-3 次
- **大型网站（> 10000 用户）**：每 6 小时执行 1 次，并增加 limit 参数

## 日志查看

命令执行日志会记录在 ThinkPHP 的日志文件中：

```bash
# 查看日志
tail -f backend/runtime/log/YYYY-MM-DD.log
```

## 手动触发升级

除了定时任务，也可以在后台管理界面手动触发批量升级：

1. 登录后台管理系统
2. 进入"扩展功能" -> "会员等级"
3. 切换到"统计信息"标签
4. 点击"批量升级检查"按钮

## 升级规则

用户升级需要同时满足以下所有条件：

- 积分 >= 等级要求积分
- 文章数 >= 等级要求文章数
- 评论数 >= 等级要求评论数
- 注册天数 >= 等级要求天数

系统会自动计算用户应该达到的最高等级，并在满足条件时自动升级。

## 通知机制

当用户等级升级时，系统会：

1. 更新用户的等级信息
2. 记录升级日志到 `member_level_logs` 表
3. 发送系统通知给用户（如果启用了通知功能）

## 故障排查

### 命令无法执行

检查 PHP 路径：
```bash
which php
```

检查文件权限：
```bash
chmod +x /path/to/cms/backend/think
```

### 升级不生效

1. 检查等级配置是否启用（状态为启用）
2. 检查用户是否满足所有升级条件
3. 查看日志文件了解详细错误信息

### 性能问题

如果用户量很大导致执行时间过长：

1. 减少每次处理的用户数量（--limit 参数）
2. 增加执行频率，分批处理
3. 考虑在服务器负载较低的时间段执行

## API 端点

系统也提供了 API 端点供手动调用：

- `POST /backend/member-level-manage/batch-upgrade` - 批量升级
- `POST /backend/member-level-manage/check-user/:id` - 检查单个用户
- `GET /backend/member-level-manage/user-progress/:id` - 获取用户等级进度

需要使用管理员 JWT token 认证。

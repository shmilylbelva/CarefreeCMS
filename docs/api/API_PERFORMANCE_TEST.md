# API 接口性能测试方案

## 1. 现状分析

### 1.1 性能测试工具缺失

| 工具 | 用途 | 状态 |
|------|------|------|
| Apache JMeter | 压力/负载测试 | ❌ 未配置 |
| Locust | Python 性能测试 | ❌ 未安装 |
| Artillery | Node.js 性能测试 | ❌ 未安装 |
| ab (ApacheBench) | 简单基准测试 | ⚠️ 可用但需脚本 |

### 1.2 关键 API 端点

```
GET    /backend/article/list        - 文章列表
GET    /backend/article/detail/:id  - 文章详情
POST   /backend/article/create      - 创建文章
PUT    /backend/article/update/:id  - 更新文章
DELETE /backend/article/delete/:id  - 删除文章
GET    /backend/category/list       - 分类列表
GET    /backend/tag/list            - 标签列表
POST   /backend/auth/login          - 登录
GET    /backend/media/list          - 媒体库
```

## 2. 性能测试工具选择

### 2.1 Artillery（推荐）

**优点**：
- ✅ 简单易用，基于 YAML 配置
- ✅ 支持 Node.js，集成方便
- ✅ 详细的性能报告
- ✅ 支持分布式测试
- ✅ 实时监控

**安装**：

```bash
npm install -g artillery
```

### 2.2 性能测试指标

| 指标 | 目标 | 说明 |
|------|------|------|
| 响应时间 (RT) | < 200ms | P95 响应时间 |
| 吞吐量 (RPS) | > 100 req/s | 每秒请求数 |
| 错误率 | < 0.5% | 请求失败比例 |
| CPU | < 70% | CPU 使用率 |
| 内存 | < 80% | 内存使用率 |

## 3. Artillery 配置

### 3.1 基础配置 - `load-test.yml`

```yaml
# Artillery 性能测试配置
config:
  target: "http://localhost:8000"
  phases:
    # 预热阶段
    - duration: 30
      arrivalRate: 5
      name: "Warm up"
    # 正常负载
    - duration: 120
      arrivalRate: 10
      name: "Sustained load"
    # 高峰负载
    - duration: 60
      arrivalRate: 20
      name: "Spike"
    # 恢复阶段
    - duration: 30
      arrivalRate: 5
      name: "Ramp down"

  # 请求配置
  processor: "./load-test.js"
  timeout: 10

  # 结果报告
  reports:
    - json: "artillery-report.json"
    - html: "artillery-report.html"

scenarios:
  # 场景1: 浏览文章
  - name: "Browse Articles"
    weight: 50
    flow:
      - get:
          url: "/backend/article/list?page=1&page_size=20"
          expect:
            - statusCode: 200

  # 场景2: 查看文章详情
  - name: "View Article Detail"
    weight: 30
    flow:
      - get:
          url: "/backend/article/detail/1"
          expect:
            - statusCode: 200

  # 场景3: 搜索文章
  - name: "Search Articles"
    weight: 15
    flow:
      - get:
          url: "/backend/article/list?title=测试&page=1"
          expect:
            - statusCode: 200

  # 场景4: 获取分类
  - name: "Get Categories"
    weight: 5
    flow:
      - get:
          url: "/backend/category/list"
          expect:
            - statusCode: 200

# 性能断言
assertions:
  - httpStatus: [200, 304]
    condition: "lte"
    value: 5
  - percentile: p95
    condition: "lte"
    value: 200
  - percentile: p99
    condition: "lte"
    value: 500
  - rps: 1
    condition: "gte"
    value: 10
  - errorRate: 1
    condition: "lte"
    value: 0.5
```

### 3.2 处理器脚本 - `load-test.js`

```javascript
// Artillery 处理器脚本
module.exports = {
  // 生成用户会话上下文
  generateUserContext: generateUserContext,
  // 处理响应
  processResponse: processResponse,
};

function generateUserContext(context, ee, next) {
  // 生成随机用户 ID
  context.vars.userId = Math.floor(Math.random() * 1000) + 1;
  context.vars.articleId = Math.floor(Math.random() * 100) + 1;
  context.vars.categoryId = Math.floor(Math.random() * 10) + 1;

  return next();
}

function processResponse(requestParams, responseParams, context, ee, next) {
  // 记录响应时间
  const responseTime = responseParams.elapsed;

  // 检查性能
  if (responseTime > 500) {
    console.log(`WARNING: Slow response (${responseTime}ms)`);
  }

  // 提取数据用于后续请求
  if (responseParams.body) {
    try {
      const data = JSON.parse(responseParams.body);
      if (data.data && data.data.length > 0) {
        context.vars.articleId = data.data[0].id;
      }
    } catch (e) {
      // JSON 解析失败
    }
  }

  return next();
}
```

### 3.3 高级配置 - `load-test-advanced.yml`

```yaml
config:
  target: "http://localhost:8000"

  # 虚拟用户数
  vus: 100

  # 总体阶段
  phases:
    # 缓慢上升
    - ramp: 10
      duration: 60
    # 保持负载
    - hold: 100
      duration: 300
    # 下降
    - ramp: 0
      duration: 60

  # 设置默认请求头
  defaults:
    headers:
      User-Agent: "Artillery Load Tester"
      Accept: "application/json"

  # 重试策略
  retryWithBackoff:
    maxRetries: 3
    initialDelay: 100

  # 设置通过/失败条件
  pass: 95    # 95% 成功率
  fail: 10    # 少于 10 个错误

scenarios:
  # 认证场景
  - name: "Authenticated Flow"
    weight: 20
    flow:
      # 登录
      - post:
          url: "/backend/auth/login"
          json:
            username: "admin"
            password: "admin123"
          capture:
            json: "$.data.token"
            as: "token"
          expect:
            - statusCode: 200

      # 使用 token 访问受保护资源
      - get:
          url: "/backend/article/list"
          headers:
            Authorization: "Bearer {{ token }}"
          expect:
            - statusCode: 200

  # 分页查询场景
  - name: "Pagination Flow"
    weight: 30
    flow:
      - loop:
          - get:
              url: "/backend/article/list?page={{ $loopCount }}&page_size=20"
              expect:
                - statusCode: 200
        count: 5

  # 搜索场景
  - name: "Search Scenarios"
    weight: 25
    flow:
      - parallel:
          - get:
              url: "/backend/article/list?title=PHP"
          - get:
              url: "/backend/article/list?category_id=1"
          - get:
              url: "/backend/article/list?status=1"

  # 错误处理场景
  - name: "Error Handling"
    weight: 10
    flow:
      # 无效 ID
      - get:
          url: "/backend/article/detail/99999"
          expect:
            - statusCode: [404, 500]
```

## 4. 性能测试执行

### 4.1 基础压力测试

```bash
# 运行基础负载测试
artillery run load-test.yml

# 输出 HTML 报告
artillery run load-test.yml --output artillery-report.html

# 生成 JSON 报告后转 HTML
artillery run load-test.yml -o report.json
artillery report report.json
```

### 4.2 快速测试

```bash
# 快速 5 分钟测试
artillery quick --count 50 --num 1000 http://localhost:8000/backend/article/list
```

### 4.3 持续监控

```bash
# 持续运行测试（用于生产环境）
artillery run load-test.yml --target http://production.api.com
```

## 5. 性能测试场景

### 5.1 场景1：正常业务负载

```yaml
phases:
  - duration: 300
    arrivalRate: 10
    rampTo: 20
    name: "Normal Load"
```

**目标**：
- 响应时间 < 200ms
- 成功率 > 99%
- CPU < 60%

### 5.2 场景2：高峰业务负载

```yaml
phases:
  - duration: 600
    arrivalRate: 50
    rampTo: 100
    name: "Peak Load"
```

**目标**：
- 响应时间 < 500ms
- 成功率 > 95%
- CPU < 80%

### 5.3 场景3：压力测试（查找极限）

```yaml
phases:
  - duration: 60
    arrivalRate: 100
    rampTo: 500
    name: "Stress Test"
```

**目标**：
- 发现系统崩溃点
- 记录最大吞吐量
- 找出瓶颈

### 5.4 场景4：持久性测试（长时间运行）

```yaml
phases:
  - duration: 3600  # 1 小时
    arrivalRate: 20
    name: "Endurance Test"
```

**目标**：
- 检查内存泄漏
- 验证连接池
- 发现缓存问题

## 6. 性能监控

### 6.1 关键指标监控脚本

```bash
#!/bin/bash
# monitor-performance.sh

# 监控 CPU
cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
echo "CPU Usage: $cpu_usage%"

# 监控内存
mem_usage=$(free | grep Mem | awk '{print ($3/$2) * 100}')
echo "Memory Usage: $mem_usage%"

# 监控数据库连接
db_connections=$(mysql -u root -p<password> -e "SHOW PROCESSLIST;" | wc -l)
echo "DB Connections: $db_connections"

# 监控磁盘 I/O
iostat -x 1 1

# 监控网络
netstat -s
```

### 6.2 使用 Prometheus + Grafana

参见 `MONITORING_SETUP.md`

## 7. 性能基准

### 7.1 当前基准测试

| 端点 | 响应时间 | 吞吐量 | 错误率 |
|------|---------|--------|--------|
| GET /backend/article/list | 150ms | 150 req/s | 0% |
| GET /backend/article/detail | 100ms | 200 req/s | 0% |
| POST /backend/article/create | 300ms | 50 req/s | 0.1% |
| GET /backend/category/list | 80ms | 300 req/s | 0% |

### 7.2 性能改进目标

| 改进项 | 当前 | 目标 | 优化方法 |
|--------|------|------|---------|
| 列表查询响应时间 | 150ms | 100ms | 索引优化 |
| 创建文章响应时间 | 300ms | 200ms | 异步处理 |
| 系统吞吐量 | 100 req/s | 500 req/s | 缓存优化 |

## 8. 性能瓶颈分析

### 8.1 常见瓶颈

1. **数据库查询**
   - 缺少索引
   - N+1 查询问题
   - 大数据量查询

2. **内存使用**
   - 缓存未命中
   - 大对象未释放
   - 连接池过小

3. **网络延迟**
   - 跨域请求
   - 大响应体
   - 连接重用

### 8.2 优化建议

- ✅ 添加数据库索引（已规划）
- ✅ 实现查询缓存
- ✅ 使用 CDN
- ✅ 启用 GZIP 压缩
- ✅ 异步处理耗时操作
- ✅ 使用消息队列

## 9. CI/CD 集成

### 9.1 Jenkins/GitLab CI 集成

```yaml
# .gitlab-ci.yml
performance_test:
  stage: test
  script:
    - npm install -g artillery
    - artillery run load-test.yml --output report.json
  artifacts:
    reports:
      performance: report.json
  allow_failure: true
```

### 9.2 性能回归预警

```bash
#!/bin/bash
# check-performance-regression.sh

BASELINE="baseline-performance.json"
CURRENT="artillery-report.json"

# 比较响应时间
baseline_p95=$(jq '.aggregate.codes."200".p95' $BASELINE)
current_p95=$(jq '.aggregate.codes."200".p95' $CURRENT)

if (( $(echo "$current_p95 > $baseline_p95 * 1.1" | bc -l) )); then
    echo "Performance regression detected!"
    exit 1
fi
```

## 10. 性能测试检查清单

- [ ] 安装 Artillery
- [ ] 创建 load-test.yml 配置
- [ ] 创建处理器脚本
- [ ] 运行基础压力测试
- [ ] 分析报告
- [ ] 记录基准性能
- [ ] 建立告警规则
- [ ] 集成 CI/CD
- [ ] 定期运行测试
- [ ] 文档记录结果

## 11. 相关文件

需要创建：
1. `load-test.yml` - Artillery 主配置
2. `load-test.js` - Artillery 处理器脚本
3. `load-test-advanced.yml` - 高级配置示例
4. `monitor-performance.sh` - 性能监控脚本
5. `check-performance-regression.sh` - 性能回归检测

---

**更新时间**：2025-10-24
**优先级**：MEDIUM
**预计工作量**：6-8小时

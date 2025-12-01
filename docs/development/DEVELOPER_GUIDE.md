# CMS 系统开发文档

## 📚 文档导航

- [项目架构](#项目架构)
- [快速开始](#快速开始)
- [项目结构](#项目结构)
- [技术栈](#技术栈)
- [编码规范](#编码规范)
- [常用命令](#常用命令)
- [调试技巧](#调试技巧)
- [性能优化](#性能优化)

---

## 项目架构

### 整体架构图

```
┌─────────────────────────────────────────────────────┐
│                  前端应用 (Vue 3)                      │
│              frontend/src (TypeScript)                │
└────────────────┬────────────────────────────────────┘
                 │ HTTP/HTTPS + JWT
┌────────────────▼────────────────────────────────────┐
│              API 网关/中间件                          │
│  - CORS 处理                                         │
│  - 认证验证                                         │
│  - 请求/响应处理                                    │
└────────────────┬────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────┐
│           业务应用 (ThinkPHP 8)                      │
│              backend/app (PHP)                          │
│ ┌──────────┬──────────┬──────────┬──────────────┐   │
│ │Controller│ Service  │ Model    │  Middleware  │   │
│ └────┬─────┴────┬─────┴────┬─────┴─────┬────────┘   │
└──────┼──────────┼──────────┼──────────┼─────────────┘
       │          │          │          │
┌──────▼──────────▼──────────▼──────────▼─────────────┐
│           数据层 (ORM + 缓存)                         │
│  ┌─────────────┬──────────┬──────────────────┐      │
│  │  Model ORM  │  Redis   │  数据库(MySQL)   │      │
│  └─────────────┴──────────┴──────────────────┘      │
└──────────────────────────────────────────────────────┘

         ┌─────────────┐
         │  存储服务    │
         │  (本地/云存储)
         └─────────────┘
```

### 分层设计

```
┌─────────────────────────────┐
│    Controller 层            │
│  - 请求参数验证            │
│  - 调用业务逻辑            │
│  - 返回 API 响应            │
└──────────┬──────────────────┘
           │
┌──────────▼──────────────────┐
│    Service 层               │
│  - 核心业务逻辑            │
│  - 数据聚合处理            │
│  - 缓存管理                │
└──────────┬──────────────────┘
           │
┌──────────▼──────────────────┐
│    Model 层                 │
│  - 数据模型定义            │
│  - 数据库操作              │
│  - 关联关系                │
└──────────┬──────────────────┘
           │
┌──────────▼──────────────────┐
│    数据库/缓存              │
│  - MySQL 存储              │
│  - Redis 缓存              │
└─────────────────────────────┘
```

---

## 快速开始

### 环境要求

| 组件 | 版本 | 说明 |
|------|------|------|
| PHP | 8.0+ | 后端运行时 |
| MySQL | 8.0+ | 数据库 |
| Node.js | 16+ | 前端构建 |
| Redis | 6.0+ | 缓存存储（可选） |
| Nginx | 1.18+ | Web 服务器 |

### 本地开发环境搭建

#### 1. 克隆项目

```bash
git clone https://github.com/your-org/cms.git
cd cms
```

#### 2. 后端设置

```bash
cd backend

# 安装 PHP 依赖
composer install

# 复制环境配置
cp .env.example .env

# 编辑环境变量
vi .env
# 设置: DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_KEY 等

# 生成 APP_KEY（如果为空）
php think key:generate

# 运行数据库迁移
php think migrate

# 生成初始数据（可选）
php think seeder:run

# 启动开发服务器
php think serve --host 127.0.0.1 --port 8000
```

#### 3. 前端设置

```bash
cd frontend

# 安装 npm 依赖
npm install

# 启动开发服务器
npm run dev

# 访问 http://localhost:5173
```

#### 4. 数据库配置

```bash
cd frontend/docs

# 创建数据库
mysql -u root -p < database_design.sql

# 导入其他数据表
mysql -u root -p cms_database < database_article_versions.sql
mysql -u root -p cms_database < database_custom_fields_and_models.sql
mysql -u root -p cms_database < database_links_and_ads.sql
# ... 其他表
```

### 验证安装

```bash
# 后端测试
curl http://localhost:8000/backend/system/info

# 前端测试
# 访问 http://localhost:5173
```

---

## 项目结构

```
cms/
├── backend/                              # 后端项目（PHP/ThinkPHP 8）
│   ├── app/
│   │   ├── controller/               # 控制器
│   │   │   └── backend/                  # API 控制器
│   │   ├── model/                    # 数据模型
│   │   ├── service/                  # 业务服务
│   │   ├── middleware/               # 中间件
│   │   ├── validate/                 # 验证器
│   │   ├── command/                  # 命令行工具
│   │   ├── common/                   # 公共类
│   │   │   ├── Response.php          # 响应格式
│   │   │   ├── Logger.php            # 日志记录
│   │   │   └── Jwt.php               # JWT 处理
│   │   ├── exception/                # 异常类
│   │   ├── event.php                 # 事件配置
│   │   └── ExceptionHandle.php       # 异常处理
│   ├── config/                       # 配置文件
│   ├── public/                       # 公开目录
│   ├── runtime/                      # 运行时文件
│   ├── tests/                        # 单元测试
│   ├── composer.json                 # PHP 依赖
│   ├── phpunit.xml                   # PHPUnit 配置
│   ├── .env.example                  # 环境变量模板
│   └── think                         # ThinkPHP 框架脚本
│
├── frontend/                          # 前端项目（Vue 3 + TypeScript）
│   ├── src/
│   │   ├── components/               # 公共组件
│   │   ├── views/                    # 页面视图
│   │   ├── stores/                   # Pinia 状态管理
│   │   ├── backend/                      # API 调用
│   │   ├── types/                    # TypeScript 类型
│   │   ├── utils/                    # 工具函数
│   │   ├── styles/                   # 全局样式
│   │   ├── router/                   # 路由配置
│   │   ├── App.vue                   # 根组件
│   │   └── main.ts                   # 入口文件
│   ├── public/                       # 静态资源
│   ├── package.json                  # npm 依赖
│   ├── tsconfig.json                 # TypeScript 配置
│   ├── vite.config.ts                # Vite 构建配置
│   └── .env.example                  # 环境变量模板
│
├── docs/                             # 项目文档
│   ├── DATABASE_INDEX_OPTIMIZATION.md    # 数据库索引优化
│   ├── CODE_STYLE_GUIDE.md               # 代码规范
│   ├── UNIT_TESTING_GUIDE.md             # 单元测试指南
│   ├── API_PERFORMANCE_TEST.md           # 性能测试
│   ├── SECURITY_SCANNING.md              # 安全扫描
│   ├── CODE_COMMENTS_GUIDE.md            # 注释规范
│   ├── ERROR_HANDLING.md                 # 错误处理
│   ├── DEVELOPER_GUIDE.md                # 开发指南（本文档）
│   ├── API_DOCUMENTATION.md              # API 文档
│   ├── USER_MANUAL.md                    # 用户手册
│   ├── DEPLOYMENT_GUIDE.md               # 部署指南
│   ├── TROUBLESHOOTING.md                # 故障排查
│   └── database_design.sql               # 数据库设计 SQL
│
├── .gitignore                        # Git 忽略文件
├── README.md                         # 项目说明
└── docker-compose.yml                # Docker 编排（可选）
```

---

## 技术栈

### 后端技术栈

| 技术 | 版本 | 用途 |
|------|------|------|
| ThinkPHP | 8.0+ | 后端框架 |
| PHP-ORM | 3.0/4.0 | 数据库 ORM |
| MySQL | 8.0+ | 关系数据库 |
| Redis | 6.0+ | 缓存/队列 |
| JWT | 6.11+ | 身份认证 |
| Composer | 2.0+ | 包管理器 |

### 前端技术栈

| 技术 | 版本 | 用途 |
|------|------|------|
| Vue.js | 3.3+ | 前端框架 |
| TypeScript | 5.0+ | 类型检查 |
| Vite | 4.0+ | 构建工具 |
| Pinia | 2.0+ | 状态管理 |
| Element Plus | 2.0+ | UI 组件库 |
| Axios | 1.0+ | HTTP 客户端 |
| Sass | 最新 | CSS 预处理 |

---

## 编码规范

### PHP 编码规范

#### 文件头部

```php
<?php
declare(strict_types=1);

namespace app\controller\api;

use think\Request;

/**
 * 控制器描述
 *
 * @package app\controller\api
 * @author  Your Name
 * @version 1.0.0
 */
class Demo extends BaseController
{
    // ...
}
```

#### 方法文档

```php
/**
 * 方法简短描述
 *
 * 详细说明...
 *
 * @param  string $param1 参数1说明
 * @param  int    $param2 参数2说明
 * @return array 返回说明
 * @throws \RuntimeException 异常说明
 */
public function methodName(string $param1, int $param2): array
{
    // 实现代码
}
```

#### 命名规范

- 类名：`PascalCase` (例：`ArticleController`)
- 方法名：`camelCase` (例：`getArticleList()`)
- 常量名：`UPPER_CASE` (例：`STATUS_ACTIVE`)
- 变量名：`camelCase` (例：`$userId`)

### TypeScript/Vue 编码规范

#### 组件结构

```vue
<template>
  <!-- 模板代码 -->
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

// 类型定义
interface Props {
  title: string
  count?: number
}

// Props 定义
const props = withDefaults(defineProps<Props>(), {
  count: 0,
})

// 响应式状态
const state = ref('')

// 计算属性
const computed Value = computed(() => {
  return state.value.toUpperCase()
})

// 方法
const handleClick = () => {
  // 处理逻辑
}
</script>

<style scoped lang="scss">
// 样式代码
</style>
```

#### 命名规范

- 组件名：`PascalCase` (例：`ArticleList.vue`)
- 文件名：`PascalCase` (例：`ArticleList.ts`)
- 变量名：`camelCase` (例：`articleList`)
- 常量名：`UPPER_CASE` (例：`API_URL`)
- CSS 类名：`kebab-case` (例：`.article-item`)

---

## 常用命令

### PHP/ThinkPHP 命令

```bash
# 启动开发服务器
php think serve --host 127.0.0.1 --port 8000

# 创建新控制器
php think make:controller backend/Article

# 创建新模型
php think make:model Article

# 创建迁移文件
php think make:migration create_articles_table

# 运行迁移
php think migrate

# 回滚迁移
php think migrate:rollback

# 创建 Seeder
php think make:seeder ArticleSeeder

# 运行 Seeder
php think seeder:run

# 创建命令
php think make:command Demo

# 清除缓存
php think cache:clear

# 生成 APP_KEY
php think key:generate

# 运行单元测试
composer test

# 代码风格检查
composer lint

# 代码风格修复
composer lint:fix

# 静态分析
composer static-analysis
```

### npm 命令

```bash
# 安装依赖
npm install

# 启动开发服务器
npm run dev

# 构建生产版本
npm run build

# 预览生产版本
npm run preview

# 代码检查
npm run lint

# 代码格式化
npm run format

# 类型检查
npm run type-check

# 运行测试
npm run test
```

---

## 调试技巧

### 后端调试

#### 1. 启用调试模式

```php
// .env 文件
APP_DEBUG=true
```

#### 2. 使用日志记录

```php
use app\common\Logger;

// 记录日志
Logger::info('Message', ['key' => 'value']);
Logger::warning('Warning message', ['data' => $data]);
Logger::error('Error message', ['exception' => $e]);
```

#### 3. 使用 var_dump 调试（开发环境）

```php
use think\facade\Log;

// 写入 trace 日志
trace('调试信息', 'info');
trace($data, 'info');
```

#### 4. 数据库查询日志

```php
// config/database.php
'debug' => true,  // 启用 SQL 日志记录
```

### 前端调试

#### 1. Vue DevTools

- 安装 Vue DevTools 浏览器扩展
- 查看组件树、props、state 等

#### 2. 控制台日志

```typescript
// 使用 console 输出
console.log('Debug:', data)
console.error('Error:', error)

// 或使用 debugger
debugger
```

#### 3. Network 标签

- 查看 API 请求/响应
- 检查请求头（如 Authorization）
- 分析响应数据

#### 4. Application 标签

- 检查 localStorage/sessionStorage
- 查看 Cookie
- 分析离线存储

---

## 性能优化

### 后端优化

#### 1. 数据库优化

```php
// ❌ N+1 查询问题
$articles = Article::all();
foreach ($articles as $article) {
    echo $article->category->name;  // 每次都查询数据库
}

// ✅ 使用 eager loading
$articles = Article::with('category')->get();
foreach ($articles as $article) {
    echo $article->category->name;  // 已经加载过，不查询数据库
}
```

#### 2. 缓存策略

```php
// 使用缓存减少数据库查询
$cacheKey = 'article:' . $articleId;
$article = cache($cacheKey) ?: Article::find($articleId);
cache($cacheKey, $article, 3600);  // 缓存 1 小时
```

#### 3. 查询优化

```php
// ❌ 查询所有字段
$articles = Article::get();

// ✅ 只查询需要的字段
$articles = Article::field('id,title,publish_time')->get();
```

### 前端优化

#### 1. 组件懒加载

```typescript
// 路由懒加载
const ArticleList = () => import('@/views/ArticleList.vue')

const routes = [
  { path: '/articles', component: ArticleList }
]
```

#### 2. 图片优化

```vue
<!-- 使用 lazy loading -->
<img v-lazy="imageUrl" alt="Article" />

<!-- 或使用原生 loading 属性 -->
<img :src="imageUrl" loading="lazy" alt="Article" />
```

#### 3. 列表虚拟化

```typescript
// 对于长列表，使用虚拟列表库
import { FixedSizeList } from 'vue-virtual-scroller'
```

---

## 常见问题解答

### Q: 如何添加新的 API 端点？

**A:**

1. 创建控制器方法
2. 在 routes 中配置路由
3. 编写单元测试
4. 更新 API 文档

参见 [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)

### Q: 如何修改数据库结构？

**A:**

1. 编写迁移文件：`php think make:migration`
2. 定义迁移逻辑
3. 运行迁移：`php think migrate`
4. 更新模型关系

参见 [数据库设计](./database_design.sql)

### Q: 如何处理错误？

**A:**

使用自定义异常类，在全局异常处理器中统一处理。

参见 [ERROR_HANDLING.md](./ERROR_HANDLING.md)

---

## 相关文档

- [API 文档](./API_DOCUMENTATION.md) - API 接口文档
- [用户手册](./USER_MANUAL.md) - 用户操作指南
- [部署指南](./DEPLOYMENT_GUIDE.md) - 生产环境部署
- [故障排查](./TROUBLESHOOTING.md) - 常见问题排查
- [安全扫描](./SECURITY_SCANNING.md) - 安全相关
- [代码规范](./CODE_STYLE_GUIDE.md) - 编码标准

---

**更新时间**: 2025-10-24
**版本**: 1.0.0
**维护者**: Your Team

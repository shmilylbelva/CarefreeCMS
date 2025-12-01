# 多站点功能使用指南

## 概述

多站点功能允许您在一个CMS系统中管理多个独立或关联的站点，适用于以下场景：
- 本地生活服务平台（如北京站、上海站、深圳站）
- 连锁企业/品牌站点
- 新闻媒体集团
- SaaS模式（为每个客户提供独立站点）

## 功能特性

### 1. 站点类型

系统支持三种站点类型：
- **主站（TYPE_MAIN = 1）**：系统的主要站点，不可删除
- **子站（TYPE_SUB = 2）**：从属于主站或其他站点的子站
- **独立站（TYPE_INDEPENDENT = 3）**：完全独立的站点

### 2. 域名绑定方式

支持三种域名绑定方式：
- **独立域名（DOMAIN_INDEPENDENT = 1）**：example.com
- **子域名（DOMAIN_SUB = 2）**：beijing.example.com
- **目录（DOMAIN_DIRECTORY = 3）**：example.com/beijing

### 3. 数据隔离

采用表前缀隔离方式：
- 主站：使用默认表前缀（如无前缀或cms_）
- 子站/独立站：使用独立表前缀（如site_beijing_）

### 4. 内容共享

支持站点间内容共享：
- **引用模式（MODE_REFERENCE = 1）**：共享但不复制，源站更新时自动同步
- **复制模式（MODE_COPY = 2）**：完全复制，创建独立副本

## 快速开始

### 1. 数据库初始化

系统已自动创建以下数据库表：
- `sites`：站点配置表
- `site_admins`：站点管理员关联表
- `site_content_share`：站点内容共享表

并为18个核心表添加了`site_id`字段。

### 2. 创建站点

#### 通过管理后台创建

1. 登录管理后台
2. 进入"站点管理"菜单
3. 点击"添加站点"按钮
4. 填写站点信息：
   - **站点代码**：英文字母、数字、下划线组成（如：beijing）
   - **站点名称**：显示名称（如：北京站）
   - **站点类型**：选择子站或独立站
   - **域名配置**：选择域名绑定方式并填写相应信息
   - **地域信息**：填写省市区信息（用于本地化服务）
   - **联系信息**：填写Logo、描述、联系方式等

5. 点击"确定"完成创建

#### 通过API创建

```php
use app\service\SiteService;

$siteService = new SiteService();

$data = [
    'site_code' => 'beijing',
    'site_name' => '北京站',
    'site_type' => 2, // 子站
    'domain_bind_type' => 2, // 子域名
    'sub_domain' => 'beijing',
    'region_code' => '110000',
    'region_name' => '北京',
    'province' => '北京市',
    'city' => '北京市',
    'status' => 1, // 启用
];

$site = $siteService->create($data);
```

### 3. 站点识别

系统通过`MultiSiteMiddleware`中间件自动识别当前站点：

1. **优先级1**：URL参数（`?site_code=beijing`）
2. **优先级2**：域名识别（独立域名或子域名）
3. **优先级3**：默认主站

识别后的站点信息会自动注入到请求对象：

```php
// 在Controller中访问当前站点
$request->site;      // 站点对象
$request->siteId;    // 站点ID
$request->siteCode;  // 站点代码
$request->siteName;  // 站点名称

// 或使用SiteContextService
use app\service\SiteContextService;

$currentSite = SiteContextService::getSite();
$siteId = SiteContextService::getSiteId();
```

### 4. 站点切换

#### 后台管理切换

在管理后台可以通过站点选择器快速切换站点，切换后查看和管理该站点的内容。

#### 程序切换

```php
use app\service\SiteContextService;

// 切换到指定站点
SiteContextService::switchSite($siteId);

// 切换到主站
SiteContextService::switchToMainSite();
```

### 5. 站点配置

每个站点可以有独立的配置：

```php
use app\service\SiteContextService;

// 获取站点配置
$value = SiteContextService::getSiteConfig('key', 'default_value');

// 设置站点配置
SiteContextService::setSiteConfig('key', 'value');

// 批量设置
SiteContextService::setSiteConfig([
    'key1' => 'value1',
    'key2' => 'value2',
]);

// 获取SEO配置
$seoTitle = SiteContextService::getSeoConfig('title');
```

## 高级用法

### 1. 为站点分配管理员

```php
use app\service\SiteService;

$siteService = new SiteService();

// 为站点分配管理员（管理员用户ID数组）
$siteService->assignAdmins($siteId, [1, 2, 3]);

// 获取站点的管理员列表
$admins = $siteService->getSiteAdmins($siteId);

// 检查管理员是否有站点访问权限
$hasAccess = $siteService->hasAccess($adminUserId, $siteId);
```

### 2. 站点统计

```php
use app\model\Site;

$site = Site::find($siteId);

// 增加访问量
$site->incrementVisitCount(1);

// 更新文章数
$site->updateArticleCount();

// 更新用户数
$site->updateUserCount();

// 或使用Service
$siteService->updateStats($siteId);
```

### 3. 内容共享

```php
use app\model\SiteContentShare;

// 检查内容是否已共享
$isShared = SiteContentShare::isShared($sourceSiteId, $targetSiteId, 'article', $articleId);

// 获取内容的所有共享记录
$shares = SiteContentShare::getContentShares($sourceSiteId, 'article', $articleId);

// 获取站点共享到其他站点的内容
$sharedToOthers = SiteContentShare::getSharedToOthers($sourceSiteId, 'article');

// 获取站点从其他站点共享的内容
$sharedFromOthers = SiteContentShare::getSharedFromOthers($targetSiteId, 'article');
```

### 4. 复制站点配置

```php
use app\service\SiteService;

$siteService = new SiteService();

// 将站点1的配置复制到站点2
$siteService->copyConfig($fromSiteId = 1, $toSiteId = 2);
```

### 5. 清除站点缓存

```php
use app\service\SiteContextService;

// 清除指定站点缓存
SiteContextService::clearCache($siteId);

// 清除所有站点缓存
SiteContextService::clearCache();
```

## 模型使用

### 在Model中使用站点过滤

为需要按站点过滤的模型添加全局作用域：

```php
use app\service\SiteContextService;

class Article extends Model
{
    // 全局查询作用域
    protected function base($query)
    {
        // 自动按当前站点过滤
        $siteId = SiteContextService::getSiteId();
        if ($siteId) {
            $query->where('site_id', $siteId);
        }
    }
}
```

### 在Controller中指定站点

```php
// 创建文章时自动关联当前站点
public function save(Request $request)
{
    $data = $request->post();
    $data['site_id'] = SiteContextService::getSiteId();

    $article = Article::create($data);

    return Response::success($article);
}
```

## API接口

### 站点管理接口

所有接口基于RESTful风格：

```
GET    /api/sites              # 获取站点列表
GET    /api/sites/{id}         # 获取站点详情
POST   /api/sites              # 创建站点
PUT    /api/sites/{id}         # 更新站点
DELETE /api/sites/{id}         # 删除站点
POST   /api/sites/batch-delete # 批量删除站点
PUT    /api/sites/{id}/status  # 更新站点状态
GET    /api/sites/options      # 获取站点选项（下拉框）
GET    /api/sites/current      # 获取当前站点
POST   /api/sites/switch       # 切换站点
POST   /api/sites/{id}/admins  # 分配管理员
GET    /api/sites/{id}/admins  # 获取管理员列表
PUT    /api/sites/{id}/stats   # 更新统计数据
POST   /api/sites/copy-config  # 复制配置
POST   /api/sites/clear-cache  # 清除缓存
```

### 请求示例

```javascript
// 获取站点列表
axios.get('/api/sites', {
  params: {
    page: 1,
    limit: 15,
    site_name: '北京',
    status: 1
  }
})

// 创建站点
axios.post('/api/sites', {
  site_code: 'shanghai',
  site_name: '上海站',
  site_type: 2,
  domain_bind_type: 2,
  sub_domain: 'shanghai',
  city: '上海市',
  status: 1
})

// 切换站点
axios.post('/api/sites/switch', {
  site_id: 2
})
```

## 配置说明

### 中间件配置

在`backend/app/middleware.php`中注册MultiSite中间件：

```php
return [
    // 全局中间件
    \app\middleware\MultiSite::class,
    // 其他中间件...
];
```

### 路由配置

站点管理路由配置（已自动生成）：

```php
// backend/route/api.php
Route::group('sites', function () {
    Route::get('', 'SiteController@index');
    Route::get('<id>', 'SiteController@read');
    Route::post('', 'SiteController@save');
    Route::put('<id>', 'SiteController@update');
    Route::delete('<id>', 'SiteController@delete');
    Route::post('batch-delete', 'SiteController@batchDelete');
    Route::put('<id>/status', 'SiteController@updateStatus');
    Route::get('options', 'SiteController@options');
    Route::get('current', 'SiteController@current');
    Route::post('switch', 'SiteController@switch');
    Route::post('<id>/admins', 'SiteController@assignAdmins');
    Route::get('<id>/admins', 'SiteController@admins');
    Route::put('<id>/stats', 'SiteController@updateStats');
    Route::post('copy-config', 'SiteController@copyConfig');
    Route::post('clear-cache', 'SiteController@clearCache');
});
```

## 注意事项

### 1. 主站保护

- 主站（site_type = 1）不能被删除
- 主站的站点代码创建后不可修改

### 2. 站点依赖

- 删除站点前需要确保没有子站点
- 删除站点前需要确保没有关联内容（文章、用户等）

### 3. 表前缀

- 表前缀一旦设置，切换站点时会自动应用
- 修改表前缀需要谨慎，可能影响数据访问

### 4. 缓存管理

- 站点信息会被缓存，修改后建议清除缓存
- 可以通过管理后台或API清除站点缓存

### 5. 权限控制

- 建议为不同站点分配专门的管理员
- 超级管理员默认可以访问所有站点

## 故障排除

### 问题1：站点无法识别

**症状**：访问子域名时总是显示主站内容

**解决方案**：
1. 检查DNS配置是否正确解析到服务器
2. 检查Nginx/Apache配置是否支持泛域名
3. 检查站点的domain_bind_type和sub_domain配置
4. 清除站点缓存

### 问题2：站点数据混乱

**症状**：不同站点看到相同的内容

**解决方案**：
1. 检查模型是否正确添加了site_id过滤
2. 检查控制器中创建内容时是否设置了site_id
3. 验证站点上下文是否正确识别

### 问题3：站点无法删除

**症状**：删除站点时提示错误

**解决方案**：
1. 检查是否是主站（主站不能删除）
2. 检查是否有子站点依赖
3. 检查是否有关联的内容数据
4. 建议先将内容迁移或删除后再删除站点

## 最佳实践

### 1. 站点规划

- 提前规划站点结构（主站-子站层级关系）
- 使用有意义的站点代码（如城市代码、品牌代码）
- 统一命名规范

### 2. 域名管理

- 建议使用子域名方式，便于管理
- 为每个站点配置独立的SSL证书
- 做好域名备案工作

### 3. 内容管理

- 合理使用内容共享功能
- 为每个站点配置独立的SEO信息
- 定期更新站点统计数据

### 4. 性能优化

- 为站点数据添加合适的索引
- 合理使用缓存
- 考虑为大流量站点使用独立数据库

### 5. 安全建议

- 为每个站点配置独立的管理员
- 定期审查站点访问权限
- 做好数据备份工作

## 下一步开发

根据规划文档（docs/MULTI_SITE_PLANNING.md），后续可以开发：

**阶段2：内容管理多站点化（2周）**
- 实现Article、Category等模型的站点自动过滤
- 开发站点间内容共享功能
- 实现跨站内容引用和复制

**阶段3：用户和权限（1-2周）**
- 完善站点管理员权限控制
- 实现前台用户的站点隔离
- 开发站点级权限管理

**阶段4：模板和主题（2周）**
- 实现站点独立模板配置
- 开发多站点模板切换
- 实现模板资源隔离

**阶段5：域名和路由（1周）**
- 完善域名识别逻辑
- 实现目录模式支持
- 优化路由性能

**阶段6：静态化和优化（2周）**
- 实现多站点静态化
- 优化数据库查询
- 添加站点监控和统计

## 技术支持

如有问题，请查阅：
- 项目文档：docs/MULTI_SITE_PLANNING.md
- API文档：docs/API_DOCUMENTATION.md
- 开发指南：docs/DEVELOPER_GUIDE.md

# 友情链接和广告管理功能文档

## 功能概述

友情链接和广告管理是CMS系统的重要扩展功能，为网站提供外部链接展示和商业广告投放能力。

---

## 一、友情链接管理

### 功能特性

✅ **分组管理** - 支持将友链按分组归类，如"合作伙伴"、"友情链接"等
✅ **链接审核** - 支持待审核、已通过、已拒绝三种状态
✅ **Logo展示** - 支持上传网站Logo，提升视觉效果
✅ **排序权重** - 自定义友链显示顺序
✅ **首页显示** - 标记是否在首页展示
✅ **点击统计** - 记录友链点击次数
✅ **审核备注** - 支持添加审核意见

### 数据库设计

**1. 友链分组表（link_groups）**

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 分组ID |
| name | varchar(50) | 分组名称 |
| description | varchar(255) | 分组描述 |
| sort | int | 排序 |
| status | tinyint | 状态：0=禁用，1=启用 |

**2. 友情链接表（links）**

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 链接ID |
| group_id | int | 分组ID |
| name | varchar(100) | 网站名称 |
| url | varchar(255) | 网站URL |
| logo | varchar(255) | Logo图片 |
| description | varchar(500) | 网站描述 |
| email | varchar(100) | 联系邮箱 |
| sort | int | 排序权重 |
| status | tinyint | 状态：0=待审核，1=已通过，2=已拒绝 |
| is_home | tinyint | 是否首页显示 |
| view_count | int | 点击次数 |
| audit_time | datetime | 审核时间 |
| audit_user_id | int | 审核人ID |
| audit_note | varchar(255) | 审核备注 |

### 使用场景

**场景1：创建友链分组**
1. 进入"友情链接"页面
2. 在左侧点击"新建分组"
3. 填写分组名称（如"合作伙伴"）
4. 设置排序和状态
5. 保存

**场景2：添加友情链接**
1. 点击"新建友链"
2. 填写网站名称、URL
3. 选择所属分组
4. 上传Logo（可选）
5. 填写网站描述
6. 设置排序、状态和是否首页显示
7. 保存

**场景3：审核友情链接**
1. 在列表中找到待审核的友链
2. 点击"通过"或"拒绝"按钮
3. 填写审核备注（可选）
4. 确认审核

### 前台模板使用

**获取友情链接列表**
```php
// 控制器
$links = \app\model\Link::where('status', 1)
    ->where('is_home', 1)
    ->order('sort', 'asc')
    ->select();

return view('index', ['links' => $links]);
```

**模板展示**
```html
<div class="友链">
  {volist name="links" id="link"}
  <a href="{$link.url}" target="_blank" title="{$link.description}">
    {if $link.logo}
    <img src="{$link.logo}" alt="{$link.name}">
    {else/}
    {$link.name}
    {/if}
  </a>
  {/volist}
</div>
```

**按分组展示**
```php
// 获取所有分组及其友链
$groups = \app\model\LinkGroup::where('status', 1)
    ->with(['links' => function($query) {
        $query->where('status', 1)->order('sort', 'asc');
    }])
    ->order('sort', 'asc')
    ->select();
```

```html
{volist name="groups" id="group"}
<div class="link-group">
  <h3>{$group.name}</h3>
  {volist name="group.links" id="link"}
  <a href="{$link.url}" target="_blank">{$link.name}</a>
  {/volist}
</div>
{/volist}
```

---

## 二、广告管理

### 功能特性

✅ **广告位管理** - 定义网站各个位置的广告位
✅ **多种类型** - 支持图片广告、代码广告、轮播广告
✅ **时间控制** - 设置广告投放的开始和结束时间
✅ **点击统计** - 记录广告的展示和点击数据
✅ **点击率分析** - 自动计算广告点击率
✅ **排序管理** - 支持多个广告按顺序轮播

### 数据库设计

**1. 广告位表（ad_positions）**

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 广告位ID |
| name | varchar(50) | 广告位名称 |
| code | varchar(50) | 广告位代码（唯一标识） |
| description | varchar(255) | 广告位描述 |
| width | int | 宽度（像素） |
| height | int | 高度（像素） |
| status | tinyint | 状态：0=禁用，1=启用 |

**2. 广告表（ads）**

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 广告ID |
| position_id | int | 广告位ID |
| name | varchar(100) | 广告名称 |
| type | varchar(20) | 广告类型：image/code/carousel |
| content | text | 广告内容（图片URL或代码） |
| link_url | varchar(255) | 链接地址 |
| images | text | 轮播图片（JSON数组） |
| start_time | datetime | 开始时间 |
| end_time | datetime | 结束时间 |
| status | tinyint | 状态：0=禁用，1=启用 |
| sort | int | 排序 |
| click_count | int | 点击次数 |
| view_count | int | 展示次数 |

**3. 广告点击统计表（ad_clicks）**

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 记录ID |
| ad_id | int | 广告ID |
| ip | varchar(45) | 访客IP |
| user_agent | varchar(255) | 用户代理 |
| referer | varchar(255) | 来源页面 |
| click_time | datetime | 点击时间 |

### 使用场景

**场景1：创建广告位**
1. 进入"广告管理"页面
2. 切换到"广告位管理"标签页
3. 点击"新建广告位"
4. 填写广告位名称（如"首页顶部横幅"）
5. 设置广告位代码（如"home_top_banner"）
6. 设置尺寸（如1200x120）
7. 保存

**场景2：添加图片广告**
1. 切换到"广告管理"标签页
2. 点击"新建广告"
3. 选择广告位
4. 选择类型：图片广告
5. 上传广告图片
6. 填写链接地址
7. 设置投放时间段（可选）
8. 保存

**场景3：添加代码广告**
1. 新建广告，选择类型：代码广告
2. 在"广告代码"框中粘贴HTML代码
   ```html
   <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
   <ins class="adsbygoogle"
        style="display:block"
        data-ad-client="ca-pub-xxxxx"
        data-ad-slot="xxxxx"></ins>
   <script>
   (adsbygoogle = window.adsbygoogle || []).push({});
   </script>
   ```
3. 保存

**场景4：添加轮播广告**
1. 新建广告，选择类型：轮播广告
2. 点击"添加图片"上传多张图片
3. 可以为每张图片删除或调整
4. 设置链接地址
5. 保存

**场景5：查看广告统计**
1. 在广告列表中点击"统计"按钮
2. 查看展示次数、点击次数、点击率
3. 可以按时间段筛选统计数据

### 前台模板使用

**显示图片广告**
```php
// 控制器
public function index()
{
    // 获取指定广告位的广告
    $ad = \app\model\Ad::where('position_id', function($query) {
            $query->table('ad_positions')
                  ->where('code', 'home_top_banner')
                  ->value('id');
        })
        ->where('status', 1)
        ->where(function($query) {
            $now = date('Y-m-d H:i:s');
            $query->where(function($q) use ($now) {
                $q->whereNull('start_time')
                  ->whereOr('start_time', '<=', $now);
            })->where(function($q) use ($now) {
                $q->whereNull('end_time')
                  ->whereOr('end_time', '>=', $now);
            });
        })
        ->order('sort', 'asc')
        ->find();

    // 增加展示次数
    if ($ad) {
        $ad->incrementViewCount();
    }

    return view('index', ['ad' => $ad]);
}
```

**模板展示**
```html
{if $ad && $ad.type == 'image'}
<div class="ad-banner">
  <a href="{$ad.link_url}" target="_blank" onclick="recordAdClick({$ad.id})">
    <img src="{$ad.content}" alt="{$ad.name}">
  </a>
</div>
{elseif $ad && $ad.type == 'code' /}
<div class="ad-banner">
  {$ad.content|raw}
</div>
{/if}

<script>
function recordAdClick(adId) {
  fetch('/backend/ads/' + adId + '/click', {
    method: 'POST'
  });
}
</script>
```

**轮播广告展示**
```html
{if $ad && $ad.type == 'carousel'}
<div class="ad-carousel">
  {volist name="ad.images" id="img"}
  <div class="carousel-item">
    <a href="{$ad.link_url}" target="_blank">
      <img src="{$img.url}" alt="{$ad.name}">
    </a>
  </div>
  {/volist}
</div>
{/if}
```

**封装广告调用函数**
```php
// app/common.php
function getAdByPosition($positionCode)
{
    $position = \app\model\AdPosition::where('code', $positionCode)
        ->where('status', 1)
        ->find();

    if (!$position) {
        return null;
    }

    $ad = \app\model\Ad::where('position_id', $position->id)
        ->where('status', 1)
        ->where(function($query) {
            $now = date('Y-m-d H:i:s');
            $query->where(function($q) use ($now) {
                $q->whereNull('start_time')
                  ->whereOr('start_time', '<=', $now);
            })->where(function($q) use ($now) {
                $q->whereNull('end_time')
                  ->whereOr('end_time', '>=', $now);
            });
        })
        ->order('sort', 'asc')
        ->find();

    if ($ad) {
        $ad->incrementViewCount();
    }

    return $ad;
}
```

**在模板中使用**
```html
{php}$ad = getAdByPosition('home_top_banner');{/php}
{if $ad}
  <!-- 展示广告 -->
{/if}
```

---

## 注意事项

### 友情链接

1. **Logo尺寸建议**：建议使用200x80像素的Logo，保持统一视觉效果
2. **URL验证**：系统会自动验证URL格式，确保链接有效
3. **审核流程**：新添加的友链默认为"待审核"状态，需要管理员审核后才能显示
4. **软删除**：删除的友链会进入回收站，可以恢复
5. **点击统计**：需要在前台链接上添加点击事件，调用API记录点击

### 广告管理

1. **广告位代码**：广告位代码是唯一标识，只能包含小写字母、数字和下划线
2. **图片尺寸**：建议上传符合广告位设定尺寸的图片
3. **代码安全**：代码广告需要注意HTML/JS代码的安全性
4. **时间控制**：
   - 不设置开始时间：立即生效
   - 不设置结束时间：永久有效
   - 设置时间段：只在指定时间段内展示
5. **点击统计**：需要在前台调用点击API才能正确统计
6. **轮播广告**：轮播图片以JSON数组格式存储，前台需要配合JS轮播插件

---

## API接口说明

### 友情链接API

```
GET    /link-groups          获取分组列表
GET    /link-groups/all      获取所有分组
POST   /link-groups          创建分组
PUT    /link-groups/:id      更新分组
DELETE /link-groups/:id      删除分组

GET    /links                获取链接列表
GET    /links/:id            获取链接详情
POST   /links                创建链接
PUT    /links/:id            更新链接
DELETE /links/:id            删除链接
POST   /links/:id/audit      审核链接
```

### 广告管理API

```
GET    /ad-positions         获取广告位列表
GET    /ad-positions/all     获取所有广告位
POST   /ad-positions         创建广告位
PUT    /ad-positions/:id     更新广告位
DELETE /ad-positions/:id     删除广告位

GET    /ads                  获取广告列表
GET    /ads/:id              获取广告详情
POST   /ads                  创建广告
PUT    /ads/:id              更新广告
DELETE /ads/:id              删除广告
GET    /ads/:id/statistics   获取广告统计
POST   /ads/:id/click        记录广告点击
```

---

## 扩展功能建议

### 友情链接

- [ ] **申请表单** - 提供前台友链申请表单
- [ ] **自动检测** - 定期检测友链网站是否正常访问
- [ ] **互链检查** - 检测对方网站是否有回链
- [ ] **链接评分** - 根据PR、权重等对友链评分
- [ ] **批量导入** - 支持批量导入友链数据
- [ ] **链接分类** - 更细致的友链分类标签

### 广告管理

- [ ] **A/B测试** - 支持多个广告轮播测试效果
- [ ] **地域定向** - 根据访客地域显示不同广告
- [ ] **用户定向** - 根据用户属性显示不同广告
- [ ] **频次控制** - 控制广告对同一用户的展示频次
- [ ] **数据报表** - 详细的广告数据分析报表
- [ ] **收益统计** - 广告收益统计和结算功能

---

**更新时间**: 2025-10-18
**版本**: 1.0

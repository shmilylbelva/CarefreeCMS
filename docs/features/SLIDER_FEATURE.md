# 幻灯片/轮播图管理功能

## 功能概述

幻灯片管理功能允许管理员创建和管理网站的轮播图/幻灯片展示。支持分组管理、自定义动画效果、时间范围控制、点击统计等功能。

## 功能特点

### 1. 幻灯片组管理

- **分组功能**：支持创建多个幻灯片组，每个组可以在不同位置使用
- **唯一代码**：每个分组有唯一的代码标识，用于前台调用
- **尺寸定义**：可为每个分组设置推荐的图片宽度和高度
- **播放设置**：
  - 自动播放开关
  - 播放间隔时间（毫秒）
  - 动画效果（滑动/淡入淡出）
- **状态管理**：支持启用/禁用分组

### 2. 幻灯片管理

- **图片管理**：上传幻灯片图片
- **链接设置**：可为幻灯片设置跳转链接和打开方式
- **内容信息**：
  - 标题
  - 描述文字
  - 按钮文字
- **排序控制**：支持自定义排序
- **时间控制**：
  - 开始时间（留空表示立即生效）
  - 结束时间（留空表示永久有效）
- **统计功能**：
  - 展示次数统计
  - 点击次数统计
  - 自动计算点击率

## 数据库设计

### slider_groups 表（幻灯片组）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 主键 |
| name | varchar(50) | 分组名称 |
| code | varchar(50) | 分组代码（唯一） |
| description | varchar(255) | 分组描述 |
| width | int | 图片宽度（像素） |
| height | int | 图片高度（像素） |
| auto_play | tinyint(1) | 是否自动播放 |
| play_interval | int | 播放间隔（毫秒） |
| animation | varchar(20) | 动画效果 |
| status | tinyint(1) | 状态 |
| create_time | datetime | 创建时间 |
| update_time | datetime | 更新时间 |

### sliders 表（幻灯片）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 主键 |
| group_id | int | 分组ID |
| title | varchar(100) | 标题 |
| image | varchar(255) | 图片URL |
| link_url | varchar(255) | 链接地址 |
| link_target | varchar(20) | 链接打开方式 |
| description | varchar(500) | 描述文字 |
| button_text | varchar(50) | 按钮文字 |
| sort | int | 排序 |
| status | tinyint(1) | 状态 |
| start_time | datetime | 开始时间 |
| end_time | datetime | 结束时间 |
| view_count | int | 展示次数 |
| click_count | int | 点击次数 |
| create_time | datetime | 创建时间 |
| update_time | datetime | 更新时间 |
| deleted_at | datetime | 删除时间（软删除） |

## 使用场景

### 1. 首页轮播

```php
// 前台模板中调用首页轮播
$sliderData = $api->get('/sliders/group/home_slider');
$sliders = $sliderData['data']['sliders'];
$group = $sliderData['data']['group'];
```

### 2. 产品展示

```php
// 产品页轮播
$sliderData = $api->get('/sliders/group/product_slider');
```

### 3. 客户案例

```php
// 案例轮播
$sliderData = $api->get('/sliders/group/case_slider');
```

## API 接口

### 幻灯片组管理

#### 获取分组列表（分页）
```
GET /backend/slider-groups
参数：
  - page: 页码
  - per_page: 每页数量
  - keyword: 搜索关键词
```

#### 获取所有分组（不分页）
```
GET /backend/slider-groups/all
参数：
  - status: 状态筛选（可选）
```

#### 获取分组详情
```
GET /backend/slider-groups/{id}
```

#### 创建分组
```
POST /backend/slider-groups
参数：
  - name: 分组名称*
  - code: 分组代码*
  - description: 分组描述
  - width: 图片宽度
  - height: 图片高度
  - auto_play: 是否自动播放（0/1）
  - play_interval: 播放间隔（毫秒）
  - animation: 动画效果（slide/fade）
  - status: 状态（0/1）
```

#### 更新分组
```
PUT /backend/slider-groups/{id}
参数：同创建
```

#### 删除分组
```
DELETE /backend/slider-groups/{id}
注意：该分组下不能有幻灯片
```

### 幻灯片管理

#### 获取幻灯片列表（分页）
```
GET /backend/sliders
参数：
  - page: 页码
  - per_page: 每页数量
  - keyword: 搜索关键词
  - group_id: 分组ID筛选
  - status: 状态筛选
```

#### 获取幻灯片详情
```
GET /backend/sliders/{id}
```

#### 创建幻灯片
```
POST /backend/sliders
参数：
  - group_id: 分组ID*
  - title: 标题
  - image: 图片URL*
  - link_url: 链接地址
  - link_target: 链接打开方式（_blank/_self）
  - description: 描述文字
  - button_text: 按钮文字
  - sort: 排序（默认0）
  - status: 状态（0/1）
  - start_time: 开始时间
  - end_time: 结束时间
```

#### 更新幻灯片
```
PUT /backend/sliders/{id}
参数：同创建
```

#### 删除幻灯片（软删除）
```
DELETE /backend/sliders/{id}
```

#### 记录幻灯片点击
```
POST /backend/sliders/{id}/click
```

#### 记录幻灯片展示
```
POST /backend/sliders/{id}/view
```

#### 按分组代码获取幻灯片（前台使用）
```
GET /backend/sliders/group/{code}
返回：
  - group: 分组信息
  - sliders: 该分组下启用且在有效时间内的幻灯片列表
```

## 前台模板使用示例

### 1. 基础轮播展示

```html
<!-- 调用 home_slider 分组的幻灯片 -->
{php}
  $sliderData = $api->get('/sliders/group/home_slider');
  $sliders = $sliderData['data']['sliders'];
  $group = $sliderData['data']['group'];
{/php}

{if !empty($sliders)}
<div class="slider-container"
     data-autoplay="{$group.auto_play}"
     data-interval="{$group.play_interval}"
     data-animation="{$group.animation}">
  {foreach $sliders as $slider}
  <div class="slider-item">
    {if $slider.link_url}
    <a href="{$slider.link_url}" target="{$slider.link_target}"
       onclick="recordSliderClick({$slider.id})">
    {/if}
      <img src="{$slider.image}" alt="{$slider.title}" />
      {if $slider.title || $slider.description}
      <div class="slider-content">
        {if $slider.title}<h3>{$slider.title}</h3>{/if}
        {if $slider.description}<p>{$slider.description}</p>{/if}
        {if $slider.button_text}
        <button class="slider-btn">{$slider.button_text}</button>
        {/if}
      </div>
      {/if}
    {if $slider.link_url}</a>{/if}
  </div>
  {/foreach}
</div>

<script>
// 记录点击
function recordSliderClick(id) {
  fetch('/backend/sliders/' + id + '/click', { method: 'POST' });
}

// 记录展示
document.querySelectorAll('.slider-item').forEach(function(item, index) {
  var sliderId = {$sliders[index]['id']};
  var observer = new IntersectionObserver(function(entries) {
    if (entries[0].isIntersecting) {
      fetch('/backend/sliders/' + sliderId + '/view', { method: 'POST' });
      observer.disconnect();
    }
  });
  observer.observe(item);
});
</script>
{/if}
```

### 2. 使用 Swiper 轮播库

```html
{php}
  $sliderData = $api->get('/sliders/group/home_slider');
  $sliders = $sliderData['data']['sliders'];
  $group = $sliderData['data']['group'];
{/php}

{if !empty($sliders)}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<div class="swiper home-slider">
  <div class="swiper-wrapper">
    {foreach $sliders as $slider}
    <div class="swiper-slide">
      {if $slider.link_url}
      <a href="{$slider.link_url}" target="{$slider.link_target}"
         onclick="recordSliderClick({$slider.id})">
      {/if}
        <img src="{$slider.image}" alt="{$slider.title}" />
        {if $slider.title || $slider.description}
        <div class="slider-caption">
          {if $slider.title}<h2>{$slider.title}</h2>{/if}
          {if $slider.description}<p>{$slider.description}</p>{/if}
          {if $slider.button_text}
          <span class="btn">{$slider.button_text}</span>
          {/if}
        </div>
        {/if}
      {if $slider.link_url}</a>{/if}
    </div>
    {/foreach}
  </div>
  <div class="swiper-pagination"></div>
  <div class="swiper-button-prev"></div>
  <div class="swiper-button-next"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
var swiper = new Swiper('.home-slider', {
  autoplay: {$group.auto_play} ? {
    delay: {$group.play_interval},
    disableOnInteraction: false
  } : false,
  effect: '{$group.animation}' === 'fade' ? 'fade' : 'slide',
  pagination: {
    el: '.swiper-pagination',
    clickable: true
  },
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev'
  },
  on: {
    slideChange: function() {
      // 记录展示
      var sliderId = {$sliders[this.activeIndex]['id']};
      fetch('/backend/sliders/' + sliderId + '/view', { method: 'POST' });
    }
  }
});

function recordSliderClick(id) {
  fetch('/backend/sliders/' + id + '/click', { method: 'POST' });
}
</script>
{/if}
```

### 3. 简单列表展示

```html
{php}
  $sliderData = $api->get('/sliders/group/case_slider');
  $sliders = $sliderData['data']['sliders'];
{/php}

{if !empty($sliders)}
<div class="case-list">
  {foreach $sliders as $slider}
  <div class="case-item">
    {if $slider.link_url}
    <a href="{$slider.link_url}" target="{$slider.link_target}">
    {/if}
      <img src="{$slider.image}" alt="{$slider.title}" />
      {if $slider.title}<h4>{$slider.title}</h4>{/if}
      {if $slider.description}<p>{$slider.description}</p>{/if}
    {if $slider.link_url}</a>{/if}
  </div>
  {/foreach}
</div>
{/if}
```

## 后台管理界面

### 功能说明

1. **Tab 切换**
   - 幻灯片管理：管理具体的幻灯片内容
   - 分组管理：管理幻灯片的分组

2. **幻灯片管理**
   - 按分组筛选
   - 按状态筛选
   - 关键词搜索
   - 图片预览
   - 统计信息展示（展示次数、点击次数、点击率）
   - 显示时间范围

3. **分组管理**
   - 创建分组并设置唯一代码
   - 配置图片尺寸
   - 设置自动播放和播放间隔
   - 选择动画效果
   - 删除前检查是否有关联幻灯片

4. **表单功能**
   - 图片上传（支持拖拽）
   - 链接打开方式选择
   - 时间范围选择
   - 排序设置

## 业务逻辑

### 1. 时间范围控制

- 如果未设置开始时间，幻灯片立即生效
- 如果未设置结束时间，幻灯片永久有效
- 如果同时设置了开始和结束时间，只在该时间段内显示
- 过期的幻灯片不会在前台显示，但数据保留

### 2. 状态控制

- 分组状态为禁用时，该分组下所有幻灯片不显示
- 幻灯片状态为禁用时，该幻灯片不显示
- 只有同时满足：分组启用 + 幻灯片启用 + 在有效时间内，才会前台显示

### 3. 排序规则

- 按 sort 字段升序排列
- sort 值相同时，按 id 降序排列
- sort 值越小越靠前

### 4. 统计功能

- **展示次数**：幻灯片出现在用户视野中时记录（可使用 Intersection Observer）
- **点击次数**：用户点击幻灯片链接时记录
- **点击率**：点击次数 / 展示次数 * 100%

### 5. 软删除

- 幻灯片支持软删除，删除后可在回收站恢复
- 分组不支持软删除，必须先删除分组下所有幻灯片

## 最佳实践

### 1. 图片尺寸

- 为每个分组设置合理的图片尺寸
- 建议使用 2x 分辨率的图片以适配高清屏
- 压缩图片文件大小以提升加载速度

### 2. 性能优化

- 图片懒加载
- 使用 CDN 加速图片访问
- 限制每个分组的幻灯片数量（建议不超过 10 张）

### 3. SEO 优化

- 为图片添加有意义的 alt 属性
- 标题使用 H 标签
- 描述文字简洁明了

### 4. 用户体验

- 自动播放间隔建议 3-5 秒
- 提供暂停按钮
- 在移动端禁用自动播放或增大间隔时间
- 提供清晰的导航指示器

## 扩展建议

### 1. 视频支持

未来可以扩展支持视频幻灯片：
- 添加 video_url 字段
- 支持 MP4、YouTube、Vimeo 等视频源
- 自动播放和循环控制

### 2. 响应式图片

为不同设备提供不同尺寸的图片：
- 添加 image_mobile、image_tablet 字段
- 根据设备自动选择合适的图片

### 3. A/B 测试

支持同一位置测试不同幻灯片效果：
- 添加 ab_test_group 字段
- 随机展示不同版本
- 对比点击率数据

### 4. 定时发布

增强的时间控制：
- 支持按星期几显示
- 支持按特定节假日显示
- 支持按地区时区显示

## 常见问题

### Q1: 幻灯片不显示？

检查以下几点：
1. 分组状态是否启用
2. 幻灯片状态是否启用
3. 是否在有效时间范围内
4. 分组代码是否正确

### Q2: 如何调整幻灯片顺序？

在编辑幻灯片时修改"排序"字段，数字越小越靠前。

### Q3: 删除分组时提示有关联幻灯片？

需要先删除或移动该分组下的所有幻灯片，才能删除分组。

### Q4: 点击统计不准确？

点击统计依赖前台调用统计API，确保前台模板中已添加统计代码。

### Q5: 如何同时在多个位置使用幻灯片？

创建多个分组，每个分组使用不同的代码标识，在不同位置调用不同的分组代码即可。

## 总结

幻灯片管理功能提供了完整的轮播图管理解决方案，支持分组管理、时间控制、统计分析等企业级功能。通过合理配置和使用，可以为网站提供美观且高效的内容展示效果。

---

**创建时间**: 2025-10-18
**版本**: 1.0

# Carefree 模板标签快速参考 v2.0.0

> 53个标签的快速查询手册

## 📋 标签分类索引

- [基础内容](#基础内容-10个) (10个)
- [媒体管理](#媒体管理-5个) (5个) ⭐新增
- [互动功能](#互动功能-3个) (3个) ⭐新增
- [实用工具](#实用工具-8个) (8个) ⭐新增4个
- [AI推荐](#ai推荐-2个) (2个) ⭐新增
- [表单验证](#表单验证-3个) (3个) ⭐新增
- [用户系统](#用户系统-6个) (6个)
- [SEO分享](#seo分享-2个) (2个)
- [高级功能](#高级功能-14个) (14个) ⭐新增4个

---

## 基础内容 (10个)

### 1. article - 文章列表
```html
{carefree:article typeid='1' limit='10' order='create_time desc' flag='hot' titlelen='30'}
    <a href="/article/{$article.id}.html">{$article.title}</a>
{/carefree:article}
```
**属性**: typeid, tagid, userid, limit, offset, order, flag, titlelen, hascover, exclude, days
**flag选项**: hot(热门), recommend(推荐), top(置顶), random(随机), updated(最近更新)

### 2. category - 分类列表
```html
{carefree:category parent='0' limit='10'}
    <a href="/category/{$category.id}.html">{$category.name}</a>
{/carefree:category}
```
**属性**: parent, limit

### 3. tag - 标签列表
```html
{carefree:tag limit='30' order='article_count desc'}
    <a href="/tag/{$tag.id}.html">{$tag.name}</a>
{/carefree:tag}
```
**属性**: limit, order
**order选项**: sort asc/desc, article_count desc, create_time desc

### 4. config - 网站配置
```html
{carefree:config name='site_name' /}
```
**常用配置**: site_name, site_logo, site_copyright, site_icp, seo_title, seo_keywords, seo_description

### 5. nav - 导航菜单
```html
{carefree:nav limit='10'}
    <a href="{$nav.url}">{$nav.title}</a>
{/carefree:nav}
```

### 6. arcinfo - 文章详情
```html
{carefree:arcinfo aid='{$article_id}'}
    <h1>{$article.title}</h1>
    <div>{$article.content|raw}</div>
{/carefree:arcinfo}
```

### 7. catinfo - 分类详情
```html
{carefree:catinfo catid='{$catid}'}
    <h1>{$category.name}</h1>
{/carefree:catinfo}
```

### 8. related - 相关文章
```html
{carefree:related aid='{$article.id}' limit='5' type='same'}
    <a href="/article/{$article.id}.html">{$article.title}</a>
{/carefree:related}
```
**type选项**: same(同分类), all(全部)

### 9. prevnext - 上下篇导航
```html
{carefree:prevnext aid='{$article.id}'}
    {if condition="$prev"}<a href="/article/{$prev.id}.html">← 上一篇</a>{/if}
    {if condition="$next"}<a href="/article/{$next.id}.html">下一篇 →</a>{/if}
{/carefree:prevnext}
```

### 10. breadcrumb - 面包屑导航
```html
{carefree:breadcrumb separator=' / '}
    {volist name="breadcrumb" id="item"}
    <a href="{$item.url}">{$item.title}</a>
    {/volist}
{/carefree:breadcrumb}
```

---

## 媒体管理 (5个)

### 11. gallery - 相册图库 ⭐新增
```html
{carefree:gallery albumid='1' limit='20' columns='4'}
    <img src="{$photo.thumb}" alt="{$photo.title}">
{/carefree:gallery}
```
**属性**: albumid, limit, orderby, columns

### 12. video - 视频列表 ⭐新增
```html
{carefree:video catid='1' limit='12' featured='1'}
    <img src="{$video.cover}">
    <h3>{$video.title}</h3>
    <span>{$video.view_count_formatted}</span>
{/carefree:video}
```
**属性**: catid, limit, orderby, featured

### 13. audio - 音频列表 ⭐新增
```html
{carefree:audio catid='2' limit='20'}
    <div>{$audio.title} - {$audio.duration_formatted}</div>
{/carefree:audio}
```

### 14. download - 文件下载 ⭐新增
```html
{carefree:download catid='5' type='pdf' limit='10'}
    <a href="{$download.file_url}">{$download.title} ({$download.file_size_formatted})</a>
{/carefree:download}
```
**type选项**: doc, pdf, zip, image, video, audio, software

### 15. slider - 幻灯片
```html
{carefree:slider group='home' limit='5'}
    <img src="{$slider.image}" alt="{$slider.title}">
{/carefree:slider}
```

---

## 互动功能 (3个)

### 16. vote - 投票系统 ⭐新增
```html
{carefree:vote voteid='1' showresult='0'}
    <h3>{$vote.title}</h3>
    {volist name="vote.options" id="option"}
        <label><input type="radio" name="option" value="{$option.id}"> {$option.title}</label>
    {/volist}
{/carefree:vote}
```
**属性**: voteid(必填), showresult(0/1)

### 17. quiz - 在线测验 ⭐新增
```html
{carefree:quiz quizid='1'}
    <h2>{$quiz.title}</h2>
    <p>题目数: {$quiz.question_count}, 限时: {$quiz.time_limit_formatted}</p>
    {volist name="quiz.questions" id="question"}
        <div>{$question.title}</div>
    {/volist}
{/carefree:quiz}
```

### 18. lottery - 抽奖活动 ⭐新增
```html
{carefree:lottery lotteryid='1'}
    <h2>{$lottery.title}</h2>
    <p>状态: {$lottery.activity_status_text}</p>
    {volist name="lottery.prizes" id="prize"}
        <div>{$prize.name} - 概率{$prize.probability_formatted}</div>
    {/volist}
{/carefree:lottery}
```

---

## 实用工具 (8个)

### 19. qrcode - 二维码生成 ⭐新增
```html
<img src="{carefree:qrcode content='https://example.com' size='200' /}">
```
**属性**: content(必填), size, logo, level

### 20. calendar - 事件日历 ⭐新增
```html
{carefree:calendar year='2025' month='1' events='1'}
    {volist name="calendar.weeks" id="week"}
        {volist name="week" id="day"}
            <td>{$day.day}</td>
        {/volist}
    {/volist}
{/carefree:calendar}
```

### 21. sitemap - 站点地图 ⭐新增
```html
{carefree:sitemap type='all' format='html'}
    <a href="{$item.loc}">{$item.title}</a>
{/carefree:sitemap}
```
**type选项**: article, category, page, all
**format选项**: html, xml, json

### 22. weather - 天气预报 ⭐新增
```html
{assign name="weather" value="{carefree:weather city='北京' days='3' /}"}
{volist name="weather.forecasts" id="day"}
    <div>{$day.date} {$day.weather_day} {$day.temp_day}°C</div>
{/volist}
```
**属性**: city, days(1-7), unit(c/f)

### 23. search - 搜索框
```html
{carefree:search action='/search' placeholder='搜索...' button='搜索' /}
```

### 24. link - 友情链接
```html
{carefree:link group='footer' limit='20'}
    <a href="{$link.url}">{$link.title}</a>
{/carefree:link}
```

### 25. comment - 评论列表
```html
{carefree:comment aid='{$article.id}' limit='20'}
    <div>{$comment.user_name}: {$comment.content}</div>
{/carefree:comment}
```

### 26. ad - 广告位
```html
{carefree:ad position='banner' limit='3'}
    <a href="{$ad.link}"><img src="{$ad.image}"></a>
{/carefree:ad}
```

---

## AI推荐 (2个)

### 27. recommend - 智能推荐 ⭐新增
```html
{carefree:recommend type='hot' userid='{$user_id}' aid='{$article.id}' limit='10'}
    <a href="/article/{$article.id}.html">{$article.title}</a>
{/carefree:recommend}
```
**type选项**: similar(相似), hot(热门), related(相关), user(用户推荐), collaborative(协同过滤)

### 28. personalize - 个性化内容 ⭐新增
```html
{carefree:personalize userid='{$user_id}' scene='home' limit='20'}
    <article>
        <h3>{$article.title}</h3>
        <span>匹配度: {$article.personalize_score}</span>
    </article>
{/carefree:personalize}
```
**scene选项**: home(首页), detail(详情页), search(搜索)

---

## 表单验证 (3个)

### 29. form - 通用表单 ⭐新增
```html
{carefree:form action='/submit' method='post' class='form'}
    {carefree:formfield name='name' type='text' label='姓名' required='1' /}
    <button type="submit">提交</button>
{/carefree:form}
```

### 30. formfield - 表单字段 ⭐新增
```html
<!-- 文本输入 -->
{carefree:formfield name='username' type='text' label='用户名' required='1' /}

<!-- 下拉选择 -->
{carefree:formfield name='gender' type='select' options='男,女,保密' /}

<!-- 单选框 -->
{carefree:formfield name='level' type='radio' options='普通,VIP,SVIP' /}

<!-- 多选框 -->
{carefree:formfield name='tags' type='checkbox' options='A,B,C' /}
```
**type选项**: text, textarea, select, radio, checkbox, email, tel, date

### 31. captcha - 验证码 ⭐新增
```html
{carefree:captcha type='image' width='120' height='40' length='4' /}
```
**type选项**: image(图片), sms(短信), email(邮件)

---

## 用户系统 (6个)

### 32. userinfo - 用户信息
```html
{carefree:userinfo uid='{$user_id}'}
    <img src="{$user.avatar}">
    <h3>{$user.nickname}</h3>
{/carefree:userinfo}
```

### 33. frontuser - 前台用户列表
```html
{carefree:frontuser limit='10' isvip='1' orderby='points desc'}
    <div>{$user.nickname} - 积分: {$user.points}</div>
{/carefree:frontuser}
```

### 34. memberlevel - 会员等级
```html
{carefree:memberlevel limit='10'}
    <div>{$level.name} - ¥{$level.price}</div>
{/carefree:memberlevel}
```

### 35. notification - 消息通知
```html
{carefree:notification userid='{$user_id}' limit='10' isread='0'}
    <div class="{if condition='!$notify.is_read'}unread{/if}">
        {$notify.content}
    </div>
{/carefree:notification}
```

### 36. author - 作者列表
```html
{carefree:author limit='12' orderby='article_count desc'}
    <div>{$author.username} - {$author.article_count}篇</div>
{/carefree:author}
```

### 37. contribution - 投稿列表
```html
{carefree:contribution userid='{$user_id}' limit='20'}
    <div>{$contrib.title} - {$contrib.status_text}</div>
{/carefree:contribution}
```

---

## SEO分享 (2个)

### 38. seo - SEO标签
```html
{carefree:seo title='{$title}' keywords='{$keywords}' description='{$description}' type='article' /}
```

### 39. share - 社交分享
```html
{carefree:share platforms='wechat,weibo,qq,douban' size='medium' style='flat' /}
```

---

## 高级功能 (14个)

### 40. multilang - 多语言 ⭐新增
```html
{carefree:multilang key='site.welcome' default='欢迎' /}
```

### 41. cache - 缓存标签 ⭐新增
```html
{carefree:cache key='hot_list' time='3600'}
    {carefree:article flag='hot' limit='10'}...{/carefree:article}
{/carefree:cache}
```

### 42. condition - 条件标签 ⭐新增
```html
{carefree:condition if='$user_id gt 0'}
    <div>已登录</div>
{/carefree:condition}
```

### 43. group - 分组标签 ⭐新增
```html
{carefree:group data='$articles' by='category_id'}
    <h3>{$group_key}</h3>
    {volist name="group_items" id="item"}...{/volist}
{/carefree:group}
```

### 44. loop - 通用循环
```html
{carefree:loop data='$custom_data' id='item'}
    <div>{$item.name}</div>
{/carefree:loop}
```

### 45. sql - SQL查询
```html
{carefree:sql sql="SELECT * FROM articles LIMIT 10" id='result'}
    <div>{$result.title}</div>
{/carefree:sql}
```

### 46. stats - 统计数据
```html
<span>{carefree:stats type='article' /}</span> 篇文章
<span>{carefree:stats type='view' /}</span> 次浏览
```

### 47. rank - 排行榜
```html
{carefree:rank type='view' days='7' limit='10'}
    <div>{$i}. {$article.title} ({$article.view_count})</div>
{/carefree:rank}
```
**type选项**: view, comment, like, download

### 48. archive - 归档列表
```html
{carefree:archive type='month' limit='12' format='Y年m月'}
    <a href="/archive/{$archive.date}.html">{$archive.date_formatted} ({$archive.count})</a>
{/carefree:archive}
```

### 49. topic - 专题列表
```html
{carefree:topic limit='8' status='1'}
    <div>{$topic.title} - {$topic.article_count}篇</div>
{/carefree:topic}
```

### 50. page - 单页列表
```html
{carefree:page limit='10'}
    <a href="/page/{$page.id}.html">{$page.title}</a>
{/carefree:page}
```

### 51. position - 内容区块
```html
{carefree:position name='home_banner'}
    {$position.content|raw}
{/carefree:position}
```

### 52. hotwords - 热门关键词
```html
{carefree:hotwords limit='10' days='7'}
    <a href="/search?q={$word.keyword}">{$word.keyword}</a>
{/carefree:hotwords}
```

### 53. randomimg - 随机图片
```html
{carefree:randomimg limit='6' source='unsplash'}
    <img src="{$img.url}">
{/carefree:randomimg}
```

---

## 常用变量

### 文章数据
```php
$article.id              // ID
$article.title           // 标题
$article.description     // 描述
$article.content         // 内容
$article.cover_image     // 封面图
$article.view_count      // 浏览量
$article.like_count      // 点赞数
$article.comment_count   // 评论数
$article.create_time     // 创建时间
$article.category.name   // 分类名称
$article.user.username   // 作者
```

### 循环变量
```php
$key                     // 索引(从0开始)
$i                       // 序号(从1开始)
$mod                     // 奇偶(0或1)
```

### 日期格式化
```html
{$article.create_time|date='Y-m-d'}           // 2025-01-11
{$article.create_time|date='Y-m-d H:i:s'}     // 2025-01-11 15:30:00
```

---

## 性能优化

### ✅ 推荐做法
```html
<!-- 限制数量 -->
{carefree:article limit='10'}...{/carefree:article}

<!-- 使用缓存 -->
{carefree:cache key='hot' time='3600'}
    {carefree:article flag='hot' limit='10'}...{/carefree:article}
{/carefree:cache}

<!-- 指定字段 -->
{carefree:article limit='10' titlelen='30'}...{/carefree:article}
```

### ❌ 避免做法
```html
<!-- 不限制数量 -->
{carefree:article}...{/carefree:article}

<!-- 过度嵌套 -->
{carefree:category}
    {carefree:article typeid='{$category.id}'}
        {carefree:tag}...{/carefree:tag}
    {/carefree:article}
{/carefree:category}
```

---

## 调试技巧

### 查看数据结构
```html
{carefree:article limit='1'}
    <pre>{$article|json_encode:JSON_PRETTY_PRINT}</pre>
{/carefree:article}
```

### 检查变量
```html
{if condition="isset($article)"}
    文章数据存在
{else}
    文章数据不存在
{/if}
```

---

## 版本信息

- **当前版本**: v2.0.0
- **标签总数**: 53个
- **新增标签**: 16个 ⭐
- **更新日期**: 2025-01-11

---

## 快速链接

- 📖 [完整文档](./CAREFREE_TAGLIB_COMPLETE_GUIDE.md)
- 🚀 [快速开始](./CAREFREE_QUICK_START.md)
- 💡 [最佳实践](./CAREFREE_BEST_PRACTICES.md)
- 🔧 [故障排查](./CAREFREE_TROUBLESHOOTING.md)

---

**CarefreeCMS v2.0.0** - 53个标签，无限可能

# 会员等级事件监听使用指南

## 概述

会员等级系统提供了事件驱动的实时升级检查机制。当用户执行特定行为时（如发布文章、评论、获得积分），系统会自动触发事件检查用户是否符合升级条件。

## 事件和监听器

### 事件类

**类名**: `app\event\UserAction`

**参数**:
- `userId` (int): 用户ID
- `actionType` (string): 行为类型
- `actionData` (array): 行为数据

**支持的行为类型**:
- `article_created`: 用户发布了文章
- `comment_created`: 用户发表了评论
- `points_changed`: 用户积分发生变化

### 监听器类

**类名**: `app\listener\CheckMemberLevelUpgrade`

监听 `UserAction` 事件，自动检查并升级用户等级。

## 使用方法

### 1. 在文章发布时触发

在文章控制器中，当用户成功发布文章后：

```php
<?php

namespace app\controller\api;

use think\facade\Event;
use app\event\UserAction;

class Article extends BaseController
{
    public function create()
    {
        // ... 文章创建逻辑 ...

        $article = Article::create($data);

        // 触发用户行为事件
        Event::trigger(new UserAction(
            $userId,
            'article_created',
            [
                'article_id' => $article->id,
                'title' => $article->title,
            ]
        ));

        return Response::success($article, '发布成功');
    }
}
```

### 2. 在评论发表时触发

在评论控制器中：

```php
<?php

namespace app\controller\api;

use think\facade\Event;
use app\event\UserAction;

class Comment extends BaseController
{
    public function create()
    {
        // ... 评论创建逻辑 ...

        $comment = Comment::create($data);

        // 触发用户行为事件
        Event::trigger(new UserAction(
            $userId,
            'comment_created',
            [
                'comment_id' => $comment->id,
                'article_id' => $comment->article_id,
            ]
        ));

        return Response::success($comment, '评论成功');
    }
}
```

### 3. 在积分变化时触发

在积分服务或控制器中：

```php
<?php

namespace app\service;

use think\facade\Event;
use app\event\UserAction;

class PointService
{
    public static function addPoints($userId, $points, $reason)
    {
        // ... 积分增加逻辑 ...

        $user = FrontUser::find($userId);
        $user->points += $points;
        $user->save();

        // 触发用户行为事件
        Event::trigger(new UserAction(
            $userId,
            'points_changed',
            [
                'points' => $points,
                'reason' => $reason,
                'total_points' => $user->points,
            ]
        ));

        return true;
    }
}
```

### 4. 在用户统计更新时触发

在统计更新后：

```php
<?php

namespace app\service;

use think\facade\Event;
use app\event\UserAction;

class UserStatsService
{
    /**
     * 更新用户文章数统计
     */
    public static function updateArticleCount($userId)
    {
        $user = FrontUser::find($userId);
        $count = Article::where('user_id', $userId)
            ->where('status', 1)
            ->count();

        $user->article_count = $count;
        $user->save();

        // 文章数变化后检查等级
        Event::trigger(new UserAction(
            $userId,
            'article_created',
            ['count' => $count]
        ));
    }

    /**
     * 更新用户评论数统计
     */
    public static function updateCommentCount($userId)
    {
        $user = FrontUser::find($userId);
        $count = Comment::where('user_id', $userId)
            ->where('status', 1)
            ->count();

        $user->comment_count = $count;
        $user->save();

        // 评论数变化后检查等级
        Event::trigger(new UserAction(
            $userId,
            'comment_created',
            ['count' => $count]
        ));
    }
}
```

## 配置文件

事件和监听器的绑定在 `config/event.php` 中配置：

```php
<?php

return [
    'bind' => [
        'app\event\UserAction' => [
            'app\listener\CheckMemberLevelUpgrade',
        ],
    ],
];
```

## 工作流程

1. 用户执行某个行为（发布文章、评论、获得积分等）
2. 业务代码触发 `UserAction` 事件
3. `CheckMemberLevelUpgrade` 监听器自动执行
4. 监听器调用 `MemberLevelService::checkAndUpgradeUser()` 检查升级
5. 如果满足条件，自动升级用户等级并发送通知
6. 记录升级日志

## 优势

相比定时任务，事件监听机制有以下优势：

1. **实时性**: 用户行为发生后立即检查升级，无需等待定时任务
2. **用户体验好**: 用户可以立即看到等级变化
3. **减少资源消耗**: 只在必要时检查，不需要定期扫描所有用户
4. **灵活性**: 可以根据不同行为类型定制升级逻辑

## 最佳实践

### 建议同时使用事件监听和定时任务

- **事件监听**: 处理实时升级，提升用户体验
- **定时任务**: 作为补充机制，处理遗漏的情况和注册天数条件

### 避免频繁触发

如果某些行为非常频繁（如浏览、点赞），建议：

1. 不为这些行为触发等级检查事件
2. 或者增加防抖机制，限制检查频率

```php
// 示例：使用缓存防抖
use think\facade\Cache;

$cacheKey = "level_check_{$userId}";
if (!Cache::has($cacheKey)) {
    Event::trigger(new UserAction($userId, 'points_changed'));
    // 5分钟内不再检查
    Cache::set($cacheKey, 1, 300);
}
```

### 异常处理

监听器中的异常不会影响主业务流程，但会记录到日志中。建议定期检查日志：

```bash
tail -f runtime/log/YYYY-MM-DD.log | grep "会员等级"
```

## 扩展其他监听器

可以为 `UserAction` 事件添加更多监听器：

```php
// config/event.php
return [
    'bind' => [
        'app\event\UserAction' => [
            'app\listener\CheckMemberLevelUpgrade',  // 等级升级检查
            'app\listener\UserStatistics',           // 用户统计
            'app\listener\AchievementCheck',         // 成就检查
        ],
    ],
];
```

## 调试

如果事件没有触发，检查：

1. 事件绑定配置是否正确（`config/event.php`）
2. 事件触发代码是否执行
3. 查看日志文件了解详细信息

```php
// 调试用：直接触发事件测试
use think\facade\Event;
use app\event\UserAction;

Event::trigger(new UserAction(1, 'points_changed', ['test' => true]));
```

## 性能考虑

- 事件监听是同步执行的，会在当前请求中完成
- 等级检查逻辑已优化，通常在 50ms 以内完成
- 如果担心性能影响，可以考虑使用队列异步处理（需要配置队列系统）

## 总结

事件监听机制为会员等级系统提供了实时、灵活的升级检查能力。建议在关键用户行为点（发布文章、评论、积分变化）触发事件，配合定时任务使用，提供最佳的用户体验。

# 数据库表重构修复进度报告

执行时间：2025-11-09

## 一、已完成工作

### ✅ 1. 创建新的统一模型（3个）

| 新模型 | 替代的旧模型 | 文件位置 | 状态 |
|-------|-------------|---------|------|
| **Relation** | ArticleCategory, ArticleTag, TopicArticle | `backend/app/model/Relation.php` | ✅ 完成 |
| **Group** | LinkGroup, SliderGroup, PointShopCategory, AdPosition | `backend/app/model/Group.php` | ✅ 完成 |
| **UserAction** | UserLike, UserFavorite, UserFollow | `backend/app/model/UserAction.php` | ✅ 完成 |

#### Relation 模型核心方法：
- `saveArticleCategories()` - 保存文章分类关联
- `saveArticleTags()` - 保存文章标签关联
- `saveTopicArticles()` - 保存专题文章关联
- `getArticleCategoryIds()` - 获取文章分类ID列表
- `getArticleTagIds()` - 获取文章标签ID列表
- `getTopicArticleIds()` - 获取专题文章ID列表

#### Group 模型核心方法：
- `getLinkGroups()` - 获取友链分组
- `getSliderGroups()` - 获取幻灯片分组
- `getPointShopCategories()` - 获取积分商品分类
- `getAdPositions()` - 获取广告位
- `getBySlug()` - 根据slug获取分组

#### UserAction 模型核心方法：
- `addLike()` / `removeLike()` - 添加/取消点赞
- `addFavorite()` / `removeFavorite()` - 添加/取消收藏
- `addFollow()` / `removeFollow()` - 添加/取消关注
- `hasLiked()` / `hasFavorited()` / `hasFollowed()` - 检查状态
- `getLikeCount()` / `getFavoriteCount()` - 获取统计数量

---

### ✅ 2. 修复Article控制器

**文件**: `backend/app/controller/api/Article.php`

#### 修改内容：

**导入语句修改：**
```php
// 旧代码
use app\model\ArticleTag;
use app\model\ArticleCategory;
use app\model\TopicArticle;

// 新代码
use app\model\Relation;
```

**index方法 - 修复分类查询：**
```php
// 旧代码：直接查询 article_categories 表
$subQuery->table('article_categories')
         ->where('category_id', $categoryId)
         ->field('article_id');

// 新代码：查询 relations 表
$subQuery->table('relations')
         ->where('source_type', 'article')
         ->where('target_type', 'category')
         ->where('target_id', $categoryId)
         ->field('source_id');
```

**save方法 - 修复标签、分类、专题关联创建：**
```php
// 旧代码
ArticleTag::create(['article_id' => $article->id, 'tag_id' => $tagId]);
ArticleCategory::create(['article_id' => $article->id, 'category_id' => $mainCategoryId, 'is_main' => 1]);
TopicArticle::create(['topic_id' => $topicId, 'article_id' => $article->id, 'sort' => 0]);

// 新代码
Relation::saveArticleTags($article->id, $tagIds, $siteId);
Relation::saveArticleCategories($article->id, $allCategories, $mainCategoryId, $siteId);
Relation::saveTopicArticles($topicId, [$article->id], $siteId);
```

**update方法 - 修复关联更新：**
```php
// 旧代码
ArticleTag::where('article_id', $id)->delete();
// 然后循环创建新关联

// 新代码
Relation::saveArticleTags($id, $tagIds, $siteId);
```

**delete方法 - 修复关联删除：**
```php
// 旧代码
ArticleTag::where('article_id', $id)->delete();
ArticleCategory::where('article_id', $id)->delete();

// 新代码
// 删除源关联
Relation::where('source_type', 'article')->where('source_id', $id)->delete();
// 删除目标关联
Relation::where('target_type', 'article')->where('target_id', $id)->delete();
```

---

### ✅ 3. 修复FrontProfile控制器

**文件**: `backend/app/controller/api/FrontProfile.php`

#### 修改内容：

**导入语句修改：**
```php
// 旧代码
use app\model\UserFavorite;
use app\model\UserLike;
use app\model\UserFollow;

// 新代码
use app\model\UserAction;
use app\model\Article;
```

**favorites方法 - 获取收藏列表：**
```php
// 旧代码
$favorites = UserFavorite::with(['article'])
    ->where('user_id', $userId)
    ->order('create_time', 'desc')
    ->paginate(...);

// 新代码
$articleIds = UserAction::getUserFavoriteArticleIds($userId);
// 分页处理
$pageIds = array_slice($articleIds, $offset, $limit);
// 获取文章详情
$articles = Article::whereIn('id', $pageIds)->select();
```

**addFavorite方法 - 添加收藏：**
```php
// 旧代码
$exists = UserFavorite::where('user_id', $userId)
    ->where('article_id', $articleId)->find();
UserFavorite::create(['user_id' => $userId, 'article_id' => $articleId]);

// 新代码
if (UserAction::hasFavorited($userId, UserAction::TARGET_ARTICLE, $articleId)) {...}
UserAction::addFavorite($userId, UserAction::TARGET_ARTICLE, $articleId);
```

**removeFavorite方法 - 取消收藏：**
```php
// 旧代码
$favorite = UserFavorite::where(...)->find();
$favorite->delete();

// 新代码
UserAction::removeFavorite($userId, UserAction::TARGET_ARTICLE, $articleId);
```

**addLike / removeLike - 点赞相关：**
```php
// 旧代码
$exists = UserLike::where(...)->find();
UserLike::create([...]);
$like->delete();

// 新代码
if (UserAction::hasLiked($userId, $targetType, $targetId)) {...}
UserAction::addLike($userId, $targetType, $targetId);
UserAction::removeLike($userId, $targetType, $targetId);
```

**follow / unfollow - 关注相关：**
```php
// 旧代码
$exists = UserFollow::where(...)->find();
UserFollow::create([...]);
$follow->delete();

// 新代码
if (UserAction::hasFollowed($userId, $followUserId)) {...}
UserAction::addFollow($userId, $followUserId);
UserAction::removeFollow($userId, $followUserId);
```

**followingList / followerList - 列表查询：**
```php
// 旧代码
$following = UserFollow::with(['followUser'])
    ->where('user_id', $userId)
    ->paginate(...);

// 新代码
$followingIds = UserAction::getUserFollowingIds($userId);
$pageIds = array_slice($followingIds, $offset, $limit);
$users = FrontUser::whereIn('id', $pageIds)->select();
```

---

## 二、待修复项目

### 🔄 优先级 1 - 高风险（需立即修复）

#### 1. LogController - 日志管理
**文件**: `backend/app/controller/api/LogController.php`
**影响**: 查询login_logs, security_logs表
**修复方案**: 改为查询operation_logs表，使用module和action字段过滤

#### 2. SystemLogger - 日志服务
**文件**: `backend/app/service/SystemLogger.php`
**影响**: 写入login_logs, security_logs表
**修复方案**: 改为写入operation_logs表

#### 3. SiteTableService - 站点表配置
**文件**: `backend/app/service/SiteTableService.php`
**影响**: $siteTables数组包含已删除的表名
**修复方案**: 更新$siteTables数组，移除已删除表，添加新表

---

### 🔄 优先级 2 - 高优先级

#### 4. LinkGroupController
**文件**: `backend/app/controller/api/LinkGroupController.php`
**修复方案**: 使用Group模型，type='link'

#### 5. AdPositionController
**文件**: `backend/app/controller/api/AdPositionController.php`
**修复方案**: 使用Group模型，type='ad'

#### 6. ArticleTagService
**文件**: `backend/app/service/tag/ArticleTagService.php`
**修复方案**: 使用Relation模型查询article-tag关联

---

### 🔄 优先级 3 - 中等优先级

#### 7. SliderGroupController
**文件**: `backend/app/controller/api/SliderGroupController.php`
**修复方案**: 使用Group模型，type='slider'

#### 8. SliderController
**文件**: `backend/app/controller/api/SliderController.php`
**修复方案**: 通过Group模型获取分组信息

#### 9. PointShop相关控制器
**文件**: `backend/app/controller/api/PointShop.php`, `PointShopManage.php`
**修复方案**: 使用Group模型，type='point_shop'

#### 10. NotificationController
**文件**: `backend/app/controller/api/NotificationController.php`
**影响**: 使用UserNotificationSetting模型
**修复方案**: 需要确认是否保留notification功能

---

### 🔄 优先级 4 - 低优先级

#### 11. OAuthConfig相关
**文件**: `backend/app/controller/api/OAuthConfigController.php`, `OAuthController.php`
**影响**: 使用OAuthConfig模型（应使用oauth_configs）
**修复方案**: 确认使用正确的oauth_configs表

#### 12. TopicController
**文件**: `backend/app/controller/api/TopicController.php`
**影响**: 使用TopicArticle模型
**修复方案**: 使用Relation模型处理专题-文章关联

#### 13. ArticleVersion
**文件**: `backend/app/controller/api/ArticleVersion.php`
**影响**: 使用ArticleTag模型
**修复方案**: 使用Relation模型

---

## 三、数据库迁移脚本状态

| 脚本文件 | 功能 | 执行状态 |
|---------|------|---------|
| `create_relations_table.sql` | 创建relations表并迁移数据 | ✅ 已执行 |
| `create_groups_table.sql` | 创建groups表并迁移数据 | ✅ 已执行 |
| `create_user_actions_table.sql` | 创建user_actions表 | ✅ 已执行 |
| `migrate_and_drop_duplicate_logs.sql` | 迁移日志表 | ✅ 已执行 |
| `drop_duplicate_tables.sql` | 删除重复表 | ✅ 已执行 |

---

## 四、需要删除的旧模型文件（15个）

### 关联表模型（3个）
- ❌ `backend/app/model/ArticleCategory.php`
- ❌ `backend/app/model/ArticleTag.php`
- ❌ `backend/app/model/TopicArticle.php`

### 分组表模型（4个）
- ❌ `backend/app/model/LinkGroup.php`
- ❌ `backend/app/model/SliderGroup.php`
- ❌ `backend/app/model/PointShopCategory.php`
- ❌ `backend/app/model/AdPosition.php`

### 用户行为表模型（3个）
- ❌ `backend/app/model/UserLike.php`
- ❌ `backend/app/model/UserFavorite.php`
- ❌ `backend/app/model/UserFollow.php`

### 日志表模型（2个）
- ❌ `backend/app/model/LoginLog.php`
- ❌ `backend/app/model/SecurityLog.php`

### 重复表模型（3个）
- ❌ `backend/app/model/OAuthConfig.php`（应使用oauth_configs）
- ❌ `backend/app/model/UserNotification.php`
- ❌ `backend/app/model/UserNotificationSetting.php`

**注意**: 删除前请确保所有引用都已修复！

---

## 五、修复进度统计

### 总体进度
- **已完成**: 5项（新模型3个 + 控制器2个）
- **待修复**: 13个控制器 + 15个旧模型删除
- **完成度**: ~21%

### 按类型统计
| 类型 | 总数 | 已完成 | 待完成 |
|------|------|--------|--------|
| 新模型创建 | 3 | 3 ✅ | 0 |
| 控制器修复 | 15 | 2 ✅ | 13 |
| 服务修复 | 3 | 0 | 3 |
| 旧模型删除 | 15 | 0 | 15 |
| **总计** | **36** | **5** | **31** |

---

## 六、后续工作计划

### 第一批（立即执行）
1. ✅ 修复Article控制器
2. ✅ 修复FrontProfile控制器
3. ⏳ 修复LogController
4. ⏳ 修复SystemLogger服务
5. ⏳ 修复SiteTableService

### 第二批（高优先级）
6. ⏳ 修复LinkGroupController
7. ⏳ 修复AdPositionController
8. ⏳ 修复ArticleTagService
9. ⏳ 修复SliderGroupController
10. ⏳ 修复PointShop相关控制器

### 第三批（测试与清理）
11. ⏳ 修复其他控制器
12. ⏳ 删除旧模型文件
13. ⏳ 全面测试
14. ⏳ 删除*_deleted备份表

---

## 七、注意事项

### ⚠️ 重要提醒
1. **不要删除旧模型**：在所有引用都修复之前，保留旧模型文件
2. **保留备份表**：*_backup 和 *_deleted 表暂时保留，确认无误后再删除
3. **测试充分性**：每修复一个控制器，建议立即测试相关功能
4. **数据完整性**：所有数据已迁移，无数据丢失

### 📋 测试清单
修复完成后需要测试的功能：
- [ ] 文章创建/编辑（分类、标签、专题）
- [ ] 文章删除（关联删除）
- [ ] 文章列表（分类筛选）
- [ ] 用户收藏/取消收藏
- [ ] 用户点赞/取消点赞
- [ ] 用户关注/取消关注
- [ ] 收藏列表
- [ ] 关注列表/粉丝列表
- [ ] 日志记录和查询
- [ ] 友链分组管理
- [ ] 幻灯片分组管理
- [ ] 广告位管理
- [ ] 积分商品分类管理

---

**报告生成时间**: 2025-11-09
**当前状态**: 🔄 修复进行中
**风险等级**: 🟡 中等风险（关键功能已修复，其他功能待修复）

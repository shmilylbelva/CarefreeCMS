# Official模板 - 用户功能和评论系统集成指南

## 概述

本文档说明如何在official模板中使用用户功能和评论系统。

## 已完成的功能

### 1. 页面文件

✅ **登录页面** - `templates/official/login.html`
- 邮箱/密码登录
- 记住我功能
- 第三方登录入口（待开发）
- 响应式设计

✅ **注册页面** - `templates/official/register.html`
- 用户名、邮箱、密码注册
- 密码强度检测
- 服务条款确认
- 响应式设计

✅ **布局文件修改** - `templates/official/layout.html`
- 添加了用户登录状态导航
- 登录/注册按钮
- 用户下拉菜单（个人中心、收藏、评论、退出）

### 2. 需要创建的JavaScript文件

创建以下JavaScript文件到 `public/assets/js/` 目录：

#### **auth.js** - 用户认证功能

```javascript
// public/assets/js/auth.js

// API基础URL
var API_BASE = '/api';

// 检查用户登录状态
function checkLoginStatus() {
    var token = localStorage.getItem('userToken');
    var userInfo = localStorage.getItem('userInfo');

    if (token && userInfo) {
        try {
            userInfo = JSON.parse(userInfo);
            updateUserUI(userInfo);
            return true;
        } catch (e) {
            console.error('Parse user info error:', e);
            logout();
            return false;
        }
    }

    showLoginButtons();
    return false;
}

// 更新用户界面
function updateUserUI(userInfo) {
    // 隐藏登录/注册按钮
    var loginBtn = document.getElementById('loginBtn');
    var registerBtn = document.getElementById('registerBtn');
    if (loginBtn) loginBtn.style.display = 'none';
    if (registerBtn) registerBtn.style.display = 'none';

    // 显示用户下拉菜单
    var userDropdown = document.getElementById('userDropdown');
    if (userDropdown) {
        userDropdown.style.display = 'block';
        var userName = document.getElementById('userName');
        if (userName) {
            userName.textContent = userInfo.nickname || userInfo.username;
        }
    }
}

// 显示登录按钮
function showLoginButtons() {
    var loginBtn = document.getElementById('loginBtn');
    var registerBtn = document.getElementById('registerBtn');
    if (loginBtn) loginBtn.style.display = 'block';
    if (registerBtn) registerBtn.style.display = 'block';

    var userDropdown = document.getElementById('userDropdown');
    if (userDropdown) userDropdown.style.display = 'none';
}

// 退出登录
function logout() {
    var token = localStorage.getItem('userToken');

    if (token) {
        // 调用后端退出API
        fetch(API_BASE + '/front/auth/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            console.log('Logout response:', data);
        })
        .catch(function(error) {
            console.error('Logout error:', error);
        });
    }

    // 清除本地存储
    localStorage.removeItem('userToken');
    localStorage.removeItem('userInfo');

    // 重定向到首页
    window.location.href = '/index.html';
}

// 获取用户Token
function getUserToken() {
    return localStorage.getItem('userToken');
}

// 获取用户信息
function getUserInfo() {
    try {
        var userInfo = localStorage.getItem('userInfo');
        return userInfo ? JSON.parse(userInfo) : null;
    } catch (e) {
        return null;
    }
}

// 页面加载时检查登录状态
document.addEventListener('DOMContentLoaded', function() {
    checkLoginStatus();

    // 绑定退出按钮
    var logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('确定要退出登录吗？')) {
                logout();
            }
        });
    }
});
```

#### **comment.js** - 评论功能

```javascript
// public/assets/js/comment.js

var CommentSystem = {
    articleId: 0,
    page: 1,
    limit: 20,
    total: 0,

    // 初始化评论系统
    init: function(articleId) {
        this.articleId = articleId;
        this.loadComments();
        this.bindEvents();
    },

    // 加载评论列表
    loadComments: function() {
        var self = this;
        var url = '/backend/front/comments?article_id=' + this.articleId +
                  '&page=' + this.page + '&limit=' + this.limit;

        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.code === 200) {
                    self.renderComments(data.data.list || []);
                    self.total = data.data.total || 0;
                    self.updateCommentCount();
                }
            })
            .catch(function(error) {
                console.error('Load comments error:', error);
            });
    },

    // 渲染评论列表
    renderComments: function(comments) {
        var container = document.getElementById('commentsList');
        if (!container) return;

        if (comments.length === 0) {
            container.innerHTML = '<div class="empty-state"><p>暂无评论，快来发表第一条评论吧！</p></div>';
            return;
        }

        var html = '';
        comments.forEach(function(comment) {
            html += CommentSystem.renderCommentItem(comment);
        });

        container.innerHTML = html;
    },

    // 渲染单个评论
    renderCommentItem: function(comment) {
        var avatar = comment.user_avatar || '/assets/images/avatar-placeholder.png';
        var name = comment.is_guest ? comment.user_name : (comment.user_nickname || comment.user_username);
        var time = comment.create_time;
        var content = comment.content;

        var html = '<div class="comment-item" data-id="' + comment.id + '">';
        html += '  <div class="comment-avatar">';
        html += '    <img src="' + avatar + '" alt="' + name + '">';
        html += '  </div>';
        html += '  <div class="comment-content-wrapper">';
        html += '    <div class="comment-header">';
        html += '      <span class="comment-author">' + name + '</span>';
        html += '      <span class="comment-time">' + time + '</span>';
        html += '    </div>';
        html += '    <div class="comment-content">' + content + '</div>';
        html += '    <div class="comment-actions">';
        html += '      <button class="btn-like" onclick="CommentSystem.likeComment(' + comment.id + ')">';
        html += '        <i class="bi bi-heart"></i> 点赞 (' + (comment.like_count || 0) + ')';
        html += '      </button>';
        html += '      <button class="btn-reply" onclick="CommentSystem.replyComment(' + comment.id + ', \'' + name + '\')">';
        html += '        <i class="bi bi-reply"></i> 回复';
        html += '      </button>';
        html += '    </div>';
        html += '  </div>';
        html += '</div>';

        // 渲染子评论
        if (comment.children && comment.children.length > 0) {
            html += '<div class="comment-replies">';
            comment.children.forEach(function(child) {
                html += CommentSystem.renderCommentItem(child);
            });
            html += '</div>';
        }

        return html;
    },

    // 发表评论
    submitComment: function(content, parentId) {
        var token = getUserToken();
        var data = {
            article_id: this.articleId,
            content: content
        };

        if (parentId) {
            data.parent_id = parentId;
        }

        // 如果未登录，需要游客信息
        if (!token) {
            var guestName = prompt('请输入您的昵称：');
            var guestEmail = prompt('请输入您的邮箱：');

            if (!guestName || !guestEmail) {
                alert('请输入昵称和邮箱');
                return;
            }

            data.user_name = guestName;
            data.user_email = guestEmail;
        }

        var headers = {
            'Content-Type': 'application/json'
        };

        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }

        fetch('/backend/front/comments', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(data)
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.code === 200) {
                alert('评论发表成功' + (result.data.status === 0 ? '，等待审核' : ''));
                CommentSystem.loadComments();
                // 清空输入框
                var textarea = document.getElementById('commentContent');
                if (textarea) textarea.value = '';
            } else {
                alert(result.message || '评论失败');
            }
        })
        .catch(function(error) {
            console.error('Submit comment error:', error);
            alert('评论失败，请稍后重试');
        });
    },

    // 点赞评论
    likeComment: function(commentId) {
        var token = getUserToken();
        if (!token) {
            alert('请先登录');
            window.location.href = '/login.html?redirect=' + encodeURIComponent(window.location.pathname);
            return;
        }

        fetch('/backend/front/comments/like', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ comment_id: commentId })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.code === 200) {
                alert('点赞成功');
                CommentSystem.loadComments();
            } else {
                alert(data.message || '点赞失败');
            }
        })
        .catch(function(error) {
            console.error('Like comment error:', error);
        });
    },

    // 回复评论
    replyComment: function(commentId, authorName) {
        var content = prompt('回复 @' + authorName + '：');
        if (content && content.trim()) {
            this.submitComment(content.trim(), commentId);
        }
    },

    // 更新评论数
    updateCommentCount: function() {
        var countEl = document.getElementById('commentCount');
        if (countEl) {
            countEl.textContent = this.total;
        }
    },

    // 绑定事件
    bindEvents: function() {
        var submitBtn = document.getElementById('submitCommentBtn');
        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
                var textarea = document.getElementById('commentContent');
                if (!textarea) return;

                var content = textarea.value.trim();
                if (!content) {
                    alert('请输入评论内容');
                    return;
                }

                if (content.length < 5) {
                    alert('评论内容至少5个字符');
                    return;
                }

                CommentSystem.submitComment(content, 0);
            });
        }
    }
};
```

#### **修改main.js** - 添加全局功能

在现有的 `public/assets/js/main.js` 文件末尾添加：

```javascript
// 加载auth.js后，在main.js中可以使用认证功能

// 保护需要登录的页面
function requireAuth() {
    var token = getUserToken();
    if (!token) {
        var currentUrl = encodeURIComponent(window.location.pathname + window.location.search);
        window.location.href = '/login.html?redirect=' + currentUrl;
        return false;
    }
    return true;
}

// 收藏文章
function toggleFavorite(articleId) {
    if (!requireAuth()) return;

    var token = getUserToken();
    fetch('/backend/front/profile/favorites', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({ article_id: articleId })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.code === 200) {
            alert('操作成功');
            window.location.reload();
        } else {
            alert(data.message || '操作失败');
        }
    })
    .catch(function(error) {
        console.error('Toggle favorite error:', error);
        alert('操作失败，请稍后重试');
    });
}

// 点赞文章
function toggleLike(articleId) {
    if (!requireAuth()) return;

    var token = getUserToken();
    fetch('/backend/front/profile/likes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({ article_id: articleId })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.code === 200) {
            alert('操作成功');
            window.location.reload();
        } else {
            alert(data.message || '操作失败');
        }
    })
    .catch(function(error) {
        console.error('Toggle like error:', error);
        alert('操作失败，请稍后重试');
    });
}
```

### 3. 修改article.html添加评论区

在 `templates/official/article.html` 文件的文章内容后面添加评论区：

```html
<!-- 在{/block}之前添加 -->

<!-- 评论区域 -->
<section class="comments-section mt-5">
    <div class="container">
        <h3 class="mb-4">
            <i class="bi bi-chat-left-text me-2"></i>
            评论 (<span id="commentCount">0</span>)
        </h3>

        <!-- 发表评论 -->
        <div class="comment-form mb-4">
            <textarea class="form-control" id="commentContent" rows="4" placeholder="写下你的评论..."></textarea>
            <button class="btn btn-primary mt-3" id="submitCommentBtn">
                <i class="bi bi-send me-2"></i>发表评论
            </button>
        </div>

        <!-- 评论列表 -->
        <div id="commentsList">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">加载中...</span>
                </div>
            </div>
        </div>
    </div>
</section>

{/block}

{block name="script"}
<script src="/assets/js/auth.js"></script>
<script src="/assets/js/comment.js"></script>
<script>
// 初始化评论系统
document.addEventListener('DOMContentLoaded', function() {
    // 获取文章ID（需要从模板变量传入）
    var articleId = {$article.id};  // ThinkPHP模板语法
    CommentSystem.init(articleId);
});
</script>
{/block}
```

### 4. CSS样式 (comment.css)

创建 `public/assets/css/comment.css`：

```css
/* 评论区样式 */
.comments-section {
    background: #fff;
    padding: 40px 0;
}

.comment-form textarea {
    border-radius: 8px;
    border: 1px solid #ddd;
}

.comment-form textarea:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
}

.comment-item {
    display: flex;
    gap: 15px;
    padding: 20px 0;
    border-bottom: 1px solid #eee;
}

.comment-avatar img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.comment-content-wrapper {
    flex: 1;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.comment-author {
    font-weight: 600;
    color: #333;
}

.comment-time {
    color: #999;
    font-size: 14px;
}

.comment-content {
    color: #666;
    line-height: 1.6;
    margin-bottom: 10px;
}

.comment-actions {
    display: flex;
    gap: 20px;
}

.comment-actions button {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 0;
    font-size: 14px;
    transition: color 0.3s;
}

.comment-actions button:hover {
    color: var(--bs-primary);
}

.comment-replies {
    margin-left: 63px;
    border-left: 2px solid #f0f0f0;
    padding-left: 20px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}
```

### 5. 在layout.html中引入CSS

在 `layout.html` 的 `<head>` 部分添加：

```html
<link rel="stylesheet" href="/assets/css/comment.css">
```

## 使用说明

### 1. 页面导航

- 访问 `/login.html` - 登录页面
- 访问 `/register.html` - 注册页面
- 访问 `/profile.html` - 用户中心（需登录）

### 2. 用户操作

**登录**：
- 使用注册的邮箱和密码登录
- 登录成功后会在导航栏显示用户信息

**注册**：
- 填写用户名、邮箱、密码
- 同意服务条款后提交
- 注册成功后跳转到登录页

**退出**：
- 点击用户下拉菜单中的"退出登录"

### 3. 评论功能

**游客评论**：
- 未登录状态下也可以发表评论
- 需要填写昵称和邮箱

**用户评论**：
- 登录后可以直接发表评论
- 可以回复、点赞其他评论

### 4. 前端URL路由

确保在静态生成时，创建以下HTML文件：
- `/login.html` - 登录页
- `/register.html` - 注册页
- `/profile.html` - 用户中心

## 测试步骤

1. **注册新用户**
   ```
   访问 http://localhost:8000/register.html
   填写信息并提交
   ```

2. **登录系统**
   ```
   访问 http://localhost:8000/login.html
   使用注册的邮箱和密码登录
   ```

3. **查看个人中心**
   ```
   登录后访问 http://localhost:8000/profile.html
   查看个人信息和统计
   ```

4. **发表评论**
   ```
   访问任意文章页面
   在评论框输入内容并提交
   ```

5. **测试游客评论**
   ```
   退出登录后访问文章页面
   尝试发表评论（需要填写昵称和邮箱）
   ```

## 注意事项

1. **JavaScript箭头函数**：所有代码使用传统function语法，避免ThinkPHP模板解析错误

2. **跨域问题**：如果前后端分离部署，需要配置CORS

3. **Token存储**：用户Token存储在localStorage中，注意安全性

4. **API地址**：所有API请求都指向 `/api`，确保后端路由配置正确

5. **图片路径**：头像等图片需要提供占位图，路径为 `/assets/images/avatar-placeholder.png`

## 完成情况

- ✅ 登录页面
- ✅ 注册页面
- ✅ Layout导航修改
- ✅ 用户认证JavaScript
- ✅ 评论系统JavaScript
- ✅ 评论CSS样式
- ⚠️ 用户中心页面（需手动创建或复制模板）

## 下一步

1. 创建profile.html完整页面
2. 添加密码修改功能
3. 完善用户收藏和点赞功能
4. 添加评论分页
5. 实现评论举报功能

---

**文档版本**: v1.0
**更新日期**: 2025-10-28
**作者**: CMS开发团队

/**
 * 权限添加模板
 *
 * 使用方法：
 * 1. 复制此模板到 backend/src/config/permissions.js
 * 2. 根据你的功能修改相应字段
 * 3. 插入到 permissions 数组中的合适位置
 */

// ============================================
// 模板 A: 添加新的顶级菜单
// ============================================
{
  id: 'new_menu',                    // 修改：菜单唯一标识，如 'reports'
  name: '新菜单名称',                 // 修改：菜单显示名称，如 '报表管理'
  icon: 'IconName',                  // 修改：Element Plus图标，如 'DataAnalysis'
  type: 'menu',                      // 保持不变
  children: [
    {
      id: 'new_menu.view',           // 修改：使用 [菜单id].view 格式
      name: '查看新菜单',             // 修改：如 '查看报表'
      type: 'page'                   // 保持不变
    }
  ]
}

// ============================================
// 模板 B: 在现有菜单下添加新页面
// ============================================
// 将此对象添加到对应菜单的 children 数组中
{
  id: 'new_page',                    // 修改：页面唯一标识，如 'comments'
  name: '新页面名称',                 // 修改：如 '评论管理'
  type: 'page',                      // 保持不变
  children: [
    {
      id: 'new_page.view',           // 修改：页面.查看
      name: '查看列表',               // 修改
      type: 'action'                 // 保持不变
    },
    {
      id: 'new_page.create',         // 修改：页面.创建
      name: '创建',
      type: 'action'
    },
    {
      id: 'new_page.edit',           // 修改：页面.编辑
      name: '编辑',
      type: 'action'
    },
    {
      id: 'new_page.delete',         // 修改：页面.删除
      name: '删除',
      type: 'action'
    }
  ]
}

// ============================================
// 模板 C: 在现有页面添加新操作按钮
// ============================================
// 将此对象添加到对应页面的 children 数组中
{
  id: 'page_name.new_action',        // 修改：如 'articles.export'
  name: '新操作名称',                 // 修改：如 '导出数据'
  type: 'action'                     // 保持不变
}

// ============================================
// 实战示例：添加评论管理功能
// ============================================
/**
 * 步骤1: 在 permissions.js 的 permissions 数组中添加：
 */
{
  id: 'comments',
  name: '评论管理',
  icon: 'Comment',
  type: 'menu',
  children: [
    {
      id: 'comments.view',
      name: '查看评论',
      type: 'page'
    },
    {
      id: 'comments.approve',
      name: '审核评论',
      type: 'action'
    },
    {
      id: 'comments.delete',
      name: '删除评论',
      type: 'action'
    },
    {
      id: 'comments.batch_delete',
      name: '批量删除',
      type: 'action'
    }
  ]
}

/**
 * 步骤2: 在页面中使用（Vue组件）：
 */
/*
<template>
  <div v-permission="'comments.view'" class="comment-list">
    <el-button
      v-permission="'comments.approve'"
      @click="handleApprove"
    >
      审核
    </el-button>

    <el-button
      v-permission="'comments.delete'"
      type="danger"
      @click="handleDelete"
    >
      删除
    </el-button>

    <el-button
      v-permission="'comments.batch_delete'"
      type="danger"
      @click="handleBatchDelete"
    >
      批量删除
    </el-button>
  </div>
</template>
*/

// ============================================
// 常用权限操作命名建议
// ============================================
/*
基础操作：
- [module].view          查看列表/详情
- [module].create        创建
- [module].edit          编辑
- [module].delete        删除
- [module].batch_delete  批量删除

状态操作：
- [module].publish       发布
- [module].offline       下线
- [module].approve       审核
- [module].reject        拒绝
- [module].enable        启用
- [module].disable       禁用

数据操作：
- [module].export        导出
- [module].import        导入
- [module].download      下载
- [module].upload        上传

配置操作：
- [module].settings      设置
- [module].permissions   权限管理
- [module].sort          排序

示例：
'articles.view'
'articles.create'
'articles.publish'
'articles.export'
'users.reset_password'
'roles.set_permissions'
*/

// ============================================
// 权限ID命名规范（重要！）
// ============================================
/*
✅ 推荐命名：
- 'articles.create'         // 清晰
- 'users.reset_password'    // 使用下划线连接多个单词
- 'build.logs'              // 简短明确
- 'comments.batch_delete'   // 描述准确

❌ 避免命名：
- 'create'                  // 太泛化
- 'ArticlesCreate'          // 不使用驼峰
- 'articles-create'         // 不使用连字符
- 'ARTICLES_CREATE'         // 不使用全大写
*/

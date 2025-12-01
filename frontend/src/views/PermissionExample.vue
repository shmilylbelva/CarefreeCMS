<template>
  <div class="permission-example">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span>权限系统使用示例</span>
          <el-tag v-if="isSuperAdmin" type="danger">超级管理员</el-tag>
        </div>
      </template>

      <!-- 用户权限信息 -->
      <el-descriptions title="当前用户权限" :column="1" border>
        <el-descriptions-item label="用户名">{{ userInfo?.username }}</el-descriptions-item>
        <el-descriptions-item label="角色">{{ userInfo?.role?.name }}</el-descriptions-item>
        <el-descriptions-item label="权限数量">{{ permissions.length }}</el-descriptions-item>
        <el-descriptions-item label="权限列表">
          <el-tag
            v-for="perm in permissions"
            :key="perm"
            size="small"
            style="margin: 2px"
          >
            {{ perm }}
          </el-tag>
        </el-descriptions-item>
      </el-descriptions>

      <!-- 示例1: 使用指令 -->
      <el-divider content-position="left">示例1: 使用 v-permission 指令</el-divider>

      <el-space wrap>
        <el-button v-permission="'article.create'" type="primary">
          创建文章 (需要 article.create)
        </el-button>

        <el-button v-permission="'article.edit'" type="warning">
          编辑文章 (需要 article.edit)
        </el-button>

        <el-button v-permission="'article.delete'" type="danger">
          删除文章 (需要 article.delete)
        </el-button>

        <el-button v-permission="'system_config.edit'" type="info">
          系统配置 (需要 system_config.edit)
        </el-button>
      </el-space>

      <!-- 示例2: 使用组件 -->
      <el-divider content-position="left">示例2: 使用 Permission 组件</el-divider>

      <el-space wrap>
        <Permission permission="article.create">
          <el-button type="primary">创建文章</el-button>
        </Permission>

        <Permission permission="article.edit">
          <el-button type="warning">编辑文章</el-button>
        </Permission>

        <Permission :permission="['article.edit', 'article.edit_own']" mode="any">
          <el-button type="success">编辑 (任一权限)</el-button>
        </Permission>

        <Permission :permission="['article.view', 'article.edit']" mode="all">
          <el-button type="info">查看并编辑 (需要全部权限)</el-button>
        </Permission>
      </el-space>

      <!-- 示例3: 使用方法检查 -->
      <el-divider content-position="left">示例3: 在代码中检查权限</el-divider>

      <el-space direction="vertical" style="width: 100%">
        <el-alert
          :title="`hasPermission('article.create'): ${hasPermission('article.create')}`"
          type="info"
          :closable="false"
        />

        <el-alert
          :title="`hasPermission('article.delete'): ${hasPermission('article.delete')}`"
          type="info"
          :closable="false"
        />

        <el-alert
          :title="`isSuperAdmin(): ${isSuperAdmin}`"
          :type="isSuperAdmin ? 'success' : 'warning'"
          :closable="false"
        />

        <el-alert
          :title="`hasAnyPermission(['article.edit', 'article.edit_own']): ${hasAnyPermission(['article.edit', 'article.edit_own'])}`"
          type="info"
          :closable="false"
        />
      </el-space>

      <!-- 示例4: 权限禁用 -->
      <el-divider content-position="left">示例4: 根据权限禁用按钮</el-divider>

      <el-space wrap>
        <el-button
          :disabled="!hasPermission('article.create')"
          type="primary"
        >
          创建文章 {{ !hasPermission('article.create') ? '(无权限)' : '' }}
        </el-button>

        <el-button
          :disabled="disableWithoutPermission('article.delete')"
          type="danger"
        >
          删除文章 {{ disableWithoutPermission('article.delete') ? '(无权限)' : '' }}
        </el-button>
      </el-space>

      <!-- 示例5: 条件渲染 -->
      <el-divider content-position="left">示例5: 条件渲染不同内容</el-divider>

      <div>
        <el-alert v-if="isSuperAdmin" title="您是超级管理员，拥有所有权限" type="success" :closable="false" />

        <el-alert
          v-else-if="hasPermission('article.*')"
          title="您拥有文章模块的所有权限"
          type="info"
          :closable="false"
        />

        <el-alert
          v-else-if="hasPermission('article.create')"
          title="您可以创建文章"
          type="warning"
          :closable="false"
        />

        <el-alert v-else title="您没有文章相关权限" type="error" :closable="false" />
      </div>

      <!-- 示例6: 列表过滤 -->
      <el-divider content-position="left">示例6: 根据权限过滤菜单</el-divider>

      <el-menu mode="horizontal">
        <el-menu-item
          v-for="item in filteredMenu"
          :key="item.path"
          :index="item.path"
        >
          <el-icon><component :is="item.icon" /></el-icon>
          <span>{{ item.title }}</span>
        </el-menu-item>
      </el-menu>

      <!-- 示例7: 权限映射 -->
      <el-divider content-position="left">示例7: 根据权限显示不同内容</el-divider>

      <el-card>
        <div>您的操作级别: <el-tag>{{ userLevel }}</el-tag></div>
        <div style="margin-top: 10px">{{ userLevelDesc }}</div>
      </el-card>

      <!-- 示例8: 实际应用场景 -->
      <el-divider content-position="left">示例8: 文章操作按钮</el-divider>

      <el-card>
        <template #header>
          <div>文章: 示例文章标题</div>
        </template>

        <el-space wrap>
          <!-- 查看按钮 - 有查看权限才显示 -->
          <Permission permission="article.view">
            <el-button size="small">查看</el-button>
          </Permission>

          <!-- 编辑按钮 - 有编辑权限或编辑自己文章权限 -->
          <Permission :permission="['article.edit', 'article.edit_own']">
            <el-button size="small" type="primary">编辑</el-button>
          </Permission>

          <!-- 删除按钮 - 仅有删除权限 -->
          <Permission permission="article.delete">
            <el-button size="small" type="danger">删除</el-button>
          </Permission>

          <!-- 发布按钮 - 有发布权限 -->
          <Permission permission="article.publish">
            <el-button size="small" type="success">发布</el-button>
          </Permission>

          <!-- 版本管理 - 有版本权限 -->
          <Permission permission="article.version">
            <el-button size="small" type="info">版本历史</el-button>
          </Permission>
        </el-space>
      </el-card>
    </el-card>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useStore } from 'vuex'
import {
  hasPermission,
  hasAnyPermission,
  hasAllPermissions,
  isSuperAdmin,
  filterByPermission,
  mapByPermission,
  disableWithoutPermission
} from '@/utils/permission'
import Permission from '@/components/Permission/index.vue'

const store = useStore()

// 用户信息和权限
const userInfo = computed(() => store.getters.userInfo)
const permissions = computed(() => store.getters.permissions || [])

// 示例菜单数据
const menuItems = ref([
  { title: '仪表盘', path: '/dashboard', icon: 'Monitor', permission: 'dashboard.view' },
  { title: '文章管理', path: '/article', icon: 'Document', permission: 'article.view' },
  { title: '分类管理', path: '/category', icon: 'Folder', permission: 'category.view' },
  { title: '用户管理', path: '/user', icon: 'User', permission: 'admin_user.view' },
  { title: '系统设置', path: '/system', icon: 'Setting', permission: 'system_config.view' }
])

// 根据权限过滤菜单
const filteredMenu = computed(() => {
  return filterByPermission(menuItems.value, item => item.permission)
})

// 根据权限映射用户级别
const userLevel = computed(() => {
  return mapByPermission({
    '*': '超级管理员',
    'article.*': '文章管理员',
    'article.edit': '编辑',
    'article.create': '作者'
  }, '访客')
})

const userLevelDesc = computed(() => {
  const levelMap = {
    '超级管理员': '您拥有系统的所有权限',
    '文章管理员': '您可以管理所有文章相关功能',
    '编辑': '您可以编辑文章',
    '作者': '您可以创建文章',
    '访客': '您只有查看权限'
  }
  return levelMap[userLevel.value] || ''
})
</script>

<style scoped>
.permission-example {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.el-divider {
  margin: 30px 0 20px;
}
</style>

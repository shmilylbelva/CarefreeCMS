<template>
  <div class="user-permission-container">
    <el-card>
      <template #header>
        <h3>用户权限管理</h3>
      </template>

      <el-tabs v-model="activeTab" type="border-card">
        <el-tab-pane label="用户管理" name="users">
          <UserList />
        </el-tab-pane>
        <el-tab-pane label="角色管理" name="roles">
          <RoleList />
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import UserList from './List.vue'
import RoleList from '../role/List.vue'

// 当前激活的tab
const activeTab = ref('users')
</script>

<style scoped>
.user-permission-container {
  padding: 20px;
}

.user-permission-container :deep(.el-card__header) {
  padding: 15px 20px;
}

.user-permission-container :deep(.el-tabs--border-card) {
  border: none;
  box-shadow: none;
}

.user-permission-container :deep(.el-tabs__header) {
  background-color: #f5f7fa;
  border-bottom: 1px solid #e4e7ed;
}

.user-permission-container :deep(.el-tabs__content) {
  padding: 0;
}

/* 移除子组件的外层card，避免重复嵌套 */
.user-permission-container :deep(.user-list),
.user-permission-container :deep(.role-list) {
  padding: 0;
}

.user-permission-container :deep(.user-list .el-card),
.user-permission-container :deep(.role-list .el-card) {
  border: none;
  box-shadow: none;
}

.user-permission-container :deep(.user-list .el-card__header),
.user-permission-container :deep(.role-list .el-card__header) {
  display: none;
}

.user-permission-container :deep(.user-list .el-card__body),
.user-permission-container :deep(.role-list .el-card__body) {
  padding: 20px 0 0 0;
}
</style>

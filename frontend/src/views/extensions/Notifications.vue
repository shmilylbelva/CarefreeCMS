<template>
  <div class="notifications-container">
    <el-card>
      <template #header>
        <span>消息通知管理</span>
      </template>

      <el-tabs v-model="activeTab">
        <!-- 消息模板 -->
        <el-tab-pane label="消息模板" name="templates">
          <el-button type="primary" @click="showCreateDialog" style="margin-bottom: 15px">
            新增模板
          </el-button>

          <el-table :data="templates" v-loading="loading">
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="name" label="模板名称" width="150" />
            <el-table-column prop="code" label="模板代码" width="150" />
            <el-table-column prop="type" label="类型" width="120" />
            <el-table-column prop="title" label="标题" />
            <el-table-column label="发送渠道" width="150">
              <template #default="{ row }">
                {{ formatChannels(row.channels) }}
              </template>
            </el-table-column>
            <el-table-column prop="status" label="状态" width="100">
              <template #default="{ row }">
                <el-tag :type="row.status ? 'success' : 'danger'">
                  {{ row.status ? '启用' : '禁用' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="150">
              <template #default="{ row }">
                <el-button size="small" @click="editTemplate(row)">编辑</el-button>
                <el-button size="small" type="danger" @click="deleteTemplate(row.id)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-tab-pane>

        <!-- 通知记录 -->
        <el-tab-pane label="通知记录" name="notifications">
          <el-form :inline="true" :model="notificationSearchForm">
            <el-form-item label="关键词">
              <el-input v-model="notificationSearchForm.keyword" placeholder="标题或内容" clearable />
            </el-form-item>
            <el-form-item label="类型">
              <el-select v-model="notificationSearchForm.type" placeholder="全部" clearable>
                <el-option label="系统通知" value="system" />
                <el-option label="评论回复" value="reply" />
                <el-option label="点赞通知" value="like" />
                <el-option label="关注通知" value="follow" />
              </el-select>
            </el-form-item>
            <el-form-item label="状态">
              <el-select v-model="notificationSearchForm.is_read" placeholder="全部" clearable>
                <el-option label="未读" :value="0" />
                <el-option label="已读" :value="1" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="loadNotifications">搜索</el-button>
            </el-form-item>
          </el-form>

          <el-table :data="notifications" v-loading="loading">
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column label="接收用户" width="150">
              <template #default="{ row }">
                {{ row.user?.nickname || '-' }}
              </template>
            </el-table-column>
            <el-table-column prop="type" label="类型" width="120" />
            <el-table-column prop="title" label="标题" width="200" />
            <el-table-column prop="content" label="内容" show-overflow-tooltip />
            <el-table-column prop="is_read" label="状态" width="100">
              <template #default="{ row }">
                <el-tag :type="row.is_read ? 'success' : 'info'">
                  {{ row.is_read ? '已读' : '未读' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="create_time" label="发送时间" width="160" />
          </el-table>

          <div class="pagination">
            <el-pagination
              v-model:current-page="notificationPagination.page"
              v-model:page-size="notificationPagination.limit"
              :total="notificationPagination.total"
              layout="total, prev, pager, next"
              @current-change="loadNotifications"
            />
          </div>
        </el-tab-pane>

        <!-- 发送系统通知 -->
        <el-tab-pane label="发送系统通知" name="send">
          <el-form :model="notifyForm" label-width="100px" style="max-width: 600px">
            <el-form-item label="通知标题">
              <el-input v-model="notifyForm.title" />
            </el-form-item>
            <el-form-item label="通知内容">
              <el-input v-model="notifyForm.content" type="textarea" :rows="5" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="sendSystemNotification">发送给所有用户</el-button>
            </el-form-item>
          </el-form>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <!-- 模板编辑对话框 -->
    <el-dialog v-model="templateDialog.visible" :title="templateDialog.isEdit ? '编辑模板' : '新增模板'" width="600px">
      <el-form :model="templateDialog.form" label-width="100px">
        <el-form-item label="模板名称">
          <el-input v-model="templateDialog.form.name" placeholder="请输入模板名称" />
        </el-form-item>
        <el-form-item label="模板代码">
          <el-input v-model="templateDialog.form.code" placeholder="英文标识，如: user_register" />
        </el-form-item>
        <el-form-item label="类型">
          <el-select v-model="templateDialog.form.type" placeholder="请选择类型">
            <el-option label="系统通知" value="system" />
            <el-option label="评论回复" value="reply" />
            <el-option label="点赞通知" value="like" />
            <el-option label="关注通知" value="follow" />
          </el-select>
        </el-form-item>
        <el-form-item label="标题">
          <el-input v-model="templateDialog.form.title" placeholder="支持变量，如: {username}" />
        </el-form-item>
        <el-form-item label="内容">
          <el-input v-model="templateDialog.form.content" type="textarea" :rows="4" placeholder="支持变量，如: {content}" />
        </el-form-item>
        <el-form-item label="发送渠道">
          <div>
            <el-checkbox
              :model-value="templateDialog.form.channelsArray.includes('site')"
              @change="(val) => handleChannelChange('site', val)"
            >站内信</el-checkbox>
            <el-checkbox
              :model-value="templateDialog.form.channelsArray.includes('email')"
              @change="(val) => handleChannelChange('email', val)"
            >邮件</el-checkbox>
            <el-checkbox
              :model-value="templateDialog.form.channelsArray.includes('sms')"
              @change="(val) => handleChannelChange('sms', val)"
            >短信</el-checkbox>
          </div>
        </el-form-item>
        <el-form-item label="状态">
          <el-radio-group v-model="templateDialog.form.status">
            <el-radio :label="1">启用</el-radio>
            <el-radio :label="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="templateDialog.visible = false">取消</el-button>
        <el-button type="primary" @click="saveTemplate">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'

const activeTab = ref('templates')
const loading = ref(false)
const templates = ref([])
const notifications = ref([])

const notifyForm = reactive({
  title: '',
  content: ''
})

const notificationSearchForm = reactive({
  keyword: '',
  type: '',
  is_read: ''
})

const notificationPagination = reactive({
  page: 1,
  limit: 20,
  total: 0
})

const templateDialog = reactive({
  visible: false,
  isEdit: false,
  form: {
    id: null,
    name: '',
    code: '',
    type: 'system',
    title: '',
    content: '',
    channelsArray: ['site'],
    status: 1
  }
})

// 格式化渠道显示
const formatChannels = (channels) => {
  if (!channels) return '-'

  const channelMap = {
    'site': '站内信',
    'email': '邮件',
    'sms': '短信'
  }

  // 如果是字符串，分割后映射
  if (typeof channels === 'string') {
    return channels.split(',')
      .map(c => channelMap[c.trim()] || c.trim())
      .join(', ')
  }

  // 如果是数组，直接映射
  if (Array.isArray(channels)) {
    return channels
      .map(c => channelMap[c] || c)
      .join(', ')
  }

  return channels
}

const loadTemplates = async () => {
  loading.value = true
  try {
    const response = await request.get('notification-manage/template-index')
    if (response.code === 200) {
      // 确保 templates 是数组
      if (Array.isArray(response.data)) {
        templates.value = response.data
      } else if (response.data && Array.isArray(response.data.data)) {
        templates.value = response.data.data
      } else if (response.data && Array.isArray(response.data.list)) {
        templates.value = response.data.list
      } else {
        console.error('响应数据格式错误，不是数组:', response.data)
        templates.value = []
      }
    }
  } catch (error) {
    console.error('加载模板失败:', error)
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

const loadNotifications = async () => {
  loading.value = true
  try {
    const params = {
      page: notificationPagination.page,
      limit: notificationPagination.limit,
      ...notificationSearchForm
    }
    const response = await request.get('notification-manage/notification-index', { params })
    if (response.code === 200) {
      notifications.value = response.data.data || []
      notificationPagination.total = response.data.total || 0
    }
  } catch (error) {
    ElMessage.error('加载通知记录失败')
  } finally {
    loading.value = false
  }
}

const sendSystemNotification = async () => {
  if (!notifyForm.title || !notifyForm.content) {
    ElMessage.warning('请填写完整信息')
    return
  }

  try {
    await ElMessageBox.confirm('确定要向所有用户发送系统通知吗？', '确认', {
      type: 'warning'
    })

    const response = await request.post('notification-manage/send-system-notification', notifyForm)

    if (response.code === 200) {
      ElMessage.success('发送成功')
      notifyForm.title = ''
      notifyForm.content = ''
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('发送失败')
    }
  }
}

// 处理渠道复选框变化
const handleChannelChange = (channel, checked) => {
  const channels = templateDialog.form.channelsArray
  if (checked) {
    if (!channels.includes(channel)) {
      channels.push(channel)
    }
  } else {
    const index = channels.indexOf(channel)
    if (index > -1) {
      channels.splice(index, 1)
    }
  }
}

const showCreateDialog = () => {
  templateDialog.isEdit = false
  Object.assign(templateDialog.form, {
    id: null,
    name: '',
    code: '',
    type: 'system',
    title: '',
    content: '',
    channelsArray: ['site'],
    status: 1
  })
  templateDialog.visible = true
}

const editTemplate = (row) => {
  templateDialog.isEdit = true

  // 处理 channels 数据，确保是数组
  let channelsArray = ['site'] // 默认值
  if (row.channels_array && Array.isArray(row.channels_array)) {
    channelsArray = [...row.channels_array]
  } else if (row.channels && typeof row.channels === 'string') {
    channelsArray = row.channels.split(',').map(c => c.trim()).filter(c => c)
  }

  // 确保 channelsArray 不为空
  if (!channelsArray || channelsArray.length === 0) {
    channelsArray = ['site']
  }

  Object.assign(templateDialog.form, {
    id: row.id,
    name: row.name || '',
    code: row.code || '',
    type: row.type || 'system',
    title: row.title || '',
    content: row.content || '',
    channelsArray: channelsArray,
    status: row.status ?? 1
  })
  templateDialog.visible = true
}

const saveTemplate = async () => {
  try {
    const data = {
      ...templateDialog.form,
      channels: templateDialog.form.channelsArray.join(',')
    }
    delete data.channelsArray

    let response
    if (templateDialog.isEdit) {
      response = await request.put(`notification-manage/template-update/${data.id}`, data)
    } else {
      response = await request.post('notification-manage/template-create', data)
    }

    if (response.code === 200) {
      ElMessage.success(templateDialog.isEdit ? '更新成功' : '创建成功')
      templateDialog.visible = false
      loadTemplates()
    }
  } catch (error) {
    ElMessage.error('操作失败')
  }
}

const deleteTemplate = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除该模板吗？', '确认', { type: 'warning' })
    const response = await request.delete(`/notification-manage/template-delete/${id}`)
    if (response.data.code === 200) {
      ElMessage.success('删除成功')
      loadTemplates()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

// 监听tab切换，自动加载对应数据
watch(activeTab, (newTab) => {
  if (newTab === 'notifications') {
    loadNotifications()
  } else if (newTab === 'templates') {
    loadTemplates()
  }
})

onMounted(() => {
  loadTemplates()
})
</script>

<style scoped>
.notifications-container {
  padding: 20px;
}

.el-checkbox {
  margin-right: 15px;
}
</style>

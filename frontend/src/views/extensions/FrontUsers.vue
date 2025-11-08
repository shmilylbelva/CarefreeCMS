<template>
  <div class="front-users-container">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span>会员列表</span>
        </div>
      </template>

      <!-- 搜索筛选 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="关键词">
          <el-input v-model="searchForm.keyword" placeholder="用户名/昵称/邮箱/手机" clearable />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="全部" clearable>
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="等级">
          <el-input v-model="searchForm.level" placeholder="等级" clearable style="width: 100px" />
        </el-form-item>
        <el-form-item label="VIP">
          <el-select v-model="searchForm.is_vip" placeholder="全部" clearable>
            <el-option label="是" :value="1" />
            <el-option label="否" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadData">搜索</el-button>
          <el-button @click="resetSearch">重置</el-button>
          <el-button type="success" @click="createUser">新增用户</el-button>
        </el-form-item>
      </el-form>

      <!-- 统计信息 -->
      <div class="stats-bar" v-if="stats">
        <el-tag>总用户: {{ stats.total }}</el-tag>
        <el-tag type="success">启用: {{ stats.active }}</el-tag>
        <el-tag type="danger">禁用: {{ stats.disabled }}</el-tag>
        <el-tag type="warning">VIP: {{ stats.vip }}</el-tag>
        <el-tag type="info">今日新增: {{ stats.today_new }}</el-tag>
      </div>

      <!-- 用户列表 -->
      <el-table :data="users" style="width: 100%" v-loading="loading">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="用户名" width="120" />
        <el-table-column prop="nickname" label="昵称" width="120" />
        <el-table-column prop="email" label="邮箱" width="180" />
        <el-table-column prop="phone" label="手机" width="120" />
        <el-table-column prop="points" label="积分" width="100">
          <template #default="{ row }">
            <el-tag type="warning">{{ row.points }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="level" label="等级" width="80">
          <template #default="{ row }">
            <el-tag>LV{{ row.level }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="is_vip" label="VIP" width="80">
          <template #default="{ row }">
            <el-tag v-if="row.is_vip" type="warning">VIP</el-tag>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column prop="vip_expire_time" label="VIP到期时间" width="160">
          <template #default="{ row }">
            <span v-if="row.is_vip && row.vip_expire_time">{{ row.vip_expire_time }}</span>
            <el-tag v-else-if="row.is_vip && !row.vip_expire_time" type="success">永久</el-tag>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status ? 'success' : 'danger'">
              {{ row.status ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="注册时间" width="160" />
        <el-table-column label="操作" width="350" fixed="right">
          <template #default="{ row }">
            <el-button size="small" @click="adjustPoints(row)">调整积分</el-button>
            <el-button size="small" @click="setLevel(row)">设置等级</el-button>
            <el-button size="small" @click="setVip(row)" type="warning">设置VIP</el-button>
            <el-button size="small" @click="toggleStatus(row)" :type="row.status ? 'danger' : 'success'">
              {{ row.status ? '禁用' : '启用' }}
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="pagination">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.limit"
          :page-sizes="[10, 20, 50, 100]"
          :total="pagination.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="loadData"
          @current-change="loadData"
        />
      </div>
    </el-card>

    <!-- 调整积分对话框 -->
    <el-dialog v-model="pointsDialog.visible" title="调整积分" width="400px">
      <el-form :model="pointsDialog.form" label-width="80px">
        <el-form-item label="用户">
          <el-input v-model="pointsDialog.user.username" disabled />
        </el-form-item>
        <el-form-item label="当前积分">
          <el-input v-model="pointsDialog.user.points" disabled />
        </el-form-item>
        <el-form-item label="调整积分">
          <el-input-number v-model="pointsDialog.form.points" :step="10" />
          <div style="color: #999; font-size: 12px; margin-top: 5px;">
            正数为增加，负数为扣除
          </div>
        </el-form-item>
        <el-form-item label="说明">
          <el-input v-model="pointsDialog.form.description" type="textarea" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="pointsDialog.visible = false">取消</el-button>
        <el-button type="primary" @click="savePoints">确定</el-button>
      </template>
    </el-dialog>

    <!-- 设置等级对话框 -->
    <el-dialog v-model="levelDialog.visible" title="设置等级" width="400px">
      <el-form :model="levelDialog.form" label-width="80px">
        <el-form-item label="用户">
          <el-input v-model="levelDialog.user.username" disabled />
        </el-form-item>
        <el-form-item label="当前等级">
          <el-input v-model="levelDialog.user.level" disabled />
        </el-form-item>
        <el-form-item label="新等级">
          <el-input-number v-model="levelDialog.form.level" :min="0" :max="10" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="levelDialog.visible = false">取消</el-button>
        <el-button type="primary" @click="saveLevel">确定</el-button>
      </template>
    </el-dialog>

    <!-- 设置VIP对话框 -->
    <el-dialog v-model="vipDialog.visible" title="设置VIP" width="400px">
      <el-form :model="vipDialog.form" label-width="100px">
        <el-form-item label="用户">
          <el-input v-model="vipDialog.user.username" disabled />
        </el-form-item>
        <el-form-item label="当前状态">
          <el-tag v-if="vipDialog.user.is_vip" type="warning">VIP会员</el-tag>
          <el-tag v-else type="info">普通用户</el-tag>
          <div v-if="vipDialog.user.is_vip && vipDialog.user.vip_expire_time" style="color: #999; font-size: 12px; margin-top: 5px;">
            到期时间: {{ vipDialog.user.vip_expire_time }}
          </div>
        </el-form-item>
        <el-form-item label="VIP状态">
          <el-radio-group v-model="vipDialog.form.is_vip">
            <el-radio :label="1">开通VIP</el-radio>
            <el-radio :label="0">取消VIP</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="有效期(天)" v-if="vipDialog.form.is_vip === 1">
          <el-input-number v-model="vipDialog.form.days" :min="1" :max="3650" />
          <div style="color: #999; font-size: 12px; margin-top: 5px;">
            设置VIP的有效天数
          </div>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="vipDialog.visible = false">取消</el-button>
        <el-button type="primary" @click="saveVip">确定</el-button>
      </template>
    </el-dialog>

    <!-- 创建用户对话框 -->
    <el-dialog v-model="createDialog.visible" title="新增用户" width="500px">
      <el-form :model="createDialog.form" :rules="createRules" ref="createFormRef" label-width="100px">
        <el-form-item label="用户名" prop="username">
          <el-input v-model="createDialog.form.username" placeholder="登录用户名" />
        </el-form-item>
        <el-form-item label="密码" prop="password">
          <el-input v-model="createDialog.form.password" type="password" placeholder="登录密码" show-password />
        </el-form-item>
        <el-form-item label="昵称" prop="nickname">
          <el-input v-model="createDialog.form.nickname" placeholder="昵称（可选，默认同用户名）" />
        </el-form-item>
        <el-form-item label="真实姓名">
          <el-input v-model="createDialog.form.real_name" placeholder="真实姓名（可选）" />
        </el-form-item>
        <el-form-item label="邮箱" prop="email">
          <el-input v-model="createDialog.form.email" placeholder="邮箱地址（可选）" />
        </el-form-item>
        <el-form-item label="手机号" prop="phone">
          <el-input v-model="createDialog.form.phone" placeholder="手机号（可选）" />
        </el-form-item>
        <el-form-item label="等级">
          <el-input-number v-model="createDialog.form.level" :min="0" :max="10" />
        </el-form-item>
        <el-form-item label="初始积分">
          <el-input-number v-model="createDialog.form.points" :min="0" :step="10" />
        </el-form-item>
        <el-form-item label="状态">
          <el-radio-group v-model="createDialog.form.status">
            <el-radio :label="1">启用</el-radio>
            <el-radio :label="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="createDialog.visible = false">取消</el-button>
        <el-button type="primary" @click="saveNewUser">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'

// 搜索表单
const searchForm = reactive({
  keyword: '',
  status: '',
  level: '',
  is_vip: ''
})

// 分页
const pagination = reactive({
  page: 1,
  limit: 20,
  total: 0
})

// 数据
const users = ref([])
const stats = ref(null)
const loading = ref(false)

// 对话框
const pointsDialog = reactive({
  visible: false,
  user: {},
  form: {
    points: 0,
    description: ''
  }
})

const levelDialog = reactive({
  visible: false,
  user: {},
  form: {
    level: 1
  }
})

const vipDialog = reactive({
  visible: false,
  user: {},
  form: {
    is_vip: 0,
    days: 30
  }
})

const createDialog = reactive({
  visible: false,
  form: {
    username: '',
    password: '',
    nickname: '',
    real_name: '',
    email: '',
    phone: '',
    level: 0,
    points: 0,
    status: 1
  }
})

const createFormRef = ref(null)

const createRules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' },
    { min: 3, max: 20, message: '用户名长度应在3-20个字符', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 6, message: '密码长度至少6个字符', trigger: 'blur' }
  ],
  email: [
    { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
  ]
}

// 加载数据
const loadData = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      limit: pagination.limit,
      ...searchForm
    }

    const response = await request.get('/front-user-manage/index', { params })

    if (response.code === 200) {
      users.value = response.data.data
      pagination.total = response.data.total
    }
  } catch (error) {
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

// 加载统计
const loadStats = async () => {
  try {
    const response = await request.get('/front-user-manage/statistics')
    if (response.code === 200) {
      stats.value = response.data
    }
  } catch (error) {
    console.error('加载统计失败', error)
  }
}

// 重置搜索
const resetSearch = () => {
  Object.assign(searchForm, {
    keyword: '',
    status: '',
    level: '',
    is_vip: ''
  })
  loadData()
}

// 调整积分
const adjustPoints = (user) => {
  pointsDialog.user = { ...user }
  pointsDialog.form.points = 0
  pointsDialog.form.description = ''
  pointsDialog.visible = true
}

const savePoints = async () => {
  try {
    const response = await request.post(
      `/front-user-manage/adjust-points/${pointsDialog.user.id}`,
      pointsDialog.form
    )

    if (response.code === 200) {
      ElMessage.success('积分调整成功')
      pointsDialog.visible = false
      loadData()
    }
  } catch (error) {
    // 响应拦截器已经显示了错误提示
  }
}

// 设置等级
const setLevel = (user) => {
  levelDialog.user = { ...user }
  levelDialog.form.level = user.level
  levelDialog.visible = true
}

const saveLevel = async () => {
  try {
    const response = await request.post(
      `/front-user-manage/set-level/${levelDialog.user.id}`,
      levelDialog.form
    )

    if (response.code === 200) {
      ElMessage.success('等级设置成功')
      levelDialog.visible = false
      loadData()
    }
  } catch (error) {
    // 响应拦截器已经显示了错误提示
  }
}

// 设置VIP
const setVip = (user) => {
  vipDialog.user = { ...user }
  vipDialog.form.is_vip = user.is_vip ? 1 : 0
  vipDialog.form.days = 30
  vipDialog.visible = true
}

const saveVip = async () => {
  try {
    const response = await request.post(
      `/front-user-manage/set-vip/${vipDialog.user.id}`,
      vipDialog.form
    )

    if (response.code === 200) {
      ElMessage.success('VIP设置成功')
      vipDialog.visible = false
      loadData()
    }
  } catch (error) {
    // 响应拦截器已经显示了错误提示
  }
}

// 切换状态
const toggleStatus = async (user) => {
  try {
    await ElMessageBox.confirm(
      `确定要${user.status ? '禁用' : '启用'}该用户吗？`,
      '确认',
      { type: 'warning' }
    )

    const response = await request.post(
      `/front-user-manage/change-status/${user.id}`,
      { status: user.status ? 0 : 1 }
    )

    if (response.code === 200) {
      ElMessage.success('操作成功')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      // 响应拦截器已经显示了错误提示
    }
  }
}

// 创建用户
const createUser = () => {
  // 重置表单
  Object.assign(createDialog.form, {
    username: '',
    password: '',
    nickname: '',
    real_name: '',
    email: '',
    phone: '',
    level: 0,
    points: 0,
    status: 1
  })
  createDialog.visible = true
}

// 保存新用户
const saveNewUser = async () => {
  if (!createFormRef.value) return

  try {
    await createFormRef.value.validate()

    const response = await request.post('/front-user-manage/create', createDialog.form)

    // 响应拦截器已经处理了code !== 200的情况，这里只处理成功的情况
    if (response.code === 200) {
      ElMessage.success('用户创建成功')
      createDialog.visible = false
      loadData()
      loadStats()
    }
  } catch (error) {
    // 表单验证错误，静默处理
    if (error instanceof Error && error.message) {
      // 响应拦截器已经显示了错误提示，这里不再重复显示
      return
    }
  }
}

onMounted(() => {
  loadData()
  loadStats()
})
</script>

<style scoped>
.front-users-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.search-form {
  margin-bottom: 20px;
}

.stats-bar {
  margin-bottom: 20px;
  display: flex;
  gap: 10px;
}

.pagination {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
</style>

<template>
  <div class="slider-container">
    <el-tabs v-model="activeTab" @tab-change="handleTabChange">
      <!-- 幻灯片管理 Tab -->
      <el-tab-pane label="幻灯片管理" name="sliders">
        <div class="toolbar">
          <el-form :inline="true" :model="sliderQuery">
            <el-form-item label="所属站点">
              <el-select v-model="sliderQuery.site_id" placeholder="选择站点" clearable style="width: 200px">
                <el-option label="全部站点" :value="null" />
                <el-option v-for="site in siteOptions" :key="site.id" :label="site.name" :value="site.id" />
              </el-select>
            </el-form-item>
            <el-form-item label="分组">
              <el-select v-model="sliderQuery.group_id" placeholder="全部分组" clearable style="width: 200px">
                <el-option v-for="group in groups" :key="group.id" :label="group.name" :value="group.id" />
              </el-select>
            </el-form-item>
            <el-form-item label="状态">
              <el-select v-model="sliderQuery.status" placeholder="全部状态" clearable style="width: 120px">
                <el-option label="禁用" :value="0" />
                <el-option label="启用" :value="1" />
              </el-select>
            </el-form-item>
            <el-form-item label="关键词">
              <el-input v-model="sliderQuery.keyword" placeholder="标题/描述" clearable style="width: 200px" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="loadSliders">查询</el-button>
              <el-button @click="resetSliderQuery">重置</el-button>
            </el-form-item>
          </el-form>
          <div>
            <el-button type="primary" @click="handleAddSlider">添加幻灯片</el-button>
          </div>
        </div>

        <el-table :data="sliders" border stripe v-loading="loading">
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column label="图片" width="120">
            <template #default="{ row }">
              <el-image
                v-if="row.image"
                :src="row.image"
                :preview-src-list="[row.image]"
                fit="cover"
                style="width: 80px; height: 50px; border-radius: 4px"
              />
            </template>
          </el-table-column>
          <el-table-column prop="title" label="标题" min-width="150" />
          <el-table-column label="分组" width="120">
            <template #default="{ row }">
              {{ row.group?.name || '-' }}
            </template>
          </el-table-column>
          <el-table-column prop="link_url" label="链接地址" min-width="180" show-overflow-tooltip />
          <el-table-column prop="sort" label="排序" width="80" />
          <el-table-column label="状态" width="80">
            <template #default="{ row }">
              <el-tag :type="row.status === 1 ? 'success' : 'info'" size="small">
                {{ row.status === 1 ? '启用' : '禁用' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="统计" width="150">
            <template #default="{ row }">
              <div style="font-size: 12px">
                <div>展示: {{ row.view_count }}</div>
                <div>点击: {{ row.click_count }}</div>
                <div v-if="row.view_count > 0">
                  点击率: {{ ((row.click_count / row.view_count) * 100).toFixed(2) }}%
                </div>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="时间范围" width="180">
            <template #default="{ row }">
              <div style="font-size: 12px">
                <div v-if="row.start_time">开始: {{ row.start_time }}</div>
                <div v-if="row.end_time">结束: {{ row.end_time }}</div>
                <div v-if="!row.start_time && !row.end_time">永久有效</div>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="150" fixed="right">
            <template #default="{ row }">
              <el-button link type="primary" size="small" @click="handleEditSlider(row)">编辑</el-button>
              <el-button link type="danger" size="small" @click="handleDeleteSlider(row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>

        <el-pagination
          v-model:current-page="sliderQuery.page"
          v-model:page-size="sliderQuery.per_page"
          :total="sliderTotal"
          :page-sizes="[10, 15, 20, 50]"
          layout="total, sizes, prev, pager, next, jumper"
          @current-change="loadSliders"
          @size-change="loadSliders"
        />
      </el-tab-pane>

      <!-- 分组管理 Tab -->
      <el-tab-pane label="分组管理" name="groups">
        <div class="toolbar">
          <el-form :inline="true" :model="groupQuery">
            <el-form-item label="所属站点">
              <el-select v-model="groupQuery.site_id" placeholder="选择站点" clearable style="width: 200px">
                <el-option label="全部站点" :value="null" />
                <el-option v-for="site in siteOptions" :key="site.id" :label="site.name" :value="site.id" />
              </el-select>
            </el-form-item>
            <el-form-item label="关键词">
              <el-input v-model="groupQuery.keyword" placeholder="分组名称/代码" clearable style="width: 200px" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="loadGroups">查询</el-button>
              <el-button @click="resetGroupQuery">重置</el-button>
            </el-form-item>
          </el-form>
          <div>
            <el-button type="primary" @click="handleAddGroup">添加分组</el-button>
          </div>
        </div>

        <el-table :data="groupList" border stripe v-loading="loading">
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="name" label="分组名称" width="150" />
          <el-table-column prop="code" label="分组代码" width="150" />
          <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
          <el-table-column label="图片尺寸" width="120">
            <template #default="{ row }">
              {{ row.width }}x{{ row.height }}
            </template>
          </el-table-column>
          <el-table-column label="自动播放" width="100">
            <template #default="{ row }">
              <el-tag :type="row.auto_play ? 'success' : 'info'" size="small">
                {{ row.auto_play ? '是' : '否' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="播放间隔" width="100">
            <template #default="{ row }">
              {{ row.play_interval }}ms
            </template>
          </el-table-column>
          <el-table-column label="动画效果" width="100">
            <template #default="{ row }">
              {{ row.animation === 'slide' ? '滑动' : '淡入淡出' }}
            </template>
          </el-table-column>
          <el-table-column label="状态" width="80">
            <template #default="{ row }">
              <el-tag :type="row.status === 1 ? 'success' : 'info'" size="small">
                {{ row.status === 1 ? '启用' : '禁用' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="200" fixed="right">
            <template #default="{ row }">
              <el-button link type="success" size="small" @click="handleShowGroupCode(row)">代码</el-button>
              <el-button link type="primary" size="small" @click="handleEditGroup(row)">编辑</el-button>
              <el-button link type="danger" size="small" @click="handleDeleteGroup(row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>

        <el-pagination
          v-model:current-page="groupQuery.page"
          v-model:page-size="groupQuery.per_page"
          :total="groupTotal"
          :page-sizes="[10, 15, 20, 50]"
          layout="total, sizes, prev, pager, next, jumper"
          @current-change="loadGroups"
          @size-change="loadGroups"
        />
      </el-tab-pane>
    </el-tabs>

    <!-- 幻灯片表单对话框 -->
    <el-dialog
      v-model="sliderDialogVisible"
      :title="sliderFormMode === 'add' ? '添加幻灯片' : '编辑幻灯片'"
      width="700px"
    >
      <el-form :model="sliderForm" :rules="sliderRules" ref="sliderFormRef" label-width="100px">
        <el-form-item label="所属站点" prop="site_id">
          <el-select v-model="sliderForm.site_id" placeholder="请选择站点" style="width: 100%;">
            <el-option v-for="site in siteOptions" :key="site.id" :label="site.name" :value="site.id" />
          </el-select>
        </el-form-item>

        <el-form-item label="分组" prop="group_id">
          <el-select v-model="sliderForm.group_id" placeholder="请选择分组" style="width: 100%">
            <el-option v-for="group in groups" :key="group.id" :label="group.name" :value="group.id" />
          </el-select>
        </el-form-item>

        <el-form-item label="标题" prop="title">
          <el-input v-model="sliderForm.title" placeholder="请输入标题" />
        </el-form-item>

        <el-form-item label="图片" prop="image">
          <el-upload
            :action="uploadUrl"
            :headers="uploadHeaders"
            :on-success="handleSliderImageSuccess"
            :on-error="handleSliderImageError"
            :before-upload="beforeImageUpload"
            :show-file-list="false"
            accept="image/*"
          >
            <img v-if="sliderForm.image" :src="sliderForm.image" class="upload-image" />
            <el-icon v-else class="upload-icon"><Plus /></el-icon>
          </el-upload>
          <div class="form-tip">建议尺寸根据分组设置</div>
        </el-form-item>

        <el-form-item label="链接地址">
          <el-input v-model="sliderForm.link_url" placeholder="请输入链接地址" />
        </el-form-item>

        <el-form-item label="打开方式">
          <el-radio-group v-model="sliderForm.link_target">
            <el-radio label="_blank">新窗口</el-radio>
            <el-radio label="_self">当前窗口</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="描述">
          <el-input v-model="sliderForm.description" type="textarea" :rows="3" placeholder="请输入描述" />
        </el-form-item>

        <el-form-item label="按钮文字">
          <el-input v-model="sliderForm.button_text" placeholder="如：了解更多" />
        </el-form-item>

        <el-form-item label="排序">
          <el-input-number v-model="sliderForm.sort" :min="0" />
          <span class="form-tip">数字越小越靠前</span>
        </el-form-item>

        <el-form-item label="状态">
          <el-radio-group v-model="sliderForm.status">
            <el-radio :label="0">禁用</el-radio>
            <el-radio :label="1">启用</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="开始时间">
          <el-date-picker
            v-model="sliderForm.start_time"
            type="datetime"
            placeholder="选择开始时间"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 100%"
          />
        </el-form-item>

        <el-form-item label="结束时间">
          <el-date-picker
            v-model="sliderForm.end_time"
            type="datetime"
            placeholder="选择结束时间"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="sliderDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="submitSliderForm" :loading="submitting">保存</el-button>
      </template>
    </el-dialog>

    <!-- 分组表单对话框 -->
    <el-dialog
      v-model="groupDialogVisible"
      :title="groupFormMode === 'add' ? '添加分组' : '编辑分组'"
      width="600px"
    >
      <el-form :model="groupForm" :rules="groupRules" ref="groupFormRef" label-width="100px">
        <el-form-item label="所属站点" prop="site_id">
          <el-select v-model="groupForm.site_id" placeholder="请选择站点" style="width: 100%;">
            <el-option v-for="site in siteOptions" :key="site.id" :label="site.name" :value="site.id" />
          </el-select>
        </el-form-item>

        <el-form-item label="分组名称" prop="name">
          <el-input v-model="groupForm.name" placeholder="请输入分组名称" />
        </el-form-item>

        <el-form-item label="分组代码" prop="code">
          <el-input v-model="groupForm.code" placeholder="如：home_slider" />
          <div class="form-tip">唯一标识，用于前台调用</div>
        </el-form-item>

        <el-form-item label="描述">
          <el-input v-model="groupForm.description" type="textarea" :rows="3" placeholder="请输入描述" />
        </el-form-item>

        <el-form-item label="图片宽度">
          <el-input-number v-model="groupForm.width" :min="1" placeholder="像素" />
          <span class="form-tip" style="margin-left: 10px">像素</span>
        </el-form-item>

        <el-form-item label="图片高度">
          <el-input-number v-model="groupForm.height" :min="1" placeholder="像素" />
          <span class="form-tip" style="margin-left: 10px">像素</span>
        </el-form-item>

        <el-form-item label="自动播放">
          <el-switch v-model="groupForm.auto_play" :active-value="1" :inactive-value="0" />
        </el-form-item>

        <el-form-item label="播放间隔">
          <el-input-number v-model="groupForm.play_interval" :min="1000" :step="1000" />
          <span class="form-tip" style="margin-left: 10px">毫秒</span>
        </el-form-item>

        <el-form-item label="动画效果">
          <el-radio-group v-model="groupForm.animation">
            <el-radio label="slide">滑动</el-radio>
            <el-radio label="fade">淡入淡出</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="状态">
          <el-radio-group v-model="groupForm.status">
            <el-radio :label="0">禁用</el-radio>
            <el-radio :label="1">启用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="groupDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="submitGroupForm" :loading="submitting">保存</el-button>
      </template>
    </el-dialog>

    <!-- 分组调用代码对话框 -->
    <el-dialog
      v-model="groupCodeDialogVisible"
      title="分组快捷调用代码"
      width="600px"
    >
      <div style="margin-bottom: 15px;">
        <el-alert
          title="使用说明"
          type="info"
          description="将以下代码复制到模板文件中，即可在前台展示此幻灯片分组。"
          show-icon
          :closable="false"
        />
      </div>

      <div style="margin-bottom: 10px;">
        <strong>Carefree 标签调用：</strong>
      </div>
      <el-input
        v-model="groupCallCode"
        type="textarea"
        :rows="8"
        readonly
      />

      <template #footer>
        <el-button @click="groupCodeDialogVisible = false">关闭</el-button>
        <el-button type="primary" @click="copyGroupCode">复制代码</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import {
  getSliderList,
  getSlider,
  createSlider,
  updateSlider,
  deleteSlider
} from '@/api/slider'
import {
  getSliderGroupList,
  getAllSliderGroups,
  getSliderGroup,
  createSliderGroup,
  updateSliderGroup,
  deleteSliderGroup
} from '@/api/sliderGroup'
import { getSiteOptions } from '@/api/site'
import { getToken } from '@/utils/auth'

const activeTab = ref('sliders')
const loading = ref(false)
const submitting = ref(false)
const siteOptions = ref([])

// 幻灯片相关
const sliders = ref([])
const sliderTotal = ref(0)
const sliderQuery = reactive({
  page: 1,
  per_page: 15,
  site_id: null,
  keyword: '',
  group_id: '',
  status: ''
})

const sliderDialogVisible = ref(false)
const sliderFormMode = ref('add')
const sliderForm = reactive({
  id: null,
  site_id: null,
  group_id: '',
  title: '',
  image: '',
  link_url: '',
  link_target: '_blank',
  description: '',
  button_text: '',
  sort: 0,
  status: 1,
  start_time: '',
  end_time: ''
})

const sliderRules = {
  site_id: [{ required: true, message: '请选择所属站点', trigger: 'change' }],
  group_id: [{ required: true, message: '请选择分组', trigger: 'change' }],
  title: [{ required: true, message: '请输入标题', trigger: 'blur' }],
  image: [{ required: true, message: '请上传图片', trigger: 'change' }]
}

const sliderFormRef = ref(null)

// 分组相关
const groups = ref([])
const groupList = ref([])
const groupTotal = ref(0)
const groupQuery = reactive({
  page: 1,
  per_page: 15,
  site_id: null,
  keyword: ''
})

const groupDialogVisible = ref(false)
const groupCodeDialogVisible = ref(false)
const groupCallCode = ref('')
const groupFormMode = ref('add')
const groupForm = reactive({
  id: null,
  site_id: null,
  name: '',
  code: '',
  description: '',
  width: 1920,
  height: 600,
  auto_play: 1,
  play_interval: 3000,
  animation: 'slide',
  status: 1
})

const groupRules = {
  site_id: [{ required: true, message: '请选择所属站点', trigger: 'change' }],
  name: [{ required: true, message: '请输入分组名称', trigger: 'blur' }],
  code: [{ required: true, message: '请输入分组代码', trigger: 'blur' }]
}

const groupFormRef = ref(null)

// 上传配置
const uploadUrl = ref(import.meta.env.VITE_API_BASE_URL + '/media/upload')
const uploadHeaders = ref({
  Authorization: 'Bearer ' + getToken()
})

// 加载幻灯片列表
const loadSliders = async () => {
  loading.value = true
  try {
    const res = await getSliderList(sliderQuery)
    sliders.value = res.data.data
    sliderTotal.value = res.data.total
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  } finally {
    loading.value = false
  }
}

// 加载分组列表
const loadGroups = async () => {
  loading.value = true
  try {
    const res = await getSliderGroupList(groupQuery)
    groupList.value = res.data.data
    groupTotal.value = res.data.total
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  } finally {
    loading.value = false
  }
}

// 加载所有分组（用于下拉选择）
const loadAllGroups = async () => {
  try {
    const res = await getAllSliderGroups({ status: 1 })
    groups.value = res.data
  } catch (error) {
    console.error('加载分组失败', error)
  }
}

// 重置幻灯片查询
const resetSliderQuery = () => {
  sliderQuery.site_id = null
  sliderQuery.keyword = ''
  sliderQuery.group_id = ''
  sliderQuery.status = ''
  sliderQuery.page = 1
  loadSliders()
}

// 重置分组查询
const resetGroupQuery = () => {
  groupQuery.site_id = null
  groupQuery.keyword = ''
  groupQuery.page = 1
  loadGroups()
}

// 添加幻灯片
const handleAddSlider = () => {
  console.log('===== 打开添加对话框 =====')
  sliderFormMode.value = 'add'
  Object.assign(sliderForm, {
    id: null,
    site_id: null,
    group_id: '',
    title: '',
    image: '',
    link_url: '',
    link_target: '_blank',
    description: '',
    button_text: '',
    sort: 0,
    status: 1,
    start_time: '',
    end_time: ''
  })
  console.log('初始化后的 sliderForm:', JSON.parse(JSON.stringify(sliderForm)))
  sliderDialogVisible.value = true
}

// 编辑幻灯片
const handleEditSlider = async (row) => {
  sliderFormMode.value = 'edit'
  try {
    const res = await getSlider(row.id)
    Object.assign(sliderForm, res.data)
    sliderDialogVisible.value = true
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  }
}

// 删除幻灯片
const handleDeleteSlider = (row) => {
  ElMessageBox.confirm('确定要删除这个幻灯片吗？', '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await deleteSlider(row.id)
      ElMessage.success('删除成功')
      loadSliders()
    } catch (error) {
      ElMessage.error(error.message || '删除失败')
    }
  })
}

// 提交幻灯片表单
const submitSliderForm = () => {
  console.log('===== 提交表单 =====')
  console.log('提交前的 sliderForm:', JSON.parse(JSON.stringify(sliderForm)))
  console.log('sliderFormRef 是否存在:', !!sliderFormRef.value)

  sliderFormRef.value.validate(async (valid) => {
    console.log('表单验证结果:', valid)

    if (!valid) {
      console.error('表单验证失败')
      // 获取验证错误信息
      sliderFormRef.value.fields.forEach(field => {
        if (field.validateState === 'error') {
          console.error(`字段 ${field.prop} 验证失败:`, field.validateMessage)
        }
      })
      return
    }

    submitting.value = true
    try {
      console.log('开始提交数据:', sliderFormMode.value)
      const data = { ...sliderForm }

      if (sliderFormMode.value === 'add') {
        const result = await createSlider(data)
        console.log('创建结果:', result)
        ElMessage.success('添加成功')
      } else {
        const result = await updateSlider(sliderForm.id, data)
        console.log('更新结果:', result)
        ElMessage.success('更新成功')
      }
      sliderDialogVisible.value = false
      loadSliders()
    } catch (error) {
      console.error('提交失败:', error)
      ElMessage.error(error.message || '操作失败')
    } finally {
      submitting.value = false
    }
  })
}

// 添加分组
const handleAddGroup = () => {
  groupFormMode.value = 'add'
  Object.assign(groupForm, {
    id: null,
    site_id: null,
    name: '',
    code: '',
    description: '',
    width: 1920,
    height: 600,
    auto_play: 1,
    play_interval: 3000,
    animation: 'slide',
    status: 1
  })
  groupDialogVisible.value = true
}

// 编辑分组
const handleEditGroup = async (row) => {
  groupFormMode.value = 'edit'
  try {
    const res = await getSliderGroup(row.id)
    Object.assign(groupForm, res.data)
    groupDialogVisible.value = true
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  }
}

// 显示分组调用代码
const handleShowGroupCode = (row) => {
  groupCallCode.value = `{carefree:slider group="${row.code}" limit="10" id="slider"}
<!-- 幻灯片容器 -->
<div class="slider-container">
  <div class="slider-wrapper">
    {volist name="slider" id="item"}
    <div class="slider-item">
      <a href="{$item.link_url}" target="{$item.link_target}">
        <img src="{$item.image}" alt="{$item.title}">
      </a>
      {if condition="$item.title || $item.description"}
      <div class="slider-caption">
        {if condition="$item.title"}
        <h3>{$item.title}</h3>
        {/if}
        {if condition="$item.description"}
        <p>{$item.description}</p>
        {/if}
        {if condition="$item.button_text"}
        <button>{$item.button_text}</button>
        {/if}
      </div>
      {/if}
    </div>
    {/volist}
  </div>
</div>
{/carefree:slider}`
  groupCodeDialogVisible.value = true
}

// 复制分组调用代码
const copyGroupCode = async () => {
  try {
    await navigator.clipboard.writeText(groupCallCode.value)
    ElMessage.success('代码已复制到剪贴板')
  } catch (error) {
    ElMessage.error('复制失败，请手动复制')
  }
}

// 删除分组
const handleDeleteGroup = (row) => {
  ElMessageBox.confirm('确定要删除这个分组吗？该分组下不能有幻灯片', '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await deleteSliderGroup(row.id)
      ElMessage.success('删除成功')
      loadGroups()
      loadAllGroups()
    } catch (error) {
      ElMessage.error(error.message || '删除失败')
    }
  })
}

// 提交分组表单
const submitGroupForm = () => {
  groupFormRef.value.validate(async (valid) => {
    if (!valid) return

    submitting.value = true
    try {
      const data = { ...groupForm }

      if (groupFormMode.value === 'add') {
        await createSliderGroup(data)
        ElMessage.success('添加成功')
      } else {
        await updateSliderGroup(groupForm.id, data)
        ElMessage.success('更新成功')
      }
      groupDialogVisible.value = false
      loadGroups()
      loadAllGroups()
    } catch (error) {
      ElMessage.error(error.message || '操作失败')
    } finally {
      submitting.value = false
    }
  })
}

// 处理图片上传成功
const handleSliderImageSuccess = (response) => {
  console.log('===== 图片上传回调 =====')
  console.log('响应数据:', response)

  if (response.code === 200) {
    sliderForm.image = response.data.file_url  // 修正：使用 file_url 而不是 url
    console.log('设置后的 sliderForm.image:', sliderForm.image)
    console.log('完整的 sliderForm:', JSON.parse(JSON.stringify(sliderForm)))

    // 手动触发表单验证，清除 image 字段的错误提示
    sliderFormRef.value?.clearValidate('image')
    console.log('已清除 image 字段验证')

    ElMessage.success('上传成功')
  } else {
    console.error('上传失败:', response.msg)
    ElMessage.error(response.msg || '上传失败')
  }
}

// 处理图片上传失败
const handleSliderImageError = (error) => {
  console.error('===== 图片上传错误 =====')
  console.error('错误信息:', error)
  ElMessage.error('上传失败，请重试')
}

// 上传前验证
const beforeImageUpload = (file) => {
  const isImage = file.type.startsWith('image/')
  const isLt5M = file.size / 1024 / 1024 < 5

  if (!isImage) {
    ElMessage.error('只能上传图片文件')
    return false
  }
  if (!isLt5M) {
    ElMessage.error('图片大小不能超过 5MB')
    return false
  }
  return true
}

// Tab 切换
const handleTabChange = (tab) => {
  if (tab === 'sliders') {
    loadSliders()
  } else if (tab === 'groups') {
    loadGroups()
  }
}

// 获取站点选项
const fetchSiteOptions = async () => {
  try {
    const res = await getSiteOptions()
    siteOptions.value = res.data || []
  } catch (error) {
    console.error('获取站点列表失败:', error)
  }
}

onMounted(() => {
  fetchSiteOptions()
  loadSliders()
  loadAllGroups()
})
</script>

<style scoped>
.slider-container {
  padding: 20px;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.el-pagination {
  margin-top: 20px;
  justify-content: center;
}

.upload-image {
  width: 200px;
  height: 120px;
  object-fit: cover;
  border-radius: 4px;
  cursor: pointer;
}

.upload-icon {
  width: 200px;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px dashed #d9d9d9;
  border-radius: 4px;
  cursor: pointer;
  font-size: 28px;
  color: #8c939d;
}

.upload-icon:hover {
  border-color: #409eff;
  color: #409eff;
}

.form-tip {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}
</style>

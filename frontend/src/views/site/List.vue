<template>
  <div class="site-list">
    <el-card>
      <template #header>
        <div class="header-actions">
          <h3>站点管理</h3>
          <el-button type="primary" @click="handleAdd">
            <el-icon><Plus /></el-icon>
            添加站点
          </el-button>
        </div>
      </template>

      <!-- 搜索栏 -->
      <div class="search-bar">
        <el-form :inline="true" :model="searchForm">
          <el-form-item label="站点名称">
            <el-input v-model="searchForm.site_name" placeholder="请输入站点名称" clearable />
          </el-form-item>
          <el-form-item label="站点代码">
            <el-input v-model="searchForm.site_code" placeholder="请输入站点代码" clearable />
          </el-form-item>
          <el-form-item label="状态">
            <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
              <el-option label="禁用" :value="0" />
              <el-option label="启用" :value="1" />
              <el-option label="维护中" :value="2" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch">搜索</el-button>
            <el-button @click="handleReset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>

      <!-- 站点列表 -->
      <el-table :data="siteList" v-loading="loading" border>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="site_code" label="站点代码" width="120" />
        <el-table-column prop="site_name" label="站点名称" min-width="150" />
        <el-table-column prop="site_type_text" label="类型" width="100" />
        <el-table-column label="网站网址" min-width="200">
          <template #default="{ row }">
            {{ row.site_url || row.full_domain || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="地域" width="150">
          <template #default="{ row }">
            {{ row.city || row.region_name || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="统计" width="180">
          <template #default="{ row }">
            <div>文章: {{ row.article_count || 0 }}</div>
            <div>用户: {{ row.user_count || 0 }}</div>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.status === 0" type="danger">禁用</el-tag>
            <el-tag v-else-if="row.status === 1" type="success">启用</el-tag>
            <el-tag v-else-if="row.status === 2" type="warning">维护中</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button size="small" @click="handleUpdateStats(row)" :loading="row.updating">更新统计</el-button>
            <el-button size="small" type="danger" @click="handleDelete(row.id)" :disabled="row.site_type === 1">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-if="total > 0"
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.limit"
        :total="total"
        :page-sizes="[15, 30, 50, 100]"
        layout="total, sizes, prev, pager, next"
        @size-change="fetchSites"
        @current-change="fetchSites"
        style="margin-top: 20px; justify-content: flex-end;"
      />
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑站点' : '添加站点'"
      width="700px"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="120px">
        <el-tabs v-model="activeTab">
          <el-tab-pane label="基本信息" name="basic">
            <el-form-item label="站点代码" prop="site_code">
              <el-input v-model="form.site_code" placeholder="请输入站点代码（字母、数字、下划线）" :disabled="isEdit" />
              <div class="form-tip">站点代码创建后不可修改</div>
            </el-form-item>

            <el-form-item label="站点名称" prop="site_name">
              <el-input v-model="form.site_name" placeholder="请输入站点名称" />
            </el-form-item>

            <el-form-item label="站点类型" prop="site_type">
              <el-radio-group v-model="form.site_type">
                <el-radio :label="1" :disabled="isEdit">主站</el-radio>
                <el-radio :label="2">子站</el-radio>
                <el-radio :label="3">独立站</el-radio>
              </el-radio-group>
            </el-form-item>

            <el-form-item label="父站点" v-if="form.site_type === 2">
              <el-select v-model="form.parent_site_id" placeholder="请选择父站点" clearable style="width: 100%;">
                <el-option
                  v-for="site in siteOptions"
                  :key="site.id"
                  :label="site.name"
                  :value="site.id"
                  :disabled="site.id === form.id"
                />
              </el-select>
            </el-form-item>

            <el-form-item label="域名绑定类型" prop="domain_bind_type">
              <el-radio-group v-model="form.domain_bind_type">
                <el-radio :label="1">独立域名</el-radio>
                <el-radio :label="2">子域名</el-radio>
                <el-radio :label="3">目录</el-radio>
              </el-radio-group>
            </el-form-item>

            <el-form-item label="子域名前缀" v-if="form.domain_bind_type === 2">
              <el-input v-model="form.sub_domain" placeholder="子域名前缀，如: beijing" />
            </el-form-item>

            <el-form-item label="静态生成目录" prop="static_output_dir">
              <el-input
                v-model="form.static_output_dir"
                placeholder="留空则自动生成（主站为根目录，其他站点为site_X）"
              />
              <div class="form-tip">
                相对于html目录的路径，如：beijing 或 sites/beijing<br>
                主站默认为空（输出到html根目录），其他站点建议使用英文目录名
              </div>
            </el-form-item>

            <el-form-item label="状态" prop="status">
              <el-radio-group v-model="form.status">
                <el-radio :label="0">禁用</el-radio>
                <el-radio :label="1">启用</el-radio>
                <el-radio :label="2">维护中</el-radio>
              </el-radio-group>
            </el-form-item>

            <el-form-item label="排序">
              <el-input-number v-model="form.sort" :min="0" />
            </el-form-item>
          </el-tab-pane>

          <el-tab-pane label="地域信息" name="region">
            <el-form-item label="地域代码">
              <el-input v-model="form.region_code" placeholder="地域代码，如: 110000" />
            </el-form-item>

            <el-form-item label="地域名称">
              <el-input v-model="form.region_name" placeholder="地域名称，如: 北京" />
            </el-form-item>

            <el-form-item label="省份">
              <el-input v-model="form.province" placeholder="省份" />
            </el-form-item>

            <el-form-item label="城市">
              <el-input v-model="form.city" placeholder="城市" />
            </el-form-item>

            <el-form-item label="区县">
              <el-input v-model="form.district" placeholder="区县" />
            </el-form-item>
          </el-tab-pane>

          <el-tab-pane label="网站信息" name="website">
            <el-form-item label="Logo">
              <div style="display: flex; gap: 10px; align-items: flex-start;">
                <el-input v-model="form.logo" placeholder="Logo URL" style="flex: 1;" />
                <el-button @click="selectMedia('logo')">选择图片</el-button>
              </div>
              <div class="form-tip">变量名: web_logo</div>
              <img v-if="form.logo" :src="form.logo" style="max-width: 200px; margin-top: 10px;" />
            </el-form-item>

            <el-form-item label="Favicon">
              <div style="display: flex; gap: 10px; align-items: flex-start;">
                <el-input v-model="form.favicon" placeholder="Favicon URL，如: /uploads/favicon.ico" style="flex: 1;" />
                <el-button @click="selectMedia('favicon')">选择文件</el-button>
              </div>
              <div class="form-tip">变量名: web_ico，支持 .ico 文件</div>
              <img v-if="form.favicon" :src="form.favicon" style="max-width: 32px; margin-top: 10px;" />
            </el-form-item>

            <el-form-item label="网站网址">
              <el-input v-model="form.site_url" placeholder="http://www.example.com" />
              <div class="form-tip">
                包含协议的完整网址，用于生成静态页面中的媒体文件URL<br>
                变量名: web_basehost，主站的网站网址会应用到所有站点的媒体文件URL
              </div>
            </el-form-item>

            <el-form-item label="版权信息">
              <el-input v-model="form.copyright" type="textarea" :rows="2" placeholder="Copyright © 2024 网站名称 版权所有" />
              <div class="form-tip">变量名: web_copyright</div>
            </el-form-item>

            <el-form-item label="ICP备案号">
              <el-input v-model="form.icp_no" placeholder="粤ICP备2024245973号" />
              <div class="form-tip">变量名: web_recordnum</div>
            </el-form-item>

            <el-form-item label="公安备案号">
              <el-input v-model="form.police_no" placeholder="请输入公安备案号" />
              <div class="form-tip">变量名: web_garecordnum</div>
            </el-form-item>

            <el-form-item label="第三方代码">
              <el-input
                v-model="form.thirdparty_code"
                type="textarea"
                :rows="4"
                placeholder='<script charset="UTF-8"></script>'
              />
              <div class="form-tip">此代码将在模板底部调用，变量名: web_thirdcode_pc</div>
            </el-form-item>

            <el-divider>联系方式</el-divider>

            <el-form-item label="联系邮箱">
              <el-input v-model="form.contact_email" placeholder="contact@example.com" />
            </el-form-item>

            <el-form-item label="联系电话">
              <el-input v-model="form.contact_phone" placeholder="联系电话" />
            </el-form-item>

            <el-form-item label="联系地址">
              <el-input v-model="form.contact_address" placeholder="联系地址" />
            </el-form-item>
          </el-tab-pane>

          <el-tab-pane label="SEO设置" name="seo">
            <el-alert type="info" :closable="false" style="margin-bottom: 20px;">
              <template #default>
                <div style="font-size: 13px; line-height: 1.6;">
                  <strong>TDK说明：</strong><br>
                  • Title（标题）：显示在浏览器标签页，搜索引擎结果的标题<br>
                  • Description（描述）：显示在搜索引擎结果下方的网站描述<br>
                  • Keywords（关键词）：帮助搜索引擎理解页面主题的关键词
                </div>
              </template>
            </el-alert>

            <el-form-item label="SEO标题">
              <el-input
                v-model="form.seo_title"
                placeholder="请输入网站标题"
                maxlength="100"
                show-word-limit
              />
              <div class="form-tip">将在首页模板的 &lt;title&gt; 标签中调用，变量名: seo_title</div>
              <div style="margin-top: 5px; color: #E6A23C; font-size: 12px;">
                建议长度：30-60个字符，包含主要关键词
              </div>
            </el-form-item>

            <el-form-item label="SEO关键词">
              <el-input
                v-model="form.seo_keywords"
                placeholder="关键词1,关键词2,关键词3"
                maxlength="200"
                show-word-limit
              />
              <div class="form-tip">将在首页模板的 &lt;meta name="keywords"&gt; 中调用，变量名: seo_keywords</div>
              <div style="margin-top: 5px; color: #E6A23C; font-size: 12px;">
                多个关键词用英文逗号分隔，建议3-5个核心关键词
              </div>
            </el-form-item>

            <el-form-item label="SEO描述">
              <el-input
                v-model="form.seo_description"
                type="textarea"
                :rows="4"
                placeholder="请输入网站描述"
                maxlength="300"
                show-word-limit
              />
              <div class="form-tip">将在首页模板的 &lt;meta name="description"&gt; 中调用，变量名: seo_description</div>
              <div style="margin-top: 5px; color: #E6A23C; font-size: 12px;">
                建议长度：80-200个字符，清晰描述网站主要内容和特点
              </div>
            </el-form-item>
          </el-tab-pane>

          <el-tab-pane label="核心设置" name="core">
            <el-divider>模板设置</el-divider>

            <el-form-item label="首页模板">
              <el-input v-model="form.index_template" placeholder="默认: index" />
              <div class="form-tip">用于生成首页的模板文件名（不含扩展名）</div>
            </el-form-item>

            <el-divider>系统功能</el-divider>

            <el-form-item label="回收站">
              <el-radio-group v-model="form.recycle_bin_enable">
                <el-radio value="open">开启</el-radio>
                <el-radio value="close">关闭</el-radio>
              </el-radio-group>
              <div class="form-tip">开启后，删除内容将进入回收站；关闭则直接物理删除</div>
            </el-form-item>

            <el-form-item label="文档副栏目">
              <el-radio-group v-model="form.article_sub_category">
                <el-radio value="open">开启</el-radio>
                <el-radio value="close">关闭</el-radio>
              </el-radio-group>
              <div class="form-tip">开启后，一篇文章可以同时属于多个分类（一个主分类+多个副分类）</div>
            </el-form-item>

            <el-divider>评论设置</el-divider>

            <el-form-item label="允许游客评论">
              <el-radio-group v-model="form.enable_guest_comment">
                <el-radio :value="true">允许</el-radio>
                <el-radio :value="false">禁止</el-radio>
              </el-radio-group>
              <div class="form-tip">允许后，未登录用户也可以发表评论（需提供昵称和邮箱）</div>
            </el-form-item>

            <el-form-item label="评论自动审核">
              <el-radio-group v-model="form.auto_approve">
                <el-radio :value="true">开启</el-radio>
                <el-radio :value="false">关闭</el-radio>
              </el-radio-group>
              <div class="form-tip">开启后，评论将自动审核通过；关闭则需要人工审核</div>
            </el-form-item>

            <el-form-item label="敏感词过滤">
              <el-radio-group v-model="form.enable_sensitive_filter">
                <el-radio :value="true">开启</el-radio>
                <el-radio :value="false">关闭</el-radio>
              </el-radio-group>
              <div class="form-tip">开启后，评论内容将进行敏感词过滤和替换</div>
            </el-form-item>
          </el-tab-pane>

          <el-tab-pane label="模板配置" name="template" v-if="isEdit">
            <el-alert type="info" :closable="false" style="margin-bottom: 20px;">
              为站点选择模板包，并可针对不同页面类型单独设置模板覆盖
            </el-alert>

            <el-form-item label="模板包">
              <el-select
                v-model="templateConfig.package_id"
                placeholder="请选择模板包"
                style="width: 100%;"
                @change="handleTemplatePackageChange"
              >
                <el-option
                  v-for="pkg in templatePackages"
                  :key="pkg.id"
                  :label="`${pkg.name} (${pkg.code})`"
                  :value="pkg.id"
                >
                  <span>{{ pkg.name }}</span>
                  <span style="float: right; color: #8492a6; font-size: 13px;">{{ pkg.code }}</span>
                </el-option>
              </el-select>
              <div class="form-tip">选择模板包后自动应用到站点</div>
            </el-form-item>

            <el-divider v-if="templateConfig.package_id">模板自定义配置</el-divider>

            <div v-if="templateConfig.package_id" style="margin-bottom: 20px;">
              <el-input
                v-model="templateCustomConfigStr"
                type="textarea"
                :rows="8"
                placeholder='{"color": "#333", "font_size": "14px"}'
              />
              <div class="form-tip">JSON格式，将覆盖模板包的默认配置</div>
              <el-button
                type="primary"
                size="small"
                style="margin-top: 10px;"
                @click="handleSaveTemplateConfig"
                :loading="savingTemplateConfig"
              >
                保存模板配置
              </el-button>
            </div>
          </el-tab-pane>
        </el-tabs>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="saving">确定</el-button>
      </template>
    </el-dialog>

    <!-- 媒体选择器 -->
    <MediaSelector
      v-model="mediaSelectorVisible"
      :multiple="false"
      :accept="mediaSelectorAccept"
      @select="handleMediaSelect"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import MediaSelector from '@/components/MediaSelector.vue'
import {
  getSiteList,
  createSite,
  updateSite,
  deleteSite,
  getSiteOptions,
  updateSiteStats,
  getSiteTemplateConfig,
  setSiteTemplatePackage,
  updateSiteTemplateConfig
} from '@/api/site'
import { getAllTemplatePackages } from '@/api/templatePackage'

const loading = ref(false)
const saving = ref(false)
const savingTemplateConfig = ref(false)
const dialogVisible = ref(false)
const isEdit = ref(false)
const formRef = ref(null)
const siteList = ref([])
const siteOptions = ref([])
const total = ref(0)
const activeTab = ref('basic')

// 媒体选择器相关
const mediaSelectorVisible = ref(false)
const currentMediaField = ref('') // 记录当前选择的字段：logo 或 favicon
const mediaSelectorAccept = ref('image') // 媒体选择器接受的文件类型

// 模板配置相关
const templatePackages = ref([])
const templateConfig = reactive({
  package_id: null,
  custom_config: {}
})
const templateCustomConfigStr = ref('{}')

const pagination = reactive({
  page: 1,
  limit: 15
})

const searchForm = reactive({
  site_name: '',
  site_code: '',
  status: null
})

const form = reactive({
  id: null,
  site_code: '',
  site_name: '',
  site_type: 2,
  parent_site_id: null,
  domain_bind_type: 1,
  sub_domain: '',
  region_code: '',
  region_name: '',
  province: '',
  city: '',
  district: '',
  logo: '',
  favicon: '',
  description: '',
  keywords: '',
  copyright: '',
  icp_no: '',
  contact_email: '',
  contact_phone: '',
  contact_address: '',
  // 网站信息
  site_url: '',
  police_no: '',
  thirdparty_code: '',
  // SEO设置
  seo_title: '',
  seo_keywords: '',
  seo_description: '',
  // 核心设置
  index_template: 'index',
  recycle_bin_enable: 'open',
  article_sub_category: 'close',
  // 评论设置
  enable_guest_comment: true,
  auto_approve: false,
  enable_sensitive_filter: true,
  status: 1,
  sort: 0
})

const rules = {
  site_code: [
    { required: true, message: '请输入站点代码', trigger: 'blur' },
    { pattern: /^[a-zA-Z0-9_]+$/, message: '站点代码只能包含字母、数字和下划线', trigger: 'blur' }
  ],
  site_name: [{ required: true, message: '请输入站点名称', trigger: 'blur' }],
  site_type: [{ required: true, message: '请选择站点类型', trigger: 'change' }]
}

// 获取站点列表
const fetchSites = async () => {
  loading.value = true
  try {
    const params = {
      ...searchForm,
      page: pagination.page,
      limit: pagination.limit
    }
    const res = await getSiteList(params)
    siteList.value = res.data.list || []
    total.value = res.data.total || 0
  } catch (error) {
    ElMessage.error('获取站点列表失败')
  } finally {
    loading.value = false
  }
}

// 获取站点选项
const fetchSiteOptions = async () => {
  try {
    const res = await getSiteOptions()
    siteOptions.value = res.data || []
  } catch (error) {
    console.error('获取站点选项失败:', error)
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  fetchSites()
}

// 重置
const handleReset = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = key === 'status' ? null : ''
  })
  handleSearch()
}

// 添加站点
const handleAdd = () => {
  isEdit.value = false
  resetForm()
  dialogVisible.value = true
}

// 编辑站点
const handleEdit = (row) => {
  isEdit.value = true

  // 先重置表单
  resetForm()

  // 复制基本字段
  Object.keys(form).forEach(key => {
    if (row[key] !== undefined && key !== 'seo_title' && key !== 'seo_keywords' && key !== 'seo_description') {
      form[key] = row[key]
    }
  })

  // 从 seo_config 中提取 SEO 字段
  if (row.seo_config) {
    form.seo_title = row.seo_config.seo_title || ''
    form.seo_keywords = row.seo_config.seo_keywords || ''
    form.seo_description = row.seo_config.seo_description || ''
  }

  // 从 config 中提取核心配置字段
  if (row.config) {
    form.index_template = row.config.index_template || 'index'
    form.recycle_bin_enable = row.config.recycle_bin_enable || 'open'
    form.article_sub_category = row.config.article_sub_category || 'close'
  }

  dialogVisible.value = true
}

// 重置表单
const resetForm = () => {
  if (formRef.value) {
    formRef.value.resetFields()
  }
  Object.keys(form).forEach(key => {
    if (key === 'site_type') {
      form[key] = 2
    } else if (key === 'domain_bind_type') {
      form[key] = 1
    } else if (key === 'status') {
      form[key] = 1
    } else if (key === 'sort') {
      form[key] = 0
    } else if (key === 'index_template') {
      form[key] = 'index'
    } else if (key === 'recycle_bin_enable') {
      form[key] = 'open'
    } else if (key === 'article_sub_category') {
      form[key] = 'close'
    } else {
      form[key] = key === 'id' ? null : ''
    }
  })
  activeTab.value = 'basic'
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return

  formRef.value.validate(async (valid) => {
    if (!valid) return

    saving.value = true
    try {
      if (isEdit.value) {
        await updateSite(form.id, form)
        ElMessage.success('站点更新成功')
      } else {
        await createSite(form)
        ElMessage.success('站点创建成功')
      }
      dialogVisible.value = false
      fetchSites()
      fetchSiteOptions()
    } catch (error) {
      ElMessage.error(error.message || '操作失败')
    } finally {
      saving.value = false
    }
  })
}

// 删除站点
const handleDelete = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除该站点吗？删除后无法恢复！', '警告', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    await deleteSite(id)
    ElMessage.success('站点删除成功')
    fetchSites()
    fetchSiteOptions()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

// 更新统计
const handleUpdateStats = async (row) => {
  // 设置按钮加载状态
  row.updating = true

  try {
    const response = await updateSiteStats(row.id)
    console.log('Update stats response:', response)

    // 显示成功消息，包含更新前后的数据对比
    const oldArticleCount = row.article_count || 0
    const oldUserCount = row.user_count || 0

    // 重新获取站点列表
    await fetchSites()

    // 找到更新后的站点数据
    const updatedSite = siteList.value.find(s => s.id === row.id)
    if (updatedSite) {
      const newArticleCount = updatedSite.article_count || 0
      const newUserCount = updatedSite.user_count || 0

      if (oldArticleCount === newArticleCount && oldUserCount === newUserCount) {
        ElMessage.success('统计数据已是最新')
      } else {
        ElMessage.success(
          `统计更新成功！文章数: ${oldArticleCount} → ${newArticleCount}，用户数: ${oldUserCount} → ${newUserCount}`
        )
      }
    } else {
      ElMessage.success('统计数据更新成功')
    }
  } catch (error) {
    console.error('更新统计错误:', error)
    ElMessage.error(error.message || '更新失败')
  } finally {
    // 清除加载状态
    row.updating = false
  }
}

// 选择媒体
const selectMedia = (field) => {
  currentMediaField.value = field
  // favicon 可以选择所有文件（包括ico），logo只能选择图片
  mediaSelectorAccept.value = field === 'favicon' ? '' : 'image'
  mediaSelectorVisible.value = true
}

// 处理媒体选择
const handleMediaSelect = (media) => {
  if (media && media.file_url) {
    form[currentMediaField.value] = media.file_url
  }
}

// ==================== 模板配置相关 ====================

// 获取模板包列表
const fetchTemplatePackages = async () => {
  try {
    const res = await getAllTemplatePackages({ site_id: form.id })
    templatePackages.value = res.data
  } catch (error) {
    console.error('获取模板包列表失败:', error)
  }
}

// 获取站点模板配置
const fetchSiteTemplateConfig = async (siteId) => {
  try {
    const res = await getSiteTemplateConfig(siteId)
    if (res.data.has_config) {
      templateConfig.package_id = res.data.package_id
      templateConfig.custom_config = res.data.custom_config || {}
      templateCustomConfigStr.value = JSON.stringify(res.data.custom_config || {}, null, 2)
    } else {
      templateConfig.package_id = null
      templateConfig.custom_config = {}
      templateCustomConfigStr.value = '{}'
    }
  } catch (error) {
    console.error('获取模板配置失败:', error)
  }
}

// 模板包改变时
const handleTemplatePackageChange = async (packageId) => {
  if (!packageId) return

  try {
    await setSiteTemplatePackage(form.id, packageId)
    ElMessage.success('模板包设置成功')
    // 重新获取配置
    await fetchSiteTemplateConfig(form.id)
  } catch (error) {
    ElMessage.error(error.message || '设置失败')
  }
}

// 保存模板自定义配置
const handleSaveTemplateConfig = async () => {
  // 验证 JSON 格式
  let customConfig
  try {
    customConfig = JSON.parse(templateCustomConfigStr.value)
  } catch (e) {
    ElMessage.error('JSON格式错误，请检查')
    return
  }

  savingTemplateConfig.value = true
  try {
    await updateSiteTemplateConfig(form.id, customConfig)
    ElMessage.success('模板配置保存成功')
    templateConfig.custom_config = customConfig
  } catch (error) {
    ElMessage.error(error.message || '保存失败')
  } finally {
    savingTemplateConfig.value = false
  }
}

onMounted(() => {
  fetchSites()
  fetchSiteOptions()
  fetchTemplatePackages()
})

// 监听编辑对话框打开，加载模板配置
watch(() => dialogVisible.value, (newVal) => {
  if (newVal && isEdit.value && form.id) {
    // 切换到模板配置tab时才加载
    if (activeTab.value === 'template') {
      fetchSiteTemplateConfig(form.id)
    }
  }
})

watch(() => activeTab.value, (newTab) => {
  if (newTab === 'template' && isEdit.value && form.id) {
    fetchSiteTemplateConfig(form.id)
  }
})
</script>

<style scoped lang="scss">
.site-list {
  .header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;

    h3 {
      margin: 0;
    }
  }

  .search-bar {
    margin-bottom: 20px;
  }

  .form-tip {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
  }
}
</style>

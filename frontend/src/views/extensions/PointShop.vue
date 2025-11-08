<template>
  <div class="point-shop-container">
    <el-card>
      <template #header>
        <span>积分商城管理</span>
      </template>

      <el-tabs v-model="activeTab">
        <!-- 商品列表 -->
        <el-tab-pane label="商品列表" name="goods">
          <el-button type="primary" @click="showGoodsDialog" style="margin-bottom: 15px">
            新增商品
          </el-button>

          <el-table :data="goods" v-loading="loading">
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="name" label="商品名称" width="200" />
            <el-table-column prop="price" label="所需积分" width="120">
              <template #default="{ row }">
                <el-tag type="warning">{{ row.price }}积分</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="stock" label="库存" width="100">
              <template #default="{ row }">
                {{ row.stock === -1 ? '无限' : row.stock }}
              </template>
            </el-table-column>
            <el-table-column prop="sales" label="销量" width="100" />
            <el-table-column prop="type" label="类型" width="100">
              <template #default="{ row }">
                {{ row.type === 'virtual' ? '虚拟商品' : '实物商品' }}
              </template>
            </el-table-column>
            <el-table-column prop="status" label="状态" width="100">
              <template #default="{ row }">
                <el-tag :type="row.status ? 'success' : 'danger'">
                  {{ row.status ? '上架' : '下架' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="150">
              <template #default="{ row }">
                <el-button size="small" @click="editGoods(row)">编辑</el-button>
                <el-button size="small" type="danger" @click="deleteGoods(row.id)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-tab-pane>

        <!-- 兑换订单 -->
        <el-tab-pane label="兑换订单" name="orders">
          <el-form :inline="true" :model="searchForm">
            <el-form-item label="订单号">
              <el-input v-model="searchForm.order_no" placeholder="订单号" clearable />
            </el-form-item>
            <el-form-item label="状态">
              <el-select v-model="searchForm.status" placeholder="全部" clearable>
                <el-option label="待发货" :value="0" />
                <el-option label="已发货" :value="1" />
                <el-option label="已完成" :value="2" />
                <el-option label="已取消" :value="3" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="loadOrders">搜索</el-button>
            </el-form-item>
          </el-form>

          <el-table :data="orders" v-loading="loading">
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="order_no" label="订单号" width="180" />
            <el-table-column prop="goods_name" label="商品名称" width="200" />
            <el-table-column prop="total_points" label="消耗积分" width="120">
              <template #default="{ row }">
                <el-tag type="warning">{{ row.total_points }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="contact_name" label="联系人" width="120" />
            <el-table-column prop="contact_phone" label="联系电话" width="120" />
            <el-table-column prop="status" label="状态" width="100">
              <template #default="{ row }">
                <el-tag :type="getStatusType(row.status)">
                  {{ getStatusText(row.status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="create_time" label="下单时间" width="160" />
            <el-table-column label="操作" width="150" fixed="right">
              <template #default="{ row }">
                <el-button v-if="row.status === 0" size="small" type="primary" @click="deliverOrder(row)">
                  发货
                </el-button>
                <el-button v-if="row.status === 1" size="small" type="success" @click="completeOrder(row)">
                  完成
                </el-button>
                <el-button v-if="row.status === 0" size="small" type="danger" @click="cancelOrder(row)">
                  取消
                </el-button>
              </template>
            </el-table-column>
          </el-table>

          <div class="pagination">
            <el-pagination
              v-model:current-page="pagination.page"
              v-model:page-size="pagination.limit"
              :total="pagination.total"
              layout="total, prev, pager, next"
              @current-change="loadOrders"
            />
          </div>
        </el-tab-pane>

        <!-- 商城统计 -->
        <el-tab-pane label="商城统计" name="stats">
          <el-descriptions v-if="stats" :column="2" border style="max-width: 600px">
            <el-descriptions-item label="总商品数">{{ stats.goods.total }}</el-descriptions-item>
            <el-descriptions-item label="上架商品">{{ stats.goods.on_sale }}</el-descriptions-item>
            <el-descriptions-item label="总订单数">{{ stats.orders.total }}</el-descriptions-item>
            <el-descriptions-item label="待发货">{{ stats.orders.pending }}</el-descriptions-item>
            <el-descriptions-item label="已发货">{{ stats.orders.delivered }}</el-descriptions-item>
            <el-descriptions-item label="已完成">{{ stats.orders.completed }}</el-descriptions-item>
            <el-descriptions-item label="今日订单">{{ stats.orders.today }}</el-descriptions-item>
            <el-descriptions-item label="本周订单">{{ stats.orders.week }}</el-descriptions-item>
          </el-descriptions>
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'

const activeTab = ref('goods')
const loading = ref(false)
const goods = ref([])
const orders = ref([])
const stats = ref(null)

const searchForm = reactive({
  order_no: '',
  status: ''
})

const pagination = reactive({
  page: 1,
  limit: 20,
  total: 0
})

const getStatusText = (status) => {
  const texts = ['待发货', '已发货', '已完成', '已取消']
  return texts[status] || '未知'
}

const getStatusType = (status) => {
  const types = ['warning', 'info', 'success', 'default']
  return types[status] || 'default'
}

const loadGoods = async () => {
  loading.value = true
  try {
    const response = await request.get('/point-shop-manage/goods-index')
    if (response.data.code === 200) {
      goods.value = response.data.data.data || []
    }
  } catch (error) {
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

const loadOrders = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      limit: pagination.limit,
      ...searchForm
    }
    const response = await request.get('/point-shop-manage/order-index', { params })
    if (response.data.code === 200) {
      orders.value = response.data.data.data || []
      pagination.total = response.data.data.total || 0
    }
  } catch (error) {
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  try {
    const response = await request.get('/point-shop-manage/statistics')
    if (response.data.code === 200) {
      stats.value = response.data.data
    }
  } catch (error) {
    console.error('加载统计失败', error)
  }
}

const showGoodsDialog = () => {
  ElMessage.info('商品创建功能开发中...')
}

const editGoods = (row) => {
  ElMessage.info('商品编辑功能开发中...')
}

const deleteGoods = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除该商品吗？', '确认', { type: 'warning' })
    const response = await request.delete(`/api/point-shop-manage/goods-delete/${id}`)
    if (response.data.code === 200) {
      ElMessage.success('删除成功')
      loadGoods()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

const deliverOrder = async (row) => {
  try {
    await ElMessageBox.confirm('确定要发货吗？', '确认', { type: 'info' })
    const response = await request.post(`/api/point-shop-manage/order-deliver/${row.id}`, {
      admin_remark: '商品已发货'
    })
    if (response.data.code === 200) {
      ElMessage.success('发货成功')
      loadOrders()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('操作失败')
    }
  }
}

const completeOrder = async (row) => {
  try {
    await ElMessageBox.confirm('确定要完成订单吗？', '确认', { type: 'success' })
    const response = await request.post(`/api/point-shop-manage/order-complete/${row.id}`)
    if (response.data.code === 200) {
      ElMessage.success('订单已完成')
      loadOrders()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('操作失败')
    }
  }
}

const cancelOrder = async (row) => {
  try {
    const { value } = await ElMessageBox.prompt('请输入取消原因', '取消订单', {
      confirmButtonText: '确定',
      cancelButtonText: '取消'
    })
    const response = await request.post(`/api/point-shop-manage/order-cancel/${row.id}`, {
      reason: value
    })
    if (response.data.code === 200) {
      ElMessage.success('订单已取消')
      loadOrders()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('操作失败')
    }
  }
}

onMounted(() => {
  loadGoods()
  loadOrders()
  loadStats()
})
</script>

<style scoped>
.point-shop-container {
  padding: 20px;
}

.pagination {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
</style>

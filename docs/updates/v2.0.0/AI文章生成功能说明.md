# AI文章批量生成功能说明文档

## 功能概述

AI文章批量生成功能允许您通过集成的AI服务（OpenAI、Claude、DeepSeek等）自动化生成高质量的文章内容。该功能支持批量生成、自定义参数、任务管理等特性。

## 核心功能

### 1. AI配置管理
- 支持多种AI提供商：OpenAI、Claude、DeepSeek、百度文心一言、阿里通义千问、智谱ChatGLM
- 灵活配置：API密钥、模型、最大Token数、温度参数等
- 安全性：API密钥自动隐藏显示
- 默认配置：可设置默认AI配置
- 连接测试：创建配置后可测试连接

### 2. 批量生成任务
- 多主题支持：一次任务支持多个主题（逗号或换行分隔）
- 自定义参数：文章长度（短/中/长）、写作风格（专业/轻松/创意）
- 自动发布：可选择生成后自动发布或保存为草稿
- 任务控制：启动、停止、查看进度
- 关联分类：生成的文章自动归类

### 3. 生成记录管理
- 详细记录：每篇生成的文章都有详细记录
- 状态追踪：成功/失败/待处理
- Token统计：记录每次生成使用的Token数
- 手动发布：生成后可手动编辑并发布

## API接口说明

### AI配置管理

#### 1. 获取AI提供商列表
```bash
GET /api/ai-configs/providers
```

#### 2. 创建AI配置
```bash
POST /api/ai-configs

参数：
{
  "name": "配置名称",
  "provider": "openai",  // openai/claude/deepseek/wenxin/tongyi/chatglm
  "api_key": "你的API密钥",
  "model": "gpt-3.5-turbo",  // 可选，模型名称
  "api_endpoint": "https://api.openai.com/v1",  // 可选，自定义端点
  "max_tokens": 2000,  // 最大token数
  "temperature": 0.7,  // 温度参数 0-1
  "is_default": 1,  // 是否默认配置
  "status": 1  // 状态：0禁用 1启用
}
```

#### 3. 获取配置列表
```bash
GET /api/ai-configs?page=1&page_size=20&provider=openai&status=1
```

#### 4. 测试AI连接
```bash
POST /api/ai-configs/:id/test
```

#### 5. 设置默认配置
```bash
POST /api/ai-configs/:id/set-default
```

### AI文章生成任务管理

#### 1. 创建生成任务
```bash
POST /api/ai-article-tasks

参数：
{
  "title": "任务名称",
  "topic": "主题1,主题2,主题3",  // 多个主题用逗号分隔
  "category_id": 1,  // 目标分类ID
  "ai_config_id": 1,  // AI配置ID
  "total_count": 10,  // 生成数量
  "settings": {
    "length": "medium",  // short/medium/long
    "style": "professional",  // professional/casual/creative
    "auto_publish": false,  // 是否自动发布
    "publish_status": 1  // 发布状态：0草稿 1已发布
  }
}
```

#### 2. 启动任务
```bash
POST /api/ai-article-tasks/:id/start
```

#### 3. 停止任务
```bash
POST /api/ai-article-tasks/:id/stop
```

#### 4. 获取任务列表
```bash
GET /api/ai-article-tasks?page=1&page_size=20&status=pending&category_id=1
```

#### 5. 获取任务详情
```bash
GET /api/ai-article-tasks/:id
```

#### 6. 获取任务的生成记录
```bash
GET /api/ai-article-tasks/:id/generated-articles?page=1&page_size=20&status=success
```

#### 7. 获取统计信息
```bash
GET /api/ai-article-tasks/statistics
```

## 使用流程

### 1. 配置AI服务

```bash
# 创建OpenAI配置
curl -X POST "http://localhost:8000/api/ai-configs" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "OpenAI GPT-3.5",
    "provider": "openai",
    "api_key": "sk-your-api-key",
    "model": "gpt-3.5-turbo",
    "max_tokens": 2000,
    "temperature": 0.7,
    "is_default": 1,
    "status": 1
  }'

# 测试连接
curl -X POST "http://localhost:8000/api/ai-configs/1/test" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 2. 创建生成任务

```bash
curl -X POST "http://localhost:8000/api/ai-article-tasks" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "AI技术文章批量生成",
    "topic": "人工智能的应用,机器学习基础,深度学习实践",
    "category_id": 1,
    "ai_config_id": 1,
    "total_count": 3,
    "settings": {
      "length": "medium",
      "style": "professional",
      "auto_publish": false
    }
  }'
```

### 3. 启动任务

```bash
curl -X POST "http://localhost:8000/api/ai-article-tasks/1/start" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. 查看进度

```bash
# 查看任务详情
curl -X GET "http://localhost:8000/api/ai-article-tasks/1" \
  -H "Authorization: Bearer YOUR_TOKEN"

# 查看生成的文章
curl -X GET "http://localhost:8000/api/ai-article-tasks/1/generated-articles" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 支持的AI提供商

### 1. OpenAI
- **provider**: `openai`
- **推荐模型**: `gpt-3.5-turbo`, `gpt-4`
- **API端点**: `https://api.openai.com/v1`（默认）
- **获取API密钥**: https://platform.openai.com/

### 2. Claude (Anthropic)
- **provider**: `claude`
- **推荐模型**: `claude-3-sonnet-20240229`, `claude-3-opus-20240229`
- **API端点**: `https://api.anthropic.com/v1`（默认）
- **获取API密钥**: https://console.anthropic.com/

### 3. DeepSeek
- **provider**: `deepseek`
- **推荐模型**: `deepseek-chat`, `deepseek-coder`
- **API端点**: `https://api.deepseek.com/v1`（默认）
- **获取API密钥**: https://platform.deepseek.com/
- **说明**: DeepSeek兼容OpenAI接口，是国产AI的优秀选择

### 4. 其他国产AI（待实现）
- 百度文心一言（wenxin）
- 阿里通义千问（tongyi）
- 智谱ChatGLM（chatglm）

## 配置建议

### 文章长度设置
- **short**: 500-800字，适合简短的新闻、快讯
- **medium**: 1000-1500字，适合普通文章、教程
- **long**: 2000-3000字，适合深度分析、专业文章

### 写作风格设置
- **professional**: 专业、严谨，适合技术文档、学术文章
- **casual**: 轻松、口语化，适合博客、生活类文章
- **creative**: 创意、富有想象力，适合营销文案、创意内容

### Token数设置
- GPT-3.5-turbo: 建议1500-3000
- GPT-4: 建议2000-4000
- Claude: 建议2000-4000

### 温度参数
- **0.3-0.5**: 更确定、一致的输出
- **0.6-0.8**: 平衡创造性和一致性（推荐）
- **0.9-1.0**: 更有创造性、多样化的输出

## 注意事项

1. **API费用**: 使用AI服务会产生费用，请合理设置生成数量和参数
2. **生成速度**: 为避免API限流，系统会在生成之间添加2秒延迟
3. **错误处理**: 连续失败5次后任务会自动停止
4. **内容审核**: AI生成的内容建议人工审核后再发布
5. **API密钥安全**: 请妥善保管API密钥，系统会自动隐藏显示

## 数据库表结构

### ai_configs - AI配置表
存储AI服务的配置信息

### ai_article_tasks - 文章生成任务表
存储批量生成任务的信息和进度

### ai_generated_articles - 生成文章记录表
存储每篇生成的文章及其元数据

## 扩展开发

### 添加新的AI提供商

1. 在 `app/service/` 目录创建新的服务类，继承 `AiService`
2. 实现 `testConnection()` 和 `generateArticle()` 方法
3. 在 `AiConfig` 模型中添加新的提供商常量
4. 在 `AiService::createFromConfig()` 中添加对应的分支

示例：
```php
class NewAiService extends AiService
{
    public function testConnection()
    {
        // 实现连接测试逻辑
    }

    public function generateArticle($topic, $options = [])
    {
        // 实现文章生成逻辑
    }
}
```

## 常见问题

### Q: 生成的文章质量不理想怎么办？
A: 可以调整以下参数：
- 增加max_tokens以获得更长的内容
- 调整temperature（降低可获得更稳定的输出）
- 修改主题描述，提供更详细的信息
- 尝试不同的AI模型

### Q: 任务失败了怎么办？
A:
- 检查AI配置的API密钥是否正确
- 使用测试连接功能验证配置
- 查看任务的error_message了解失败原因
- 检查AI服务的余额和配额

### Q: 如何自定义生成提示词？
A: 修改 `AiService::buildArticlePrompt()` 方法，自定义提示词模板。

## 技术支持

如有问题或建议，请联系技术支持团队。

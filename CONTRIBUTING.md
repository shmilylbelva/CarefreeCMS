# 贡献指南

感谢您对本CMS项目的关注！我们欢迎任何形式的贡献，无论是Bug报告、功能建议还是代码提交。

## 目录

1. [行为准则](#行为准则)
2. [如何贡献](#如何贡献)
3. [开发流程](#开发流程)
4. [提交规范](#提交规范)
5. [代码审查](#代码审查)
6. [问题报告](#问题报告)
7. [功能建议](#功能建议)

---

## 行为准则

### 我们的承诺

为了营造一个开放且热情的环境，我们作为贡献者和维护者承诺：无论年龄、体型、残疾、种族、性别认同和表达、经验水平、国籍、外貌、种族、宗教或性认同和取向，参与我们项目和社区的每个人都不会受到骚扰。

### 我们的标准

有助于创建积极环境的行为包括：

- ✅ 使用欢迎和包容的语言
- ✅ 尊重不同的观点和经验
- ✅ 优雅地接受建设性批评
- ✅ 关注对社区最有利的事情
- ✅ 对其他社区成员表示同情

不可接受的行为包括：

- ❌ 使用性化的语言或图像，以及不受欢迎的性关注或挑逗
- ❌ 恶意评论、侮辱性/贬损性评论以及人身或政治攻击
- ❌ 公开或私下骚扰
- ❌ 未经明确许可发布他人的私人信息
- ❌ 在专业环境中可能被合理认为不适当的其他行为

---

## 如何贡献

### 贡献类型

我们欢迎以下类型的贡献：

1. **代码贡献**
   - Bug修复
   - 新功能开发
   - 性能优化
   - 重构改进

2. **文档贡献**
   - 修正文档错误
   - 添加使用示例
   - 翻译文档
   - 改进说明

3. **测试贡献**
   - 编写单元测试
   - 编写集成测试
   - 提高测试覆盖率

4. **设计贡献**
   - UI/UX改进
   - 图标设计
   - 交互设计

5. **其他贡献**
   - Bug报告
   - 功能建议
   - 代码审查
   - 社区支持

### 首次贡献

如果您是第一次贡献开源项目，可以从以下方面开始：

- 查看标有 `good first issue` 的问题
- 修复文档中的拼写错误或格式问题
- 添加代码注释
- 编写测试用例

---

## 开发流程

### 1. Fork项目

点击项目页面右上角的"Fork"按钮，将项目fork到您的GitHub账户。

### 2. 克隆仓库

```bash
# 克隆你fork的仓库
git clone https://github.com/你的用户名/cms.git
cd cms

# 添加上游仓库
git remote add upstream https://github.com/原项目/cms.git
```

### 3. 创建分支

```bash
# 同步最新代码
git fetch upstream
git checkout develop
git merge upstream/develop

# 创建功能分支
git checkout -b feature/your-feature-name
```

### 4. 配置开发环境

#### 后端环境

```bash
cd backend
composer install
cp .env.example .env
# 编辑 .env 配置数据库等信息
php think migrate:run
```

#### 前端环境

```bash
cd frontend
npm install
# 或使用pnpm
pnpm install
```

### 5. 开发和测试

```bash
# 后端开发
cd backend
php think run

# 前端开发
cd frontend
npm run dev
```

### 6. 提交代码

```bash
# 查看修改
git status

# 添加修改
git add .

# 提交修改（遵循提交规范）
git commit -m "feat(article): 添加文章导出功能"

# 推送到你的fork仓库
git push origin feature/your-feature-name
```

### 7. 创建Pull Request

1. 访问您fork的仓库页面
2. 点击"New Pull Request"按钮
3. 选择目标分支（通常是`develop`）
4. 填写PR描述
5. 提交PR等待审查

---

## 提交规范

### 提交消息格式

遵循 [Conventional Commits](https://www.conventionalcommits.org/) 规范：

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Type（类型）

- `feat`: 新功能
- `fix`: Bug修复
- `docs`: 文档更新
- `style`: 代码格式调整（不影响代码逻辑）
- `refactor`: 重构
- `perf`: 性能优化
- `test`: 测试相关
- `chore`: 构建过程或辅助工具的变动

### Scope（范围）

模块名称，如：`article`, `user`, `auth`, `cache`等

### Subject（主题）

- 使用祈使句，现在时："change" 不是 "changed" 也不是 "changes"
- 首字母小写
- 结尾不加句号

### 示例

```bash
# 新功能
git commit -m "feat(article): 添加文章批量导出功能"

# Bug修复
git commit -m "fix(auth): 修复token过期后无法刷新的问题"

# 文档更新
git commit -m "docs(api): 更新API文档中的认证说明"

# 性能优化
git commit -m "perf(query): 优化文章列表查询，使用预加载避免N+1"

# 重构
git commit -m "refactor(cache): 重构缓存管理，使用统一的CacheManager"
```

### 详细提交示例

```
feat(article): 添加文章批量导出功能

- 支持导出为Excel和CSV格式
- 可选择导出字段
- 支持按条件筛选后导出
- 添加导出进度提示

Closes #123
```

---

## 代码审查

### 提交Pull Request前的检查清单

- [ ] 代码遵循项目的编码规范
- [ ] 所有测试通过
- [ ] 添加了新功能的测试
- [ ] 更新了相关文档
- [ ] 提交消息符合规范
- [ ] 没有合并冲突
- [ ] 代码已经过自我审查

### PR描述模板

```markdown
## 变更类型
- [ ] Bug修复
- [ ] 新功能
- [ ] 重构
- [ ] 文档更新
- [ ] 其他

## 变更描述
简要描述本次PR的目的和内容。

## 相关Issue
Closes #issue编号

## 测试
描述如何测试这些变更。

## 截图（如适用）
添加相关截图。

## 检查清单
- [ ] 代码遵循项目规范
- [ ] 所有测试通过
- [ ] 添加了测试
- [ ] 更新了文档
```

### 代码审查标准

审查者会检查以下方面：

1. **功能性**
   - 代码是否实现了预期功能
   - 是否处理了边界情况
   - 是否处理了错误情况

2. **代码质量**
   - 是否遵循编码规范
   - 命名是否清晰
   - 是否有足够的注释
   - 是否有重复代码

3. **安全性**
   - 是否有安全漏洞
   - 输入是否经过验证
   - 是否有SQL注入风险

4. **性能**
   - 是否有性能问题
   - 是否有N+1查询
   - 是否合理使用缓存

5. **测试**
   - 是否有测试覆盖
   - 测试是否充分

---

## 问题报告

### 如何报告Bug

在提交Bug报告前，请先搜索是否已有类似问题。如果没有，请创建新Issue并包含以下信息：

#### Bug报告模板

```markdown
## Bug描述
清晰简洁地描述Bug。

## 重现步骤
1. 访问 '...'
2. 点击 '...'
3. 滚动到 '...'
4. 看到错误

## 预期行为
描述您期望发生什么。

## 实际行为
描述实际发生了什么。

## 截图
如果适用，添加截图帮助解释问题。

## 环境信息
- OS: [例如 Windows 11]
- 浏览器: [例如 Chrome 120]
- PHP版本: [例如 8.0]
- MySQL版本: [例如 8.0]

## 附加信息
添加任何其他关于问题的上下文信息。
```

### Bug严重程度

- **P0 - 严重**: 系统崩溃、数据丢失、安全漏洞
- **P1 - 高**: 主要功能无法使用
- **P2 - 中**: 功能可用但有明显问题
- **P3 - 低**: 小问题、UI问题

---

## 功能建议

### 如何提交功能建议

#### 功能建议模板

```markdown
## 功能描述
清晰简洁地描述您建议的功能。

## 问题背景
这个功能解决了什么问题？为什么需要这个功能？

## 建议的解决方案
详细描述您认为应该如何实现这个功能。

## 替代方案
是否考虑过其他解决方案？如果有，请描述。

## 附加信息
添加任何其他上下文信息或截图。
```

### 功能建议评估标准

维护者会根据以下标准评估功能建议：

1. **必要性**: 是否解决了真实需求
2. **通用性**: 是否对大多数用户有用
3. **可行性**: 实现难度和成本
4. **一致性**: 是否符合项目方向
5. **优先级**: 相对于其他功能的优先级

---

## 开发指南参考

- **部署文档**: [DEPLOYMENT.md](DEPLOYMENT.md)
- **开发规范**: [docs/development/DEVELOPER_GUIDE.md](docs/development/DEVELOPER_GUIDE.md)
- **系统架构**: [ARCHITECTURE.md](ARCHITECTURE.md)
- **API设计指南**: [docs/backend/API_DESIGN_GUIDE.md](docs/backend/API_DESIGN_GUIDE.md)
- **多站点使用指南**: [MULTI_SITE_GUIDE.md](MULTI_SITE_GUIDE.md)
- **错误处理指南**: [ERROR_HANDLING_GUIDE.md](ERROR_HANDLING_GUIDE.md)
- **文档索引**: [DOCUMENTATION_INDEX.md](DOCUMENTATION_INDEX.md)

---

## 联系方式

如果您有任何问题，可以通过以下方式联系我们：

- 创建GitHub Issue
- 发送邮件到项目维护者
- 加入项目讨论群

---

## 许可证

通过向本项目提交代码，您同意您的贡献将按照项目许可证进行许可。

---

**感谢您的贡献！** 🎉

您的每一个贡献都让这个项目变得更好。无论大小，我们都非常感激。

---

**最后更新**: 2025-11-26
**维护者**: CMS项目团队

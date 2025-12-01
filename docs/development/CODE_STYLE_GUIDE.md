# PHP 代码规范检查方案

## 1. 项目现状分析

### 1.1 当前代码质量工具缺失

| 工具 | 状态 | 用途 |
|------|------|------|
| PHP-CS-Fixer | ❌ 未配置 | 代码格式化 |
| PHPStan | ❌ 未配置 | 静态分析 |
| PHP Mess Detector | ❌ 未配置 | 代码质量 |
| PHPUnit | ❌ 未配置 | 单元测试 |
| PHP Codesniffer | ❌ 未配置 | 编码标准 |

### 1.2 代码规范问题发现

从样本文件 `app/controller/backend/Article.php` 中发现：

✅ **遵循的规范**：
- 使用 PHP 8.0+ 语法（declare 类型声明）
- 命名空间正确
- 类名遵循 PascalCase
- 方法名遵循 camelCase

⚠️ **存在的问题**：
- 缺少方法文档注释（PHPDoc）
- 缺少参数类型提示
- 缺少返回值类型提示
- 长行代码（部分行超过120字符）
- 缺少异常处理文档

## 2. 代码规范标准

### 2.1 PSR 标准

- **PSR-1**: Basic Coding Standard
- **PSR-2**: Coding Style Guide (已弃用，用PSR-12替代)
- **PSR-12**: Extended Coding Style Guide
- **PSR-4**: Autoloader Standard (已实现)

### 2.2 项目定制规范

#### 文件头部

```php
<?php
declare(strict_types=1);

namespace app\controller\api;

/**
 * 类描述
 *
 * 详细说明...
 *
 * @package app\controller\api
 * @author  Your Name <your@email.com>
 * @since   1.0.0
 */
class SampleController
{
}
```

#### 方法文档

```php
/**
 * 方法描述
 *
 * 详细说明...
 *
 * @param  string $param1 参数1说明
 * @param  int    $param2 参数2说明
 * @return array 返回说明
 * @throws \Exception 可能抛出的异常
 */
public function sampleMethod(string $param1, int $param2): array
{
    // 实现代码
}
```

#### 类属性

```php
/**
 * 请求对象
 * @var \think\Request
 */
protected Request $request;
```

## 3. 实施计划

### 3.1 第一步：安装代码检测工具

```bash
# 在 composer.json 中添加开发依赖
composer require --dev \
    friendsofphp/php-cs-fixer \
    phpstan/phpstan \
    squizlabs/php_codesniffer
```

### 3.2 第二步：配置规范检查工具

#### `.php-cs-fixer.dist.php`

```php
<?php
$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
    ])
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => true,
        'blank_line_after_class_opening' => true,
        'blank_line_after_namespace' => true,
        'blank_line_before_statement' => true,
        'cast_spaces' => true,
        'class_attributes_separation' => [
            'elements' => ['const', 'property', 'method'],
        ],
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'function_typehint_space' => true,
        'method_chaining_indentation' => true,
        'native_function_casing' => true,
        'no_extra_blank_lines' => true,
        'no_trailing_whitespace' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'phpdoc_align' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_tag_casing' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'single_quote' => true,
        'spaces_inside_parentheses' => false,
        'statement_indentation' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
    ])
    ->setFinder($finder);
```

#### `phpstan.neon`

```neon
parameters:
    level: 8
    paths:
        - app
    excludePaths:
        - tests
        - vendor
    reportUnmatchedIgnoredErrors: false
    checkMissingIterableValueType: false
    ignoreErrors:
        - '#Undefined class#'
        - '#Call to an undefined method#'
```

### 3.3 第三步：创建 npm 脚本

更新 `package.json` 或创建 `composer.json` 脚本：

```json
{
    "scripts": {
        "lint": "php-cs-fixer fix --dry-run --diff",
        "lint:fix": "php-cs-fixer fix",
        "static-analysis": "phpstan analyse",
        "phpcs": "phpcs --standard=PSR12 app",
        "phpcs:fix": "phpcbf --standard=PSR12 app"
    }
}
```

### 3.4 第四步：预提交钩子

创建 `.git/hooks/pre-commit`：

```bash
#!/bin/bash

# 运行代码检查
composer lint
if [ $? -ne 0 ]; then
    echo "代码检查失败，请运行 'composer lint:fix' 修复"
    exit 1
fi

# 运行静态分析
composer static-analysis
if [ $? -ne 0 ]; then
    echo "静态分析失败"
    exit 1
fi
```

## 4. 常见代码问题及修复

### 4.1 缺少类型提示

**❌ 不规范**：
```php
public function index(Request $request)
{
    $page = $request->get('page', 1);
    return $list;
}
```

**✅ 规范**：
```php
/**
 * 获取文章列表
 *
 * @param Request $request 请求对象
 * @return array 文章列表
 */
public function index(Request $request): array
{
    $page = (int)$request->get('page', 1);
    return $list;
}
```

### 4.2 缺少方法文档

**❌ 不规范**：
```php
protected function validate($data, $validate)
{
    // 实现代码
}
```

**✅ 规范**：
```php
/**
 * 验证数据
 *
 * @param  array        $data     数据
 * @param  string|array $validate 验证器名或规则
 * @return array|true
 * @throws ValidateException
 */
protected function validate(array $data, string|array $validate): array|bool
{
    // 实现代码
}
```

### 4.3 长行代码

**❌ 不规范**：
```php
$query->where(function($q) use ($categoryId) {
    $q->where('category_id', $categoryId)->whereOr('id', 'in', function($subQuery) use ($categoryId) {
        $subQuery->table('article_categories')->where('category_id', $categoryId)->field('article_id');
    });
});
```

**✅ 规范**：
```php
$query->where(function ($q) use ($categoryId) {
    $q->where('category_id', $categoryId)
        ->whereOr('id', 'in', function ($subQuery) use ($categoryId) {
            $subQuery->table('article_categories')
                ->where('category_id', $categoryId)
                ->field('article_id');
        });
});
```

### 4.4 命名规范

**❌ 不规范**：
```php
$u = $request->get('user_id');
$p = $request->get('page', 1);
$result_data = [];
```

**✅ 规范**：
```php
$userId = (int)$request->get('user_id');
$page = (int)$request->get('page', 1);
$resultData = [];
```

## 5. 检查清单

- [ ] 所有文件顶部声明 `declare(strict_types=1);`
- [ ] 所有类都有 PHPDoc 注释
- [ ] 所有公共方法都有类型提示和 PHPDoc
- [ ] 所有字符串都使用单引号（除非必要）
- [ ] 命名遵循规范（类:PascalCase，方法:camelCase）
- [ ] 缩进使用 4 个空格
- [ ] 行长度不超过 120 字符
- [ ] 异常有详细说明
- [ ] 参数验证完整

## 6. 相关文件生成

以下文件需要创建：

1. `.php-cs-fixer.dist.php` - PHP-CS-Fixer 配置
2. `phpstan.neon` - PHPStan 配置
3. `.phpcs.xml` - PHP CodeSniffer 配置
4. `pre-commit` - Git 预提交钩子

## 7. 预期收益

- ✅ 统一代码风格
- ✅ 提前发现潜在 bug
- ✅ 提高代码可读性
- ✅ 降低代码维护成本
- ✅ 便于团队协作

---

**更新时间**：2025-10-24
**优先级**：HIGH
**预计工作量**：4-6小时

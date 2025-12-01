<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\service\TemplatePackageService;

/**
 * 模板包安装命令
 * 用于安装、创建、更新模板包
 */
class TemplatePackageInstall extends Command
{
    protected function configure()
    {
        $this->setName('template:install')
            ->addArgument('action', Argument::REQUIRED, '操作：install|create|update')
            ->addArgument('code', Argument::REQUIRED, '模板包代码')
            ->addOption('name', null, Option::VALUE_OPTIONAL, '模板包名称')
            ->addOption('author', null, Option::VALUE_OPTIONAL, '作者')
            ->addOption('pkg-version', null, Option::VALUE_OPTIONAL, '版本号', '1.0.0')
            ->addOption('description', null, Option::VALUE_OPTIONAL, '描述')
            ->setDescription('模板包管理命令');
    }

    protected function execute(Input $input, Output $output)
    {
        $action = $input->getArgument('action');
        $code = $input->getArgument('code');

        $service = new TemplatePackageService();

        try {
            switch ($action) {
                case 'install':
                    // 从现有文件安装模板包
                    $output->writeln("正在安装模板包: {$code}");
                    $result = $service->installPackage($code);
                    if ($result) {
                        $output->writeln("<info>✓ 模板包安装成功！</info>");
                    }
                    break;

                case 'create':
                    // 创建新模板包
                    $packageData = [
                        'name' => $input->getOption('name') ?: $code,
                        'code' => $code,
                        'version' => $input->getOption('pkg-version'),
                        'author' => $input->getOption('author') ?: 'Admin',
                        'description' => $input->getOption('description') ?: '模板包描述',
                        'is_system' => 0,
                        'is_global' => 1,
                        'status' => 1
                    ];

                    // 默认模板列表
                    $templates = [
                        [
                            'name' => '布局模板',
                            'type' => 'layout',
                            'file' => 'layout.html',
                            'description' => '基础布局模板'
                        ],
                        [
                            'name' => '首页模板',
                            'type' => 'index',
                            'file' => 'index.html',
                            'description' => '网站首页'
                        ],
                        [
                            'name' => '分类页模板',
                            'type' => 'category',
                            'file' => 'category.html',
                            'description' => '分类列表页'
                        ],
                        [
                            'name' => '文章页模板',
                            'type' => 'article',
                            'file' => 'article.html',
                            'description' => '文章详情页'
                        ],
                        [
                            'name' => '单页模板',
                            'type' => 'page',
                            'file' => 'page.html',
                            'description' => '单页面'
                        ],
                        [
                            'name' => '搜索页模板',
                            'type' => 'search',
                            'file' => 'search.html',
                            'description' => '搜索结果页'
                        ],
                        [
                            'name' => '标签页模板',
                            'type' => 'tag',
                            'file' => 'tag.html',
                            'description' => '标签页'
                        ]
                    ];

                    $output->writeln("正在创建模板包: {$code}");
                    $package = $service->createPackage($packageData, $templates);
                    $output->writeln("<info>✓ 模板包创建成功！包ID: {$package->id}</info>");
                    $output->writeln("模板包目录: templates/{$code}/");
                    break;

                case 'update':
                    // 更新现有模板包配置
                    $output->writeln("正在更新模板包: {$code}");
                    $result = $service->updateExistingPackage($code);
                    if ($result) {
                        $output->writeln("<info>✓ 模板包更新成功！</info>");
                    }
                    break;

                default:
                    $output->writeln("<error>未知操作: {$action}</error>");
                    $output->writeln("可用操作: install, create, update");
                    return 1;
            }

            return 0;

        } catch (\Exception $e) {
            $output->writeln("<error>错误: {$e->getMessage()}</error>");
            return 1;
        }
    }
}
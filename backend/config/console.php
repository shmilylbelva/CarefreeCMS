<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'cron:run' => \app\command\CronRun::class,
        'build:static' => \app\command\BuildStatic::class,
        'member:level-upgrade' => \app\command\MemberLevelUpgrade::class,
        'media:migrate' => \app\command\MediaMigrate::class,
        'template:install' => \app\command\TemplatePackageInstall::class,
    ],
];

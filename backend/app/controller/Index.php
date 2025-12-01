<?php

namespace app\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return '<style>*{ padding: 0; margin: 0; }</style>欢迎使用逍遥内容管理系统';
    }

    public function hello($name = 'CarefreeCMS')
    {
        return 'hello,' . $name;
    }
}

<?php

// +----------------------------------------------------------------------
// | 多语言设置
// +----------------------------------------------------------------------

use think\facade\Env;

return [
    // 默认语言
    'default_lang'    => Env::get('lang.default_lang', 'en-us'),
    // 允许的语言列表
    'allow_lang_list' => ['en-us', 'zh-cn'],
    // 多语言自动侦测变量名
    'detect_var'      => 'lang',
    // 是否使用Cookie记录
    'use_cookie'      => true,
    // 多语言cookie变量
    'cookie_var'      => 'octopus_lang',
    // 多语言header变量
    'header_var'      => 'octopus-lang',
    // 扩展语言包
    'extend_list'     => [],
    // Accept-Language转义为对应语言包名称
    'accept_language' => [
        'en-US' => 'en-us',
        'zh-hans-cn' => 'zh-cn',
    ],
    // 是否支持语言分组
    'allow_group'     => true,
];

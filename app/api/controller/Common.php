<?php

declare(strict_types=1);

namespace app\api\controller;

use think\Response;
use think\facade\Config;
use think\facade\Lang;
use app\common\controller\GlobalController;

class Common extends GlobalController
{
    protected $error = '';

    public function initialize()
    {
        parent::initialize();
        // load config
        Config::load('api/common/field', 'field');
        Config::load('api/common/reserved', 'reserved');
        Config::load('api/common/response', 'response');
        // load language pack
        foreach (glob(createPath(base_path(), 'api', 'lang', 'field', Lang::getLangSet(), '*') . '.php') as $filename) {
            Lang::load($filename);
        }
        foreach (glob(createPath(base_path(), 'api', 'lang', 'layout', Lang::getLangSet(), '*') . '.php') as $filename) {
            Lang::load($filename);
        }
    }

    protected function json(array $data = [], int $code = 200, array $header = [], array $options = [])
    {
        return Response::create($data, 'json', $code)->header(array_merge(Config::get('response.default_header') ?: [], $header))->options($options);
    }

    protected function success(string $message = '', array $data = [], array $header = [])
    {
        $httpBody = ['success' => true, 'message' => $message, 'data' => $data];
        return $this->json($httpBody, 200, $header);
    }

    protected function error(string $message = '', array $data = [], array $header = [])
    {
        $httpBody = ['success' => false, 'message' => $message, 'data' => $data];
        return $this->json($httpBody, 200, $header);
    }
}

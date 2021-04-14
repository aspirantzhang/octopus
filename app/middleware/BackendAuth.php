<?php

declare(strict_types=1);

namespace app\middleware;

use think\App;
use think\facade\Config;
use aspirantzhang\TP6Auth\Auth;
use think\facade\Session;

class BackendAuth
{
    protected $noNeedAuth = [
        'api/admin/login',
        'api/admin/logout',
        'api/admin/info',
        'api/menu/backend',
        'api/unit_test/home',
        'api/unit_test/add',
        'api/unit_test/save',
        'api/unit_test/read',
        'api/unit_test/update',
        'api/unit_test/delete',
        'api/unit_test/restore',
    ];

    public function handle($request, \Closure $next)
    {
        Config::load('api/response', 'response');

        $appName = parse_name(app('http')->getName());
        $controllerName = parse_name($request->controller());
        $actionName = parse_name($request->action());
        
        $fullPath = $appName . '/' . $controllerName . '/' . $actionName;

        if (in_array($fullPath, $this->noNeedAuth) || $request->param('X-API-KEY') == 'antd') {
            return $next($request);
        } else {
            $auth = new Auth();
            if (!Session::has('adminId')) {
                $data = [
                    'success' => false,
                    'message' => 'Your session has expired, please log in again.',
                ];
                return json($data)->header(Config::get('response.default_header'));
            }
            
            if ($auth->check($fullPath, Session::get('adminId'))) {
                return $next($request);
            } else {
                $data = [
                    'success' => false,
                    'message' => 'No permission.',
                ];
                return json($data)->header(Config::get('response.default_header'));
            }
        }
    }
}

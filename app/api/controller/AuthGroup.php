<?php

declare(strict_types=1);

namespace app\api\controller;

use think\facade\Config;
use app\api\service\AuthGroup as AuthGroupService;

class AuthGroup extends Common
{
    protected $authGroup;

    public function initialize()
    {
        $this->authGroup = new AuthGroupService();
        parent::initialize();
    }

    public function home()
    {
        $result = $this->authGroup->treeListAPI($this->request->only($this->authGroup->getAllowHome()), ['rules']);

        return $this->json(...$result);
    }

    public function add()
    {
        $result = $this->authGroup->addAPI();

        return $this->json(...$result);
    }

    public function save()
    {
        $result = $this->authGroup->saveAPI($this->request->only($this->authGroup->getAllowSave()), ['rules']);

        return $this->json(...$result);
    }

    public function read(int $id)
    {
        $result = $this->authGroup->readAPI($id, ['rules']);

        return $this->json(...$result);
    }

    public function update(int $id)
    {
        $result = $this->authGroup->updateAPI($id, $this->request->only($this->authGroup->getAllowUpdate()), ['rules']);

        return $this->json(...$result);
    }

    public function delete()
    {
        $result = $this->authGroup->deleteAPI($this->request->param('ids'), $this->request->param('type'));

        return $this->json(...$result);
    }

    public function restore()
    {
        $result = $this->authGroup->restoreAPI($this->request->param('ids'));

        return $this->json(...$result);
    }

    public function i18nRead(int $id)
    {
        $result = $this->authGroup->i18nReadAPI($id);

        return $this->json(...$result);
    }

    public function i18nUpdate(int $id)
    {
        $result = $this->authGroup->i18nUpdateAPI($id, $this->request->only(Config::get('lang.allow_lang_list')));

        return $this->json(...$result);
    }

    public function revisionHome(int $id)
    {
        $result = $this->app->revision->listAPI($this->authGroup->getTableName(), $id, (int)$this->request->param('page') ?: 1);

        return $this->json($result);
    }

    public function revisionRestore(int $id)
    {
        $result = $this->app->revision->restoreAPI(
            $this->authGroup->getTableName(),
            $id,
            (int)$this->request->param('revisionId'),
            $this->authGroup->getRevisionTable()
        );

        return $this->json($result);
    }

    public function revisionRead(int $revisionId)
    {
        $result = $this->app->revision->readAPI((int)$revisionId);

        return $this->json($result);
    }
}

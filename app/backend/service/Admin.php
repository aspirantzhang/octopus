<?php

declare(strict_types=1);

namespace app\backend\service;

use app\backend\logic\Admin as AdminLogic;

class Admin extends AdminLogic
{
    public function listAPI($requestParams)
    {
        $data = $this->getListData($requestParams)->toArray();

        if ($data) {
            $layout = $this->buildList($requestParams, ['groups' => arrayToTree($this->getAllGroups())])->toArray();

            $layout['dataSource'] = $data['dataSource'];
            $layout['meta'] = $data['pagination'];

            return resSuccess('', $layout);
        } else {
            return resError('Get list failed.');
        }
    }

    public function addAPI()
    {
        $page = $this->buildAdd(['groups' => arrayToTree($this->getAllGroups())])->toArray();
        
        if ($page) {
            return resSuccess('', $page);
        } else {
            return resError('Get page failed.');
        }
    }

    public function saveAPI($data)
    {
        $result = $this->saveNew($data);
        if ($result) {
            return resSuccess('Add successfully.');
        } else {
            return resError($this->error);
        }
    }

    public function readAPI($id)
    {
        $admin = $this->where('id', $id)->with(['groups' => function ($query) {
            $query->where('auth_group.status', 1)->visible(['id']);
        }])->visible($this->allowRead)->find();

        if ($admin) {
            $admin = $admin->hidden(['groups.pivot'])->toArray();
            $admin['groups'] = extractFromAssocToIndexed($admin['groups'], 'id');

            $layout = $this->buildEdit($id, ['groups' => arrayToTree($this->getAllGroups())])->toArray();
            $layout['dataSource'] = $admin;

            return resSuccess('', $layout);
        } else {
            return resError('Admin not found.');
        }
    }


    public function updateAPI($id, $data)
    {
        $admin = $this->where('id', $id)->find();
        if ($admin) {
            $admin->startTrans();
            try {
                $admin->groups()->detach();
                if (count($data['groups'])) {
                    $admin->groups()->attach($data['groups']);
                }
                $admin->allowField($this->allowUpdate)->save($data);
                $admin->commit();
                return resSuccess('Update successfully.');
            } catch (\Exception $e) {
                $admin->rollback();
                return resError('Update failed.');
            }
        } else {
            return resError('Admin not found.');
        }
    }

    public function deleteAPI($id)
    {
        $admin = $this->find($id);
        if ($admin) {
            if ($admin->delete()) {
                return resSuccess('Delete completed successfully.');
            } else {
                return resError('Delete failed.');
            }
        } else {
            return resError('Admin not found.');
        }
    }

    public function batchDeleteAPI($idArray)
    {
        if (count($idArray)) {
            $result = $this->whereIn('id', $idArray)->select()->delete();
            if ($result) {
                return resSuccess('Delete completed successfully.');
            } else {
                return resError('Delete failed.');
            }
        } else {
            return resError('Nothing to do.');
        }
    }

    public function loginAPI($data)
    {
        $result = $this->checkPassword($data);
        if (-1 === $result) {
            return ['status' => 'error', 'type' => 'account', 'currentAuthority' => 'guest'];
        } elseif (false == $result) {
            return ['status' => 'error', 'type' => 'account', 'currentAuthority' => 'guest'];
        } else {
            return ['status' => 'ok', 'type' => 'account', 'currentAuthority' => 'admin'];
        }
    }
}

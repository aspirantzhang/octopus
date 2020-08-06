<?php

declare(strict_types=1);

namespace app\backend\service;

use app\backend\logic\AuthGroup as AuthGroupLogic;

class AuthGroup extends AuthGroupLogic
{
    public function listApi($params)
    {
        $page = $this->buildList($params)->toArray();
        $data = $this->getListData($params)->toArray();

        if ($data) {
            $result = $page;
            $result['dataSource'] = $data['dataSource'];
            $result['meta'] = $data['pagination'];
            return resSuccess('', $result);
        } else {
            return resError('Get list failed.');
        }
    }

    public function treeListApi($params)
    {
        $page = $this->buildList($params)->toArray();
        $data = $this->getTreeListData($params);

        if ($data) {
            $result = $page;
            $result['dataSource'] = $data;
            $result['meta'] = [
                'total' => 0,
                'per_page' => 10,
                'page' => 1,
            ];
            return resSuccess('', $result);
        } else {
            return resError('Get list failed.');
        }
    }

    public function addApi()
    {
        $page = $this->buildAdd()->toArray();
        if ($page) {
            return resSuccess('', $page);
        } else {
            return resError('Get page failed.');
        }
    }

    public function saveApi($data)
    {
        
        $result = $this->saveNew($data);
        if ($result) {
            return resSuccess('Add successfully.');
        } else {
            return resError($this->error);
        }
    }

    public function readApi($id)
    {
        $group = $this->where('id', $id)->find();
        if ($group) {
            $list = $this->buildInner($id)->toArray();
            $data = $group->visible($this->allowRead)->toArray();

            $result = $list;
            $result['dataSource'] = $data;

            return resSuccess('', $result);
        } else {
            return resError('Group not found.');
        }
    }

    public function updateApi($id, $data)
    {
        $admin = $this->where('id', $id)->find();
        if ($admin) {
            if ($admin->allowField($this->allowUpdate)->save($data)) {
                return resSuccess('Update successfully.');
            } else {
                return resError('Update failed.');
            }
        } else {
            return resError('Admin not found.');
        }
    }

    public function deleteApi($id)
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
}

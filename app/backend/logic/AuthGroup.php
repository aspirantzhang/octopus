<?php
declare (strict_types = 1);

namespace app\backend\logic;

use app\backend\model\AuthGroup as AuthGroupModel;
use BlueM\Tree;
use BlueM\Tree\Serializer\HierarchicalTreeJsonSerializer;

class AuthGroup extends AuthGroupModel
{

    protected function getNormalList($data)
    {
        $search = getSearchParam($data, $this->allowSearch);
        $sort = getSortParam($data, $this->allowSort);
        $perPage = getPerPageParam($data);

        return $this->withSearch(array_keys($search), $search)
                    ->order($sort['name'], $sort['order'])
                    ->visible($this->allowList)
                    ->paginate($perPage);
    }

    public function saveNew($data)
    {
        $ifExists = $this->withTrashed()->where('name', $data['name'])->find();
        if ($ifExists) {
            $this->error = 'Sorry, that name already exists.';
            return -1;
        }
        $result = $this->allowField($this->allowSave)->save($data);
        if ($result) {
            return $this->getData('id');
        } else {
            $this->error = 'Save failed.';
            return 0;
        }
    }

    protected function getTreeList($data)
    {
        $search = getSearchParam($data, $this->allowSearch);
        $sort = getSortParam($data, $this->allowSort);

        $data = $this->withSearch(array_keys($search), $search)
                    ->order($sort['name'], $sort['order'])
                    ->visible($this->allowTree)
                    ->select();

        $serializer = new HierarchicalTreeJsonSerializer();

        $tree = new Tree($data->toArray(), ['rootId' => 0, 'id' => 'id', 'parent' => 'parent_id', 'jsonSerializer' => $serializer]);

        return $tree;
    }



}

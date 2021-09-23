<?php

declare(strict_types=1);

namespace app\api\model;

use app\api\service\AuthGroup as AuthGroupService;
use aspirantzhang\octopusPageBuilder\Builder;

class AuthRule extends Common
{
    protected $readonly = ['id'];
    protected $titleField = 'rule_title';
    protected $revisionTable = [];

    protected function setAddonData($params = [])
    {
        return [
            'parent_id' => $this->treeDataAPI([], [], $params['id'] ?? 0)
        ];
    }

    // Relation
    public function groups()
    {
        return $this->belongsToMany(AuthGroupService::class, 'auth_group_rule', 'group_id', 'rule_id');
    }

    // Accessor

    // Mutator

    // Searcher
}

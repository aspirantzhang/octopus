<?php

return [
    'titleField' => 'group_title',
    'uniqueValue' => ['group_title'],
    'ignoreFilter' => [],
    'allowHome' => ['parent_id', 'group_title', 'rules'],
    'allowRead' => ['parent_id', 'group_title', 'rules'],
    'allowSave' => ['parent_id', 'group_title', 'rules'],
    'allowUpdate' => ['parent_id', 'group_title', 'rules'],
    'allowTranslate' => ['group_title'],
    'revisionTable' => ['auth_group_rule' => 'group_id'],
];

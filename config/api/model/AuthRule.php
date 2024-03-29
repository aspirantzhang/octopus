<?php

return [
    'titleField' => 'rule_title',
    'uniqueValue' => [],
    'ignoreFilter' => [],
    'allowHome' => ['parent_id', 'rule_path', 'rule_title', 'type', 'condition'],
    'allowRead' => ['parent_id', 'rule_path', 'rule_title', 'type', 'condition'],
    'allowSave' => ['parent_id', 'rule_path', 'rule_title', 'type', 'condition'],
    'allowUpdate' => ['parent_id', 'rule_path', 'rule_title', 'type', 'condition'],
    'allowTranslate' => ['rule_title'],
    'revisionTable' => [],
];

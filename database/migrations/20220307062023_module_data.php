<?php

declare(strict_types=1);

namespace DbMigrations;

use Phinx\Migration\AbstractMigration;

final class ModuleData extends AbstractMigration
{
    public function up()
    {
        $currentTime = date('Y-m-d H:i:s');
        $field = [
            'admin_name' => [
                'type' => 'input',
                'unique' => true,
                'filter' => true,
                'translate' => false,
                'position' => 'tab.main',
                'order' => 0,
                'allow' => [
                    'browse' => true,
                    'read' => true,
                    'add' => true,
                    'edit' => true,
                ],
                'validate' => [
                    'required' => true,
                    'length' => [
                        'min' => 0,
                        'max' => 255
                    ]
                ]
            ],
            'password' => [
                'type' => 'password',
                'unique' => false,
                'filter' => true,
                'translate' => false,
                'position' => 'tab.main',
                'order' => 0,
                'hideInColumn' => true,
                'allow' => [
                    'browse' => false,
                    'read' => true,
                    'add' => true,
                    'edit' => true,
                ],
                'validate' => [
                    'required' => true,
                    'length' => [
                        'min' => 0,
                        'max' => 255
                    ],
                ]
            ],
            'display_name' => [
                'type' => 'input',
                'unique' => false,
                'filter' => true,
                'translate' => true,
                'position' => 'tab.main',
                'order' => 0,
                'allow' => [
                    'browse' => true,
                    'read' => true,
                    'add' => true,
                    'edit' => true,
                ],
                'validate' => [
                    'length' => [
                        'min' => 0,
                        'max' => 255
                    ]
                ]
            ],
            'comment' => [
                'type' => 'textarea',
                'unique' => false,
                'filter' => true,
                'translate' => true,
                'order' => 0,
                'position' => 'sidebar.main',
                'hideInColumn' => true,
                'allow' => [
                    'browse' => true,
                    'read' => true,
                    'add' => true,
                    'edit' => true,
                ],
                'validate' => [
                    'length' => [
                        'min' => 0,
                        'max' => 255
                    ]
                ]
            ],
        ];
        $mainTableRows = [
            [
                'id' => 1,
                'table_name' => 'admin',
                'field' => json_encode($field),
                'layout' => '',
                'setting' => '',
                'create_time' => $currentTime,
                'update_time' => $currentTime,
            ],
        ];
        $this->table('module')->insert($mainTableRows)->save();
        $i18nTableRows = [
            [
                'original_id' => 1,
                'lang_code' => 'en-us',
                'module_title' => 'Admin',
                'translate_time' => $currentTime,
            ],
            [
                'original_id' => 1,
                'lang_code' => 'zh-cn',
                'module_title' => '管理员',
                'translate_time' => $currentTime,
            ]
        ];
        $this->table('module_i18n')->insert($i18nTableRows)->save();
    }

    public function down()
    {
        $this->execute('DELETE FROM module');
    }
}
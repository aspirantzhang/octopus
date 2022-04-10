<?php

declare(strict_types=1);

namespace tests\app\core\Layout;

use app\core\domain\Layout\ListLayout;
use Mockery as m;

class ListLayoutTest extends \tests\TestCase
{
    protected function setUp(): void
    {
        $model = m::mock('app\core\CoreModel');
        $this->class = new ListLayout($model);
    }

    public function testParseDataSourceWithValidArray()
    {
        $data = [
            'dataSource' => [
                ['admin_name' => 'zhang1'],
                ['admin_name' => 'zhang2'],
            ],
            'pagination' => [
                'total' => 2,
                'per_page' => 10,
                'page' => 1,
            ],
        ];
        $this->class->withData($data);
        $this->getReflectMethod('parseDataSource');
        $this->assertEqualsCanonicalizing($data['dataSource'], $this->getPropertyValue('dataSource'));
        $this->assertEqualsCanonicalizing($data['pagination'], $this->getPropertyValue('meta'));
    }

    public function testParseDataSourceWithEmptyArray()
    {
        $data = [];
        $this->class->withData($data);
        $this->getReflectMethod('parseDataSource');
        $this->assertNull($this->getPropertyValue('dataSource'));
        $this->assertNull($this->getPropertyValue('meta'));
    }

    public function testParseTableColumnWithValidArray()
    {
        $modelField = [
            [
                'name' => 'admin_name',
                'type' => 'input',
                'unique' => true,
                'filter' => true,
                'translate' => false,
                'position' => 'tab.main',
                'order' => 1,
            ],
            [
                'name' => 'comment',
                'type' => 'textarea',
                'unique' => false,
                'filter' => true,
                'translate' => true,
                'order' => 99,
                'position' => 'sidebar.main',
                'hideInColumn' => true,
            ],
        ];
        $model = m::mock('app\core\CoreModel');
        $model->shouldReceive('getModuleField')
            ->once()
            ->andReturn($modelField);
        $this->class = new ListLayout($model);
        $this->getReflectMethod('parseTableColumn');
        $expect = [
            [
                'name' => 'admin_name',
                'type' => 'input',
                'order' => 1,
            ],
            [
                'name' => 'comment',
                'type' => 'textarea',
                'order' => 99,
            ],
        ];
        $this->assertEqualsCanonicalizing($expect, $this->getPropertyValue('tableColumn'));
    }

    public function testParseOperationWithValidArray()
    {
        $modelPosition = [
            [
                'name' => 'button1',
                'position' => 'list.tableToolbar',
            ],
            [
                'name' => 'button2',
                'position' => 'list.batchToolbar',
            ],
            [
                'name' => 'button3',
                'position' => 'other',
            ],
        ];
        $model = m::mock('app\core\CoreModel');
        $model->shouldReceive('getModuleOperation')
            ->once()
            ->andReturn($modelPosition);
        $this->class = new ListLayout($model);
        $this->getReflectMethod('parseOperation');

        $this->assertEqualsCanonicalizing([[
            'name' => 'button1',
            'position' => 'list.tableToolbar',
        ]], $this->getPropertyValue('tableToolbar'));
        $this->assertEqualsCanonicalizing([[
            'name' => 'button2',
            'position' => 'list.batchToolbar',
        ]], $this->getPropertyValue('batchToolbar'));
    }

    public function testJsonSerializeInterface()
    {
        $modelField = [
            [
                'name' => 'admin_name',
                'type' => 'input',
                'unique' => true,
                'filter' => true,
                'translate' => false,
                'position' => 'tab.main',
                'order' => 1,
            ],
            [
                'name' => 'comment',
                'type' => 'textarea',
                'unique' => false,
                'filter' => true,
                'translate' => true,
                'order' => 99,
                'position' => 'sidebar.main',
                'hideInColumn' => true,
            ],
        ];
        $modelPosition = [
            [
                'name' => 'button1',
                'position' => 'list.tableToolbar',
            ],
            [
                'name' => 'button2',
                'position' => 'list.batchToolbar',
            ],
            [
                'name' => 'button3',
                'position' => 'other',
            ],
        ];
        $model = m::mock('app\core\CoreModel');
        $model->shouldReceive('getModuleField')
            ->once()
            ->andReturn($modelField);
        $model->shouldReceive('getModuleOperation')
            ->once()
            ->andReturn($modelPosition);
        $this->class = new ListLayout($model);
        $data = [
            'dataSource' => [
                ['admin_name' => 'zhang1'],
                ['admin_name' => 'zhang2'],
            ],
            'pagination' => [
                'total' => 2,
                'per_page' => 10,
                'page' => 1,
            ],
        ];
        $expect = [
            'page' => [],
            'layout' => [
                'tableColumn' => [
                    [
                        'name' => 'admin_name',
                        'type' => 'input',
                        'order' => 1,
                    ],
                    [
                        'name' => 'comment',
                        'type' => 'textarea',
                        'order' => 99,
                    ],
                ],
                'tableToolBar' => [
                    [
                        'name' => 'button1',
                        'position' => 'list.tableToolbar',
                    ],
                ],
                'batchToolBar' => [
                    [
                        'name' => 'button2',
                        'position' => 'list.batchToolbar',
                    ],
                ],
            ],
            'dataSource' => [
                ['admin_name' => 'zhang1'],
                ['admin_name' => 'zhang2'],
            ],
            'meta' => [
                'total' => 2,
                'per_page' => 10,
                'page' => 1,
            ],
        ];
        $this->assertEquals(json_encode($expect), json_encode($this->class->withData($data)));
    }
}

<?php

declare(strict_types=1);

namespace app\api\logic;

use app\api\view\Model as ModelView;
use think\facade\Db;
use think\facade\Config;
use think\Exception;

class Model extends ModelView
{
    protected function existMysqlReservedKeywords(array $fieldNames)
    {
        $mysqlReservedKeywords = include createPath(root_path(), 'config', 'api', 'common', 'mysql_reserved') . '.php';
        $intersect = array_intersect($mysqlReservedKeywords, $fieldNames);
        if (empty($intersect)) {
            return false;
        }
        $this->error = __('mysql reserved keyword', ['keyword' => implode(',', $intersect)]);
        return true;
    }

    protected function existReservedFieldNames(array $fieldNames)
    {
        $reservedFieldNames = Config::get('reserved.reserved_field');
        $intersect = array_intersect($reservedFieldNames, $fieldNames);
        if (empty($intersect)) {
            return false;
        }
        $this->error = __('reserved field name', ['fieldName' => implode(',', $intersect)]);
        return true;
    }

    protected function isReservedTable(string $tableName): bool
    {
        if (in_array($tableName, Config::get('reserved.reserved_table'))) {
            $this->error = __('reserved table name');
            return true;
        }
        return false;
    }

    private function tableExists(string $tableName): bool
    {
        try {
            Db::query("select 1 from `$tableName` LIMIT 1");
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    protected function tableAlreadyExist(string $tableName): bool
    {
        if ($this->tableExists($tableName)) {
            $this->error = __('table already exists', ['tableName' => $tableName]);
            return true;
        }
        return false;
    }

    protected function tableNotExist(string $tableName): bool
    {
        if (!$this->tableExists($tableName)) {
            $this->error = __('table not exist', ['tableName' => $tableName]);
            return true;
        }
        return false;
    }

    protected function extractTranslateFields(array $allFields): array
    {
        $result = [];
        foreach ($allFields as $field) {
            // only input/textarea/textEditor can be translated
            if (
                isset($field['type']) &&
                ($field['type'] === 'input' || $field['type'] === 'textarea' || $field['type'] === 'textEditor') &&
                ($field['allowTranslate'] ?? false)
            ) {
                // cannot be marked as 'editDisabled' and 'translate' ATST
                if (
                    isset($field['settings']['display']) &&
                    in_array('editDisabled', $field['settings']['display'])
                ) {
                    throw new Exception(__('edit disabled fields cannot set as translate', ['fieldName' => $field['name']]));
                }
                array_push($result, $field['name']);
            }
        }
        return $result;
    }

    protected function extractAllFields($designData)
    {
        $allFields = [];
        foreach ($designData['tabs'] as $tab) {
            $allFields = [...$allFields, ...$tab];
        }
        foreach ($designData['sidebars'] as $sidebar) {
            $allFields = [...$allFields, ...$sidebar];
        }
        return $allFields;
    }
}

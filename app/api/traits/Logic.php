<?php

declare(strict_types=1);

namespace app\api\traits;

use think\facade\Db;
use think\facade\Lang;
use think\facade\Config;
use think\helper\Str;
use think\Exception;

trait Logic
{
    public function addI18nStatus(array $rawDataSource)
    {
        $dataSource = [];

        // add lang value null for all element
        $languages = Config::get('lang.allow_lang_list');
        foreach ($rawDataSource as $record) {
            foreach ($languages as $langCode) {
                $record['i18n'][$langCode] = null;
            }
            $dataSource[] = $record;
        }

        $ids = array_column($dataSource, 'id');
        $idsFlipped = array_flip($ids);
        $i18nData = Db::table($this->getLangTableName())->whereIn('original_id', implode(',', $ids))->select()->toArray();
        foreach ($i18nData as $i18n) {
            $originalIdIndex = $idsFlipped[$i18n['original_id']];
            // $record['i18n']['en-us'] = '2021-06-18T14:33:38+08:00';
            $translateTime = $i18n['translate_time'] ? convertTime($i18n['translate_time'], 'Y-m-d\TH:i:sP') : null;
            $dataSource[$originalIdIndex]['i18n'][$i18n['lang_code']] = $translateTime;
        }

        return $dataSource;
    }

    private function getSortParam(array $data, array $allowSort): array
    {
        $sort = [
            'name' => 'id',
            'order' => 'desc',
        ];

        if (isset($data['sort'])) {
            // check if exist in allowed list
            $sort['name'] = in_array($data['sort'], $allowSort) ? $data['sort'] : 'id';
        }
        if (isset($data['order'])) {
            $sort['order'] = ('asc' == $data['order']) ? 'asc' : 'desc';
        }

        return $sort;
    }

    private function getListParams(array $params, array $allowHome, array $allowSort): array
    {
        $result = [];
        $result['trash'] = $params['trash'] ?? 'withoutTrashed';
        $result['per_page'] = $params['per_page'] ?? 10;
        $result['visible'] = array_diff($allowHome, ['sort', 'order', 'page', 'per_page', 'trash']);
        $result['search']['values'] = array_intersect_key($params, array_flip($result['visible']));
        $result['search']['keys'] = array_keys($result['search']['values']);
        $result['sort'] = $this->getSortParam($params, $allowSort);
        return $result;
    }

    protected function getListData(array $parameters = [], array $withRelation = [], string $type = 'normal'): array
    {
        $params = $this->getListParams($parameters, $this->getAllowHome(), $this->getAllowSort());
        $result = $this;

        if ($params['trash'] !== 'withoutTrashed') {
            $result = $result->{$params['trash'] == 'onlyTrashed' ? 'onlyTrashed' : 'withTrashed'}();
        }

        $result = $this->withI18n($result->with($withRelation))
            ->withSearch($params['search']['keys'], $params['search']['values'])
            ->order($params['sort']['name'], $params['sort']['order'])
            ->visible($params['visible']);

        if ($type === 'paginated') {
            return $result->paginate($params['per_page'])->toArray();
        }
        return $result->select()->toArray();
    }

    protected function checkUniqueValue(array $data, int $originalId = null): bool
    {
        $uniqueValue = $this->getUniqueField();
        foreach ($uniqueValue as $field) {
            if (isset($data[$field]) && $this->ifExists($field, $data[$field], $originalId)) {
                $this->error = __('field value already exists', ['fieldName' => Lang::get($this->getTableName() . '.' . $field)]);
                return false;
            }
        }
        return true;
    }

    /**
     * Check if a value already exists in the database
     * @return bool
     */
    protected function ifExists(string $fieldName, $value, ?int $originalId)
    {
        if ($this->isTranslateField($fieldName)) {
            if ($originalId) {
                return (bool)Db::name($this->getLangTableName())->where($fieldName, $value)->where('original_id', '<>', $originalId)->find();
            }
            return (bool)Db::name($this->getLangTableName())->where($fieldName, $value)->find();
        }
        if ($originalId) {
            return (bool)$this->withTrashed()->where($fieldName, $value)->where('id', '<>', $originalId)->find();
        }
        return (bool)$this->withTrashed()->where($fieldName, $value)->find();
    }

    protected function clearParentId(int $id)
    {
        Db::table($this->getTableName())
            ->where('id', $id)
            ->update(['parent_id' => 0]);
        return true;
    }

    protected function handleMutator(array $fieldsData)
    {
        foreach ($fieldsData as $fieldName => $fieldValue) {
            $mutator = 'set' . Str::studly($fieldName) . 'Attr';
            if (method_exists($this, $mutator)) {
                $fieldsData[$fieldName] = $this->$mutator($fieldValue);
            }
        }
        return $fieldsData;
    }

    protected function saveI18nData(array $rawData, int $originalId, string $langCode, $translateTime = null)
    {
        // keep only allowed
        $filteredData = array_intersect_key($rawData, array_flip($this->getAllowTranslate()));
        // sync translate time if specific
        if ($translateTime) {
            $filteredData['translate_time'] =  $translateTime;
        }
        $data = array_merge($filteredData, [
            'original_id' => $originalId,
            'lang_code' => $langCode
        ]);
        try {
            Db::name($this->getLangTableName())->save($data);
        } catch (Exception $e) {
            throw new Exception(__('failed to store i18n data'));
        }
    }

    protected function updateI18nData(
        array $rawData,
        int $originalId,
        string $langCode,
        string $currentTime = null,
        bool $forceSetTime = false
    ) {
        $filteredData = array_intersect_key($rawData, array_flip($this->getAllowTranslate()));

        $rawData['complete'] = isset($rawData['complete']) ? (bool)$rawData['complete'] : false;

        if ($rawData['complete'] || $forceSetTime) {
            $filteredData['translate_time'] =  $currentTime;
        }

        $record = Db::name($this->getLangTableName())
            ->where('original_id', $originalId)
            ->where('lang_code', $langCode)
            ->find();

        if ($record) {
            // update
            try {
                Db::name($this->getLangTableName())
                    ->where('original_id', $originalId)
                    ->where('lang_code', $langCode)
                    ->update($filteredData);
                return;
            } catch (Exception $e) {
                throw new Exception(__('failed to store i18n data'));
            }
        }
        // add new
        try {
            if (isset($rawData['complete']) && (bool)$rawData['complete'] === true) {
                $this->saveI18nData($rawData, $originalId, $langCode, $currentTime);
                return;
            }
            $this->saveI18nData($rawData, $originalId, $langCode);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}

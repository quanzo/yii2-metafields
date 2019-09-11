<?php
namespace x51\yii2\modules\metafields;

use \x51\yii2\classes\fields\models\FieldsModel;
use \x51\yii2\modules\metafields\models\Record;
use \Yii;

/**
 * posts module definition class
 */
class Module extends \yii\base\Module
{
    const EVENT_BEFORE_SAVE = 'beforeSetFields';
    const EVENT_AFTER_SAVE_SUCCESS = 'afterSetFieldsSuccess';
    const EVENT_AFTER_SAVE_FAIL = 'afterSetFieldsFail';

    public $type = '';
    public $fields;
    public $rules = [];

    public $formViewFile;

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = '\x51\yii2\modules\metafields\controllers';

    /**
     * {@inheritdoc}
     */
    //public $defaultController = 'menu';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (!isset($this->module->i18n->translations['module/metafields'])) {
            $this->module->i18n->translations['module/metafields'] = [
                'class' => '\yii\i18n\PhpMessageSource',
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'module/metafields' => 'messages.php',
                ],
            ];
        }
        if (empty($this->formViewFile)) {
            $this->formViewFile = $this->getViewPath() . '/form.php';
        }
    } // end init()

    /**
     * Возвращает все записи соспоставленные tid
     *
     * @param int $tid
     * @return array
     */
    public function getRecords($tid)
    {
        return Record::find()->where([
            'tid' => intval($tid),
            'type' => $this->type,
        ])->all();
    }

    /**
     * Устанавливает (сохраняет) данные, заданные в виде ассоциативного массива
     *
     * @param integer $tid
     * @param array $arData
     * @return boolean
     */
    public function setFieldsByArray($tid, array $arData)
    {
        $modelMeta = Record::className();
        $changeMetaCounter = 0;
        $insertMetaCounter = 0;
        $delMetaCounter = 0;

        $beforeEvent = new \x51\yii2\modules\metafields\events\BeforeSaveEvent($this, $tid, $arData);
        $this->trigger(self::EVENT_BEFORE_SAVE, $beforeEvent);
        if (!$beforeEvent->isValid) {
            return false;
        }
        $tid = $beforeEvent->tid;
        $arData = $beforeEvent->arData;

        $transaction = Record::getDb()->beginTransaction();
        try {
            $records = $this->getRecords($tid); // записи, которые правим

// 1 - сохранение полей по совпадающему ключу
            foreach ($records as $i => $record) {
                if (isset($arData[$record->meta_key])) {
                    if ($arData[$record->meta_key] != $record->meta_value) {
                        $record->meta_value = strval($arData[$record->meta_key]);
                        $record->save();
                        $changeMetaCounter++;
                    }
                    unset($records[$i], $arData[$record->meta_key]);
                }
            }
            if ($records) {
                $records = array_values($records);
            }
// 2
            $indexCM = 0;
            foreach ($arData as $meta_key => $meta_value) {
                if (isset($records[$indexCM])) {
                    $meta = $records[$indexCM];
                    $meta->meta_key = strval($meta_key);
                    $meta->meta_value = strval($meta_value);
                    $meta->save();
                    unset($records[$indexCM]);
                    $changeMetaCounter++;
                    $indexCM++;
                } else {
                    $newMeta = new $modelMeta();
                    $newMeta->tid = $tid;
                    $newMeta->type = $this->type;
                    $newMeta->meta_value = strval($meta_value);
                    $newMeta->meta_key = strval($meta_key);
                    Yii::debug($newMeta->attributes);

                    $newMeta->save();
                    $insertMetaCounter++;
                }
            }
// 3
            if (!empty($records)) {
                foreach ($records as $meta) {
                    $meta->delete();
                    $delMetaCounter++;
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            $afterEvent = new \x51\yii2\modules\metafields\events\AfterSaveEvent($this, $tid, $arData, false);
            $this->trigger(self::EVENT_AFTER_SAVE_FAIL, $afterEvent);

            return false;
        }

        $afterEvent = new \x51\yii2\modules\metafields\events\AfterSaveEvent($this, $tid, $arData, true);
        $this->trigger(self::EVENT_AFTER_SAVE_SUCCESS, $afterEvent);

        return true;
    } // end setFieldsByArray

    /**
     * Получить все данные по tid в виде модели. Данных нет - возвращен null будет
     *
     * @param integer $tid
     * @return \x51\yii2\classes\fields\models\FieldsModel
     */
    public function getFields($tid)
    {
        $records = $this->getRecords($tid);
        if (!empty($records)) {
            // заполнение модели
            $model = new FieldsModel($this->fields, $this->rules);
            foreach ($records as $i => $record) {
                $key = $record->meta_key;
                $field = $model->getField($key);
                if ($field) {
                    $field->setValueSave($record->meta_value);
                    $model->$key = $field->getValue();
                }
            }
            $module = $this;
            $model->funcSave = function ($arData) use ($tid, $module) {
                return $module->setFieldsByArray($tid, $arData);
            };
            return $model;
        }
        return null;
    }

    /**
     * Возвращает поле данных в виде объекта, который не привязан к модели (к данным в базе). И не может быть сохранен.
     *
     * @param [type] $fname
     * @return void
     */
    public function getEmptyField($fname)
    {
        if (!empty($this->fields[$fname])) {
            $fconf = $this->fields[$fname];
            return Yii::createObject($fconf);
        }
        return null;
    }

    /**
     * Пустая модель (со значениями по умолчанию) с привязкой к определенному tid
     *
     * @param integer $tid
     * @return \x51\yii2\classes\fields\models\FieldsModel
     */
    public function getFieldsBlank($tid)
    {
        $model = new FieldsModel($this->fields, $this->rules);
        $module = $this;
        $model->funcSave = function ($arData) use ($tid, $module) {
            return $module->setFieldsByArray($tid, $arData);
        };
        return $model;
    }

    /**
     * Получить строки данных из вертикальной ьаблицы в виде моделей
     * Доступен фильтр - ключ массив - имя метапараметра, а значение - значение параметра
     * Постраничный вывод
     *
     * @param array $where
     * @param integer $page
     * @param boolean $ppage
     * @param int $total
     * @return array
     */
    public function getModels(array $where = [], $page = 1, $ppage = false, &$total = null)
    {
        $res = [];
        // постраничный вывод
        $pageMode = false;
        if ($ppage > 0) {
            $ppage = intval($ppage);
            $page = intval($page);
            $page = $page > 0 ? intval($page) : 1;
            $pageMode = $ppage > 0;
        }

        // подготовка запроса
        $query = Record::find()->select(['id', 'tid', 'type', 'meta_key', 'meta_value'])->where(['type' => $this->type]);
        $this->expandQueryCondition($query, $where);

        //$query = Record::find()->where($where)->andWhere(['type' => $this->type]);

        if ($pageMode) { // настройка постраничного вывода
            // подсчет общего кол-ва записей для расчета вывода
            /*if ($total) {

            } else {*/
            $total = $query->select(['tid'])->distinct(true)->count();
            //}
            $pages = floor($total / $ppage);
            if ($total - $pages * $ppage > 0) {
                $pages++;
            }
            if ($page > $pages) {
                $page = $pages;
            }

            // настройка запроса
            $query->distinct(false)->select(['id', 'tid', 'type', 'meta_key', 'meta_value'])->limit($ppage)->offset(($page - 1) * $ppage);
        }

        $query->indexBy('id')->orderBy(['tid' => 'ASC']);
        $module = $this;
        // создание моделей на основании записей из бд
        foreach ($query->each() as $record) {
            $tid = $record->tid;
            if (!isset($res[$record->tid])) {
                // модель новая - создадим ее пустую
                $res[$tid] = new FieldsModel($this->fields, $this->rules);
                $res[$tid]->funcSave = function ($arData) use ($tid, $module) {
                    return $module->setFieldsByArray($tid, $arData);
                };
            }
            // наполнение модели на основании записей привязанных к определенному tid
            $key = $record->meta_key;
            $field = $res[$tid]->getField($key);

            if ($field) {
                // в бд - сохраненные данные во внутреннем формате, который может отличаться
                // например массив храниться сериализованным
                $field->setValueSave($record->meta_value);
                // теперь запишем данные в модель
                $res[$tid]->$key = $field->getValue();
            }
        }
        return $res;
    } // end getModels

    /**
     * Почти как getModels, только возвращает массив tid
     *
     * @param array $where
     * @param integer $page
     * @param boolean $ppage
     * @param [type] $total
     * @return void
     */
    public function getTid(array $where = [], $page = 1, $ppage = false, &$total = null)
    {
        $res = [];
// постраничный вывод
        $pageMode = false;
        if ($ppage > 0) {
            $ppage = intval($ppage);
            $page = intval($page);
            $page = $page > 0 ? intval($page) : 1;
            $pageMode = $ppage > 0;
        }

// подготовка запроса
        $query = Record::find()->select(['tid'])->distinct(true)->where(['type' => $this->type]);
        $this->expandQueryCondition($query, $where);
        if ($pageMode) { // настройка постраничного вывода
            $total = $query->select(['tid'])->count();
            $pages = floor($total / $ppage);
            if ($total - $pages * $ppage > 0) {
                $pages++;
            }
            if ($page > $pages) {
                $page = $pages;
            }

            // настройка запроса
            $query->limit($ppage)->offset(($page - 1) * $ppage);
        }
        return $query->orderBy(['tid' => 'ASC'])->column();
    }

    public function getDistinctParam($paramName, array $where = [], $converted = false, callable $filter = null)
    {
        $query = Record::find()->select(['meta_value'])->distinct(true)->where(['type' => $this->type, 'meta_key' => $paramName])->orderBy(['meta_value' => 'ASC']);
        $this->expandQueryCondition($query, $where);
        if ($filter) {
            $funcFilter = $filter;

        } else {
            $funcFilter = function ($paramName, $paramValue) {
                return true;
            };
        }

        if ($converted) {
            $arValues = [];
            $field = $this->getEmptyField($paramName);
            foreach ($query->each() as $arRow) {
                $field->setValueSave($arRow['meta_value']);
                $v = $field->getValue();
                if ($funcFilter($paramName, $v)) {
                    $arValues[] = $v;
                }
            }
            return $arValues;
        } else {
            return $query->column();
        }
    } // end getDistinctParam

    /**
     * Формирует форму для добавления/редактирования записи с определенным $tid и возвращает ее
     * Опции:
     * textSubmitButton
     * success
     * fail
     *
     * @param integer $tid
     * @return void
     */
    public function form($tid, array $options = [], $formViewFile = false)
    {
        $request = Yii::$app->request;
        $model = $this->getFields($tid);
        $ifNew = false;
        if (empty($model)) {
            $model = $this->getFieldsBlank($tid);
            $ifNew = true;
        }

        $params = [
            'form_id' => $this->id . '_' . $tid,
            'submit_value' => $this->id . '_' . $tid,
            'pjax_id' => 'pjax_' . $this->id . '_' . $tid,
            'model' => $model,
            'tid' => $tid,
            'ifNew' => $ifNew,
            'message' => [],
            'status' => 'success',
        ];
        $this->expandFormParams($model, $options, $params);

        if ($request->isPost && $request->validateCsrfToken() && $model->load($request->post(), $params['form_id'])) {
            if ($model->save()) {
                $params['message'] = ['success', $params['text']['success']];
                $params['status'] = 'success';
            } else {
                $params['message'] = ['warning', $params['text']['fail']];
                $params['status'] = 'fail';
            }
        }
        if ($formViewFile) {
            return Yii::$app->view->renderFile($formViewFile, $params);
        } else {
            return Yii::$app->view->renderFile($this->formViewFile, $params);
        }
    } // end form

    /**
     * Форма для добавления новой записи.
     * Опции:
     * textSubmitButton
     *
     * @param boolean|string $formViewFile
     * @return void
     */
    public function formAddUniqueRecord(array $options = [], $formViewFile = false)
    {
        $request = Yii::$app->request;
        $tid = microtime(true) * 10000;
        $model = $this->getFields($tid);
        if (!empty($model)) {
            $tid .= rand(1, 99);
        }
        $ifNew = true;
        $model = $this->getFieldsBlank($tid);

        $params = [
            'form_id' => $this->id,
            'submit_value' => $this->id,
            'pjax_id' => 'pjax_' . $this->id,
            'model' => $model,
            'ifNew' => $ifNew,
            'message' => [],
            'status' => 'success',
        ];
        $this->expandFormParams($model, $options, $params);

        if ($request->isPost && $request->validateCsrfToken() && $model->load($request->post(), $params['form_id'])) {
            if ($model->save()) {
                $params['message'] = ['success', $params['text']['success']];
                $params['status'] = 'success';
            } else {
                $params['message'] = ['warning', $params['text']['fail']];
                $params['status'] = 'fail';
            }
        }
        if ($formViewFile) {
            return Yii::$app->view->renderFile($formViewFile, $params);
        } else {
            return Yii::$app->view->renderFile($this->formViewFile, $params);
        }
    } // end formAddUniqueRecord

    protected function expandFormParams($model, array $options, array &$params)
    {
        $params['text'] = [
            'submitButton' => Yii::t('module/metafields', 'Save'),
            'success' => Yii::t('module/metafields', 'Save success'),
            'fail' => Yii::t('module/metafields', 'Save failed'),
        ];

        foreach (['submitButton', 'success', 'fail'] as $text) {
            $opt_name = 'text' . ucfirst($text);
            if (!empty($options[$opt_name])) {
                $params['text'][$text] = $options[$opt_name];
            }
        }

        if (!empty($options['event']['beforeRender']) && is_callable($options['event']['beforeRender'])) {
            $model->on(FieldsModel::EVENT_BEFORE_RENDER, $options['event']['beforeRender']);
        }
        if (!empty($options['event']['afterRender']) && is_callable($options['event']['afterRender'])) {
            $model->on(FieldsModel::EVENT_AFTER_RENDER, $options['event']['afterRender']);
        }
        if (!empty($options['event']['beforeSave']) && is_callable($options['event']['beforeSave'])) {
            $model->on(FieldsModel::EVENT_BEFORE_SAVE, $options['event']['beforeSave']);
        }

    } // end expandFormParams

    /**
     * Расширяет запрос условиями для поиска
     *
     * @param [type] $query
     * @param array $where
     * @return void
     */
    protected function expandQueryCondition(\x51\yii2\modules\metafields\models\RecordQuery $query, array $where)
    {
        if ($where) {
            $queryPrev = $query;
            foreach ($where as $meta_key => $meta_value) {
                $q = Record::find()->where(['meta_key' => $meta_key, 'meta_value' => $meta_value])->select(['tid']);
                $queryPrev->andWhere(['in', 'tid', $q]);
                $queryPrev = $q;
            }
        }
    } // end expandQueryCondition
} // end class

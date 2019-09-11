metafields модуль для Yii 2
===========================

The module allows you to bind data configured as an array to any identifier.
Edit them in a form and save.

The form for editing or adding is specified in the module configuration.
Implemented using pjax. You can insert into any display with one line of code.

Data is saved in a vertical table. The configuration of the fields is specified
in the module settings. Used by <https://github.com/quanzo/yii2-fields>

Each data set is presented in the database in several rows. Each set has a
specific id, which is set and defines the entire set of fields. As well as the
type that is set during configuration.

\------------------------------------

Модуль позволяет привязать данные, сконфигурированные в виде массива, к любому
идентификатору. Редактировать их в форме и сохранять.

Форма для редактирования или добавления задается в конфигурации модуля.
Реализована с использованием pjax. Можно вставить в любое отображение одной
строчкой кода.

Данные сохраняются в вертикальную таблицу. Конфигурирование полей задается в
настройках модуля. Используется <https://github.com/quanzo/yii2-fields>

Каждый набор данных представлен в БД несколькими строками. Каждый набор имеет
определенный id, который задается и определяет весь набор полей. А также тип,
который задается при конфигурировании.

 

Example config
--------------

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
'modules' => [
 'metafieldstest' => [
     'class' => 'x51\yii2\modules\metafields\Module',
     'type' => 'post',
     'fields' => [
         'title' => [
            'class' => '\x51\yii2\classes\fields\Input',
            'title' => 'Заголовок',
            'name' => 'name_field_title',
            'value' => 'Unknown',
            'options' => [
                'class' => 'form-control',
            ],
            'rules' => [
                ['required'],
            ],
        ],
        'desc' => [
            'class' => '\x51\yii2\classes\fields\Input',
            'title' => 'Пояснение',
            'name' => 'name_field_desc',
            'value' => 'Unknown',
            'options' => [
                'class' => 'form-control',
            ],
            'rules' => [
                ['required'],
            ],
        ],
        'multi' => [
            'class' => '\x51\yii2\classes\fields\MultipleInput',
            'title' => 'Multi',
            'name' => 'multi_field',
            'value' => ['Unknown', 'Unknown', 'Unknown'],
            'count' => 5,
            'options' => [
                'class' => 'form-control',
            ],
            'rules' => [
                ['required'],
            ],
        ],
        'content' => [
            'class' => '\x51\yii2\classes\fields\EditorjsInput',
            'title' => 'Это контент',
            'value' => '',
            'name' => 'content',
            'moduleEditorjs' => 'editorjs',
        ],
    ],
 ],
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

 

### Параметры

`type` - задает тип записи. Используется при сохранении записей.

`fields` - конфигурация полей. Используется для сохранения в бд и для
формирования формы. Каждое поле определяется классом.

 

Как использовать
----------------

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$module = \Yii::$app->getModule('metafieldstest');
echo $module->form($tid);
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

where

\$tid - The unique identifier of the record within its type. For example,
entries are tied to a specific “article”. The post type is set to “article”. The
article has id = 100. Therefore, \$tid = 100.

The type of record is set in the module settings.

If the record exists, it will be edited and its contents will be displayed in
the form.

\------------------------------------

Уникальный идентификатор записи в рамках ее типа. Например, записи привязаны к
определенной статье. Тип записи задан "article". Статья имеет id=100.
Следовательно и \$tid = 100.

Тип записи задается в настройках модуля.

Если запись существует, то она будет редактироваться и в форме будет выведено ее
содержимое.

\------------------------------------

 

 

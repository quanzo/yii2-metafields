<?php

namespace x51\yii2\modules\metafields\models;

use Yii;


class Record extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%metafields_meta}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'tid', 'meta_key'], 'required'],
            [['tid'], 'integer'],
            [['meta_key'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('module/metafields', 'ID'),
            'type' => Yii::t('module/metafields', 'Type'),
            'tid' => Yii::t('module/metafields', 'TID'),
            'meta_key' => Yii::t('module/metafields', 'Key'),
            'meta_value' => Yii::t('module/metafields', 'Value'),            
        ];
    }

    /**
     * {@inheritdoc}
     * @return RecordQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new RecordQuery(get_called_class());
    }    
}

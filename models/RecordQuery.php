<?php

namespace x51\yii2\modules\metafields\models;

/**
 * This is the ActiveQuery class for [[Menu]].
 *
 * @see Menu
 */
class RecordQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Menu[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Menu|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

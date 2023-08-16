<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%cashType}}".
 *
 * @property int $id
 * @property string $name
 *
 * @property Cash[] $cashes
 */
class CashType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cashType}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCashes()
    {
        return $this->hasMany(Cash::className(), ['cashType_id' => 'id']);
    }
}

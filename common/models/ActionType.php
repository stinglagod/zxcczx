<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%actionType}}".
 *
 * @property int $id
 * @property string $name
 * @property string $shortName
 *
 * @property Action[] $actions
 * @property Ostatok[] $ostatoks
 */
class ActionType extends \yii\db\ActiveRecord
{
    const RESERVSOFT=1;
    const RESERVHARD=2;
    const MOVE=3;
    const RENT=4;
    const REPAIRS=5;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%actionType}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 100],
            [['shortName'], 'string', 'max' => 50],
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
            'shortName' => Yii::t('app', 'Short Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActions()
    {
        return $this->hasMany(Action::className(), ['actionType_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOstatoks()
    {
        return $this->hasMany(Ostatok::className(), ['actionType_id' => 'id']);
    }
}

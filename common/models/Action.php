<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%action}}".
 *
 * @property int $id
 * @property string $name
 * @property int $sing
 * @property string $type
 * @property string $shortName
 * @property int $sequence
 * @property int $order
 * @property int $antipod_id
 *
 * @property Movement[] $movements
 */
class Action extends \yii\db\ActiveRecord
{
    const SOFTRENT=3;       //убрали мягкий резерв
    const UNSOFTRENT=4;     //убрали мягкий резерв
    const HARDRESERV=3;
    const UNHARDRESERV=4;
    const ISSUE=5;
    const RETURN=6;
    const TOREPAIR=7;
    const FROMREPAIR=8;
    const PRIHOD=9;
    const UHOD=10;
    const ISSUERENT=11;
    const RETURNRENT=12;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%action}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sing','order','antipod_id'], 'integer'],
            [['type','sequence'], 'string'],
            [['name','shortName'], 'string', 'max' => 100],
            [['antipod_id'], 'exist', 'skipOnError' => true, 'targetClass' => Action::className(), 'targetAttribute' => ['action_id' => 'id']],
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
            'sing' => Yii::t('app', 'Sing'),
            'type' => Yii::t('app', 'Type'),
            'shortName' => Yii::t('app', 'Короткое имя'),
            'sequence' => Yii::t('app', 'Последовательность'),
            'order' => Yii::t('app', 'Порядок'),
            'antipod_id'=> Yii::t('app', 'Антипод'),

        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMovements()
    {
        return $this->hasMany(Movement::className(), ['action_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAntipod()
    {
        return $this->hasOne(Action::className(), ['id' => 'antipod_id']);
    }
}

<?php

namespace common\models;

use rent\entities\Client\Client;
use Yii;

/**
 * This is the model class for table "{{%ostatok}}".
 *
 * @property int $id
 * @property string $dateTime
 * @property int $qty
 * @property int $product_id
 * @property int $movement_id
 * @property int $client_id
 * @property int $actionType_id
 *
 * @property Client $client
 * @property Movement $movement
 * @property Product $product
 * @property ActionType $actionType
 */
class Ostatok extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ostatok}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dateTime'], 'safe'],
            [['qty', 'product_id', 'movement_id', 'client_id','actionType_id'], 'integer'],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['movement_id'], 'exist', 'skipOnError' => true, 'targetClass' => Movement::className(), 'targetAttribute' => ['movement_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['actionType_id'], 'exist', 'skipOnError' => true, 'targetClass' => ActionType::className(), 'targetAttribute' => ['actionType_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'dateTime' => Yii::t('app', 'Date Time'),
            'qty' => Yii::t('app', 'Qty'),
            'type' => Yii::t('app', 'Type'),
            'product_id' => Yii::t('app', 'Product ID'),
            'movement_id' => Yii::t('app', 'Movement ID'),
            'client_id' => Yii::t('app', 'Client ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Client::className(), ['id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMovement()
    {
        return $this->hasOne(Movement::className(), ['id' => 'movement_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActionType()
    {
        return $this->hasOne(ActionType::className(), ['id' => 'actionType_id']);
    }
}

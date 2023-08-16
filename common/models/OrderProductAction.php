<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_product_action}}".
 *
 * @property int $order_product_id
 * @property int $movement_id
 *
 * @property Movement $movement
 * @property OrderProduct $orderProduct
 */
class OrderProductAction extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_product_action}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_product_id', 'movement_id'], 'required'],
            [['order_product_id', 'movement_id'], 'integer'],
            [['order_product_id', 'movement_id'], 'unique', 'targetAttribute' => ['order_product_id', 'movement_id']],
            [['movement_id'], 'exist', 'skipOnError' => true, 'targetClass' => Movement::className(), 'targetAttribute' => ['movement_id' => 'id']],
            [['order_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrderProduct::className(), 'targetAttribute' => ['order_product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_product_id' => Yii::t('app', 'Order Product ID'),
            'movement_id' => Yii::t('app', 'Movement ID'),
        ];
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
    public function getOrderProduct()
    {
        return $this->hasOne(OrderProduct::className(), ['id' => 'order_product_id']);
    }
}

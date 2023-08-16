<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_cash}}".
 *
 * @property int $order_id
 * @property int $cash_id
 *
 * @property Cash $cash
 * @property Order $order
 */
class OrderCash extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_cash}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'cash_id'], 'required'],
            [['order_id', 'cash_id'], 'integer'],
            [['order_id', 'cash_id'], 'unique', 'targetAttribute' => ['order_id', 'cash_id']],
            [['cash_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cash::className(), 'targetAttribute' => ['cash_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'cash_id' => Yii::t('app', 'Cash ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCash()
    {
        return $this->hasOne(Cash::className(), ['id' => 'cash_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
}

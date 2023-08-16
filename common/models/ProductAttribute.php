<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product_attribute}}".
 *
 * @property int $product_id
 * @property int $attribute_id
 * @property string $value
 *
 * @property Attribute $attribute0
 * @property Product $product
 */
class ProductAttribute extends \yii\db\ActiveRecord
{
    const SCENARIO_TABULAR = 'tabular';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product_attribute}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'attribute_id'], 'required'],
            [['product_id', 'attribute_id'], 'integer'],
            [['value'], 'required', 'except' => self::SCENARIO_TABULAR],
            [['value'], 'string', 'max' => 255],
            [['product_id', 'attribute_id'], 'unique', 'targetAttribute' => ['product_id', 'attribute_id']],
            [['attribute_id'], 'exist', 'skipOnError' => true, 'targetClass' => Attribute::className(), 'targetAttribute' => ['attribute_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'product_id' => Yii::t('app', 'Product ID'),
            'attribute_id' => Yii::t('app', 'Attribute ID'),
            'value' => Yii::t('app', 'Value'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProdAttribute()
    {
        return $this->hasOne(Attribute::className(), ['id' => 'attribute_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
}

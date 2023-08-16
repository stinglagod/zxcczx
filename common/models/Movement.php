<?php

namespace common\models;

use rent\entities\Client\Client;
use rent\entities\User\User;
use Yii;

/**
 * This is the model class for table "{{%movement}}".
 *
 * @property int $id
 * @property string $name
 * @property string $dateTime
 * @property int $qty
 * @property int $action_id
 * @property int $client_id
 * @property string $created_at
 * @property string $updated_at
 * @property int $autor_id
 * @property int $product_id
 * @property int $lastChangeUser_id
 * @property int $active
 *
 * @property Action $action
 * @property Product $product
 * @property User $autor
 * @property \rent\entities\Client\Client $client
 * @property User $lastChangeUser
 * @property OrderProductAction[] $orderProductActions
 * @property OrderProduct[] $orderProducts
 * @property Ostatok[] $ostatoks
 */
class Movement extends protect\MyActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%movement}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dateTime', 'created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['qty', 'action_id', 'client_id', 'autor_id', 'lastChangeUser_id','product_id','active'], 'integer'],
            [['action_id'], 'exist', 'skipOnError' => true, 'targetClass' => Action::className(), 'targetAttribute' => ['action_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['autor_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['autor_id' => 'id']],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['lastChangeUser_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['lastChangeUser_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Наименование'),
            'dateTime' => Yii::t('app', 'Date Time'),
            'qty' => Yii::t('app', 'Qty'),
            'action_id' => Yii::t('app', 'Action ID'),
            'client_id' => Yii::t('app', 'Client ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'autor_id' => Yii::t('app', 'Autor ID'),
            'lastChangeUser_id' => Yii::t('app', 'Last Change User ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'active' => Yii::t('app', 'Активен'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAction()
    {
        return $this->hasOne(Action::className(), ['id' => 'action_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
 * @return \yii\db\ActiveQuery
 */
    public function getAutor()
    {
        return $this->hasOne(User::className(), ['id' => 'autor_id']);
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
    public function getLastChangeUser()
    {
        return $this->hasOne(User::className(), ['id' => 'lastChangeUser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProductActions()
    {
        return $this->hasMany(OrderProductAction::className(), ['movement_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProducts()
    {
        return $this->hasMany(OrderProduct::className(), ['id' => 'order_product_id'])->viaTable('{{%order_product_action}}', ['movement_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOstatoks()
    {
        return $this->hasMany(Ostatok::className(), ['movement_id' => 'id']);
    }


    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (empty($this->dateTime)) {
                $this->dateTime=date('Y-m-d H:i:s');
            }
//            if (empty($this->qty)) {
//                $this->qty=1;
//            }
            if (empty($this->name)) {
                $this->name=Action::findOne($this->action_id)->name;
            }
            return true;
        } else {
            return false;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->changeOstatki($insert);

        $this->changeStatusOrderProducts();

    }

    /**
     * Изменения остатков
     * @param $insert
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function changeOstatki($insert)
    {
        /** @var Ostatok $ostatok */
        $ostatok=Ostatok::find()->where(['movement_id'=>$this->id])->one();
//      Если движение активно, тогда меняем остатки
        if (($this->active)) {
            if (!($ostatok)) {
                $ostatok= new Ostatok();
                $ostatok->movement_id=$this->id;
            }
            $ostatok->dateTime=$this->dateTime;
            $ostatok->product_id=$this->product_id;

            $ostatok->client_id=$this->client_id;
            $ostatok->actionType_id = $this->action->actionType_id;
            $ostatok->qty=$this->qty;

            $res=$ostatok->save();
        } else {
//           Если по текущчему движение, есть остатки, тогда их удаялем
            if ($ostatok) {
                $ostatok->delete();
            }
        }
    }

    /**
     * Если у этого движения есть позции заказа, тогда проходоим по ним и пересчитываем статус
     */
    private function changeStatusOrderProducts()
    {
        foreach ($this->orderProducts as $orderProduct) {
            $orderProduct->changeStatus();
        }
    }
}


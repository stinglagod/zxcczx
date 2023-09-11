<?php

namespace common\models;

use rent\entities\Client\Client;
use rent\entities\User\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * This is the model class for table "{{%order}}".
 *
 * @property int $id
 * @property string $cod
 * @property string $created_at
 * @property string $updated_at
 * @property int $autor_id
 * @property int $lastChangeUser_id
 * @property string $is_active
 * @property int $client_id
 * @property string $name
 * @property string $customer
 * @property string $address
 * @property string $description
 * @property string $dateBegin
 * @property string $dateEnd
 * @property int $status_id
 * @property int $responsible_id
 * @property int $statusPaid_id
 * @property int $googleEvent_id
 * @property int $customer_id
 * @property string $telephone
 * @property string $comment
 *
 * @property User $autor
 * @property Client $client
 * @property User $lastChangeUser
 * @property Status $status
 * @property User $responsible
 * @property OrderCash[] $orderCashes
 * @property Cash[] $cashes
 * @property OrderProduct[] $orderProducts
 * @property OrderBlock[] $orderBlocks
 */
class Order extends \yii\db\ActiveRecord
{
    const NOPAID=0;         //не оплачен
    const FULLPAID=1;       //полностью
    const PARTPAID=2;       //частично
    const OVAERPAID=3;      //переплачен

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at','dateBegin','dateEnd'], 'safe'],
            [['autor_id', 'lastChangeUser_id', 'client_id','status_id','responsible_id','statusPaid_id','customer_id'], 'integer'],
            [['is_active','name','customer','address','description','googleEvent_id','comment'], 'string'],
            [['dateEnd', 'dateBegin'], 'validateDate'],
            [['autor_id', 'lastChangeUser_id', 'client_id','status_id','responsible_id','statusPaid_id'], 'integer'],
            [['is_active','name','customer','address','description','googleEvent_id'], 'string'],
            [['cod'], 'string', 'max' => 20],
            [['telephone'], 'string', 'max' => 20],
            [['comment'], 'string', 'max' => 255],
            [['googleEvent_id'], 'string', 'min'=>5, 'max' => 1024],
            [['autor_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['autor_id' => 'id']],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
            [['lastChangeUser_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['lastChangeUser_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['status_id' => 'id']],
            [['responsible_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['responsible_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Номер'),
            'cod' => Yii::t('app', 'Cod'),
            'created_at' => Yii::t('app', 'Дата создания'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'autor_id' => Yii::t('app', 'Autor ID'),
            'lastChangeUser_id' => Yii::t('app', 'Last Change User ID'),
            'is_active' => Yii::t('app', 'Is Active'),
            'client_id' => Yii::t('app', 'Client ID'),
            'name' => Yii::t('app', 'Имя заказа'),
            'customer' => Yii::t('app', 'Имя заказчика'),
            'address' => Yii::t('app', 'Адрес'),
            'description' => Yii::t('app', 'Примечание'),
            'dateBegin' => Yii::t('app', 'Дата начала мероприятия'),
            'dateEnd' => Yii::t('app', 'Окончание'),
            'status_id' => Yii::t('app', 'Статус заказа'),
            'responsible_id' => Yii::t('app', 'Менеджер'),
            'responsibleName' => Yii::t('app', 'Менеджер'),
            'statusPaidName' => Yii::t('app', 'Статус оплаты'),
            'owner'=> Yii::t('app', 'Мои заказы'),
            'hideClose'=> Yii::t('app', 'Скрыть закрытые'),
            'hidePaid'=> Yii::t('app', 'Скрыть полностью оплаченные'),
            'hideClose'=> Yii::t('app', 'Скрыть закрытые'),
            'statusPaid_id'=> Yii::t('app', 'Статус оплаты'),
            'telephone'=> Yii::t('app', 'Телефон заказчика'),
            'customer_id'=> Yii::t('app', 'Заказчик'),
            'comment'=> Yii::t('app', 'Комментарий заказчика'),

        ];
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
    public function getOrderCashes()
    {
        return $this->hasMany(OrderCash::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCashes()
    {
        return $this->hasMany(Cash::className(), ['id' => 'cash_id'])->viaTable('{{%order_cash}}', ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProducts()
    {
        return $this->hasMany(OrderProduct::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderBlocks()
    {
        return $this->hasMany(OrderBlock::class, ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(Status::className(), ['id' => 'status_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResponsible()
    {
        return $this->hasOne(User::className(), ['id' => 'responsible_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerReg()
    {
        return $this->hasOne(User::class, ['id' => 'customer_id']);
    }

    /**
     * возращает актуальный заказ у пользователя, если его нет возращает false
     * @return \yii\db\ActiveQuery
     */
    static public function getActual()
    {

        if (empty($userId=Yii::$app->user->id)) {
            return false;
        }

        $orders=self::find()
            ->where(['autor_id'=>$userId])
            ->andWhere(['in','status_id',array(Status::NEWFRONTEND)])
            ->andWhere(['>=','dateBegin',date("Y-m-d 00:00:00")])
            ->orderBy(['dateBegin'=>SORT_ASC])
            ->indexBy('id')->all();
        if (empty($orders)) {
            return new Order();
//            if ($orders->save()) {
//                $session = Yii::$app->session;
//                unset($session['activeOrderId']);
//                return $orders;
//            } else {
//                return $orders->errors[0];
//            }
            return false;
        }
        return current($orders);
        return $orders[0];
    }
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $user_id=empty(Yii::$app->user)?1:Yii::$app->user->id;
            $this->client_id = User::findOne($user_id)->client_id;
            if ($this->isNewRecord) {
                $this->autor_id = $user_id;
                $this->created_at = date('Y-m-d H:i:s');
            }
            $this->updated_at = date('Y-m-d H:i:s');
            $this->lastChangeUser_id = $user_id;


            if (Yii::$app->id==='app-frontend') {
                if (empty($this->customer_id)) {
                    $this->customer_id=$user_id;
                }
            } else {
                if (empty($this->responsible_id)) {
                    $this->responsible_id=$user_id;
                }
            }


//          Если не указано время, тогда начало действия по умолчанию 00:00:00
            if (empty($this->dateBegin)) {
                $this->dateBegin = date('Y-m-d 00:00:00');
            }
//          Если не указано время, тогда конец действия заказа в 23:59:59
            if (empty($this->dateEnd)) {
                $this->dateEnd = date('Y-m-d 23:59:59', strtotime($this->dateBegin . "+1 days"));
            } else {
//              Проверяем указано ли время
//              TODO: Пока сделал принудильно менять на 23:59:59, если время не указано. Надо сделать на уровне виджета
                if (date('H:i:s',strtotime($this->dateEnd))=='00:00:00') {
                    $this->dateEnd=date('Y-m-d 23:59:59', strtotime($this->dateEnd));
                }
            }

            if (empty($this->status_id)) {
                if (Yii::$app->id==='app-frontend') {
                    $this->status_id = Status::NEWFRONTEND;
                } else {
                    $this->status_id = Status::NEW;
                }
            }

            $changeDateBegin=false;
            if ($this->dateBegin<>$this->getOldAttribute('dateBegin')) {
                $changeDateBegin=true;
            }
            $changeDateEnd=false;
            if ($this->dateBegin<>$this->getOldAttribute('dateEnd')) {
                $changeDateEnd=true;
            }
//            $session = Yii::$app->session;
            if ($orderProducts = $this->orderProducts) {
                foreach ($orderProducts as $orderProduct)
                {
                    if ($changeDateBegin) {
                        $orderProduct->dateBegin=$this->dateBegin;
                    }

                    if ($changeDateEnd) {
                        $orderProduct->dateEnd=$this->dateEnd;
                    }

                    if ($orderProduct->check($this->status->action_id)===false) {
//                        $session->setFlash('error', 'Ошибка при сохранении заказа. У товара: '. $orderProduct->getName(). ' нет достаточного кол-ва на эти даты');
                        return false;
                    }
                }
            }


            return parent::beforeSave($insert);
        } else {
            return false;
        }
    }
    public function afterSave($insert, $changedAttributes)
    {
        if (empty($this->name)) {
            $this->name='Заказ №'.$this->id;
            $this->save();
        }

        if (key_exists('dateBegin',$changedAttributes)) {
            if ($orderProducts=$this->orderProducts) {
                foreach ($orderProducts as $orderProduct) {
                    $orderProduct->dateBegin=$this->dateBegin;
                    if (!($orderProduct->save())) {
                        return false;
                    }
                }
            }
            // создаем собыите в календаре
            $this->changeGoogleCalendar();
        }
        if (key_exists('dateEnd',$changedAttributes)) {
            if ($orderProducts=$this->orderProducts) {
                foreach ($orderProducts as $orderProduct) {
                    $orderProduct->dateEnd=$this->dateEnd;
                    if (!($orderProduct->save())) {
                        return false;
                    }
                }
            }
        }
        if (key_exists('name',$changedAttributes)) {
            // создаем собыите в календаре
            $this->changeGoogleCalendar();
        }

        parent::afterSave($insert, $changedAttributes);

    }

    /**
     * Что делаем перед удалением
     * @return bool
     */
    public function beforeDelete()
    {
//      Удаляем все позиции
        foreach ($this->orderProducts as $orderProduct) {
            if (!$orderProduct->delete()) {
                return false;
            }
        }
//      Удаляем все движение денег
        foreach ($this->cashes as $cashe) {
            if (!$cashe->delete()) {
                return false;
            }
        }
//      Удаляем событие в google календаре
        $this->changeGoogleCalendar(true);
        return parent::beforeDelete();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(OrderProduct::class, ['order_id' => 'id']);
    }

    /**
     * Добавляем товар в заказ. (мягкий резерв)
     * @param integer $productId            Идентификатор продукта
     * @param integer $qty                  Кол-во
     * @param string $type                  Тип позиции (Аренда, Продажа)
     * @param $orderBlock_id                Идентификатор блока куда добавить
     * @param null $parent_id               Кому принадлежит(какой составной позции)
     * @param null $period                  Период(для аренды)
     * @param null $dateBegin               Дата начала
     * @param null $dateEnd                 Дата конца
     * @return bool|string
     */

    public function addToBasket($productId,$qty,$type=OrderProduct::RENT,$orderBlock_id,$parent_id=null,$period=null,$dateBegin=null,$dateEnd=null)
    {
        $dateBegin=$dateBegin?$dateBegin:$this->dateBegin;
        $dateEnd=$dateEnd?$dateEnd:$this->dateEnd;
        if ($this->status_id==Status::CLOSE) {
            return "Нельзя добавить позицию в закрытый заказ";
        }
        if ($this->status_id==Status::CANCELORDER) {
            return "Нельзя добавить позицию в отмененный заказ";
        }
        if (empty($orderBlock_id)) {
            $orderBlock_id=$this->getDefaultBlock()->id;
        }
        //TODO: завязать на конфигурации или товаре миниальный период
        $period=$period?$period:1;

        $product=Product::findOne($productId);
        //      Проверяем наличие
        if (($qty<=0) and ($qty > $product->getBalance($dateBegin,$dateEnd))) {
            return "Не достаточно товаров на эти даты|".$product->getBalance($dateBegin,$dateEnd);
        };



        $orderProduct=OrderProduct::find()->where(['orderBlock_id'=>$orderBlock_id,'product_id'=>$productId,'type'=>$type]);
//      Если добавляем товар в составную позицию, тогда ищем есть ли соответствующая позция в составной
        if ($parent_id) {
            // Проверяем статус составной позиции
            $orderProductParent=OrderProduct::findOne($parent_id);
            if ($orderProductParent->readOnly()) {
                return "Нельзя добавить товар. Составная позиция только для чтения";
            }
            $orderProduct=$orderProduct->andWhere(['parent_id'=>$parent_id]);
        }
        /** @var OrderProduct $orderProduct */
        $orderProduct=$orderProduct->one();
//      Если в рамках этого заказа и этого блока данный товар уже присутствует, тогда позиции увеличиваем на нужное кол-во
//      При условии, что данную позиицию можно редактировать
        if (($orderProduct) and (!$orderProduct->readOnly())) {
            $orderProduct->qty = $orderProduct->qty+$qty;
        } else {
            $orderProduct=new OrderProduct();
            $orderProduct->product_id=$productId;
            $orderProduct->order_id=$this->id;
            $orderProduct->qty=$qty;
            $orderProduct->type=$type;
            $orderProduct->orderBlock_id=$orderBlock_id;
            $orderProduct->parent_id=$parent_id?$parent_id:null;
            $orderProduct->name=empty($name)?null:$name;

            $orderProduct->dateBegin=$dateBegin;
            if ($type==OrderProduct::RENT) {
                $orderProduct->dateEnd=$dateEnd;
                $orderProduct->period=$period;
                $orderProduct->cost=$product->priceRent;
            } else {
                $orderProduct->cost=$product->priceSale;
            }
        }
        if ($orderProduct->save()){
            return true;
        } else {
            return $orderProduct->firstErrors[0];
        }
    }
    /**
     * Добавляем пустую(составную) позицю в зака
     */
    public function addEmptyToBasket ($orderBlock_id=null,$qty=1)
    {
        if ($this->status_id==Status::CLOSE) {
            return "Нельзя добавить позицию в закрытый заказ";
        }
        if ($this->status_id==Status::CANCELORDER) {
            return "Нельзя добавить позицию в отмененный заказ";
        }
        $orderProduct=new OrderProduct();
        $orderProduct->type=OrderProduct::COLLECT;
        $orderProduct->order_id=$this->id;
        $orderProduct->orderBlock_id=$orderBlock_id;
        $orderProduct->set=OrderProduct::getDefaultSet();
        $orderProduct->name=OrderProduct::getDefaultName();
        $orderProduct->qty=$qty;
        return $orderProduct->save();
    }
    /**
     * Добавляем услугу в зака
     */
    public function addServiceToBasket ($service_id)
    {
        if ($service=Service::findOne($service_id)){

            $orderProduct=new OrderProduct();
            $orderProduct->order_id=$this->id;
            $orderProduct->type=OrderProduct::SERVICE;
            $orderProduct->service_id=$service->id;
            $orderProduct->name=$service->name;
            $orderProduct->qty=1;
            if ($service->is_depend) {
                $orderProduct->cost=$this->calculateDependServiceCost();
            } else {
                $orderProduct->cost=$service->defaultCost;
            }

            return $orderProduct->save();
        } else {
            return false;
        }


    }

    /**
     * Возращаем массив позиций с разбивкой по блокам
     * Если параметр $orderBlockName не равен, тогда добавляем новый блок
     */
    public function getOrderProductsByBlock($orderBlock_id=null)
    {
        $aqOrderBlock=OrderBlock::find()->where(['order_id'=>$this->id]);
        if ($orderBlock_id) {
            $aqOrderBlock->andWhere(['id'=>$orderBlock_id]);
        }
        $orderBlocks=$aqOrderBlock->indexBy('id')->all();

        $respone=array();
        if (empty($orderBlocks)) {
            $orderBlock = new OrderBlock();
            $orderBlock->name = Block::getDefaultName();
            $orderBlock->order_id= $this->id;
            $orderBlock->save();
            $orderBlocks=$aqOrderBlock->indexBy('id')->all();
        }
        if ($orderBlocks) {
            foreach ($orderBlocks as $item) {
                // ArrayDataProvider
                $query= new Query;
                $dataProvider = new ArrayDataProvider([
                    'allModels' => $query->from('{{%order_product}}')->where(['orderBlock_id'=>$item->id])->groupBy('parent_id')->indexBy('id')->all(),
                    'pagination' => [ //постраничная разбивка
                        'pageSize' => 10, // 10 новостей на странице
                    ],
                ]);
//              убираем пагинацию
                $dataProvider->pagination = false;
                $respone[$item->id] = ['orderBlock' => $item, 'dataProvider' => $dataProvider];
            }
        } else {

        }

        return $respone;
    }

    public function getOrderBlock($orderBlock_id)
    {
//        $query=OrderProduct::find()
//            ->where(['order_id'=>$this->id])
//            ->andWhere(['orderBlock_id'=>$orderBlock_id])
//            ->indexBy('id');
        $query=OrderBlock::find()
            ->where(['id'=>$orderBlock_id])
            ->with(['orderProducts']);
        // ActiveDataProvider
        $dataProvider = new ActiveDataProvider([
            'pagination' => [
                'pageSize' => 10,
            ],
            'query' => $query
        ]);
//        return $dataProvider;
        return var_dump($query->all());
    }

    /**
     * Ищем блок по умолчанию. Ищется самый первый блок в заказе, либо если блоков нет, создается новый
     * @return array|OrderBlock|\yii\db\ActiveRecord|null
     */
    public function getDefaultBlock()
    {
        if (!($orderBlock=OrderBlock::find()->where(['order_id'=>$this->id])->orderBy('id')->one())){
            $orderBlock=new OrderBlock(['name'=>Block::getDefaultName(),'order_id'=>$this->id]);
            $orderBlock->save();
        }
        return $orderBlock;
    }

    private $_summ;
    public function getSumm()
    {
        if (empty($this->_summ)) {
//          Возможно лучше использовать sql запрос?
            $this->_summ=-0;
            foreach ($this->orderProducts as $orderProduct) {
//              Не считаем в сумме стоимость позиции, которая находится в составной. Мы берем только общую стоимость
//              составной позиции
                if ($orderProduct->parent_id==$orderProduct->id) {
                    $this->_summ+=$orderProduct->getSumm();
                }
            }
        }
        return $this->_summ;
    }

    private $_paid;
    public function getPaid()
    {
        if (empty($this->_paid)) {
            $this->_paid=$this->getCashes()->sum('sum');
        }
        return $this->_paid;
    }

    private $_statusText;
    public function getStatusText ()
    {
        if (empty($this->_statusText)) {

            $status=$this->status->shortName;
            if ($orderProducts=$this->orderProducts) {

                foreach ($orderProducts as $orderProduct) {
                    if ($issue=$orderProduct->getBalance(Action::ISSUE)){
                        if ($return=$orderProduct->getBalance(Action::RETURN)){
                            if (($return+$issue)==0) {
                                $status = 'Завершен';
                            } else {
                                $status = 'Частично возращен';
                            }
                        } else {
                            $status=1;
                        }
                    }
                }
            }
        }
    }

    /**
     * Возращает стастус оплаты заказа
     * @param bool $text определяет в каком виде отдавать статус, по умолчанию выдает код статуса оплаты
     * @return int|string
     */
    public function getPaidStatus($text=false)
    {
        $status=$text?'Не оплачен':self::NOPAID;
        if ($this->getPaid()>0) {
            if ($this->getPaid()==$this->getSumm()) {
                $status=$text?'Полностью оплачен':self::FULLPAID;
            } else if ($this->getPaid()>$this->getSumm()) {
                $status=$text?'Переплачен':self::OVAERPAID;
            } else if ($this->getPaid()<$this->getSumm()) {
                $status=$text?'Частично оплачен':self::PARTPAID;
            }
        }
        return $status;
    }

    /**
     * Считает стоимость
     * @param null $percent
     * @return float|int
     */
    public function calculateDependServiceCost($percent=null)
    {
        $total=OrderProduct::find()->where(['order_id'=>$this->id, 'is_montage'=>1])->sum('cost');
//        'SELECT sum(cost*qty*IFNULL(period,1)) FROM `order_product` WHERE `is_montage` = 1 ORDER BY `type`  ASC'
        $result = Yii::$app->db->createCommand('SELECT sum(cost*qty*IFNULL(period,1)) as summ FROM `order_product` WHERE `is_montage` = 1 and `order_id`=:order_id')
            ->bindValue(':order_id', $this->id)
            ->queryOne();
        $total=$result['summ'];

        $session = Yii::$app->session;
        if (empty($percent)) {
            if ($dependService=Service::getDependService()) {
                $percent=$dependService->percent;
            }
        }

        return $total*$percent/100;

    }

    /**
     * Пересчитывает стоимость зависимой услуги
     */
    public function recalcDependServiceCost()
    {
        $dependService=Service::getDependService();
        if ($orderProductDependService=OrderProduct::find()
            ->where(['service_id'=>$dependService->id,'order_id'=>$this->id])
            ->andWhere(['status_id' => null])
            ->one()) {
            $orderProductDependService->cost=$this->calculateDependServiceCost($dependService->percent);
            $orderProductDependService->save();
        }
    }

    private  $_services;
    /**
     * Возращает все услуги заказа
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getServices()
    {
        if (empty($this->_services)) {
            $this->_services=$this->getServicesQuery()->all();
        }
        return $this->_services;
    }
    public function getServicesQuery()
    {
        $query=new Query();

        return  $query->from('{{%order_product}}')->where(['order_id'=>$this->id,'type'=>OrderProduct::SERVICE]);
    }

    /**
     * Меняет статус заказа
     * Перебираются все позиции заказа и устанавливается минимальный статус
     * ИСКЛЮЧЕНИЕ если выдача у продажи, тогда данный статус не учитывается
     * Также устанавливает статус оплаты этого заказа
     * const NEW=1;            //При создании заказа
     * const NEWFRONTEND=11;   //При создании заказа посетителем
     * const SMETA=2;          //При добавлении товара
     * const PARTISSUE=3;      //Частично выданы товары
     * const ISSUE=4;          //Товары выданы полностью
     * const PARTRETURN=7;     //Частично возращены товары
     * const RETURN=5;         //Товары возращены полностью
     * const CLOSE=6;          //Закрыт
     * const CANCELORDER=9;    //Отменен
     * @param integer $status_id   Если есть это значение, тогда меняем статус на это значение (тольок закрытие, отмена)
     * @return bool
     */
    public function changeStatus($status_id=null)
    {
        if (($this->status_id == Status::CLOSE) or ($this->status_id == Status::CANCELORDER)) {
            return false;
        }
        if ($status_id) {
            if ($this->canChangeStatus($status_id)) {
                foreach ($this->orderProducts as $orderProduct) {
                    //              Если отмена статуса, тогда освобождаем бронь для товаров
                    if ($status_id==Status::CANCELORDER) {
                        $orderProduct->deactivateMovement(Action::HARDRESERV);
                        $orderProduct->deactivateMovement(Action::UNHARDRESERV);
                    }
                    $orderProduct->status_id=$status_id;
                    $orderProduct->save();
                }
                $this->status_id=$status_id;
                $this->save();
                return true;
            } else {
                return false;
            }
        }
        /** @var OrderProduct $mainOrderProduct */
        $mainOrderProduct = null;
        $rent = false;

        /** @var OrderProduct $orderProduct */
        foreach ($this->orderProducts as $orderProduct) {
            if ($orderProduct->type==OrderProduct::SERVICE) {
                continue;
            }
            if (empty($mainOrderProduct)) {
                $mainOrderProduct=$orderProduct;
                continue;
//                $status = $orderProduct->status;
//                $rent = ($orderProduct->type == OrderProduct::RENT) ? true : false;
            }
            //Если покаким-то причинам статус не установлен у позиции
            if (empty($mainOrderProduct->status_id)) {
                $mainOrderProduct->changeStatus();
            }
            if (empty($orderProduct->status_id)) {
                $orderProduct->changeStatus();
            }
            if ($orderProduct->status_id!=$mainOrderProduct->status_id) {
                if (($mainOrderProduct->status->order > $orderProduct->status->order)and(!$orderProduct->isLastCurrentStatus())) {
                    $mainOrderProduct=$orderProduct;
                } else if (($mainOrderProduct->status->order < $orderProduct->status->order)and($mainOrderProduct->isLastCurrentStatus())) {
                    $mainOrderProduct=$orderProduct;
                }
            } else {
                if (($mainOrderProduct->isLastCurrentStatus()) and (!$orderProduct->isLastCurrentStatus())) {
                    $mainOrderProduct=$orderProduct;
                }
            }

        }
        if ($mainOrderProduct) {
            $this->status_id=$mainOrderProduct->status_id;
        }

        $this->statusPaid_id= $this->getPaidStatus();
        if ($this->save()) {
            return $this->changeStatusPaid();
        } else {
            return false;
        }
    }


    private $_canChangeStatus;
    /**
     * Проверяем можно ли менять на указанны статус $status_id
     * Реалзиована проверка тольк на закрытие и отмена
     * Закрыть можно тольок если все обязательства выполнены
     * @param $status_id
     * @return mixed
     */
    public function canChangeStatus($status_id)
    {
        if (empty($this->_canChangeStatus[$status_id])) {

            if (($this->status_id==Status::CLOSE) or ($this->status_id==Status::CANCELORDER)) {
                $this->_canChangeStatus[$status_id]=false;
                return false;
            }
            if ($status_id == Status::CLOSE) {
//              Нельзя закрыть если текущий статус Новый или Составлена смета. Просто нет смысла
                if (($this->status_id==Status::NEW) or ($this->status_id==Status::SMETA)) {
                    $this->_canChangeStatus[$status_id]=false;
                    return false;
                }
//              Можно закрыть, если 100% оплата и 100% возрат товаров
                $balanceGoods=0;
//                $balancePays=$this->getPaidStatus();
//echo $balancePays;
                /** @var OrderProduct $orderProduct */
                foreach ($this->orderProducts as $orderProduct) {
                    if (!($orderProduct->isLastCurrentStatus())) {
                        $balanceGoods=1;
                        break;
                    }
                }

echo $balanceGoods;
                echo $this->getPaidStatus();
                if (($balanceGoods==0) and ($this->getPaidStatus()==self::FULLPAID)) {
//echo "tut";
                    $this->_canChangeStatus[$status_id]=true;
                } else {
                    $this->_canChangeStatus[$status_id]=false;

                }

            } else if ($status_id == Status::CANCELORDER) {
//              Можно отменить, если нет выдачи товаров и нет прихода денег
                $balanceGoods=0;
                /** @var OrderProduct $orderProduct */
                foreach ($this->orderProducts as $orderProduct) {
                    if (($orderProduct->getBalance($orderProduct->getOperation(Action::ISSUE),$orderProduct->dateEnd))){
                        $balanceGoods=1;
                        break;
                    } else if ($orderProduct->type==OrderProduct::COLLECT) {
                        if ($orderProduct->readOnly()) {
                            $balanceGoods=1;
                        } else {
                            /** @var OrderProduct $child */
                            foreach ($orderProduct->childs as $child) {
                                if (($child->getBalance($child->getOperation(Action::ISSUE),$child->dateEnd))){
                                    $balanceGoods=1;
                                    break;
                                }
                            }
                        }
                    }
                }
//                echo $balanceGoods;
                if (($balanceGoods==0) and ($this->getPaidStatus()==self::NOPAID)) {
                    $this->_canChangeStatus[$status_id]=true;
                } else {
                    $this->_canChangeStatus[$status_id]=false;
                }
            } else if ($status_id == Status::SMETA) {
                if (($this->status_id==Status::NEW) or ($this->status_id==Status::NEWFRONTEND)) {
                    $this->_canChangeStatus[$status_id]=true;
                }
            } else {
                $this->_canChangeStatus[$status_id]=false;
            }
        }

//        echo $this->_canChangeStatus[$status_id];
        return $this->_canChangeStatus[$status_id];
    }

    public function getResponsibleName()
    {
        if ($this->responsible_id) {
            return $this->responsible->getShortName();
        } else {
            return '<не указан>';
        }

    }
    public function getStatusPaidName()
    {
        $arr=[
            self::NOPAID => "Не оплачен",
            self::FULLPAID => "Оплачен полностью",
            self::PARTPAID => "Оплачен частично",
            self::OVAERPAID => "Переплачен",
        ];
        if ($this->statusPaid_id)
            return $arr[$this->statusPaid_id];
        else
            return false;
//        return $this->getPaidStatus(true);
    }
    static public function getStatusPaidsArray()
    {
        $arr=[
            '-1' => "Скрыть оплаченные",
            '-2' => "Показать все",
            self::NOPAID => "Не оплачен",
            self::FULLPAID => "Оплачен полностью",
            self::PARTPAID => "Оплачен частично",
            self::OVAERPAID => "Переплачен",
        ];
        return $arr;
    }
    static public function getStatusArray()
    {
        $arr=[
            '-1' => "Скрыть закрытые(отмененные)",
            '-2' => "Показать все"
        ];
        $arr=$arr + ArrayHelper::map(Status::find()->orderBy('order')->asArray()->all(), 'id', 'name');
        return $arr;
    }
    public function changeStatusPaid()
    {
        $this->statusPaid_id=$this->getPaidStatus();
        return $this->save();
    }

    /**
     * Добавляем изменяем событие в календаре гугл, посредством webhoock через ресурс https://www.integromat.com
     * Если $delete - истина, тогда удаляем событие
     * @return bool
     */
    public function changeGoogleCalendar($delete=null)
    {
        $domain='rent4b.ru';
        if (key_exists('SERVER_NAME',$_SERVER)) {
            if ((is_int(strripos($_SERVER['SERVER_NAME'],'local'))) or
                (is_int(strripos($_SERVER['SERVER_NAME'],'dev'))) or
                (is_int(strripos($_SERVER['SERVER_NAME'],'admin')))
            ) {
                return false;
            } else {
                $domain=$_SERVER['SERVER_NAME'];
            }
        }

        $myCurl = curl_init();
        curl_setopt_array($myCurl, array(
//            CURLOPT_URL => 'https://hook.integromat.com/4ut2ne3q1yb5svk8cdmunr6f1x7ewpu1',
            CURLOPT_URL => 'https://hook.integromat.com/w34l6v6o96zd6vf2vijldxu1dawrwlft',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'name'=> $this->name,
                'dateBegin'=>$this->dateBegin,
                'dateEnd'=>$this->dateEnd,
                'customer'=>$this->customer,
                'order_id'=>$this->id,
                'googleEvent_id'=>$this->googleEvent_id,
                'url'=> $domain,
                'delete' => $delete
            ))
        ));
        $response = curl_exec($myCurl);
        curl_close($myCurl);
        if ($response) {
            $this->googleEvent_id=$response;
            $this->save(true,["googleEvent_id"]);
        }

//        echo "Ответ на Ваш запрос: ".$response;
        return true;
    }

    /**
     * Проверяем даты. Дата окончания не должна быть раньше даты начала. Все даты не должны быть раньше текущей даты
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function validateDate()
    {

        $currentDate = Yii::$app->getFormatter()->asDate(time());

        $dateBegin=strtotime($this->dateBegin);
        $dateEnd=strtotime($this->dateEnd);
        if ($dateBegin > $dateEnd){
            $this->addError(null,'"Дата окончания", не может быть раньше "даты начала"');
//            $this->addError('dateBegin', '"Проверьте дату окончания"');
//            $this->addError('dateEnd', '"Дата окончания", не может быть раньше "даты начала"');
        }

//        if ($this->isNewRecord){
//            if ($currentDate > $this->dateBegin) {
//                $this->addError('dateBegin', '"Дата начала", не может быть раньше текущей даты');
//            }
//
//            if ($currentDate > $this->dateEnd){
//                $this->addError('dateEnd', '"Дата окончания", не может быть раньше текущей даты');
//            }
//        }

    }


    /**
     * Получаем доставку в заказе
     * @return array|\yii\db\ActiveRecord
     */
    private $_delivery=null;
    public function getDelivery()
    {
        if (!($this->_delivery)) {
            $this->_delivery=$this->getOrderProducts()
//                ->andWhere(['not', ['service_id' => null]])
                ->andWhere(['type'=>OrderProduct::SERVICE])
//                ->joinWith('service')
//                ->andWhere(['not',['service.serviceType_id'=>null]])
                ->one();
        }
        return $this->_delivery;
    }

    /**
     * Добавляем доставку в заказ с идентификатором $id
     * @param $id
     * @return bool
     */
    public function addDelivery($id){
        $delivery=$this->getDelivery();
        if (($delivery)and($delivery->id!=$id)) {
            $delivery->delete();
        }
        $orderProduct= new OrderProduct();
        $orderProduct->service_id=$id;
        $service=Service::findOne($id);
        $orderProduct->cost=$service->defaultCost;
        $orderProduct->order_id=$this->id;
        $orderProduct->type=OrderProduct::SERVICE;
        $orderProduct->name=$service->name;
        $orderProduct->qty=1;
        if ($orderProduct->save()) {
            $this->_delivery=$orderProduct;
            return true;
        } else {
            return false;
        }
    }

}

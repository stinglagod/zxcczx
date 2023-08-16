<?php

namespace common\models;

use common\models\protect\MyActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%order_product}}".
 *
 * @property int $id
 * @property int $order_id
 * @property string $type
 * @property int $product_id
 * @property string $name
 * @property int $set
 * @property int $qty
 * @property double $cost
 * @property string $dateBegin
 * @property string $dateEnd
 * @property int $period
 * @property int $periodType_id
 * @property int $orderBlock_id
 * @property int $parent_id
 * @property string $comment
 * @property int $is_montage
 * @property int $service_id
 * @property int $status_id
 *
 * @property Order $order
 * @property PeriodType $periodType
 * @property Product $product
 * @property OrderProductAction[] $orderProductActions
 * @property Movement[] $movements
 * @property OrderBlock[] $orderBlocks
 * @property Service service
 * @property Status $status
 * @property OrderProduct[] $childs
 */
class OrderProduct extends MyActiveRecord
{
    const RENT = 'rent';
    const SALE = 'sale';
    const SERVICE = 'service';
    const COLLECT = 'collect';


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_product}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'product_id', 'set', 'qty', 'period', 'periodType_id', 'orderBlock_id', 'parent_id', 'service_id', 'status_id'], 'integer'],
            [['type'], 'string'],
            [['cost'], 'number'],
            [['dateBegin', 'dateEnd', 'is_montage'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['comment'], 'string', 'max' => 256],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['periodType_id'], 'exist', 'skipOnError' => true, 'targetClass' => PeriodType::class, 'targetAttribute' => ['periodType_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['orderBlock_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrderBlock::class, 'targetAttribute' => ['orderBlock_id' => 'id']],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrderProduct::class, 'targetAttribute' => ['parent_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::class, 'targetAttribute' => ['service_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['status_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'type' => Yii::t('app', 'Тип'),
            'product_id' => Yii::t('app', 'Товар'),
            'name' => Yii::t('app', 'Name'),
            'set' => Yii::t('app', 'Set'),
            'qty' => Yii::t('app', 'Кол-во'),
            'cost' => Yii::t('app', 'Цена'),
            'dateBegin' => Yii::t('app', 'Начало'),
            'dateEnd' => Yii::t('app', 'Конец'),
            'period' => Yii::t('app', 'Период'),
            'periodType_id' => Yii::t('app', 'Period Type ID'),
            'comment' => Yii::t('app', 'Комментарий'),
            'is_montage' => Yii::t('app', 'Монтаж/Демонтаж ?'),
            'service_id' => Yii::t('app', 'Service ID'),
            'status_id' => Yii::t('app', 'Status ID'),
        ];
    }

    const SCENARIO_DEFAULT = 'default';
    const SCENARIO_READONLY = 'readonly';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_READONLY] = ['status_id'];
        return $scenarios;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeriodType()
    {
        return $this->hasOne(PeriodType::className(), ['id' => 'periodType_id']);
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
    public function getParent()
    {
        return $this->hasOne(OrderProduct::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProductActions()
    {
        return $this->hasMany(OrderProductAction::className(), ['order_product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMovements()
    {
        return $this->hasMany(Movement::className(), ['id' => 'movement_id'])->viaTable('{{%order_product_action}}', ['order_product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderBlock()
    {
        return $this->hasOne(OrderBlock::class, ['id' => 'orderBlock_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id' => 'status_id']);
    }

    /**
     * Возращаем всех детей позиции
     * @return \yii\db\ActiveQuery
     */
    public function getChilds()
    {
//        OrderProduct::find()->where(['parent_id'=>$this->id])->all();
        return $this->hasMany(OrderProduct::class, ['parent_id' => 'id']);
    }

    /**
     * Проверка кол-во, на даты. Перед соххранением
     *
     */
    public function check($newAction_id = null)
    {
//      не нужна проверка для коллекций и услуг
        if (($this->type == self::COLLECT) or ($this->type == self::SERVICE)) {
            return true;
        }
        $newAction_id = ($newAction_id) ? $newAction_id : $this->order->status->action_id;
        if ($newAction_id == Action::SOFTRENT) {
            return true;
        }

        $oldQty = $this->getOldAttribute('qty');
        $ostatok = Product::getBalancById($this->product_id, $this->dateBegin, $this->dateEnd);

        if ($this->qty > ($ostatok + $oldQty)) {
            $errorMsg='На складе нет такого кол-во товаров на эти даты. Доступно: ' . $ostatok . ' Товар: ' . $this->product_id . ' Заказ: ' . $this->order_id;
            $this->addError('',$errorMsg);
            $session = Yii::$app->session;
            $session->setFlash('error', $errorMsg);
            return false;
        }

        return true;
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if (($this->status_id == Status::NEW) or
            ($this->status_id == Status::SMETA) or
            ($this->status_id == null)) {
            $this->scenario = self::SCENARIO_DEFAULT;
        } else {
            $this->scenario = self::SCENARIO_READONLY;
        }

        if ($this->scenario == self::SCENARIO_READONLY) {
            return parent::save($runValidation, ['status_id']);
        } else {
            return parent::save($runValidation, $attributeNames);
        }

    }

    public function beforeSave($insert)
    {

        if (parent::beforeSave($insert)) {
            $session = Yii::$app->session;
//          проверяем можно ли редактировать
//            if ($this->readOnly()) {
//                $session->setFlash('error', 'Данную позицию нельзя редактировать');
//                return false;
//            }

//          Для составной позиции кол-во всегда =1
//            if ($this->type==self::COLLECT) {
//                $this->qty=1;
//            }
//          Для сервиса проверяем, что бы не дублировалось
            if (($this->type == self::SERVICE) and ($this->isNewRecord)) {
                $count = OrderProduct::find()->where(['order_id' => $this->order->id, 'service_id' => $this->service_id])->count();
                if ($count >= 1) {
                    $session->setFlash('error', 'Нельзя добавить одну услугу дважды: ' . $this->service->name);
                    return false;
                }
            }

//          Проверить есть ли такое кол-во на складе
//          Найти сколько уже забронировано
//          Найти какое кол-во есть на складе
            if ($this->check() === false) {
                return false;
            }

            return parent::beforeSave($insert);
        } else {
            return false;
        }
    }


    public function afterSave($insert, $changedAttributes)
    {
        if ($this->parent_id == null) {
            $this->parent_id = $this->id;
            $this->save();
        }
//        TODO: А если обновление движение не пройдет?
        $this->updateMovement($insert, $changedAttributes);
//      Если записываем услугу, то пересчитывать не надо
        if ($this->type != self::SERVICE) {
            $this->order->recalcDependServiceCost();
        }


        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Обновляем движения позиции. Позиции можно менять только при жесткой брони, значит меняем значения движения
     * жесткой брони
     */
    private function updateMovement($insert, $changedAttributes)
    {

//      Если позиция только для чтения, тогда и движение редактировать не зачем
        if ($this->readOnly()) {
            return false;
        }
//      не нужны движения для услуг и для коллекций
//        if (($this->type == self::COLLECT) or ($this->type == self::SERVICE)) {
        if (($this->type == self::SERVICE)) {
            return false;
        }
//      Если запись новая, тогда добавляем движение
        if ($insert) {
            $this->addMovement(Action::HARDRESERV);
        } else {
            $dateBegin = null;
            $dateEnd = null;
            $qty = null;
            $is_change = false;
            if (key_exists('dateBegin', $changedAttributes)) {
                $dateBegin = $this->dateBegin;
                $is_change = true;
            }
            if (key_exists('dateEnd', $changedAttributes)) {
                $dateEnd = $this->dateEnd;
                $is_change = true;
            }
            if (key_exists('qty', $changedAttributes)) {
                $qty = $this->qty;
                $is_change = true;
            }
            if ($is_change) {
                $this->changeMovement(Action::HARDRESERV, (-1 * $qty), false, $dateBegin);
                if ($this->type != self::SALE) {
                    $this->changeMovement(Action::UNHARDRESERV, $qty, false, $dateEnd);
                }
            }
        }
        return true;
    }

    /**
     * Добавляем движение товара
     * @param int $action_id Идентификатор действия товара
     * @param null $qty Кол-во
     * @param null $dateBegin Дата начала
     * @param null $dateEnd Дата Конца
     * @return bool
     */
    public function addMovement($action_id, $qty = null, $dateBegin = null, $dateEnd = null)
    {

//      Если товар продажа то не надо освобождать после жесткого резерва
        if (($this->type == self::SALE) and ($action_id == Action::UNHARDRESERV)) {
            return true;
        }
//      Если составная то не надо освобождать после жесткого резерва
        if (($this->type == self::COLLECT) and ($action_id == Action::UNHARDRESERV)) {
            return true;
        }
//      Если нет стоимости


        $action_id = $this->getOperation($action_id);
        /** @var Action $action */
        $action = Action::findOne($action_id);
//      Проверяем баланс. Для любого движения со знаком -
        if ($action->sing == 0) {
            $operationBalance = $this->getOperationBalance($action_id);
            if ($qty > $operationBalance) {
                Yii::error('Кол-во больше чем можно');
                return false;
            }
        }

//      Если не заполнены поля, заполняем по данным позиции
        if (empty($qty)) {
            $qty = $this->qty;
        }
        if (empty($dateBegin)) {
            $dateBegin = $this->dateBegin ? $this->dateBegin : $this->order->dateBegin;
        }
        if (empty($dateEnd)) {
            $dateEnd = $this->dateEnd ? $this->dateEnd : $this->order->dateEnd;
        }

//      Создаем движение


        $movement = new Movement();
        $qty = $action->sing ? $qty : (-1 * $qty);
        $movement->qty = $qty;
        $movement->action_id = $action_id;
        $movement->product_id = $this->product_id;
        $movement->dateTime = $dateBegin;

//      Если составная, тогда деактивируем движения, что бы не проходили остатки
        if ($this->type == self::COLLECT) {
            $movement->active = 0;
        } else {
            $movement->active = 1;
        }

//      Если выдача товара
        if (($action_id == Action::ISSUE) or ($action_id == Action::ISSUERENT)) {
            $movement->dateTime = $dateBegin;
//          Деактивируем Начало брони
            $this->deactivateMovement(Action::HARDRESERV, $qty);
        }
//      Если возрат товара
        if (($action_id == Action::RETURN) or ($action_id == Action::RETURNRENT)) {
            $movement->dateTime = $dateEnd;
//          Деактивируем Конец брони
            $this->deactivateMovement(Action::UNHARDRESERV, $qty);
        }

//      Сохраняем
        if ($movement->save()) {
            $this->link('movements', $movement);
//          Т.к. в afterSave происходит до link() и не видно всех движений, то запускаем изменения статуса тут:
            $this->changeStatus();
            // Если есть антипод, тогда и делаем движение по антиподу
            // Если товар продажа то антипод не нужен
            if ($action->antipod_id) {
                return $this->addMovement($action->antipod_id, null, $dateEnd);
            }
//          Меняем статус
            return true;
        } else {
            return false;
        }

    }

    /**
     * Удаление движения
     * @param null $action_id
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function removeMovement($action_id = null)
    {
        $movements = $this->getMovements();
        if ($action_id) {
            $movements = $movements->where(['action_id' => $action_id]);
        }
        $movements = $movements->all();

        foreach ($movements as $movement) {
            $this->unlink('movements', $movement, true);
            $movement->delete();
        }
    }

    /**
     * Изменение движения по action_id
     * Если $howmuch истина, тогда $newQty - указывается на сколько увеличить(уменьшить) значение. иначе
     * новое значениеё
     */

    public function changeMovement($action_id, $newQty = null, $howmuch = false, $date = null)
    {

        /** @var Movement $movement */
        $movement = $this->getMovements()->where(['action_id' => $action_id])->one();
        if (!($movement)) {
            return false;
        }

        if ($howmuch) {
            $movement->qty = $movement->qty + $newQty;

            if ($movement->qty == 0) {
                $movement->delete();
            }
        } else {
            if (empty($newQty)) {
                $action = Action::findOne($action_id);
                $newQty = $action->sing ? $this->qty : (-1 * $this->qty);
            }
            $movement->qty = $newQty;
        }
        if ($date) {
            $movement->dateTime = $date;
        }
        return $movement->save();

    }

    /**
     * @return int
     */
    public static function getDefaultSet()
    {
        return time();
    }

    public static function getDefaultName($empty = null)
    {
        if ($empty) {
            return '<Новая коллекция>';
        } else {
            return '<Произвольная позиция>';
        }

    }

    public function getName()
    {
        if ($this->type == OrderProduct::COLLECT) {
            return $this->name;
        } else {
            return $this->product->name;
        }
    }

    public function getThumb()
    {
        if ($this->type == OrderProduct::COLLECT) {
            return Yii::$app->request->baseUrl . '/20c20/img/nofoto-300x243.png';
        } else {
            return $this->product->getThumb(\common\models\File::THUMBSMALL);
        }
    }

    /**
     * Сколько можно выдать товара по данной операции
     */
    public function getOperationBalance($action_id)
    {
        $action_id = $this->getOperation($action_id);
        //TODO: В случае если удаляются позиции, нужно проверить можно ли их удалить
        if ($action_id == 0) {
            return $this->qty;
        }
//       Если составная позиция, проверяем
        return $this->qty - $this->getBalance($action_id);

    }

    private $_summ;

    /**
     * Сумма позции с учетом аренды(продажи)
     */
    public function getSumm()
    {
        if (empty($this->_summ)) {
            $this->_summ = $this->cost * $this->qty;
            if ($this['type'] == self::RENT) {
                $this->_summ *= $this->period;
            }
        }
        return $this->_summ;
    }

    /**
     * Текущий баланс по позиции. Если не указан $action_id, тогда полный баланс
     * @param int $action_id Идентификатор действия
     * @param null $date Дата
     * @return int
     */
    public function getBalance($action_id = null, $date = null)
    {
//       Если составная позиция ищем по движениям
        if ($this->type == self::COLLECT) {
            if ($this->status_id == Status::ISSUE) {
                return $this->qty;
            } else {
                return 0;
            }
        }

        $action_id = $this->getOperation($action_id);
        $movements_id = $this->getMovements()->select(['id']);
        if ($action_id) {
            $movements_id = $movements_id->andWhere(['action_id' => $action_id]);
        }

        $movements_id = $movements_id->column();


        $ostatok = Ostatok::find()->select('SUM([[qty]]) as sum1')->where(['in', 'movement_id', $movements_id]);
        if ($date) {
            $ostatok = $ostatok->andWhere(['<=', 'dateTime', $date]);
        }

        if ($ostatok = $ostatok->asArray()->all()) {
            $action = Action::findOne($action_id);
            $qty = (int)$ostatok[0]['sum1'];
            return $action->sing ? $qty : (-1 * $qty);
        }
        return 0;

    }

    public function beforeDelete()
    {
//        Временно отключил
//        if ($this->readOnly()) {
//            return false;
//        }
        $this->removeMovement();
//      Если есть дети, тогда их тоже удаляем
        foreach ($this->childs as $child) {
            $child->removeMovement();
        }

        return parent::beforeDelete();

    }

    public function afterDelete()
    {
        parent::afterDelete(); // TODO: Change the autogenerated stub
        $this->order->changeStatus();
    }

    /**
     * Можно ли редактировать запись
     * М
     */
    private $_readOnly;

    public function readOnly()
    {
        //нельзя редактировать, если уже есть выдача
        if (empty($this->_readOnly)) {
            if (($this->status_id == Status::NEW) or ($this->status_id == Status::SMETA) or (empty($this->status_id))) {
                $this->_readOnly = false;
            } else {
                $this->_readOnly = true;
            }
        }
        return $this->_readOnly;
    }

    /**
     * Небольшой костыль, что бы определить по статус, можно редактировать или нет. Сделано для view _gridOrderProduct
     * т.к. там имеем дело с массивом и нет доступа к методам модели
     * @param $status_id
     * @return bool
     */
    static public function readOnlyByStatus($status_id)
    {
        if (($status_id == Status::NEW) or ($status_id == Status::SMETA) or (empty($status_id))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Меняю операции в зависсмости от типа позици.(аренда, продажа)
     *
     */
    public function getOperation($action_id)
    {
        if ($action_id == Action::ISSUE) {
            if ($this->type == 'rent') {
                return Action::ISSUERENT;
            }
        } else if ($action_id == Action::RETURN) {
            if ($this->type == 'rent') {
                return Action::RETURNRENT;
            }
        }
        return $action_id;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::class, ['id' => 'service_id']);
    }


    /**
     * Функция деакцивации движения по позиции
     * Нужна для того, что бы деактивировать бронь(снятие брони) при выдаче(возрате) товара
     * В случае, если не полная выдача товара, тогда бронь уменьшается на кол-во выдачи
     * если $action_id не заполнено - деактивируются все движения
     * @param $action_id
     * @return boolean
     */
    public function deactivateMovement($action_id = null, $qty = null)
    {
        $movement = $this->getMovements();
        if ($action_id) {
            $movement->andWhere(['action_id' => $action_id]);
        }
//      Находим Движение, которое надо деактивировать
        if (!($movements = $movement->all())) {
//            $this->addError('movement','Нет движений по данной позиции')
            return true;
        }
        /** @var Movement $movement */
        foreach ($movements as $movement) {
            if ($qty) {
                $movement->qty = $movement->qty - $qty;
                if ($movement->qty == 0) {
                    $movement->active = 0;
                }
            } else {
                $movement->active = 0;
//                $movement->qty=$this->qty;
            }
            if (!$movement->save()) {
//                echo "tut";exit;
                return false;
            };
        }
//        /** @var Movement $movement */
//        if ($movement->one()) {
//            if ($qty) {
//                $movement->qty=$movement->qty - $qty;
//                if ($movement->qty==0) {
//                    $movement->active=0;
//                }
//            } else {
//                $movement->active=0;
////                $movement->qty=$this->qty;
//            }
//
//            return $movement->save();
//        } else {
//            return false;
//        }

    }

    /**
     * Изменяет статус позиции.
     */
    public function changeStatus()
    {
        if (($this->status_id == Status::CLOSE) or ($this->status_id == Status::CANCELORDER)) {
            return false;
        }
        if (Yii::$app->id==='app-frontend') {
            $status_id = Status::NEWFRONTEND;
        } else {
            $status_id = Status::NEW;
        }


        /** @var Movement $movement */
        foreach ($this->movements as $movement) {
//          Т.к. у нас есть нескольок выдач(для прокатных и продажных товаров) мы определяем Какое действие нам нужно
            if ($this->type == self::RENT) {
                $action_issue = Action::ISSUERENT;
                $action_return = Action::RETURNRENT;
            } else {
                $action_issue = Action::ISSUE;
                $action_return = Action::RETURN;
            }
            if (($movement->action_id == Action::HARDRESERV) and
                ($status_id == Status::NEW)) {
                $status_id = Status::SMETA;
            } else if (($movement->action_id == $action_issue) and ($status_id == Status::SMETA)) {
//              Если товара реален, тогда надо проверить остатки
                if ($movement->product_id) {
//                  Проверяем сколько выдано
                    if ($this->getOperationBalance($action_issue)) {
                        $status_id = Status::PARTISSUE;
                    } else {
                        $status_id = Status::ISSUE;
                    }
                } else {
                    $status_id = Status::ISSUE;
                }
            } else if (($movement->action_id == $action_return) and ($status_id == Status::ISSUE)) {
//              Если товара реален, тогда надо проверить остатки
                if ($movement->product_id) {
//                  Проверяем сколько возращено
                    if ($this->getOperationBalance($action_return)) {
                        $status_id = Status::PARTRETURN;
                    } else {
                        $status_id = Status::RETURN;
                    }
                }
            }
        }
        if ($this->status_id != $status_id) {
            $this->status_id = $status_id;
            $this->save();
            $this->order->changeStatus();
        }
    }

    private $_isLastCurrentStatus;

    /**
     * Определяем является ли текущий статус последним. Т.е. больше ничего с позицией делать нельзя
     * @return bool
     */
    public function isLastCurrentStatus()
    {
        if (empty($this->_isLastCurrentStatus)) {
            $this->_isLastCurrentStatus = false;
            if (($this->type == self::RENT) and ($this->status_id == Status::RETURN)) {
                $this->_isLastCurrentStatus = true;
            } else if (($this->type == self::SALE) and ($this->status_id == Status::ISSUE)) {
                $this->_isLastCurrentStatus = true;
            } else if (($this->type == self::COLLECT) and ($this->status_id == Status::ISSUE)) {
                $this->_isLastCurrentStatus = true;
//           Тут надо проверить кто является детьми
//                $rent=false;
//                foreach ($this->childs as $child) {
//                    if ($child->type==self::RENT) {
//                        $rent=true;
//                        break;
//                    }
//                }
//                if (($rent)and ($this->status_id==Status::RETURN)) {
//                    $this->_isLastCurrentStatus = true;
//                } else if (($rent===false) and ($this->status_id==Status::ISSUE)) {
//                    $this->_isLastCurrentStatus = true;
//                }
            } else if ($this->type == self::SERVICE) {
                $this->_isLastCurrentStatus = true;
            }
        }
        return $this->_isLastCurrentStatus;
    }

    /**
     * Возращаем подпись к сумме
     */
    public function getCurrency()
    {
        if ($this->type == 'rent') {
            return 'сутки/руб.';
        } else if ($this->type == 'sale') {
            return 'руб';
        }

    }

}

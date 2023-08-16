<?php

namespace common\models;
use common\models\protect\MyActiveRecord;

use rent\entities\Client\Client;
use Yii;

/**
 * This is the model class for table "{{%product}}".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $tag
 * @property string $cod
 * @property double $primeCost
 * @property double $cost
 * @property string $is_active
 * @property int $client_id
 * @property string $hash
 * @property double $priceRent
 * @property double $priceSale
 * @property double $pricePrime
 * @property string $productType
 * @property int $on_site
 *
 * @property Movement[] $movements
 * @property OrderProduct[] $orderProducts
 * @property Ostatok[] $ostatoks
 * @property \rent\entities\Client\Client $client
 */
class Product extends MyActiveRecord
{
    const PRODUCT='product';
    const SERVICE='service';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['priceRent', 'priceSale','pricePrime'], 'number'],
            [['client_id','on_site'], 'integer'],
            [['is_active'], 'string'],
            [['productType'], 'string'],
            [['name'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 1024],
            [['tag'], 'string', 'max' => 512],
            [['cod'], 'string', 'max' => 20],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['client_id' => 'id']],
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
            'description' => Yii::t('app', 'Описание'),
            'tag' => Yii::t('app', 'Теги'),
            'cod' => Yii::t('app', 'код'),
            'pricePrime' => Yii::t('app', 'Себестоимость'),
            'priceSale' => Yii::t('app', 'Цена продажи'),
            'priceRent' => Yii::t('app', 'Цена аренды'),
            'productType' => Yii::t('app', 'Тип номенклатуры'),
            'is_active' => Yii::t('app', 'Is Active'),
            'client_id' => Yii::t('app', 'Client ID'),
            'categoriesArray' => Yii::t('app', 'Категории'),
            'tagsArray' => Yii::t('app', 'Теги'),
            'on_site' => Yii::t('app', 'Отображать на сайте'),

        ];
    }

    private $_prodAttributesArray;

    public function __set($name, $value) {

        if ($this->_prodAttributesArray===null) {
            $this->_prodAttributesArray=$this->initProdAttributesByName();
        }

        if (array_key_exists($name,$this->_prodAttributesArray)) {
            $this->_prodAttributesArray[$name]->value=$value;
        } else {
            parent::__set($name, $value);
        }
    }
//

    public function __get($name)
    {
//        TODO: надо бы переделать не очень красиво
        if ($name==='id') {
            return parent::__get($name);
        }
        if ($this->_prodAttributesArray===null) {
            $this->_prodAttributesArray=$this->initProdAttributesByName();
        }

        if (array_key_exists($name,$this->_prodAttributesArray)) {
            return $this->_prodAttributesArray[$name]->value;
        };

        return parent::__get($name);
    }

    public function initProdAttributesById()
    {
        return $this->initProdAttributes('attribute_id');
    }
    public function initProdAttributesByName()
    {
        return $this->initProdAttributes('prodAttribute.attr_name');
    }

    private function initProdAttributes($columnName)
    {
        /** @var ProductAttribute[] $productAttributes*/
//        return $this->getProductAttributes();
        $productAttributes = $this->getProductAttributes()->with('prodAttribute')->indexBy($columnName)->all();
        $column=($columnName=='attribute_id')?'id':'attr_name';
        $attributes = Attribute::find()->indexBy($column)->all();

        foreach (array_diff_key($attributes,$productAttributes) as $attribute) {
            $column=($columnName=='attribute_id')?$attribute->id:$attribute->attr_name;
            $productAttributes[$column] = new ProductAttribute(['attribute_id' => $attribute->id,'product_id'=>$this->id]);
        }

        foreach ($productAttributes as $productAttribute) {
            $productAttribute->setScenario(ProductAttribute::SCENARIO_TABULAR);
        }
        return $productAttributes;
    }

    public function safeAttributes()
    {
        $names = parent::safeAttributes();
        $attributes = Attribute::find()->indexBy('attr_name')->all();
        foreach ($attributes as $attribute) {
            $names[]=$attribute->attr_name;
        }
        return $names;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMovements()
    {
        return $this->hasMany(Movement::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProducts()
    {
        return $this->hasMany(OrderProduct::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOstatoks()
    {
        return $this->hasMany(Ostatok::className(), ['product_id' => 'id']);
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
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['id' => 'category_id'])->viaTable('{{%product_category}}', ['product_id' => 'id']);
    }


    private $_categoriesArray;

    public function getCategoriesArray()
    {
        if ($this->_categoriesArray===null) {
            $this->_categoriesArray = $this->getCategories()->select('id')->column();
        }
        return $this->_categoriesArray;
    }

    public function setCategoriesArray($value)
    {
        return $this->_categoriesArray= (array)$value;
    }

    private $_tagsArray;

    public function getTagsArray()
    {
        if ($this->_tagsArray===null) {
            if ($this->_tagsArray = $this->tag?explode(',',$this->tag):array()) {
//                foreach ($this->_tagsArray as $key => $value) {
//                    $key=$value;
//                }
            }
        }
//        return $this->tag;
//        return ['red', 'green'];
        return $this->_tagsArray;
    }

    public function setTagsArray($value)
    {
        return $this->_tagsArray= (array)$value;
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->updateCategories();

        parent::afterSave($insert, $changedAttributes);
    }
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->updateTags();
            $this->updateProdAttributes();
            return true;
        } else {
            return false;
        }
    }
    private function updateProdAttributes()
    {
        foreach ($this->_prodAttributesArray as $prodAttribute) {
            $prodAttribute->save();
        }
    }
    private function updateCategories()
    {
        $currentCategoryIds = $this->getCategories()->select('id')->column();
        $newCategoryIds = $this->getCategoriesArray();
//      Если нет категории, тогда добавляем корень
        if ((count($newCategoryIds)==0)or($newCategoryIds[0]=='')) {
            $newCategoryIds=array(Category::getRoot()->id);
        }
//      тут мы ищем какие категории у нас добавились. вычитаем массив $newCategoryIds из $currentCategoryIds
//      И найденные новые категории добавляем.
        foreach (array_filter(array_diff($newCategoryIds,$currentCategoryIds))as $categoryId) {
            /** @var Category $category*/
            if ($category=Category::findOne($categoryId)) {
                $this->link('categories',$category);
            }
        }

        foreach (array_filter(array_diff($currentCategoryIds,$newCategoryIds))as $categoryId) {
            /** @var Category $category*/
            if ($category=Category::findOne($categoryId)) {
                $this->unlink('categories',$category,true);
            }
        }
    }

    /**
     * Обновляем теги.
     */
    private function updateTags()
    {
        $newTagNames=$this->getTagsArray();
        $currentTagNames=$this->tag?explode($this->tag,','):array();
        $this->tag=implode(',',$newTagNames);
        //TODO: Написать добавление тегов общий справочник.
        foreach (array_filter(array_diff($newTagNames,$currentTagNames))as $tagName) {
            Tag::findOrCreateTag($tagName);
        }
    }


    public function getThumb($size=File::THUMBMIDDLE) {
        /** @var File[] $images*/
        if ($images=$this->getFiles()) {
            return $images[0]->getUrl($size);
        } else {
            return Yii::$app->request->baseUrl.'/200c200/img/nofoto-300x243.png';
        }
    }

    public function getShortDescription() {
        return $this->description?mb_substr($this->description,0,255,'UTF-8').'...':'';
    }

    /*
     * Остаток на дату. С учетом резерва и брони
     */
    public function getBalance($dateBegin=null,$dateEnd=null,$reservSoft=false) {
        $ostatok=Ostatok::find()->where(['ostatok.product_id'=>$this->id]);

        if ($reservSoft===false) {
            $ostatok->joinWith(['movement']);
            $ostatok->andWhere(['<>','movement.action_id',1]);
            $ostatok->andWhere(['<>','movement.action_id',2]);
        }


        if (empty($dateBegin)) {
            $dateBegin=date('y-m-d');

        };
        $ostatok->andWhere(['<=','ostatok.dateTime',$dateBegin]);
        $ostatokBeginQty=(int)$ostatok->sum('ostatok.qty');
        if (!(empty($dateEnd))) {
            $ostatokEndQty=Ostatok::find()
                ->where(['ostatok.product_id'=>$this->id])
                ->andWhere(['<','ostatok.qty',0]);

            if ($reservSoft===false) {
                $ostatokEndQty->joinWith(['movement']);
                $ostatokEndQty->andWhere(['<>','movement.action_id',1]);
                $ostatokEndQty->andWhere(['<>','movement.action_id',2]);
            }

            $ostatokEndQty=$ostatokEndQty->andWhere(['>','ostatok.dateTime',$dateBegin])
                ->andWhere(['<=','ostatok.dateTime',$dateEnd])
                ->sum('ostatok.qty');
            $balance=$ostatokBeginQty+$ostatokEndQty;
            $ostatokBeginQty=($balance>$ostatokBeginQty)?$ostatokBeginQty:$balance;
        };

        return $ostatokBeginQty?$ostatokBeginQty:0;
    }
    /**
     * Публичный статичный метод, для поиска остатка
     */
    public static function getBalancById($id,$dateBegin=null,$dateEnd=null,$reservSoft=false)
    {
        if ($product=self::findOne($id)) {
            return $product->getBalance($dateBegin,$dateEnd,$reservSoft);
        } else {
            return false;
        }
    }

    /*
     * Всего сколько на товара в наличии на дату. Без учета резерва, брони и ремонта
     */
    public function getBalanceStock($date=null)
    {
        $ostatok=Ostatok::find()
            ->where(['ostatok.product_id'=>$this->id])
            ->andWhere(['actionType_id'=>ActionType::MOVE]);
        
        if (!(empty($date))) {
            $ostatok->andWhere(['=<','ostatok.dateTime',$date]);
        };
        $balance=$ostatok->sum('ostatok.qty');
        return $balance?$balance:0;
    }
    /**
     *  Количество арендованных
     */
    public function getBalanceRent($dateBegin,$dateEnd)
    {
        return false;
    }

    //    TODO: Сделать по изящнее
//https://elisdn.ru/blog/33/generaciia-url-dlia-vlojennih-kategorii-v-yii
    public function getUrl($alias=null)
    {
        if ($alias==null) {
            $category=$this->getCategories()->one();
            $alias=$category->alias;
        }
        if (\Yii::$app->id=='app-frontend') {
            $response='/catalog'.$alias.'/'.$this->id;
        } else if (\Yii::$app->id=='app-backend') {
            $response='/admin/category'.$alias.'/'.$this->id;
        }

        return $response;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductAttributes()
    {
        return $this->hasMany(ProductAttribute::className(), ['product_id' => 'id']);
    }

    /**
     * Т.к. свойство attributes зарезервировоно, поэтому переименовал так getAttributes -> getProdAttributes
     * @return \yii\db\ActiveQuery
     */
    public function getProdAttributes()
    {
        return $this->hasMany(Attribute::className(), ['id' => 'attribute_id'])->viaTable('{{%product_attribute}}', ['product_id' => 'id']);
    }

    public function getProdAttribute($id)
    {
        if ($productAttribute=ProductAttribute::find()->where(['attribute_id'=>$id])->andWhere(['product_id'=>$this->id])->one()){
            return $productAttribute;
        } else {
            return false;
        }
    }


}

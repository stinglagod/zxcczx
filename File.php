<?php

namespace common\models;

use backend\controllers\LogController;
use Codeception\Module\Cli;
use rent\entities\Client\Client;
use rent\entities\User\User;
use Yii;
use yii\helpers\BaseUrl;

/**
 * This is the model class for table "{{%file}}".
 *
 * @property int $id
 * @property resource $hash
 * @property string $ext
 * @property string $name
 * @property boolean $private
 * @property boolean $main
 * @property int $autor_id
 * @property string $created_at
 * @property string $updated_at
 * @property int $lastChangeUser_id
 * @property int $client_id
 * @property int $width
 * @property int $height
 *
 */
class File extends \yii\db\ActiveRecord
{
    const DELETE=1;
    const PUBLICATION=2;
    const NOTPUBLICATION=3;
    const MAIN=4;
    const NOTMAIN=5;

    const IMAGE=1;
    const VIDEO=2;
    const WORD=3;
    const EXCEL=4;
    const PDF=5;

    const THUMBSMALL=1;
    const THUMBMIDDLE=2;
    const FULLIMAGE=4;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%file}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hash'], 'string'],
            [['hash'], 'required'],
            [['ext'], 'string', 'max' => 4],
            [['name'], 'string', 'max' => 255],
            [['width','height'],'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'hash' => 'Hash',
            'ext' => 'Type',
            'name' => 'Имя файла',
            'width' => 'Ширина изображения',
            'height' => 'Высота изображения',
        ];
    }

    /** Директория расположения на сервере файла
     * возращает относительный путь
     * {@inheritdoc}
     */
    private function getDir() {
        $path= \Yii::$app->params['uploadDir'] . $this->hash . DIRECTORY_SEPARATOR;

        if (!(is_dir(Yii::getAlias('@backend/web'. DIRECTORY_SEPARATOR.$path))))
            mkdir(Yii::getAlias('@backend/web'.$path), 0755, true);
        return $path;
    }
    /** Получаем адрес файла
     * {@inheritdoc}
     */
    public function getUrl($size=null)
    {
        $urlSize='';
        if (($size)and (\Yii::$app->params['image_filter'])) {
            if (stristr($size,'c')) {
                $arr=explode('c',$size);
                $urlSize='/'.$arr[0].'c'.$arr[1];
            } elseif ($size == self::THUMBSMALL ){
                if ($width=\Yii::$app->params['thumbSmallWidth']){
                    $urlSize='/'.$width;
                }
                if ($height=\Yii::$app->params['thumbSmallHeight']){
                    if ($urlSize) {
                        $urlSize.='c'.$height;
                    } else {
                        $urlSize='/-c'.$height;
                    }
                } else {
                    if ($urlSize) {
                        $urlSize.='c-';
                    }
                }
            }elseif ($size == self::THUMBMIDDLE ){
                if ($width=\Yii::$app->params['thumbMiddleWidth']){
                    $urlSize='/'.$width;
                }
                if ($height=\Yii::$app->params['thumbMiddleHeight']){
                    if ($urlSize) {
                        $urlSize.='c'.$height;
                    } else {
                        $urlSize='/-c'.$height;
                    }
                }
            }elseif ($size ==self::FULLIMAGE){
                if ($width=\Yii::$app->params['imageWidth']){
                    $urlSize='/'.$width;
                }
                if ($height=\Yii::$app->params['imageHeight']){
                    if ($urlSize) {
                        $urlSize.='c'.$height;
                    } else {
                        $urlSize='/-c'.$height;
                    }
                }
            }
        }
        return '/admin'.$urlSize.$this->getDir().$this->id.'.'.$this->ext;
    }
    /** Получаем физическое расположение на сервере
     * {@inheritdoc}
     */
    public function getPath()
    {
        return \Yii::getAlias('@backend/web').$this->getDir().$this->id.'.'.$this->ext;
    }

    private $_path;
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->client_id=User::findOne(Yii::$app->user->id)->client_id;
            $this->_path=$this->getPath();
            return true;
        } else {
            return false;
        }
    }
    public function afterDelete()
    {
        if (parent::afterDelete()) {
            unlink($this->_path);
            unset($this->_path);
            return true;
        } else {
            return false;
        }
//
    }

    public static function getQtyFiles($hash)
    {
        return File::find()->where(['hash'=>$hash])->count();
    }
    public static function getFiles($hash)
    {
        return File::find()->where(['hash'=>$hash])->all();

    }

    /** Получаем миниатюру файла
     * {@inheritdoc}
     */
    public function getThumb($size=null)
    {
        switch ($this->format) {
            case self::IMAGE:
                return ($this->getUrl());
                return ($this->getDir().$this->id.'.'. $this->ext);
                return '/'.$this->getDir() . $this->id .'.'. $this->ext;
            case self::VIDEO:
                return '/images/mp4.png';
            case self::WORD:
                return '/images/docx.png';
            case self::EXCEL:
                return '/images/xlsx.png';
            case self::PDF:
                return '/images/pdf.png';
            default:
                return '/images/nofoto.png';

        }
    }
    /**
     * Определяем тип файлова
     *
     * {@inheritdoc}
     */
    public function getFormat()
    {
        if (($this->ext=='bmp')
            or ($this->ext=='jpg')
            or ($this->ext=='jpeg')
            or ($this->ext=='png')){
            return self::IMAGE;
        } elseif ($this->ext=='mp4') {
            return self::VIDEO;
        } elseif (($this->ext=='doc') or ($this->ext=='docx')) {
            return self::WORD;
        } elseif (($this->ext=='xls') or ($this->ext=='xlsx')) {
            return self::EXCEL;
        } elseif (($this->ext=='pdf')) {
            return self::PDF;
        }
    }
    /**
     * Получаем файл по имени файла(url)
     * {@inheritdoc}
     */
    public function getFileByFile($file,$filename='')
    {
        if (!($filename)) {
            $tmp=explode('/',$file);
            $filename=array_pop($tmp);
        }
        $ext = explode('.', $filename);
        $ext = array_pop($ext);
        $this->ext=$ext;
        $this->name=$filename;

        if(rename($file, $this->getPath())){
            return $this->save();
        }
        return false;
    }
    /**
     * Получаем файл по тексту
     * {@inheritdoc}
     */
    public function getFileByString($file)
    {
        return true;
    }
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                if (empty($this->autor_id)) {
                    $this->autor_id=Yii::$app->user->id;
                }
                $this->created_at=date('Y-m-d H:i:s');
            }
            if (empty($this->lastChangeUser_id)) {
                $this->lastChangeUser_id=Yii::$app->user->id;
            }
            $this->updated_at = date('Y-m-d H:i:s');




            return true;
        } else {
            return false;
        }
    }
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

//      записываем разрешение для картинок
//        if (($this->getFormat()==self::IMAGE)and (empty($this->width))) {
//            list($width, $height, $type, $attr) = getimagesize($this->getPath());
//            $this->width=$width;
//            $this->height=$height;
//            $this->save();
//        }

        \Yii::error($changedAttributes);
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }
    
}

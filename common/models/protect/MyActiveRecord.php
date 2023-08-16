<?php
namespace common\models\protect;

use common\models\File;
use rent\entities\User\User;
use yii\db\ActiveRecord;
use yii\db\Query;

class MyActiveRecord extends ActiveRecord
{
    /**
     * Возращаемс хеш любой модели
     * @return string hash
     */
    public function getHash()
    {
        return md5(get_class($this) . '-' . $this->id);
    }

    protected function getQtyFiles()
    {
        return File::getQtyFiles($this->hash);
    }

//    TODO: сделать выборку файлов по расширениям
    public function getFiles($type=null)
    {
        if ($this->isNewRecord) {
            return false;
        } else {
            return File::find()->where(['hash'=>$this->hash])->all();
        }
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->canGetProperty('client_id'))
                if (empty($this->client_id)) {
                    $this->client_id=User::findOne(\Yii::$app->user->id)->client_id;
                }
            if ($this->isNewRecord) {
                if ($this->canGetProperty('created_at'))
                    $this->created_at=date('Y-m-d H:i:s');
                if ($this->canGetProperty('autor_id'))
                    $this->autor_id=\Yii::$app->user->id;
            }
            if ($this->canGetProperty('lastChangeUser_id'))
                $this->lastChangeUser_id = \Yii::$app->user->id;

            if ($this->canGetProperty('updated_at'))
                $this->updated_at = date('Y-m-d H:i:s');
            return true;
        } else {
            return false;
        }
    }

}
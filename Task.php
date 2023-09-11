<?php

namespace rent\entities\Support\Task;

use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use rent\entities\behaviors\ClientBehavior;
use rent\entities\Client\Client;
use rent\entities\Client\Site;
use rent\entities\Support\Task\File;
use rent\entities\User\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * @property integer $id
 * @property string $name                           //имя задачи
 * @property string $text                           //описание задачи
 * @property integer $responsible_id                //Ответственный
 * @property string $responsible_name
 * @property integer $customer_id                   //Инициатор
 * @property string $customer_name
 * @property integer $status                        //Статус
 * @property integer $type                          //Тип
 * @property integer $is_completed                  //Выполнена
 * @property string $commentClosed                  //Комментарий почему закрыта, но не выполнена
 * @property integer $priority                      //Приоритет
 *
 * @property integer $site_id
 * @property string $site_name
 * @property integer $client_id
 * @property string $client_name
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $author_id
 * @property integer $lastChangeUser_id
 *
 * @property \rent\entities\Client\Site $site
 * @property Comment[] $comments
 * @property File[] $files
 *
 */
class Task extends ActiveRecord
{
    const PREFIX_DEFAULT_NAME='Задача';
    const STATUS_NEW=1;                 //Новая заявка
    const STATUS_IN_WORK=5;             //В работе
    const STATUS_WAITING_RESPONSE=7;    //Ожидание ответа
    const STATUS_SEND_RESPONSE=8;       //Ответ получен
    const STATUS_CLOSED=10;             //Закрыта
    const STATUS_DELETED=15;            //Удалена

    const TYPE_BUG=1;                   //Ошибка
    const TYPE_PROPOSAL=5;              //Предложение
    const TYPE_ENHANCEMENT=10;          //Улучшение

    public static function create(
                                  string $name,
                                  User $customer,
                                  string $text,
                                  Client $client,
                                  int $type,
                                  User $responsible,
                                  int $priority

    ):self
    {

        return $entity=new self([
            'name'=>$name,
            'text'=>$text,
            'customer_id'=>$customer->id,
            'customer_name'=>$customer->getShortName(),
            'client_id'=>$client->id,
            'client_name'=>$client->name,
            'status'=>self::STATUS_NEW,
            'type'=>$type,
            'responsible_id'=>$responsible?$responsible->id:null,
            'responsible_name'=>$responsible?$responsible->getShortName():null,
            'priority'=>$priority,
        ]);
    }
    public function edit(string $name,string $text,int $type,int $status,User $responsible,$priority,?Client $client=null):void
    {
        $this->name=$name;
        $this->text=$text;
        $this->changeResponsible($responsible);
        $this->changeType($type);
        $this->changePriority($priority);
        $this->status=$status;
        if ($client) {
            $this->client_id=$client->id;
            $this->client_name=$client->name;
        }
    }
    public function changeResponsible(User $responsible):void
    {
        $this->responsible_id=$responsible->id;
        $this->responsible_name=$responsible->getShortName();
    }
    public function changeType(int $type):void
    {
        $this->type=$type;
    }
    public function changePriority(int $priority):void
    {
        $this->priority=$priority;
    }

    public function onInWork():void
    {
        $this->status=self::STATUS_IN_WORK;
    }
    public function onClose(bool $isCompleted=true,string $commentClosed=null):void
    {
        $this->status=self::STATUS_CLOSED;
        $this->is_completed=$isCompleted;
        $this->commentClosed=$commentClosed;
        if ($isCompleted===false) {
            if (empty($commentClosed)) {
                throw new \RuntimeException('Ошибка! Нельзя закрыть не выполненную заявку без причины закрытия');
            }
        }
    }
    public function onDelete():void
    {
        $this->status=self::STATUS_DELETED;
    }
    public function onWaitingResponse():void
    {
        $this->status=self::STATUS_WAITING_RESPONSE;
    }
    public function onSendResponse():void
    {
        $this->status=self::STATUS_SEND_RESPONSE;
    }

    public function isStatusNew():bool
    {
        return $this->status==self::STATUS_NEW;
    }
    public function isResponsible(int $authorId):bool
    {
        return $this->responsible_id==$authorId;
    }

#Comment
    public function addComment(string $message,User $author):Comment
    {
        $comments=$this->comments;
        $comment=Comment::create($message,$author);
        $comments[]=$comment;
        $this->comments=$comments;

        return $comment;
    }
#File
    public function addFile(UploadedFile $file): void
    {
        $files = $this->files;
        $files[] = File::create($file);
        $this->files=$files;
    }

    public function removeFile($id): void
    {
        $files = $this->files;
        foreach ($files as $i => $file) {
            if ($file->isIdEqualTo($id)) {
                unset($files[$i]);
                $this->files=$files;
                return;
            }
        }
        throw new \DomainException('File is not found.');
    }
    public function getClient() :ActiveQuery
    {
        return $this->hasOne(Site::class, ['id' => 'client_id']);
    }

    public function getSite() :ActiveQuery
    {
        return $this->hasOne(Site::class, ['id' => 'site_id']);
    }

    public function getComments():ActiveQuery
    {
        return $this->hasMany(Comment::class, ['task_id' => 'id']);
    }
    public function getFiles():ActiveQuery
    {
        return $this->hasMany(File::class, ['task_id' => 'id']);
    }

    public static function tableName(): string
    {
        return '{{%support_tasks}}';
    }

    public function behaviors(): array
    {
        return [
            ClientBehavior::class,
            TimestampBehavior::class,
            'SaveRelationsBehavior'=>
                [
                    'class' => SaveRelationsBehavior::class,
                    'relations' => [
                        'comments',
                        'files'
                    ],
                ],
        ];
    }
    public function attributeLabels()
    {
        return self::getAttributeLabels();
    }

    /**
     * Вывел в статику, что бы иметь доступ извне не создавая объект
     * @return void
     */
    public static function getAttributeLabels():array
    {
        return [
            'name'=>'Название',
            'text'=>'Текст заявки',
            'responsible_id'=>'Ответственный',
            'customer_id'=>'Инициатор',
            'status'=>'Статус',
            'type'=>'Тип заявки',
            'is_completed'=>'Выполнена?',
            'commentClosed'=>'Комментарий почему не выполнена',
            'priority'=>'Приоритет',
            'site_id'=>'Сайт',
            'client_id'=>'Клиент',
            'created_at'=>'Дата создания',
            'updated_at'=>'Дата редактирования',
            'author_id'=>'Автор',
            'lastChangeUser_id'=>'Последний редактор',

        ];
    }
    /**
     * @throws \Exception
     */
    public static function getLabelByAttribute(string $attribute):?string
    {
        $result=ArrayHelper::getValue(self::getAttributeLabels(), $attribute);

        return $result??$attribute;
    }
    public static function getAttributeDescriptions():array
    {
        return [
            'commentClosed'=>'Если заявка закрыта не выполненной, тогда обязательно нужно написать причину',
        ];
    }
    public static function getDescriptionByAttribute(string $attribute):?string
    {
        $result=ArrayHelper::getValue(self::getAttributeDescriptions(), $attribute);
        return $result??'';
    }

    public static function getPriorityLabels():array
    {
        return [
          0 => 'Без приоритета',
          1 => '1',
          2 => '2',
          3 => '3 - срочно!',
        ];
    }
    public static function getPriorityLabel($attribute):?string
    {

        $result=ArrayHelper::getValue(self::getPriorityLabels(), $attribute);
        return $result??$attribute;
    }
    public static function getTypeLabels():array
    {
        return [
          static::TYPE_BUG => 'Ошибка',
          static::TYPE_PROPOSAL => 'Предложение(обсуждение)',
          static::TYPE_ENHANCEMENT => 'Улучшение',
        ];
    }
    public static function getTypeLabel($attribute):?string
    {
        $result=ArrayHelper::getValue(self::getTypeLabels(), $attribute);
        return $result??$attribute;
    }

    public static function getStatusLabels():array
    {
        return [
            static::STATUS_NEW => 'Новая',
            static::STATUS_IN_WORK => 'В работе',
            static::STATUS_WAITING_RESPONSE => 'Ожидание ответа пользователя',
            static::STATUS_SEND_RESPONSE => 'Ответ получен',
            static::STATUS_CLOSED => 'Закрыта',
            static::STATUS_DELETED => 'Удалена',
        ];
    }
    public static function getStatusLabel($attribute):?string
    {
        $result=ArrayHelper::getValue(self::getStatusLabels(), $attribute);
        return $result??$attribute;
    }
    public static function find($all=false)
    {
        if (($all)or \Yii::$app->user->can('super_admin')) {
            return parent::find();
        } else {
            return parent::find()->andWhere(['client_id' => Yii::$app->settings->getClientId()]);
        }

    }
    ###
//    private function getDefaultName():string
//    {
//        return self::PREFIX_DEFAULT_NAME . ' №: ' .$this->id;
//    }
    public function canAddComment():bool
    {
        return (
            $this->status == self::STATUS_NEW or
            $this->status == self::STATUS_IN_WORK or
            $this->status == self::STATUS_WAITING_RESPONSE or
            $this->status == self::STATUS_SEND_RESPONSE
        );
    }




}

<?php
namespace common\models;

use Yii;
use yii\base\Model;
use rent\entities\User\User;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\rent\entities\User\User',
                'filter' => ['status' => \rent\entities\User\User::STATUS_ACTIVE],
                'message' => 'Нет пользователя с таким адресом электронной почты.'
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = \rent\entities\User\User::findOne([
            'status' => \rent\entities\User\User::STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        if (!$user) {
            return false;
        }
        
        if (!\rent\entities\User\User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user]
            )
            ->setFrom(Yii::$app->params['robotEmail'])
            ->setTo($this->email)
            ->setSubject('Пароль сброшен для ' . Yii::$app->name)
            ->send();
    }
}

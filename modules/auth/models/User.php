<?php

namespace auth\models;

use Yii;
use helpers\traits\UserJWT;
use scheduler\models\Appointments;
use scheduler\models\ManagedUsers;
use yii\web\User as WebUser;

class User extends BaseModel implements \yii\web\IdentityInterface
{
    use UserJWT;
    public $token;

    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;
   

    public static function tableName()
    {
        return '{{%users}}';
    }

    public function rules()
    {
        return [
            ['username', 'required'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
        ];
    }

    public function afterLogin(WebUser $identity, $cookieBased, $duration)
    {
        parent::afterLogin($identity, $cookieBased, $duration);
        $this->last_login_at = date('Y-m-d H:i:s');
        $this->save(false, ['last_login_at']);
    }  

    public function getOnlineStatus()
    {
        if (!$this->last_login_at) {
            return 'Never logged in';
        }

        $lastLoginTime = strtotime($this->last_login_at);
        $timeDifference = time() - $lastLoginTime;

        if ($timeDifference <= 1) { 
            return 'Online';
        }

        return Yii::$app->formatter->asRelativeTime($this->last_login_at);
    }


    public static function findIdentity($id)
    {
        return static::findOne(['user_id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public function fields()
    {
        return ['username', 'token', 'updated_at', 'created_at'];
    }

    public static function findByUsername($username)
    {
        return static::find()
            //->select('username, password_hash, auth_key, created_at, updated_at')
            //->where(['status' => self::STATUS_ACTIVE])
            ->andWhere(['OR', ['username' => $username]])
            ->one();
    }
    
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
            'is_deleted' => FALSE,
        ]);
    }
    
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->token = $this->getJWT();
        
        $parse = Yii::$app->formatter;
        $this->created_at = $parse->asDate($this->created_at, 'php:Y-m-d H:i:s');
        $this->updated_at = $parse->asDate($this->updated_at, 'php:Y-m-d H:i:s');
    }
    
    public function getId()
    {
        return $this->getPrimaryKey();
    }
    
    public function getAuthKey()
    {
        return $this->auth_key;
    }
    
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
    
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
    
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
    
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
    public function getProfile(){
         return $this->hasOne(Profiles::class, ['user_id' => 'user_id']);
    }

    public function getAppointments()
    {
        return $this->hasMany(Appointments::class, ['user_id' => 'user_id']);
    }

    public function getManagedBy()
{
    return $this->hasMany(ManagedUsers::class, ['user_id' => 'user_id']);
}
}

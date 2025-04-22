<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $full_name
 * @property string $phone
 * @property string $email
 * @property string $username
 * @property string $password
 * @property string $role
 *
 * @property Request[] $requests
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{

    /**
     * ENUM field values
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role'], 'default', 'value' => 'user'],
            [['full_name', 'phone', 'email', 'username', 'password'], 'required'],
            [['role'], 'string'],
            [['full_name', 'email', 'username', 'password'], 'string', 'max' => 100],
            [['phone'], 'string', 'max' => 20],
            ['role', 'in', 'range' => array_keys(self::optsRole())],
            [['username'], 'unique'],
            [['email'], 'unique'],
            ['email', 'email'],
            [['phone'], 'unique'],
            ['username', 'match', 'pattern' => '/^[A-z]\w*$/i'],
            ['full_name', 'match', 'pattern' => '/^[А-яЁё -]*$/u', 'message'=> 'Разрешен ввод только кириллицы, пробела и тире'],
            ['phone', 'match', 'pattern' => '/^\+?7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'],
            [['password'], 'string', 'min' => 6],
        ];
    }

    public function beforeSave($insert){
        $this->password = md5($this->password);
        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'full_name' => 'ФИО',
            'phone' => 'Телефон',
            'email' => 'Email',
            'username' => 'Логин',
            'password' => 'Пароль',
        ];
    }

    /**
     * Gets query for [[Requests]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequests()
    {
        return $this->hasMany(Request::class, ['user_id' => 'id']);
    }


    /**
     * column role ENUM value labels
     * @return string[]
     */
    public static function optsRole()
    {
        return [
            self::ROLE_ADMIN => 'admin',
            self::ROLE_USER => 'user',
        ];
    }

    /**
     * @return string
     */
    public function displayRole()
    {
        return self::optsRole()[$this->role];
    }

    /**
     * @return bool
     */
    public function isRoleAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function setRoleToAdmin()
    {
        $this->role = self::ROLE_ADMIN;
    }

    /**
     * @return bool
     */
    public function isRoleUser()
    {
        return $this->role === self::ROLE_USER;
    }

    public function setRoleToUser()
    {
        $this->role = self::ROLE_USER;
    }

    public static function findIdentity($id){
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null){
        return null;
    }

    public function getId(){
        return $this->id;
    }

    public function getAuthKey(){
        return null;
    }
    public function validateAuthKey($authKey){
        return false;
    }
    public static function findByUsername($username)
    {
        return User::findOne(['username' => $username]);
    }

    public function validatePassword($password){
        return $this->password === md5($password);
    }
}

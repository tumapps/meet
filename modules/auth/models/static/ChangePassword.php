<?php

namespace auth\models\static;

use Yii;
use yii\base\Model;
use auth\models\PasswordHistory;
use auth\models\User;


/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user This property is read-only.
 *
 */
class ChangePassword extends Model
{
    public  $oldPassword;
    public  $newPassword;
    public  $confirm_password;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [

            [['oldPassword', 'confirm_password'], 'required', 'message' => 'This field can not be blank'],
            ['newPassword', 'required', 'message' => 'This field can not be blank'],
            ['newPassword', 'string', 'min' => 8],
            ['newPassword', 'validatePassword'],
            ['newPassword', 'validateNewPassword'],
            ['oldPassword', 'validateOldPassword'],
            ['confirm_password', 'required', 'message' => 'This field can not be blank'],
            ['confirm_password', 'compare', 'compareAttribute' => 'newPassword', 'message' => "The passwords do not match."],
            // ['newPassword', 'match', 'pattern' => '/^\S*(?=\S*[\W])(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', 'message' => 'Password Should contain at atleast: 1 number, 1 lowercase letter, 1 uppercase letter and 1 special character'],
        ];
    }

    /**
     * Validates the old password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateOldPassword($attribute, $params)
    {
        $user = Yii::$app->user->identity;

        if (!$user || !Yii::$app->security->validatePassword($this->oldPassword, $user->password_hash)) {
            $this->addError($attribute, 'The old password is incorrect.');
        }
    }

    public function validatePasswordMatch($attribute, $params)
    {
        if ($this->newPassword !== $this->confirm_password) {
            $this->addError($attribute, 'The passwords do not match.');
        }
    }


    // Validate the new password against password history
    public function validateNewPassword($attribute, $params)
    {
        $user = Yii::$app->user->identity;
        $newPasswordHash = md5($this->newPassword);

        // Check if the new password is in the password history
        if (PasswordHistory::find()->where(['user_id' => $user->id, 'old_password' => $newPasswordHash])->exists()) {
            $this->addError($attribute, 'The new password cannot be the same as any of your previous passwords.');
        }
    }

    // public function validatePassword($attribute, $params)
    // {
    //     $password = $this->$attribute;

    //     $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    //     if (!preg_match($pattern, $password)) {
    //         $this->addError($attribute, 'The password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one special symbol, and one number.');
    //     }
    // }

    public function validatePassword($attribute, $params)
    {
        $password = $this->$attribute;

        if (!preg_match('/[A-Z]/', $password)) {
            $this->addError($attribute, 'The password must include at least one uppercase letter.');
            return;
        }

        if (!preg_match('/[a-z]/', $password)) {
            $this->addError($attribute, 'The password must include at least one lowercase letter.');
            return;
        }

        if (!preg_match('/\d/', $password)) {
            $this->addError($attribute, 'The password must include at least one numeric character.');
            return;
        }

        if (!preg_match('/[@$!%*?&]/', $password)) {
            $this->addError($attribute, 'The password must include at least one special symbol');
            return;
        }
    }



    /**
     * updates password of the currently logged in user.
     * @return bool whether the password is updated successfully
     */

    public function updatePassword()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = Yii::$app->user->identity;

        if (!$user instanceof \auth\models\User) {
            $this->addError('newPassword', 'Unable to retrieve user record.');
            return false;
        }

        if (!Yii::$app->security->validatePassword($this->oldPassword, $user->password_hash)) {
            $this->addError('oldPassword', 'The current password is incorrect.');
            return false;
        }

        $newPasswordHash = Yii::$app->security->generatePasswordHash($this->newPassword);
        $user->password_hash = $newPasswordHash;

        if ($user->save()) {
            $passwordHistory = new PasswordHistory();
            $passwordHistory->user_id = $user->id;
            $passwordHistory->old_password = md5($this->oldPassword);
            if (!$passwordHistory->save()) {
                Yii::error('Failed to save password history: ' . json_encode($passwordHistory->getErrors()), __METHOD__);
            }

            return true;
        }

        $this->addError('newPassword', 'Failed to update the password.');
        return false;
    }
}

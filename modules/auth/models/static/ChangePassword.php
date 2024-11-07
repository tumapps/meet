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

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
         
            ['oldPassword', 'required', 'message' => 'This field can not be blank'],
            // ['newPassword', 'required', 'message' => 'Please choose a password you can remember'],
            ['newPassword', 'required', 'message' => 'This field can not be blank'],
            ['newPassword', 'string', 'min' => 8],
            ['newPassword', 'validateNewPassword'],
            ['oldPassword', 'validateOldPassword'],
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
        $oldPasswordHash = md5($this->oldPassword);

        // Update the user's password
        $user->password_hash = Yii::$app->security->generatePasswordHash($this->newPassword);
        
        if ($user->save()) {
            // Store the old password in the password history
            $passwordHistory = new PasswordHistory();
            $passwordHistory->user_id = $user->id;
            $passwordHistory->old_password = $oldPasswordHash;
            $passwordHistory->save();

            return true;
        }

        return false;

    }
}

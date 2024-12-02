<?php

namespace auth\controllers;

use Yii;
use yii\web\Response;
use auth\models\static\Login;
use auth\models\static\Register;
use auth\models\static\PasswordReset;
use auth\models\static\ChangePassword;
use auth\models\static\PasswordResetRequest;
use auth\models\RefreshToken;
use auth\models\User;
use auth\models\searches\UserSearch;
use auth\models\Profiles;
use Google\Service\Analytics\Profile;
use yii\base\InvalidArgumentException;

class AuthController extends \helpers\ApiController
{

	public function actionIndex()
	{
		$searchModel = new UserSearch();
		$search = $this->queryParameters(Yii::$app->request->queryParams, 'UserSearch');
		$dataProvider = $searchModel->search($search);

		// Fetch data
		$users = $dataProvider->getModels();
		$authManager = Yii::$app->authManager;

		foreach ($users as &$user) {
			// Only include necessary fields
			$userData = [
				'id' => $user->id,
				'username' => $user->username,
				'email' => $user->profile->email_address,
				'fullname' => $user->profile->first_name . ' ' . $user->profile->last_name,
				'mobile' => $user->profile->mobile_number,
				'roles' => array_keys($authManager->getRolesByUser($user->id)),
			];

			$user = $userData;
		}

		$dataProvider->setModels($users);

		return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
	}



	public function actionGetUsers()
	{
		// $profiles = Profiles::find()->all();
		$profiles = Profiles::find()
			->joinWith('user')
			->where(['users.can_be_booked' => true])
			->all();

		if (empty($profiles)) {
			return $this->errorResponse(['message' => 'No profiles found']);
		}
		$formattedProfiles = [];
		foreach ($profiles as $profile) {
			$formattedProfiles[] = [
				'user_id' => $profile->user_id,
				'email' => $profile->email_address,
				'name' => $profile->first_name . ' ' . $profile->last_name,
				'middle_name' => $profile->middle_name,
				'mobile_number' => $profile->mobile_number
			];
		}
		return $this->payloadResponse(['profiles' => $formattedProfiles]);
	}

	public function actionToggleAccountStatus($id)
	{
		if (!$id) {
			return $this->errorResponse(['message' => ['User ID is required']]);
		}

		$user = User::findOne($id);
		if (!$user) {
			return $this->errorResponse(['message' => ['User not found']]);
		}

		if ($user->status === User::STATUS_INACTIVE) {
			$user->status = User::STATUS_ACTIVE;
			$message = 'Account unlocked successfully';
		} else {
			$user->status = User::STATUS_INACTIVE;
			$message = 'Account locked successfully';
		}

		if ($user->save(false)) {
			return $this->payloadResponse(['message' => [$message]]);
		} else {
			return $this->errorResponse(['message' => ['Failed to update account status']]);
		}
	}


	public function actionGetUser($id)
	{
		if (!$id) {
			return $this->errorResponse(['message' => ['User ID is required']]);
		}
		$user = User::findOne($id);
		if (!$user) {
			return $this->errorResponse(['message' => ['User not found']]);
		}

		$profile = Profiles::find()->where(['user_id' => $id])->one();
		if (!$profile) {
			return $this->errorResponse(['message' => ['Profile not found']]);
		}

		$roles = \Yii::$app->authManager->getRolesByUser($id);
		$roleNames = array_keys($roles);

		$formattedUser = [
			'user_id' => $user->id,
			'status' => $user->status,
			'username' => $user->username,
			'email' => $profile->email_address,
			'name' => trim($profile->first_name . ' ' . $profile->last_name),
			'first_name' => $profile->first_name,
			'first_last' => $profile->last_name,
			'mobile_number' => $profile->mobile_number,
			'roles' => $roleNames,
		];

		return $this->payloadResponse(['user' => $formattedUser]);
	}


	public function actionSearchUser() {}

	public function actionLogin()
	{
		$model = new Login();
		$dataRequest['Login'] = Yii::$app->request->getBodyParams();
		if ($model->load($dataRequest) && $model->login()) {
			$user = Yii::$app->user->identity;
			$canBeBooked = $user->can_be_booked;
			$roles = Yii::$app->authManager->getRolesByUser($user->user_id);
			$this->generateRefreshToken($user);

			return $this->payloadResponse(
				[
					'username' => $user->username,
					'token' => $user->token,
					'menus' => $this->filterMenus($roles),
					'roles' => array_keys($roles)
				],
				['statusCode' => 200, 'message' => 'Access granted']
			);
		}
		return $this->errorResponse($model->getErrors());
	}


	public function filterMenus($roles)
	{
		$menus = Yii::$app->params['menus'];

		$roleNames = array_keys($roles);

		if (in_array('su', $roleNames)) {
			return $menus;
		} elseif (in_array('secretary', $roleNames)) {
			$allowedRoutes = ['default.dashboard', 'appointments'];
		} elseif (in_array('user', $roleNames)) {
			$allowedRoutes = ['default.dashboard', 'appointments', 'availability'];
		} else {
			$allowedRoutes = [];
		}

		$menus = array_values(array_filter($menus, function ($menu) use ($allowedRoutes) {
			return in_array($menu['route'], $allowedRoutes);
		}));

		return $menus;
	}



	public function actionRegister()
	{
		$model = new Register();
		$dataRequest['Register'] = Yii::$app->request->getBodyParams();
		if ($model->load($dataRequest)) {
			if ($model->save()) {
				return $this->payloadResponse(['username' => $dataRequest['Register']['username']], ['statusCode' => 200, 'message' => 'Created Successfully']);
			}
			return $this->errorResponse($model->getErrors());
		}
	}

	public function actionPasswordResetRequest()
	{
		$model = new PasswordResetRequest();
		$dataRequest['PasswordResetRequest'] = Yii::$app->request->getBodyParams();

		if ($model->load($dataRequest) && $model->validate()) {
			if ($model->sendEmail()) {
				return $this->payloadResponse(['message' => 'Password reset link has been sent to your email']);
			} else {
				return $this->errorResponse(['message' => 'Unable to send password reset email. Please try again later.']);
			}
		}

		return $this->errorResponse($model->getErrors());
	}

	public function actionResetPassword()
	{
		$dataRequest['PasswordReset'] = Yii::$app->request->getBodyParams();
		$token = $dataRequest['PasswordReset']['token'] ?? null;

		if (empty($token)) {
			return $this->errorResponse(['message' => ['missing password reset token']]);
		}

		try {
			$model = new PasswordReset($token);
		} catch (InvalidArgumentException $e) {
			return $this->errorResponse(['message' => [$e->getMessage()]]);
		}

		if ($model->load($dataRequest) && $model->validate()) {
			if ($model->resetPassword()) {
				return $this->payloadResponse(['message' => 'Password updated successfully']);
			}
		}

		return $this->errorResponse($model->getErrors());
	}

	public function actionChangePassword()
	{
		// $user = Yii::$app->user->identity;
		$model = new ChangePassword();
		$dataRequest['ChangePassword'] = Yii::$app->request->getBodyParams();

		if ($model->load($dataRequest) && $model->updatePassword()) {
			return $this->payloadResponse(['message' => 'Your Password has been updated successfully']);
		}
		return $this->errorResponse($model->getErrors());
	}

	public function actionMe()
	{
		$user = Yii::$app->user->identity;
		if (!$user) {
			return $this->errorResponse(['errorMassage' => ['You must be logged in to view your profile']]);
		}

		if (Yii::$app->request->isPut) {
			$profile = $user->profile;
			$dataRequest = Yii::$app->request->getBodyParams();

			if ($profile->load($dataRequest, '')) {
				if (!$profile->validate()) {
					return $this->errorResponse($profile->getErrors());
				}

				if ($profile->save(false)) {
					return $this->payloadResponse(['message' => 'Profile updated successfully', 'profile' => $profile], ['statusCode' => 200]);
				} else {
					return $this->errorResponse(['errorMassage' => ['Failed to update profile']]);
				}
			} else {
				return $this->errorResponse(['errorMassage' => ['Failed to load profile data']]);
			}
		}

		return $this->payloadResponse($user->profile, ['statusCode' => 201]);
	}

	public function actionRefresh()
	{
		$refreshToken = Yii::$app->request->cookies->getValue('refresh-token', false);
		if (!$refreshToken) {
			// return $this->errorResponse(['statusCode' => [440]]);
			Yii::$app->response->statusCode = 440;
			return ['errorResponse' => ['errors' => ['message' => 'session expired']]];
		}
		$userRefreshToken = RefreshToken::findOne(['token' => $refreshToken]);
		if (Yii::$app->request->getMethod() == 'POST') {
			// Getting new JWT after it has expired
			if (empty($userRefreshToken)) {
				// return $this->errorResponse(['statusCode' => [440]]);
				Yii::$app->response->statusCode = 440;
				return ['errorResponse' => ['errors' => ['message' => 'session expired']]];
			}
			$user = User::findIdentity($userRefreshToken->user_id);
			if (empty($user)) {
				$userRefreshToken->delete();
				// return $this->errorResponse(['statusCode' => [440]]);
				Yii::$app->response->statusCode = 440;
				return ['errorResponse' => ['errors' => ['message' => 'session expired']]];
			}
			$this->generateRefreshToken($user);
			return $this->payloadResponse(['username' => $user->username, 'token' => (string) $user->token], ['statusCode' => 200]);
		} elseif (Yii::$app->request->getMethod() == 'DELETE') {
			// Logging out
			if (!empty($userRefreshToken)) {
				$userRefreshToken->forceDelete();
			}
			return $this->toastResponse(['statusCode' => 200, 'message' => 'Logout successful']);
		}
		return $this->errorResponse(500);
	}


	private function generateRefreshToken(User $user)
	{
		$refreshToken = Yii::$app->security->generateRandomString(200);
		// TODO: Don't always regenerate - you could reuse existing one if user already has one with same IP and user agent
		if (($userRefreshToken = RefreshToken::findOne(['user_id' => $user->user_id, 'user_agent' => Yii::$app->request->userAgent])) !== null) {
			$userRefreshToken->ip = Yii::$app->request->userIP;
			$refreshToken = $userRefreshToken->token;
		} else {
			$userRefreshToken = new RefreshToken([
				'user_id' => $user->user_id,
				'token' => $refreshToken,
				'ip' => Yii::$app->request->userIP,
				'user_agent' => Yii::$app->request->userAgent,
				//'data' => Yii::$app->request,
			]);
		}
		if (!$userRefreshToken->save(false)) {
			// return $this->errorResponse(['statusCode' => [440]]);
			Yii::$app->response->statusCode = 440;
			return ['errorResponse' => ['errors' => ['message' => 'session expired']]];
		}
		// Send the refresh-token to the user in a HttpOnly cookie that Javascript can never read and that's limited by path
		Yii::$app->response->cookies->add(new \yii\web\Cookie([
			'name' => 'refresh-token',
			'value' => $refreshToken,
			'httpOnly' => true,
			'sameSite' => 'none',
			'secure' => true,
			'path' => '/v1/auth/refresh',  //endpoint URI for renewing the JWT token using this refresh-token, or deleting refresh-token
		]));
		return $userRefreshToken;
	}
}

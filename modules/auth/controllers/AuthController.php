<?php

namespace auth\controllers;

use Yii;
use auth\models\static\Login;
use auth\models\static\Register;
use auth\models\static\PasswordReset;
use auth\models\static\ChangePassword;
use auth\models\static\PasswordResetRequest;
use auth\models\RefreshToken;
use auth\models\User;
use auth\models\searches\UserSearch;
use auth\models\Profiles;
use scheduler\models\AppointmentAttendees;
use scheduler\models\ManagedUsers;
use yii\base\InvalidArgumentException;

class AuthController extends \helpers\ApiController
{
	public function actionIndex()
	{
		$searchModel = new UserSearch();
		$search = $this->queryParameters(Yii::$app->request->queryParams, 'UserSearch');
		$dataProvider = $searchModel->search($search);

		$users = $dataProvider->getModels();
		$authManager = Yii::$app->authManager;

		// Retrieve query parameters to be used when searching attendees
		$queryParams = Yii::$app->request->queryParams;
		$type = $queryParams['type'] ?? null;
		$appointmentDate = $queryParams['appointment_date'] ?? null;
		$startTime = $queryParams['start_time'] ?? null;
		$endTime = $queryParams['end_time'] ?? null;

		$currentUserId = Yii::$app->user->id;
		$currentUserRoles = array_keys($authManager->getRolesByUser($currentUserId));

		$isSecretary = in_array('secretary', $currentUserRoles);


		$managedUserIds = [];
		if ($isSecretary) {
			// Fetch only the users managed by the logged-in secretary
			$managedUserIds = ManagedUsers::find()
				->select('user_id')
				->where(['secretary_id' => $currentUserId])
				->column();
		}

		$filteredUsers = [];
		foreach ($users as $user) {
			$roles = array_keys($authManager->getRolesByUser($user->user_id));

			if (in_array('su', $roles)) {
				continue;
			}

			if ($isSecretary && !in_array($user->user_id, $managedUserIds)) {
				continue;
			}

			if ($type === 'attendees' && $appointmentDate && $startTime && $endTime) {
				$conflictExists = AppointmentAttendees::isAttendeeUnavailable($user->id, $appointmentDate, $startTime, $endTime);

				if ($conflictExists) {
					continue;
				}
			}

			$filteredUsers[] = [
				'id' => $user->user_id,
				'username' => $user->username,
				'status' => $user->status,
				'last_activity' => $user->last_login_at,
				'email' => $user->profile->email_address,
				'fullname' => $user->profile->first_name . ' ' . $user->profile->last_name,
				'mobile' => $user->profile->mobile_number,
				'roles' => $roles,
				// 'is_available' => $isAvailable
			];
		}

		$dataProvider->setModels($filteredUsers);

		return $this->payloadResponse($dataProvider, ['oneRecord' => false]);
	}

	public function actionGetUsers()
	{
		$profiles = Profiles::find()
			->joinWith('user')
			->where(['users.can_be_booked' => true])
			->all();

		if (empty($profiles)) {
			return $this->errorResponse(['message' => ['No profiles found']]);
		}

		$authManager = Yii::$app->authManager;
		$formattedProfiles = [];

		foreach ($profiles as $profile) {
			$roles = array_keys($authManager->getRolesByUser($profile->user_id));
			if (!in_array('user', $roles)) {
				continue;
			}

			$formattedProfiles[] = [
				'user_id' => $profile->user_id,
				'username' => $profile->user->username,
				// 'email' => $profile->email_address,
				// 'status' => $profile->user->status,
				// 'last_activity' => $profile->user->last_login_at,
				// 'name' => $profile->first_name . ' ' . $profile->last_name,
				// 'middle_name' => $profile->middle_name,
				// 'mobile_number' => $profile->mobile_number,
				// 'roles' => $roles,
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
			'email_address' => $profile->email_address,
			'name' => trim($profile->first_name . ' ' . $profile->last_name),
			'first_name' => $profile->first_name,
			'last_name' => $profile->last_name,
			'mobile_number' => $profile->mobile_number,
			'roles' => $roleNames,
		];

		return $this->payloadResponse(['user' => $formattedUser]);
	}

	public function actionUpdateUser($id)
	{
		Yii::$app->user->can('su');
		$dataRequest['UpdateUser'] = Yii::$app->request->getBodyParams();

		$user = User::findOne($id);
		if (!$user) {
			return $this->errorResponse(['message' => ['User not found']]);
		}

		$profile = $user->profile;
		if (!$profile) {
			return $this->errorResponse(['message' => ['User Profile not found']]);
		}


		$transaction = Yii::$app->db->beginTransaction();

		try {
			$user->load($dataRequest['UpdateUser'], '');
			if (!$user->validate() || !$user->save(false)) {
				return $this->errorResponse($user->getErrors());
			}

			$profile->load($dataRequest['UpdateUser'], '');
			if (!$profile->validate() || !$profile->save(false)) {
				return $this->errorResponse($profile->getErrors());
			}

			$transaction->commit();

			return $this->payloadResponse(['message' => 'User updated successfully']);
		} catch (\Exception $e) {
			$transaction->rollBack();
			return $this->errorResponse(['message' => $e->getMessage()]);
		}
	}

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

	// public function filterMenus($roles)
	// {
	// 	$menus = Yii::$app->params['menus'];

	// 	$roleNames = array_keys($roles);

	// 	if (in_array('su', $roleNames)) {
	// return $menus;
	// 		$allowedRoutes = ['roles', 'permissions', 'admin', 'appointments', 'default.users', 'meetings-approval', 'venues', 'events', 'venue-management'];
	// 	} elseif (in_array('secretary', $roleNames)) {
	// 		$allowedRoutes = ['home', 'appointments'];
	// 	} elseif (in_array('user', $roleNames)) {
	// 		$allowedRoutes = ['home', 'appointments', 'availability'];
	// 	} elseif (in_array('registrar', $roleNames)) {
	// 		$allowedRoutes = ['meetings-approval', 'venues', 'events', 'venue-management'];
	// 	} else {
	// 		$allowedRoutes = [];
	// 	}

	// 	$menus = array_values(array_filter($menus, function ($menu) use ($allowedRoutes) {
	// 		return in_array($menu['route'], $allowedRoutes);
	// 	}));

	// 	return $menus;
	// }

	public function filterMenus($roles)
	{
		$menus = Yii::$app->params['menus'];
		$roleNames = array_keys($roles);

		$filteredMenus = [];

		$removeRoles = function ($menu) use (&$removeRoles) {
			unset($menu['roles']);
			if (isset($menu['children'])) {
				$menu['children'] = array_map($removeRoles, $menu['children']);
			}
			return $menu;
		};

		if (in_array('su', $roleNames)) {
			$filteredMenus = array_map($removeRoles, array_values(array_filter($menus, function ($menu) {
				return isset($menu['route']) && !in_array($menu['route'], ['availability', 'home']);
			})));

			if (isset($menus['iam'])) {
				$filteredMenus[] = [
					'IAM' => array_map($removeRoles, $menus['iam']),
				];
			}

			return $filteredMenus;
		}

		$allowedRoutes = [];
		foreach ($menus as $menu) {
			if (isset($menu['roles']) && is_array($menu['roles'])) {
				if (in_array('*', $menu['roles']) || array_intersect($roleNames, $menu['roles'])) {
					$allowedRoutes[] = $menu['route'];
				}
			}
		}

		$filteredMenus = array_values(array_filter($menus, function ($menu) use ($allowedRoutes) {
			return isset($menu['route']) && in_array($menu['route'], $allowedRoutes);
		}));

		$filteredMenus = array_map($removeRoles, $filteredMenus);

		return $filteredMenus;
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
				return $this->toastResponse(['message' => ['Password reset link has been sent to your email']]);
			} else {
				return $this->errorResponse(['message' => ['Unable to send password reset email. Please try again later.']]);
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
				return $this->toastResponse(['message' => ['Password updated successfully']]);
			}
		}

		return $this->errorResponse($model->getErrors());
	}

	public function actionChangePassword()
	{
		$model = new ChangePassword();
		$dataRequest['ChangePassword'] = Yii::$app->request->getBodyParams();

		if ($model->load($dataRequest) && $model->updatePassword()) {
			Yii::$app->response->statusCode = 440;
			return ['errorResponse' => ['errors' => ['message' => 'session expired']]];
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

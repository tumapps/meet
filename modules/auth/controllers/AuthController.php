<?php

namespace auth\controllers;

use Yii;
use yii\web\Response;
use auth\models\static\Login;
use auth\models\static\Register;
use auth\models\static\PasswordReset;
use auth\models\static\PasswordResetRequest;
use auth\models\RefreshToken;
use auth\models\searches\UserSearch;
use auth\models\Profiles;

class AuthController extends \helpers\ApiController
{

	public function actionIndex()
    {
        // Yii::$app->user->can('schedulerAvailabilityList');
        $searchModel = new UserSearch();
        $search = $this->queryParameters(Yii::$app->request->queryParams,'UserSearch');
        $dataProvider = $searchModel->search($search);
        return $this->payloadResponse($dataProvider,['oneRecord'=>false]);
    }

    public function actionGetUsers()
    {
	    $profiles = Profiles::find()->all();

	    if (empty($profiles)) {
	        return $this->errorResponse(['message' => 'No profiles found']);
	    }
	    $formattedProfiles = [];
	    foreach ($profiles as $profile) {
	        $formattedProfiles[] = [
	            'user_id' => $profile->user_id,
	            'email' => $profile->email_address,
	            'first_name' => $profile->first_name,
	            'middle_name' => $profile->middle_name,
	            'last_name' => $profile->last_name,
	            'mobile_number' => $profile->mobile_number
	        ];
	    }
	    return $this->payloadResponse(['profiles' => $formattedProfiles]);

    }

	public function actionLogin()
	{
		$model = new Login();
		$dataRequest['Login'] = Yii::$app->request->getBodyParams();
		if ($model->load($dataRequest) && $model->login()) {
			$user = Yii::$app->user->identity;
			$this->generateRefreshToken($user);
			$accesToken = $this->getToken();
			return $this->payloadResponse(['username' => $user->username, 'token' => $accesToken], ['statusCode' => 200, 'message' => 'Access granted']);
		}
		return $this->errorResponse($model->getErrors());
	}

	public function actionRegister()
	{
		$model = new Register();
		$dataRequest['Register'] = Yii::$app->request->getBodyParams();
		if ($model->load($dataRequest)) {
			if($model->save()){
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
			return $model->sendEmail();
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

		if(empty($token)){
			return $this->errorResponse(['message' => ['missing password reset token']]);
		}
		
		try {
        	$model = new PasswordReset($token);
    	} catch (InvalidArgumentException $e) {
        	return $this->errorResponse(['message' => $e->getMessage()]);
    	}

		if ($model->load($dataRequest) && $model->validate()) {
	        if ($model->resetPassword()) {
	            return $this->payloadResponse(['message' => 'Password updated successfully']);
	        }
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

	public function getToken(): string
    {
        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;
        if (!$jwt->key) {
            throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'The JWT secret must be configured first.'));
        }

        $signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
        $time = time();
        $token = $jwt->getBuilder()
            ->setIssuer(Yii::$app->params['jwt.issuer']) 
            ->setAudience(Yii::$app->params['jwt.audience'])
            ->setId(Yii::$app->params['jwt.id'], true) 
            ->setIssuedAt(time())
            ->setExpiration($time + 3600 * 72) 
            ->set('user_id', Yii::$app->user->identity->id)  
            ->set('username', Yii::$app->user->identity->username)
            ->sign($signer, $jwt->key) 
            ->getToken(); 

        return (string)$token;
    }
	
	public function actionRefresh()
	{
		$refreshToken = Yii::$app->request->cookies->getValue('refresh-token', false);
		if (!$refreshToken) {
			return $this->errorResponse([
					'statusCode' => [
						'440'
					]
				]);
		}
		$userRefreshToken = RefreshToken::findOne(['token' => $refreshToken]);
		if (Yii::$app->request->getMethod() == 'POST') {
			// Getting new JWT after it has expired
			if (empty($userRefreshToken)) {
				return $this->errorResponse([
					'statusCode' => [
						'440'
					]
				]);
			}
			$user = \auth\models\User::findIdentity($userRefreshToken->user_id);
			if (empty($user)) {
				$userRefreshToken->delete();
				return $this->errorResponse([
					'statusCode' => [
						'440'
					]
				]);
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
		return $this->errorResponse([
					'statusCode' => [
						'500'
					]
				]);
	}


	private function generateRefreshToken(\auth\models\User $user)
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
			// return $this->errorResponse(440);
			return $this->errorResponse([
					'statusCode' => [
						'440'
					]
				]);
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
<?php

namespace auth\controllers;

use Yii;
use yii\web\Response;
use auth\models\static\Login;
use auth\models\RefreshToken;

class AuthController extends \helpers\ApiController
{

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

	public function actionMe()
	{
		$user = Yii::$app->user->identity;
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
			// return $this->errorResponse(440);
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
			$user = User::findIdentity($userRefreshToken->user_id);
			if (empty($user)) {
				$userRefreshToken->delete();
				// return $this->errorResponse(440);
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
		// return $this->errorResponse(500);
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
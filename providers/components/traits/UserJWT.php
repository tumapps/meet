<?php

namespace helpers\traits;

use Yii;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use yii\web\Request as WebRequest;

/**
 * Trait to handle JWT-authorization process. Should be attached to User model.
 * If there are many applications using user model in different ways - best way
 * is to use this trait only in the JWT related part.
 */
trait UserJwt
{

    /**
     * Store JWT token header items.
     * @var array
     */
    protected static $decodedToken;
    /**
     * Getter for secret key that's used for generation of JWT
     * @return string secret key used to generate JWT
     */
    protected static function getSecretKey()
    {
        return hash_hmac('sha256', md5(date('DYM') . Yii::$app->id), sha1(date('myd')));
    }

    /**
     * Getter for expIn token that's used for generation of JWT
     * @return integer time to add expIn token used to generate JWT
     */
    protected static function getExpireIn()
    {
        return strtotime('+5 minute');
    }

    /**
     * Getter for "header" array that's used for generation of JWT
     * @return array JWT Header Token param, see http://jwt.io/ for details
     */
    protected static function getHeaderToken()
    {
        return [
            'app' => Yii::$app->name,
        ];
    }

    /**
     * Logins user by given JWT encoded string. If string is correctly decoded
     * - array (token) must contain 'jti' param - the id of existing user
     * @param  string $accessToken access token to decode
     * @return mixed|null          User model or null if there's no user
     * @throws \yii\web\ForbiddenHttpException if anything went wrong
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $secret = static::getSecretKey();

        // Decode token and transform it into array.
        // Firebase\JWT\JWT throws exception if token can not be decoded
        try {

            $decoded = JWT::decode($token, new Key($secret, static::getAlgo()));
        } catch (\Exception $e) {
            return false;
        }

        static::$decodedToken = (array)$decoded;

        // If there's no jti param - exception
        if (!isset(static::$decodedToken['jti'])) {
            return false;
        }

        // JTI is unique identifier of user.
        // For more details: https://tools.ietf.org/html/rfc7519#section-4.1.7
        $id = static::$decodedToken['jti'];

        return static::findByJTI($id);
    }

    /**
     * Finds User model using static method findOne
     * Override this method in model if you need to complicate id-management
     * @param  string $id if of user to search
     * @return mixed       User model
     */
    public static function findByJTI($token)
    {
        return static::findOne(['auth_key' => $token, 'status' => \auth\models\User::STATUS_ACTIVE]);
    }

    /**
     * Getter for encryption algorytm used in JWT generation and decoding
     * Override this method to set up other algorytm.
     * @return string needed algorytm
     */
    public static function getAlgo()
    {
        return 'HS256';
    }

    /**
     * Returns some 'id' to encode to token. By default is current model id.
     * If you override this method, be sure that findByJTI is updated too
     * @return integer any unique integer identifier of user
     */
    public function getJTI()
    {
        return $this->getAuthKey();
    }

    /**
     * Encodes model data to create custom JWT with model.id set in it
     * @return string encoded JWT
     */
    public function getJWT()
    {
        // Collect all the data
        $secret = static::getSecretKey();
        $currentTime = time();
        $request = Yii::$app->request;
        $hostInfo = '';
        // There is also a \yii\console\Request that doesn't have this property
        if ($request instanceof WebRequest) {
            $hostInfo = $request->hostInfo;
        }
        // Merge token with presets not to miss any params in custom
        // configuration
        $token = [
            'iss' => $hostInfo,
            'aud' => $hostInfo,
            'iat' => $currentTime,
            'nbf' => $currentTime,
            'user_id' => $this->getPrimaryKey(),
            'can_be_booked' => $this->can_be_booked,
            'roles' => array_keys(Yii::$app->authManager->getRolesByUser($this->user_id)),
            'exp' => static::getExpireIn()
        ];
        // Set up id
        $token['jti'] = $this->getJTI();
        return JWT::encode($token, $secret, static::getAlgo(), Yii::$app->id, static::getHeaderToken());
    }
} 
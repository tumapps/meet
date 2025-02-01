<?php

namespace scheduler\models;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 *@OA\Schema(
 *  schema="SystemSettings",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="app_name", type="string",title="App name", example="string"),
 *  @OA\Property(property="system_email", type="string",title="System email", example="string"),
 *  @OA\Property(property="category", type="string",title="Category", example="string"),
 *  @OA\Property(property="email_scheme", type="string",title="Email scheme", example="string"),
 *  @OA\Property(property="email_smtps", type="string",title="Email smtps", example="string"),
 *  @OA\Property(property="email_port", type="integer",title="Email port", example="integer"),
 *  @OA\Property(property="email_encryption", type="string",title="Email encryption", example="string"),
 *  @OA\Property(property="email_password", type="string",title="Email password", example="string"),
 *  @OA\Property(property="description", type="string",title="Description", example="string"),
 *  @OA\Property(property="email_username", type="string",title="Email username", example="string"),
 *  @OA\Property(property="updated_at", type="int",title="Updated at", example="int"),
 *  @OA\Property(property="created_at", type="int",title="Created at", example="int"),
 *  @OA\Property(property="is_deleted", type="boo",title="Is deleted", example="boo"),
 * )
 */

class SystemSettings extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%system_settings}}';
    }
    /**
     * list of fields to output by the payload.
     */
    // public function fields()
    // {
    //     return array_merge(
    //         parent::fields(),
    //         [
    //             'id',
    //             'app_name',
    //             'system_email',
    //             'category',
    //             'email_scheme',
    //             'email_smtps',
    //             'email_port',
    //             'email_encryption',
    //             'email_password',
    //             'description',
    //             'email_username',
    //             'updated_at',
    //             'created_at',
    //             'is_deleted',
    //         ]
    //     );
    // }
    public function fields()
    {
        $fields = parent::fields();

        $customFields = [
            'id',
            'app_name',
            'system_email',
            'category',
            'email_scheme',
            'email_smtps',
            'email_port',
            'email_encryption',
            'description',
            'email_username',
            'updated_at',
            'created_at',
            'is_deleted',
        ];

        unset($fields['email_password']);

        $customFields['decrypted_email_password'] = function () {
            return '[PROTECTED]';
        };

        return array_merge($fields, $customFields);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['system_email', 'email_scheme', 'email_smtps', 'email_port', 'email_encryption', 'email_password', 'email_username'], 'required'],
            [['system_email'], 'email'],
            [['email_port', 'updated_at', 'created_at'], 'default', 'value' => null],
            [['email_port', 'updated_at', 'created_at'], 'integer'],
            [['description'], 'string'],
            [['is_deleted'], 'boolean'],
            [['app_name'], 'string', 'max' => 255],
            [['system_email', 'email_scheme', 'email_smtps', 'email_password', 'email_username'], 'string', 'max' => 128],
            [['email_encryption'], 'in', 'range' => ['tls', 'ssl', 'none']],
            [['category'], 'string', 'max' => 20],
            [['email_encryption'], 'string', 'max' => 10],
            [['email_smtps'], 'validateSmtpConnection']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_name' => 'App Name',
            'system_email' => 'System Email',
            'category' => 'Category',
            'email_scheme' => 'Email Scheme',
            'email_smtps' => 'Email Smtps',
            'email_port' => 'Email Port',
            'email_encryption' => 'Email Encryption',
            'email_password' => 'Email Password',
            'description' => 'Description',
            'email_username' => 'Email Username',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'is_deleted' => 'Is Deleted',
        ];
    }

    public function beforeSave($insert)
    {
        if (!empty($this->email_password)) {
            if (!password_get_info($this->email_password)['algo']) {
                $this->email_password = password_hash($this->email_password, PASSWORD_BCRYPT);
            }
        }

        return parent::beforeSave($insert);
    }


    public function validateSmtpConnection($attribute, $params)
    {
        try {
            $dsn = sprintf(
                '%s://%s:%s@%s:%d',
                $this->email_scheme,
                $this->email_username,
                $this->email_password,
                $this->email_smtps,
                $this->email_port
            );

            // Create Symfony Mailer Transport
            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);

            // Try sending a test email (does not actually send, but validates credentials)
            $testEmail = (new Email())
                ->from($this->system_email)
                ->to($this->system_email) // Self-test
                ->subject('SMTP Validation Test')
                ->text('Testing SMTP credentials.');

            $mailer->send($testEmail);
        } catch (TransportExceptionInterface $e) {
            $this->addError($attribute, 'SMTP connection failed: ' . $e->getMessage());
        }
    }
}

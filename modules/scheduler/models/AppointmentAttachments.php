<?php

namespace scheduler\models;

use Yii;
use app\providers\components\GoogleStorageComponent;

/**
 *@OA\Schema(
 *  schema="AppointmentAttachments",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="appointment_id", type="integer",title="Appointment id", example="integer"),
 *  @OA\Property(property="file_url", type="string",title="File url", example="string"),
 *  @OA\Property(property="file_name", type="string",title="File name", example="string"),
 *  @OA\Property(property="is_deleted", type="int",title="Is deleted", example="int"),
 *  @OA\Property(property="created_at", type="integer",title="Created at", example="integer"),
 *  @OA\Property(property="updated_at", type="integer",title="Updated at", example="integer"),
 * )
 */

class AppointmentAttachments extends BaseModel
{
    // public $uploadedFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%appointment_files}}';
    }
    /**
     * list of fields to output by the payload.
     */
    public function fields()
    {
        return array_merge(
            parent::fields(),
            [
                'id',
                'appointment_id',
                'file_url',
                'file_name',
                'is_deleted',
                'created_at',
                'updated_at',
            ]
        );
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['appointment_id', 'file_name', 'file_url', 'self_link'], 'required'],
            [['appointment_id', 'is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['appointment_id', 'is_deleted'], 'integer'],
            [['file_url', 'file_name'], 'string', 'max' => 255],
            // [['uploadedFile'], 'file', 'extensions' => 'pdf, doc, docx', 'maxSize' => 2 * 1024 * 1024, 'skipOnEmpty' => false, 'message' => 'Uplize'],
            [['appointment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Appointments::class, 'targetAttribute' => ['appointment_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'appointment_id' => 'Appointment ID',
            'file_url' => 'File Url',
            'file_name' => 'File Name',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Appointment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAppointment()
    {
        return $this->hasOne(Appointments::class, ['id' => 'appointment_id']);
    }

 

    // public function fileUpload($uploadedFile, $appointmentId)
    // {
    //     $destination = 'playground/francis/appointments/' . $appointmentId . '/' . $uploadedFile->name;

    //     $googleStorage = new GoogleStorageComponent();
    //     $object = $googleStorage->uploadFile($uploadedFile->tempName, $destination, true);

    //     $this->appointment_id = $appointmentId;
    //     $this->file_name = $uploadedFile->name;
    //     $this->file_url = $object->info()['mediaLink'];
    //     $this->self_link = $object->info()['selfLink'];

    //     if (!$this->validate()) {
    //         Yii::error('Validation failed: ' . json_encode($this->getErrors()), __METHOD__);
    //         return $this->getErrors();
    //     }

    //     if (!$this->save()) {
    //         Yii::error('Save failed: ' . json_encode($this->getErrors()), __METHOD__);
    //         return $this->getErrors();
    //     }

    //     return true;
    // }

    public function fileUpload($uploadedFile, $appointmentId)
    {
        try {
            $destination = 'playground/francis/appointments/' . $appointmentId . '/' . $uploadedFile->name;

            $googleStorage = new GoogleStorageComponent();
            $object = $googleStorage->uploadFile($uploadedFile->tempName, $destination, true);

            $this->appointment_id = $appointmentId;
            $this->file_name = $uploadedFile->name;
            $this->file_url = $object->info()['mediaLink'];
            $this->self_link = $object->info()['selfLink'];
            // if (!$this->validate()) {
            //     Yii::error('Validation failed: ' . json_encode($this->getErrors()), __METHOD__);
            //     return ['status' => 'error', 'message' => $this->getErrors()];
            // }

            if (!$this->save()) {
                Yii::error('Save failed: ' . json_encode($this->getErrors()), __METHOD__);
                return ['status' => 'error', 'message' => $this->getErrors()];
            }

            return true;
        } catch (\Exception $e) {
            Yii::error('Google Cloud upload failed: ' . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => 'Failed to upload file This may be caused by network issues check your internet connection' .  $e->getMessage()];
        }
    }



    public static function getAppointmentAttachment($id)
    {
        $attachment = self::find()
            ->where(['appointment_id' => $id])
            ->select(['file_name', 'file_url', 'self_link'])
            ->one();

        if (!$attachment) {
            return null;
        }

        return [
            'fileName' => $attachment->file_name,
            'downloadLink'  => $attachment->file_url,
            // 'previewLink' => $attachment->self_link,
        ];
    }
}

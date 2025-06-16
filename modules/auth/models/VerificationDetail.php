<?php

namespace auth\models;

use Yii;
/**
 *@OA\Schema(
 *  schema="VerificationDetail",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="student_id", type="integer",title="Student id", example="integer"),
 *  @OA\Property(property="staff_id", type="integer",title="Staff id", example="integer"),
 *  @OA\Property(property="is_deleted", type="int",title="Is deleted", example="int"),
 *  @OA\Property(property="created_at", type="int",title="Created at", example="int"),
 *  @OA\Property(property="updated_at", type="int",title="Updated at", example="int"),
 * )
 */

class VerificationDetail extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%verification_detail}}';
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
            'student_id',
            'staff_id',
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
            [['student_id', 'staff_id'], 'required'],
            [['student_id', 'staff_id', 'is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['student_id', 'staff_id', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['student_id'], 'exist', 'skipOnError' => true, 'targetClass' => StudentProfile::class, 'targetAttribute' => ['student_id' => 'std_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'student_id' => 'Student ID',
            'staff_id' => 'Staff ID',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Student]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(StudentProfile::class, ['std_id' => 'student_id']);
    }
}

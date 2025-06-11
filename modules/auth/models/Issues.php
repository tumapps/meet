<?php

namespace auth\models;

use Yii;
/**
 *@OA\Schema(
 *  schema="Issues",
 *  @OA\Property(property="id", type="integer",title="Id", example="integer"),
 *  @OA\Property(property="staff_id", type="integer",title="Staff id", example="integer"),
 *  @OA\Property(property="student_id", type="integer",title="Student id", example="integer"),
 *  @OA\Property(property="issue", type="string",title="Issue", example="string"),
 *  @OA\Property(property="is_deleted", type="int",title="Is deleted", example="int"),
 *  @OA\Property(property="created_at", type="int",title="Created at", example="int"),
 *  @OA\Property(property="updated_at", type="int",title="Updated at", example="int"),
 * )
 */

class Issues extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%issues}}';
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
            'staff_id',
            'student_id',
            'issue',
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
            [['staff_id', 'student_id', 'issue'], 'required'],
            [['staff_id', 'student_id', 'is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['staff_id', 'student_id', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['issue'], 'string'],
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
            'staff_id' => 'Staff ID',
            'student_id' => 'Student ID',
            'issue' => 'Issue',
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

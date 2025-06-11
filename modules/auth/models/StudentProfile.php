<?php

namespace auth\models;

use Yii;

/**
 *@OA\Schema(
 *  schema="StudentProfile",
 *  @OA\Property(property="std_id", type="integer",title="Std id", example="integer"),
 *  @OA\Property(property="reg_number", type="string",title="Reg number", example="string"),
 *  @OA\Property(property="student_email", type="string",title="Student email", example="string"),
 *  @OA\Property(property="fee_paid", type="float",title="Fee paid", example="float"),
 *  @OA\Property(property="total_fee", type="float",title="Total fee", example="float"),
 *  @OA\Property(property="photo", type="string",title="Photo", example="string"),
 *  @OA\Property(property="status", type="string",title="Status", example="string"),
 *  @OA\Property(property="class", type="string",title="Class", example="string"),
 *  @OA\Property(property="school", type="string",title="School", example="string"),
 *  @OA\Property(property="department", type="string",title="Department", example="string"),
 *  @OA\Property(property="year_of_study", type="string",title="Year of study", example="string"),
 *  @OA\Property(property="is_deleted", type="int",title="Is deleted", example="int"),
 *  @OA\Property(property="created_at", type="int",title="Created at", example="int"),
 *  @OA\Property(property="updated_at", type="int",title="Updated at", example="int"),
 * )
 */

class StudentProfile extends BaseModel
{
    public $paid_percentage;
    public $remaining_percentage;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%student_profile}}';
    }
    /**
     * list of fields to output by the payload.
     */
    public function fields()
    {
        return array_merge(
            parent::fields(),
            [
                'std_id',
                'reg_number',
                'student_email',
                'fee_paid',
                'total_fee',
                'paid_percentage',
                'remaining_percentage',
                'photo',
                'status',
                'class',
                'school',
                'department',
                'year_of_study',
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
            [['reg_number', 'student_email', 'fee_paid', 'total_fee', 'status', 'class', 'school', 'department', 'year_of_study'], 'required'],
            [['fee_paid', 'total_fee'], 'number'],
            [['is_deleted', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['reg_number'], 'string', 'max' => 20],
            [['student_email', 'school', 'department'], 'string', 'max' => 100],
            [['photo'], 'string', 'max' => 255],
            [['status', 'class'], 'string', 'max' => 50],
            [['year_of_study'], 'string', 'max' => 10],
            [['reg_number'], 'unique'],
            [['student_email'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'std_id' => 'Std ID',
            'reg_number' => 'Reg Number',
            'student_email' => 'Student Email',
            'fee_paid' => 'Fee Paid',
            'total_fee' => 'Total Fee',
            'photo' => 'Photo',
            'status' => 'Status',
            'class' => 'Class',
            'school' => 'School',
            'department' => 'Department',
            'year_of_study' => 'Year Of Study',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Issues]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIssues()
    {
        return $this->hasMany(Issues::class, ['student_id' => 'std_id']);
    }

    /**
     * Gets query for [[VerificationDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerificationDetails()
    {
        return $this->hasMany(VerificationDetail::class, ['student_id' => 'std_id']);
    }

    public function calculateFeePercentages()
{
    $feePaid = (float) $this->fee_paid;
    $totalFee = (float) $this->total_fee;

    $percentage = $totalFee > 0 ? round(($feePaid / $totalFee) * 100) : 0;

    $this->paid_percentage = $percentage;
    $this->remaining_percentage = 100 - $percentage;
}
}

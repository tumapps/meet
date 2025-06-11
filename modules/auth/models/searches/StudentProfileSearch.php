<?php

namespace auth\models\searches;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use auth\models\StudentProfile;

/**
 * StudentProfileSearch represents the model behind the search form of `auth\models\StudentProfile`.
 */
class StudentProfileSearch extends StudentProfile
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['std_id', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['reg_number', 'student_email', 'photo', 'status', 'class', 'school', 'department', 'year_of_study'], 'safe'],
            [['fee_paid', 'total_fee'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = StudentProfile::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'std_id' => $this->std_id,
            'fee_paid' => $this->fee_paid,
            'total_fee' => $this->total_fee,
            'is_deleted' => $this->is_deleted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['ilike', 'reg_number', $this->reg_number])
            ->andFilterWhere(['ilike', 'student_email', $this->student_email])
            ->andFilterWhere(['ilike', 'photo', $this->photo])
            ->andFilterWhere(['ilike', 'status', $this->status])
            ->andFilterWhere(['ilike', 'class', $this->class])
            ->andFilterWhere(['ilike', 'school', $this->school])
            ->andFilterWhere(['ilike', 'department', $this->department])
            ->andFilterWhere(['ilike', 'year_of_study', $this->year_of_study]);

        return $dataProvider;
    }
}

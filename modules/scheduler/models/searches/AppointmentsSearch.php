<?php

namespace scheduler\models\searches;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use scheduler\models\Appointments;

/**
 * AppointmentsSearch represents the model behind the search form of `scheduler\models\Appointments`.
 */
class AppointmentsSearch extends Appointments
{
    public $search;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['appointment_date', 'is_deleted','start_time', 'end_time', 'contact_name', 'email_address', 'mobile_number', 'search','subject', 'appointment_type', 'status', 'created_at', 'updated_at'], 'safe'],
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
        $query = Appointments::find();

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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'appointment_date' => $this->appointment_date,
            // 'is_deleted' => false,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->orFilterWhere(['ilike', 'contact_name', $this->search])
            ->orFilterWhere(['ilike', 'email_address', $this->search])
            ->orFilterWhere(['ilike', 'mobile_number', $this->search])
            ->orFilterWhere(['ilike', 'subject', $this->search])
            ->orFilterWhere(['ilike', 'appointment_type', $this->search])
            ->orFilterWhere(['ilike', 'description', $this->search]);
            // ->orFilterWhere(['ilike', 'status', $this->search]);

        return $dataProvider;
    }
}

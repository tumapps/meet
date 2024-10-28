<?php

namespace scheduler\models\searches;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use scheduler\models\Availability;

/**
 * AvailabilitySearch represents the model behind the search form of `scheduler\models\Availability`.
 */
class AvailabilitySearch extends Availability
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['start_date', 'end_date', 'is_deleted','start_time', 'end_time', 'description', 'created_at', 'updated_at'], 'safe'],
            [['is_full_day'], 'boolean'],
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
        $query = Availability::find();

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
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time,
            // 'is_deleted' => false,
            'is_deleted' => (int) 0,
            'end_time' => $this->end_time,
            'is_full_day' => $this->is_full_day,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['ilike', 'description', $this->description]);

        return $dataProvider;
    }
}

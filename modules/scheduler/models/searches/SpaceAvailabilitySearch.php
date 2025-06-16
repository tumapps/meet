<?php

namespace scheduler\models\searches;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use scheduler\models\SpaceAvailability;

/**
 * SpaceAvailabilitySearch represents the model behind the search form of `scheduler\models\SpaceAvailability`.
 */
class SpaceAvailabilitySearch extends SpaceAvailability
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'space_id', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['date', 'start_time', 'end_time'], 'safe'],
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
        $query = SpaceAvailability::find();

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
            'space_id' => $this->space_id,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_deleted' => $this->is_deleted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        return $dataProvider;
    }
}

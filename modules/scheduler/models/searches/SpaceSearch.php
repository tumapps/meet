<?php

namespace scheduler\models\searches;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use scheduler\models\Space;

/**
 * SpaceSearch represents the model behind the search form of `scheduler\models\Space`.
 */
class SpaceSearch extends Space
{
    public $search;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['name', 'opening_time', 'closing', 'location', 'description','search'], 'safe'],
            [['is_locked'], 'boolean'],
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
        $query = Space::find();
        //$query->with('level'); // eager loading

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
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
            'is_locked' => $this->is_locked,
            'is_deleted' => $this->is_deleted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->orFilterWhere(['ilike', 'name', $this->search])
            ->orFilterWhere(['ilike', 'location', $this->search])
            ->orFilterWhere(['ilike', 'description', $this->search]);

        return $dataProvider;
    }
}

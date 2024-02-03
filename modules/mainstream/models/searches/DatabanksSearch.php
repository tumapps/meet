<?php

namespace mainstream\models\searches;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use iam\models\Databanks;

/**
 * DatabanksSearch represents the model behind the search form of `iam\models\Databanks`.
 */
class DatabanksSearch extends Databanks
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['databank_id', 'databank_name', 'category', 'security_key', 'description'], 'safe'],
            [['status', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
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
        $query = Databanks::find();

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
            'status' => $this->status,
            'is_deleted' => $this->is_deleted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['ilike', 'databank_id', $this->databank_id])
            ->andFilterWhere(['ilike', 'databank_name', $this->databank_name])
            ->andFilterWhere(['ilike', 'category', $this->category])
            ->andFilterWhere(['ilike', 'security_key', $this->security_key])
            ->andFilterWhere(['ilike', 'description', $this->description]);

        return $dataProvider;
    }
}

<?php

namespace scheduler\models\searches;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use scheduler\models\SystemSettings;

/**
 * SystemSettingsSearch represents the model behind the search form of `scheduler\models\SystemSettings`.
 */
class SystemSettingsSearch extends SystemSettings
{
    public $search;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'email_port', 'updated_at', 'created_at'], 'integer'],
            [['app_name', 'system_email', 'category', 'email_scheme', 'email_smtps', 'search', 'email_encryption', 'email_password', 'description', 'email_username'], 'safe'],
            [['is_deleted'], 'boolean'],
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
        $query = SystemSettings::find();

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
            'email_port' => $this->email_port,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'is_deleted' => $this->is_deleted,
        ]);

        $query->orFilterWhere(['ilike', 'app_name', $this->search])
            ->orFilterWhere(['ilike', 'system_email', $this->search])
            ->orFilterWhere(['ilike', 'category', $this->search])
            ->orFilterWhere(['ilike', 'email_scheme', $this->search])
            ->orFilterWhere(['ilike', 'email_smtps', $this->search])
            ->orFilterWhere(['ilike', 'email_encryption', $this->search])
            ->orFilterWhere(['ilike', 'email_password', $this->search])
            ->orFilterWhere(['ilike', 'description', $this->search])
            ->orFilterWhere(['ilike', 'email_username', $this->search]);

        return $dataProvider;
    }
}

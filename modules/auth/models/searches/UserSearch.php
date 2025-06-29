<?php

namespace auth\models\searches;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use auth\models\User;

/**
 * UserSearch represents the model behind the search form of `auth\models\User`.
 */
class UserSearch extends User
{
    /**
     * {@inheritdoc}
     */
    public $search;
    public function rules()
    {
        return [
            [['user_id', 'status', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['username', 'auth_key', 'password_hash', 'search'], 'safe'],
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
        $query = User::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['defaultPageSize' => \Yii::$app->params['defaultPageSize'], 'pageSizeLimit' => [1, \Yii::$app->params['pageSizeLimit']]],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            //$query->where('0=1');
            return $dataProvider;
        }

        $query->joinWith('profile');

        // grid filtering conditions
        if (isset($this->search)) {
            $query->orFilterWhere(['ilike', 'username', $this->search])
                  ->orFilterWhere(['ilike', 'profiles.first_name', $this->search])
                  ->orFilterWhere(['ilike', 'profiles.last_name', $this->search])
                  ->orFilterWhere(['ilike', 'profiles.email_address', $this->search]);
        } else {
            $query->andFilterWhere(['ilike', 'username', $this->username]);
        }
        return $dataProvider;
    }
}

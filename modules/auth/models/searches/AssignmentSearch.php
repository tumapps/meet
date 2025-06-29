<?php

namespace auth\models\searches;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class AssignmentSearch extends Model
{
    public $id;
    public $username;
    public $search;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'username', 'search'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('rbac-admin', 'ID'),
            'username' => Yii::t('rbac-admin', 'Username'),
            'name' => Yii::t('rbac-admin', 'Name'),
        ];
    }

    /**
     * Create data provider for Assignment model.
     * @param  array                        $params
     * @param  \yii\db\ActiveRecord         $class
     * @param  string                       $usernameField
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params, $class, $usernameField)
    {
        $query = $class::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['ilike', $usernameField, $this->username]);

        return $dataProvider;
    }
}

<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Users;

/**
 * UsersSearch represents the model behind the search form about `common\models\Users`.
 */
class UsersSearch extends Users
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['_id', 'type', 'active'], 'integer'],
            [['uuid', 'name', 'login', 'pass', 'tagId', 'whoIs', 'image', 'contact', 'connectionDate', 'createdAt', 'changedAt'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = Users::find();

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
            '_id' => $this->_id,
            'type' => $this->type,
            'active' => $this->active,
            'connectionDate' => $this->connectionDate,
            'createdAt' => $this->createdAt,
            'changedAt' => $this->changedAt,
        ]);

        $query->andFilterWhere(['like', 'uuid', $this->uuid])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'login', $this->login])
            ->andFilterWhere(['like', 'tagId', $this->tagId])
            ->andFilterWhere(['like', 'whoIs', $this->whoIs])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'contact', $this->contact]);

        return $dataProvider;
    }
}

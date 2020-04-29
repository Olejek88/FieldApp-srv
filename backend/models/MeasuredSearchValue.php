<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\MeasuredValue;

/**
 * MeasuredSearchValue represents the model behind the search form about `common\models\MeasuredValue`.
 */
class MeasuredSearchValue extends MeasuredValue
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['_id'], 'integer'],
            [['uuid', 'equipmentUuid', 'operationUuid', 'date', 'value', 'createdAt', 'changedAt'], 'safe'],
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
        $query = MeasuredValue::find();

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
            'date' => $this->date,
            'createdAt' => $this->createdAt,
            'changedAt' => $this->changedAt,
        ]);

        $query->andFilterWhere(['like', 'uuid', $this->uuid])
            ->andFilterWhere(['like', 'equipmentUuid', $this->equipmentUuid])
            ->andFilterWhere(['like', 'operationUuid', $this->operationUuid])
            ->andFilterWhere(['like', 'value', $this->value]);

        return $dataProvider;
    }
}

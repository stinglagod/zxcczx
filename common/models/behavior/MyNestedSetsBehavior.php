<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 24.01.2019
 * Time: 10:38
 */
namespace common\models\behavior;
use common\models\Category;
use creocoder\nestedsets\NestedSetsBehavior;

class MyNestedSetsBehavior extends NestedSetsBehavior
{
    /**
     * Gets the children of the node.
     * @param integer|null $depth the depth
     * @return \yii\db\ActiveQuery
     */
    public function children($depth = null)
    {
        $condition = [
            'and',
            ['>', $this->leftAttribute, $this->owner->getAttribute($this->leftAttribute)],
            ['<', $this->rightAttribute, $this->owner->getAttribute($this->rightAttribute)],
        ];
        if (\Yii::$app->id=='app-frontend') {
            $condition[]=['on_site'=>1];
        }

        if ($depth !== null) {
            $condition[] = ['<=', $this->depthAttribute, $this->owner->getAttribute($this->depthAttribute) + $depth];
        }

        $this->applyTreeAttributeCondition($condition);

        return $this->owner->find()->andWhere($condition)->addOrderBy([$this->leftAttribute => SORT_ASC]);
    }
}
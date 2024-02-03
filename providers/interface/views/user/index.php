<?php

use dashboard\models\Users;
use helpers\Html;
use yii\helpers\Url;
use helpers\grid\GridView;

/** @var yii\web\View $this */
/** @var dashboard\models\UsersSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Users';
?>
<div class="users-index row">
  <div class="col-md-12">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title"><?= Html::encode($this->title) ?> </h3>
        <div class="block-options">
          <?= Html::customButton(['type' => 'modal', 'url' => Url::to(['create']), 'appearence' => ['type' => 'text', 'text' => 'Create Users', 'theme' => 'primary']]) ?>
        </div>
      </div>
      <div class="block-content">
        <div class="users-search my-3">
          <?= $this->render('_search', ['model' => $searchModel]); ?>
        </div>

        <?= GridView::widget([
          'dataProvider' => $dataProvider,
          'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'user_id',
            'username',
            'user_code',
            'mobile_number',
            'email_address:email',
            //'first_name',
            //'middle_name',
            //'last_name',
            //'auth_key',
            //'password_hash',
            //'status',
            //'is_deleted',
            //'created_at',
            //'updated_at',
            [
              'class' => \helpers\grid\ActionColumn::className(),
              'template' => '{update} {delete}',
              'urlCreator' => function ($action, Users $model, $key, $index, $column) {
                return Url::toRoute([$action, 'user_id' => $model->user_id]);
              },
              'buttons' => [
                'update' => function ($url, $model, $key) {
                  return Html::customButton(['type' => 'modal', 'url' => Url::to(['update', 'id' => $key]), 'modal' => ['title' => 'Update  Users'], 'appearence' => ['icon' => 'edit', 'theme' => 'info']]);
                },
                'delete' => function ($url, $model, $key) {
                  return Html::customButton(['type' => 'link', 'url' => Url::to(['delete', 'id' => $key]),  'appearence' => ['icon' => 'trash', 'theme' => 'danger', 'data' => ['message' => 'Do you want to delete this users?']]]);
                }
              ],
              'visibleButtons' => [
                'update' => false, //Yii::$app->user->can('update'),
                'delete' => true, //Yii::$app->user->can('delete'),
              ],
            ],
          ],
        ]); ?>


      </div>
    </div>
  </div>
</div>
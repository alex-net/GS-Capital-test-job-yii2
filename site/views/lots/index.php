<?php

use yii\helpers\Html;
use yii\grid\GridView;

use app\models\Lot;

echo Html::a('Добавить новый лот', ['/lots/add']);

echo GridView::widget([
    'dataProvider' => Lot::list(),
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'html',
            'value' => function($m) {
                return Html::a($m->name, ['/lots/edit', 'lotId' => $m->id]);
            }
        ],
        'dprice',
        'dtime',
        [
            'attribute' => 'state',
            'format' => 'raw',
            'value' => function ($m) {
                $text = $m::STATES[$m->state];
                if ($m->state == $m::STATE_NEW) {
                    $text = Html::a($text, ['start', 'lotId' => $m->id], ['title' => 'Запусьть торги']);
                }
                return $text;
            }
        ]
    ],
]);
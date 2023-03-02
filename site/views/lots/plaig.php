<?php

use yii\widgets\ListView;
use yii\helpers\Html;

use app\models\Lot;
use app\assets\LotsPlaingAsset;

$this->title = 'Торгии';

LotsPlaingAsset::register($this);

echo Html::tag('button', 'xx:xx:xx', [
    'title' => 'Серверное время, (обновить данные на странице)',
    'class' => 'server-time',
    'data-time' => time() * 1000,
]);

echo ListView::widget([
    'dataProvider' => Lot::playList(),
    'summary' => false,
    'itemView' => 'item-play',
    'itemOptions' => function($m) {
        return  [
            'class' => ['lot-item', 'item-' . $m['id']],
            'data-id' => $m['id'],
        ];
    },
    'options' => [
        'class' => 'plaing-list',
    ],
]);
<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

class LotsPlaingAsset extends AssetBundle
{
    public $baseUrl = '@web';
    public $basePath = '@webroot';

    public $js = ['js/lots-plaing.js'];

    public $depends = [YiiAsset::class];
}
<?php

use yii\helpers\Html;

use app\models\Lot;

$price = Yii::$app->formatter->asDecimal($model['curr'], 2) . 'р.';
//if ($model['state'] == Lot::STATE_PLAY) {
$price = Html::tag('span', $price,  ['title' => 'Поднять цену (сделать ставку)', 'class' => 'up-price']);
//}
?>


<?= $model['name'] ?> (<?=$price  ?>)
<div class="time-lost"  title = 'До окончания торгов осталось ...'>
    <div class="scale"></div>
</div>

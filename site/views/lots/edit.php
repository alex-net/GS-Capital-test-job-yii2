<?php

use  yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$f = ActiveForm::begin();
foreach ($model->activeAttributes() as $k) {
    if ($k == 'state') {
        continue;
    }
    echo $f->field($model, $k);;
}
echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'name' => 'save']);
if (!$model->isNewRecord) {
    echo Html::submitButton('Удалить', ['class' => 'btn btn-danger', 'name' => 'kill']);
}

ActiveForm::end();

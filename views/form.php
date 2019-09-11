<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/*
@var string $form_id
@var \x51\yii2\classes\fields\models\FieldsModel $model
 */

Pjax::begin(['id' => $pjax_id, 'enablePushState' => false]);

if (!empty($message)) {
?>
    <div class="alert alert-<?=$message[0]?> auto-class" role="alert" data-auto-class="hide" data-auto-class-timer="5000"><?=$message[1]?></div>    
<?php
    $this->registerJs('if (typeof Refresher != "undefined") {
        Refresher.call();
    }');
}

$form = ActiveForm::begin([
    'options' => [
        'data-pjax' => true,
        'id' => $form_id,
    ],
]);
//echo '<pre>';var_dump(Yii::$app->request->post());echo '</pre>';

echo $model->render($form_id);

echo Html::submitButton(
    $text['submitButton'],
    ['class' => 'btn btn-success', 'name' => 'submit', 'value' => $form_id]
);

ActiveForm::end();
Pjax::end();

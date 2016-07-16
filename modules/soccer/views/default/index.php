<?php
use yii\helpers\Url;

echo Url::to(''); // текущий URL
echo Url::toRoute(['view', 'id' => 'contact']); // тот же контроллер, другой экшн
echo Url::toRoute('match/index'); // тот же модуль, другие контроллер и экшн
?>

<div class="soccer-default-index">
    <h1><?= $this->context->action->uniqueId ?></h1>
    <p>
        This is the view content for action "<?= $this->context->action->id ?>".
        The action belongs to the controller "<?= get_class($this->context) ?>"
        in the "<?= $this->context->module->id ?>" module.567567
    </p>
    <p>
        You may customize this page by editing the following file:<br>
        <code><?= __FILE__ ?></code>
    </p>
</div>
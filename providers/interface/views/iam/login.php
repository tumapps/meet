<?php

use helpers\Html;
use helpers\widgets\ActiveForm;
/** @var yii\web\View $this */
?>
<div class="row justify-content-center push">
    <div class="col-md-8 col-lg-6 col-xl-3">
        <!-- Sign In Block -->
        <div class="block block-rounded mb-0">
            <div class="block-header block-header-default">
                <h3 class="block-title">Sign In</h3>
                <div class="block-options">
                    <a class="btn-block-option fs-sm" href="#">Forgot Password?</a>
                </div>
            </div>
            <div class="block-content">
                <div class="p-sm-3 px-lg-4 px-xxl-2 py-lg-1">
                    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                    <div class="py-3">
                        <div class="mb-4">
                            <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'class' => 'form-control form-control-alt form-control-lg', 'placeholder' => 'Username'])->label(false) ?>
                        </div>
                        <div class="mb-4">
                            <?= $form->field($model, 'password')->passwordInput(['class' => 'form-control form-control-alt form-control-lg', 'placeholder' => 'Password'])->label(false) ?>
                        </div>
                        <div class="mb-4">
                                <?= $form->field($model, 'rememberMe')->checkbox([
                                    'class'=>'form-check-input',
                                    'template' => "<div class=\"form-check\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
                                ]) ?>
                        </div>
                    </div>
                    <div class="row mb-4">
                            <div class="col-md-6 col-xl-12">
                                <?= Html::submitButton('<i class="fa fa-fw fa-sign-in-alt me-1 opacity-50"></i> Login', ['class' => 'btn w-100 btn-alt-primary text-center']) ?>
                            </div>
                        </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <!-- END Sign In Block -->
    </div>
</div>
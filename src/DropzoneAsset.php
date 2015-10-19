<?php

namespace common\widgets\dropzone;


use yii\web\AssetBundle;

class DropzoneAsset extends AssetBundle
{
    public $sourcePath = '@common/widgets/dropzone/assets';
    public $css = [
        'css/dropzone.css'
    ];

    public $js = [
        'js/dropzone.js',
    ];

    public $depends = [
        'frontend\assets\AppAsset',
    ];
}

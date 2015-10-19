<?php
/**
 * Created by PhpStorm.
 * User: viktory
 * Date: 11.08.15
 * Time: 11:07
 */

namespace common\widgets\dropzone;

use yii\web\UploadedFile;

/**
 * Interface UploadFormInterface
 *
 * @property UploadedFile $file
 *
 * @package common\widgets\dropzone
 */
interface UploadFormInterface
{
    const FILE_NAME = 'file';

    public function rules();

    public static function getEntity();
}
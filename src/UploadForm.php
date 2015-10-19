<?php
/**
 * Created by PhpStorm.
 * User: viktory
 * Date: 09.08.15
 * Time: 17:35
 */

namespace common\widgets\dropzone;

use yii\base\Model;

class UploadForm extends Model implements UploadFormInterface
{

    public $file;

    public function rules()
    {
        return [
            [[static::FILE_NAME], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg'],
        ];
    }

    public static function getEntity()
    {
        return 'testEntity';
    }
}
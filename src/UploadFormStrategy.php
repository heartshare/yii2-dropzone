<?php
/**
 * Created by PhpStorm.
 * User: viktory
 * Date: 11.08.15
 * Time: 11:17
 */

namespace common\widgets\dropzone;


use yii\base\ExitException;
use yii\base\Object;
use yii\web\UploadedFile;

/**
 * Class UploadFormStrategy
 *
 * @property UploadedFile $file
 * @property string $filename
 *
 * @package common\widgets\filedrop
 */
class UploadFormStrategy extends Object
{

    protected $mainDir = '/Users/viktory/www/stitch_files';

    private $_publicPath;
    private $_path;
    private $_filename;
    /**
     * @var UploadFormInterface
     */
    private $_form;

    public function __construct(UploadFormInterface $uploadFormInterface)
    {
        $this->_form = $uploadFormInterface;
    }

    public function setFile(UploadedFile $uploadedFile)
    {
        $this->_form->file = $uploadedFile;
    }

    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * @return bool
     */
    public function upload()
    {
        $result = false;
        if ($this->_form->validate()) {
            $this->_filename = $this->getFullFilename();
            $this->_path = $this->getPath();
            $this->_publicPath = $this->getPublicPath();
            $result = $this->_form->file->saveAs($this->_path . $this->_filename);
        }
        return $result;
    }

    /**
     * @return string
     */
    protected function getPublicPath()
    {
        $path = [\Yii::$app->urlManager->baseUrl, 'files', $this->_form->getEntity()];
        $path[] = $this->getSubDir($this->_filename);
        return implode('/', $path) . '/';
    }

    /**
     * @return string
     */
    protected function getFullFilename()
    {
        return $this->generateFileName() . '.' . $this->_form->file->extension;
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        $path = [$this->mainDir, $this->_form->getEntity()];
        $path[] = $this->getSubDir($this->_filename);
        $path = implode(DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR;
        $this->createDir($path);
        return $path;
    }

    /**
     * @param string $path
     */
    protected function createDir($path)
    {
        if (!is_dir($path)) {
            try {
                mkdir($path, 0777, true);
                chmod($path, 0777);
            } catch (ExitException $e) {
                var_dump($path);die;
            }
        }
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    protected function getSubDir($filename)
    {
        return substr(md5($filename), 0, 2);
    }

    /**
     * generate random filename
     * @return string
     */
    protected function generateFileName()
    {
        $id = str_replace('.', '', uniqid('', true));
        return substr($id, 0, 20);
    }
}
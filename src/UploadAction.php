<?php
/**
 * Created by PhpStorm.
 * User: viktory
 * Date: 09.08.15
 * Time: 18:26
 */

namespace common\widgets\dropzone;


use yii\base\Action;
use yii\base\Exception;
use yii\web\Response;
use yii\web\UploadedFile;

class UploadAction extends Action
{
    public $model;

    public $type = null;
    /**
     * Constructor.
     *
     * @param string $id the ID of this action
     * @param Controller $controller the controller that owns this action
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($id, $controller, $config = [])
    {
        $controller->enableCsrfValidation = false;
        parent::__construct($id, $controller, $config);
    }

    public function run()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        if (\Yii::$app->request->isPost) {
            if ((\Yii::$app->request->post('delete', null)) === null) {
                $result = $this->upload();
            } else {
                $result = $this->delete();
            }
            echo json_encode($result);
        }
    }

    protected function delete()
    {
        $files = \Yii::$app->session->get(UploadFormInterface::FILE_NAME, []);
        $this->type = \Yii::$app->request->post('type');
        $filename = \Yii::$app->request->post('file');
        if (!empty($files[$this->type][$filename])) {
            $files[$this->type][$filename]['is_deleted'] = true;
        }
        \Yii::$app->session->set(UploadFormInterface::FILE_NAME, $files);
        return ['status' => true];
    }

    protected function upload()
    {
        $form = $this->getUploadForm();

        $this->model = new UploadFormStrategy($form);
        $this->model->file = UploadedFile::getInstanceByName(UploadFormInterface::FILE_NAME);
        if ($this->model->upload()) {
            $result = $this->afterUpload();

            $return = ['status' => true, 'data' => $result];
        } else {
            $return = ['status' => false];//todo return errors
        }
        return $return;
    }

    protected function getUploadForm()
    {
        $this->type = \Yii::$app->request->post('type');
        if ($this->type === null) {
            throw new Exception('Type can not be empty');
        }

        switch ($this->type) {
        case UploadForm::getEntity():
            $form = new UploadForm();
            break;
        default:
            throw new Exception('Incorrect type. UploadForm is not found');
        }

        return $form;
    }

    protected function afterUpload()
    {
        $files = \Yii::$app->session->get(UploadFormInterface::FILE_NAME, []);

        $result = [
            'filename' => $this->model->filename,
            'time' => time(),
        ];
        $files[$this->type][$this->model->filename] = $result;
        \Yii::$app->session->set(UploadFormInterface::FILE_NAME, $files);

        return $result;
    }
}
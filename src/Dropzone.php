<?php
namespace common\widgets\dropzone;

use common\helpers\Html;
use common\widgets\dropzone\events\DropzoneJsEventInterface;
use common\widgets\dropzone\events\Error;
use common\widgets\dropzone\events\Remove;
use common\widgets\dropzone\events\Sending;
use common\widgets\dropzone\events\Success;
use yii\base\Widget;

class Dropzone extends Widget
{
    public $id;

    /**
     * Url for uploading
     * @var string
     */
    public $url;

    /**
     * POST variables which will be sent with file
     * @var array
     */
    public $data;

    /**
     * in MB
     * @var int
     */
    public $maxFilesize = 5;

    /**
     * @var int
     */
    public $maxFiles = 1;

    /**
     * Mimetypes end extensions of allowed files
     * @var array|string
     */
    public $acceptedFiles;

    /**
     * Predefined allowed types
     * @var array|string
     */
    public $acceptedTypes;

    /**
     * Array with errorKeys and errorTexts
     * @var array
     */
    public $errors = [];

    /**
     * The message that gets displayed before any files are dropped.
     * @var string
     */
    public $defaultText = 'Добавить новые файлы';

    /**
     * The text to be used to remove a file
     * @var string
     */
    public $removeLinkText = 'Удалить';

    /**
     * This will add a link to every file preview to remove or cancel (if already uploading) the file.
     * @var bool
     */
    public $addRemoveLinks = true;

    /**
     * @var string
     */
    public $removeSelector = '.dz-remove';

    public $removeUrl;

    /**
     * String that contains the template used for each dropped image. Change it to fulfill your needs but make sure to properly provide all elements.
     * @var string
     */
    public $previewTemplate;

    /**
     * @var array
     */
    public $events = [];

    /**
     * Predefined types of files (with mime and ext)
     * @var array
     */
    protected $defaultAcceptedTypes = [
        'image' => ['mime' => ['image/jpeg', 'image/pjpeg', 'image/png'], 'ext' => ['.jpg', '.jpe', '.jpeg', '.png']],
        'pdf' => ['mime' => ['application/pdf', 'application/x-pdf', 'application/x-bzpdf', 'application/x-gzpdf'], 'ext' => ['.pdf']],
        'djvu' => ['mime' => ['image/vnd.djvu', 'image/x-djvu'], 'ext' => ['.djvu', '.djv']],
        'pm' => ['mime' => ['application/xml', 'text/xml'], 'ext' => ['xsd']],
    ];

    private $_errors = [
        'dictMaxFilesExceeded' => 'Превышено максимальное колличество файлов',
        'dictInvalidFileType' => 'Неверный тип файла',
        'dictFallbackMessage' => 'Your browser does not support drag\'n\'drop file uploads',
        'dictFileTooBig' => 'Размер файла на должен превышать {{maxFilesize}} Mb',
        'dictResponseError' => 'Не удалось загрузить файл'
    ];

    private $_className = 'dropzone';

    /**
     * Dropzone params which must be encoded
     * @var array
     */
    private $_dropzoneEncodeParams = ['url', 'acceptedFiles', 'dictDefaultMessage', 'previewTemplate', 'dictRemoveFile'];

    /**
     * The name of the file param that gets transferred.
     * @var string
     */
    private $_paramName;


    public function init()
    {
        $this->_paramName = UploadFormInterface::FILE_NAME;
        $this->previewTemplate = $this->createPreviewTemplate();

        $this->addRemoveLinks = (bool)$this->addRemoveLinks;
        //-----
        if (empty($this->id)) {
            throw new Exception('ID is empty');
        }

        if (empty($this->url)) {
            throw new Exception('Url is empty');
        }

        if (empty($this->maxFilesize)) {
            throw new Exception('maxFilesize is empty');
        }

        if (!is_int($this->maxFilesize)) {
            throw new Exception('maxFilesize is not integer');
        }

        if (empty($this->maxFiles)) {
            throw new Exception('maxFiles is empty');
        }

        if (!is_int($this->maxFiles)) {
            throw new Exception('maxFiles is not integer');
        }
        //-----


        //-----
        if (!empty(array_diff(array_keys($this->errors), array_keys($this->_errors)))) {
            throw new Exception('Incorrect errorNames');
        }
        $this->errors = array_merge($this->_errors, $this->errors);
        //-----


        //-----
        if (empty($this->events['sending'])) {
            $this->events['sending'] = new Sending($this->data);
        }

        if (empty($this->events['error'])) {
            $this->events['error'] = new Error($this->errors['dictResponseError']);
        }

        $removeEvent = null;
        if ($this->addRemoveLinks) {
            if (empty($this->removeUrl)) {
                $this->removeUrl = $this->url;
            }
            $removeEvent = new Remove($this->removeUrl, $this->data);
        }

        if (empty($this->events['success'])) {
            $this->events['success'] = new Success(
                $this->errors['dictResponseError'],
                $this->addRemoveLinks ? $this->removeSelector : null,
                $removeEvent->getJs()
            );
        }

        foreach ($this->events as $k => $v) {
            if (!($v instanceof DropzoneJsEventInterface)) {
                throw new Exception("$k is not an instantiated object of a class that implements an interface DropzoneJsEventInterface");
            }
        }
        //-----


        //-----
        if (!empty($this->acceptedTypes)) {
            if (empty($this->acceptedFiles)) {
                $this->acceptedFiles = [];
            }
            if (!is_array($this->acceptedFiles)) {
                $this->acceptedFiles = [$this->acceptedFiles];
            }
            if (!is_array($this->acceptedTypes)) {
                $this->acceptedTypes = [$this->acceptedTypes];
            }
            foreach ($this->acceptedTypes as $v) {
                if (empty($this->defaultAcceptedTypes[$v])) {
                    throw new Exception('Invalid acceptedTypes');
                }
                $this->acceptedFiles = array_merge(
                    $this->acceptedFiles,
                    $this->defaultAcceptedTypes[$v]['ext'],
                    $this->defaultAcceptedTypes[$v]['mime']
                );
            }
        }

        if (is_array($this->acceptedFiles)) {
            $this->acceptedFiles = implode(', ', $this->acceptedFiles);
        }
        //-----

        $this->registerAssets();
    }

    public function run()
    {
        $options = [];
        $options['class'] = isset($options['class']) ? [$options['class']] : [];
        $options['class'][] = $this->_className;
        $options['class'] = implode(' ', $options['class']);
        $options['id'] = $this->id;
        return Html::tag('div', '', $options);
    }

    public function registerAssets()
    {
        $view = $this->getView();
        DropzoneAsset::register($view);
        $view->registerJs($this->_getJs());
    }

    protected function createPreviewTemplate()
    {
        $str = [];
        $str[] = Html::tag('div', Html::img('', ['data-dz-thumbnail' => '']), ['class' => 'dz-image']);
        $str[] = Html::beginTag('div', ['class' => 'dz-details']) .
            Html::tag('div', Html::tag('span', '', ['data-dz-size' => '']), ['class' => 'dz-size']) .
            Html::tag('div', Html::tag('span', '', ['data-dz-name' => '']), ['class' => 'dz-filename']) .
            Html::endTag('div');
        $str[] = Html::tag('div', Html::tag('span', '', ['class' => 'dz-upload', 'data-dz-uploadprogress' => '']), ['class' => 'dz-progress']);
        $str[] = Html::tag('div', Html::tag('span', '', ['data-dz-errormessage' => '']), ['class' => 'dz-error-message']);
        $str[] = Html::beginTag('div', ['class' => 'dz-success-mark']) .
            Html::beginTag('svg', [
                'width' => '54px',
                'height' => '54px',
                'viewBox' => '0 0 54 54',
                'version'=> '1.1',
                'xmlns'=> 'http://www.w3.org/2000/svg',
                'xmlns:xlink' => 'http://www.w3.org/1999/xlink',
                'xmlns:sketch' => 'http://www.bohemiancoding.com/sketch/ns'
            ]) .
            Html::tag('title', 'Check') .
            Html::tag('defs') .
            Html::beginTag('g', [
                'id' => 'Page-1',
                'stroke' => 'none',
                'stroke-width' => '1',
                'fill' => 'none',
                'fill-rule' => 'evenodd',
                'sketch:type' => 'MSPage'
            ]) .
            Html::tag('path', '', [
                'd' => 'M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z',
                'id' => 'Oval-2',
                'stroke-opacity' => '0.198794158',
                'stroke' => '#747474',
                'fill-opacity' => '0.816519475',
                'fill' => '#FFFFFF',
                'sketch:type' => 'MSShapeGroup'

            ]) .
            Html::endTag('g') .
            Html::endTag('svg') .
            Html::endTag('div');
        $str[] = Html::beginTag('div', ['class' => 'dz-error-mark']) .
            Html::beginTag('svg', [
                'width' => '54px',
                'height' => '54px',
                'viewBox' => '0 0 54 54',
                'version'=> '1.1',
                'xmlns'=> 'http://www.w3.org/2000/svg',
                'xmlns:xlink' => 'http://www.w3.org/1999/xlink',
                'xmlns:sketch' => 'http://www.bohemiancoding.com/sketch/ns'
            ]) .
            Html::tag('title', 'Error') .
            Html::tag('defs') .
            Html::beginTag('g', [
                'id' => 'Page-1',
                'stroke' => 'none',
                'stroke-width' => '1',
                'fill' => 'none',
                'fill-rule' => 'evenodd',
                'sketch:type' => 'MSPage'
            ]) .
            Html::beginTag('g', [
                'id' => 'Check-+-Oval-2',
                'stroke' => '#747474',
                'stroke-opacity' => '0.198794158',
                'fill' => '#FFFFFF',
                'fill-opacity' => '0.816519475',
                'sketch:type' => 'MSLayerGroup'
            ]) .
            Html::tag('path', '', [
                'd' => 'M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z',
                'id' => 'Oval-2',
                'sketch:type' => 'MSShapeGroup'
            ]) .
            Html::endTag('g') .
            Html::endTag('g') .
            Html::endTag('svg') .
            Html::endTag('div');
        $str = Html::tag('div', implode("\n", $str), ['class' => 'dz-preview dz-file-preview']);

        return $str;
    }

    private function _getJs()
    {
        $paramStrs = implode(", \n", $this->_collectOptions());

        return <<<JS
        var id = '{$this->id}'.replace(/[\-_](\w)/g, function(match) {
            return match.charAt(1).toUpperCase();
        });;
        Dropzone.options[id] = false;
        $("#{$this->id}").dropzone({ $paramStrs });
JS;
    }

    private function _collectOptions()
    {
        $params = [
            'url' => $this->url,
            'maxFilesize' => $this->maxFilesize,
            'maxFiles' => $this->maxFiles,
            'acceptedFiles' => $this->acceptedFiles,
            'dictDefaultMessage' => $this->defaultText,
            'addRemoveLinks' => (bool)$this->addRemoveLinks,
            'previewTemplate' => $this->previewTemplate,
            'dictRemoveFile' => $this->removeLinkText
        ];
        $paramStrs = [];

        $params = array_merge($params, $this->_collectErrors());
        $params = array_merge($params, $this->_collectEvents());

        foreach ($params as $k => $v) {
            if (empty($v)) {
                continue;
            }
            $paramStrs[] = $k . ' : ' . (in_array($k, $this->_dropzoneEncodeParams) ? json_encode($v) : $v);
        }

        return $paramStrs;
    }

    private function _collectErrors()
    {
        $params = [];
        foreach ($this->errors as $k => $v) {
            $params[$k] = json_encode($v);
        }
        return $params;
    }

    private function _collectEvents()
    {
        $params = [];
        /**
         * @var $v DropzoneJsEventInterface
         */
        foreach ($this->events as $k => $v) {
            $result = $v->getJs();
            if ($result !== null) {
                $params[$k] = $result;
            }
        }
        return $params;
    }
}
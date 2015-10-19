<?php
/**
 * Created by PhpStorm.
 * User: viktory
 * Date: 20.08.15
 * Time: 15:12
 */

namespace common\widgets\dropzone\events;


class Remove implements DropzoneJsEventInterface
{
    public $url;
    public $data;

    public function __construct($url, $data = null)
    {
        $this->url = $url;
        $this->data = $data;
    }
    /**
     * @return string
     */
    public function getJs()
    {
        $result = null;
        $data = [];

        if (!empty($this->data) && is_array($this->data)) {
            $data = $this->data;
        }
        $data = json_encode($data);

        $result = <<<JS
        function(e) {
            var params = $.extend(true, {delete: 1, file: e.data.file}, {$data});
            $.post('{$this->url}', params);
        }
JS;


        return $result;
    }
}
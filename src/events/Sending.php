<?php
/**
 * Created by PhpStorm.
 * User: viktory
 * Date: 20.08.15
 * Time: 15:12
 */

namespace common\widgets\dropzone\events;


class Sending implements DropzoneJsEventInterface
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
     * @return string
     */
    public function getJs()
    {
        $result = null;
        if (!empty($this->data) && is_array($this->data)) {
            $data = [];
            foreach ($this->data as $k => $v) {
                $data[] = <<<JS
                data.append("$k", "$v");
JS;
            }
            $data = implode('', $data);

            $result = <<<JS
            function(file, xhr, data) {
                $data
            }
JS;
        }
        return $result;
    }
}
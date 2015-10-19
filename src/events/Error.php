<?php
/**
 * Created by PhpStorm.
 * User: viktory
 * Date: 20.08.15
 * Time: 15:12
 */

namespace common\widgets\dropzone\events;


class Error implements DropzoneJsEventInterface
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
    /**
     * @return string
     */
    public function getJs()
    {
        $result = null;

        $result = <<<JS
        function(file, message, xhr) {
            var node, _i, _len, _ref, _results;

            if (!!xhr && !!xhr.status && ((xhr.status < 200) || (xhr.status > 299))) {
                message = "{$this->message}";
            } else {
                message = message || "{$this->message}";
            }

            if (file.previewElement) {
                file.previewElement.classList.add("dz-error");

                _ref = file.previewElement.querySelectorAll("[data-dz-errormessage]");
                _results = [];
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                    node = _ref[_i];
                    _results.push(node.textContent = message);
                }
                return _results;
            }
        }
JS;

        return $result;
    }
}
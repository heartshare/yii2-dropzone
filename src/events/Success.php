<?php
/**
 * Created by PhpStorm.
 * User: viktory
 * Date: 20.08.15
 * Time: 15:12
 */

namespace common\widgets\dropzone\events;


class Success implements DropzoneJsEventInterface
{
    public $errorMessage;
    public $removeSelector;
    public $removeEvent;

    public function __construct($errorMessage, $removeSelector, $removeEvent)
    {
        $this->errorMessage = $errorMessage;
        $this->removeSelector = $removeSelector;
        $this->removeEvent = $removeEvent;
    }
    /**
     * @return string
     */
    public function getJs()
    {
        $result = null;

        $result = <<<JS
        function(file, response) {
            if (!!response.status) {
                if (file.previewElement) {
                    if(!!'{$this->removeSelector}') {
                        $(file.previewElement).find('{$this->removeSelector}').on(
                            'click',
                            {file : response.data.filename},
                            {$this->removeEvent}
                        )
                    }

                    return file.previewElement.classList.add("dz-success");
                }
            } else {
                file.accepted = false;
                var text = response.text || "{$this->errorMessage}";
                this.emit("error", file, text);
            }
        }
JS;


        return $result;
    }
}
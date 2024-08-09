<?php

namespace helpers\widgets\swal;

use Yii;
use yii\bootstrap5\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Alert widget renders a message from session flash or custom messages.
 * @package yii2mod\alert
 */
class Alert extends Widget
{
    /**
     * Info type of the alert
     */
    const TYPE_INFO = 'info';

    /**
     * Error type of the alert
     */
    const TYPE_ERROR = 'error';

    /**
     * Success type of the alert
     */
    const TYPE_SUCCESS = 'success';

    /**
     * Warning type of the alert
     */
    const TYPE_WARNING = 'warning';

    /**
     * Input type of the alert
     */
    const TYPE_INPUT = 'input';

    /**
     * @var string the type of the alert to be displayed. One of the `TYPE_` constants.
     * Defaults to `TYPE_SUCCESS`
     */
    public $type = self::TYPE_SUCCESS;

    /**
     * All the flash messages stored for the session are displayed and removed from the session
     * Defaults to true.
     * @var bool
     */
    public $useSessionFlash = true;

    /**
     * @var bool If set to true, the user can dismiss the modal by clicking outside it.
     * Defaults to true.
     */
    public $allowOutsideClick = true;

    /**
     * @var int Auto close timer of the modal. Set in ms (milliseconds).
     * Default - 2,5 second
     */
    public $timer = 3000;

    /**
     * @var string customer alert callback
     */
    public $callback = 'function() {}';

    /**
     * Initializes the widget
     */
    public function init()
    {
        parent::init();

        if ($this->useSessionFlash) {
            $session = Yii::$app->getSession();
            $flashes = $session->getAllFlashes();

            foreach ($flashes as $type => $data) {
                $data = (array)$data;
                foreach ($data as $message) {
                    $this->options['icon'] = $type;
                    $this->options['showConfirmButton'] = false;
                    $this->options['timerProgressBar'] = true;
                    $this->options['title'] = $message;
                }
                $session->removeFlash($type);
            }
        }
    }

    /**
     * Render alert
     * @return string|void
     */
    public function run()
    {
        if (array_key_exists("title", $this->options)) {
            $this->registerAssets();
        }
    }

    /**
     * Register client assets
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        AlertAsset::register($view);
        $js = "Swal.fire({$this->getOptions()}, {$this->callback});";
        $view->registerJs($js, $view::POS_END);
    }

    /**
     * Get plugin options
     * @return string
     */
    public function getOptions()
    {
        $this->options['allowOutsideClick'] = ArrayHelper::getValue($this->options, 'allowOutsideClick', $this->allowOutsideClick);
        $this->options['timer'] = ArrayHelper::getValue($this->options, 'timer', $this->timer);
        $this->options['icon'] = ArrayHelper::getValue($this->options, 'icon', $this->type);

        return Json::encode($this->options);
    }
}

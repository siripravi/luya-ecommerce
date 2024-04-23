<?php
namespace app\components;
use luya\helpers\ArrayHelper;

use Yii;

//use yii\widgets\ActiveForm;
use exocet\bootstrap5md\widgets\ActiveForm;
use yii\widgets\Pjax;
use luya\forms\Model;


/**
 * Forms Component
 *
 * @property ActiveForm $form
 * @property Model $model
 *
 * @author Basil Suter <git@nadar.io>
 * @since 1.0.0
 */
class Forms extends \luya\forms\Forms
{
    const EVENT_AFTER_SAVE = 'afterSave';
    //const EVENT_AFTER_VALID = 'afterValidate';

    /**
     * @var string The session variable name
     */
    public $sessionFormDataName = '';

    /**
     * @var string The Active Form class, for configurations options see {{$activeFormClassOptions}}.
     */
    public $activeFormClass = 'exocet\bootstrap5md\widgets\ActiveForm'; //'yii\widgets\ActiveForm';
    public $pjaxFormClass = "yii\widgets\Pjax";
    public $isPjax = false;
    /**
     * @var array A configuration array which will be passed to ActiveForm::begin($options). Example usage `['enableClientValidation' => false]`
     */
    public $activeFormClassOptions = [];
    public $pjaxClassOptions = [];
    /**
     * @var array An array of options which will be passed to {{ Html::submitButton(..., $options)}} submit buttons.
     */
    public $submitButtonsOptions = ['class' => 'btn btn-success btn-buy px-2'];

    /**
     * @var array An array of options which will be passed to {{ Html::submitButton(..., $options)}} back buttons.
     */
    public $backButtonOptions = [];

    /**
     * @var boolean Indicates whether the current model has been loaded or not. This does not say anything about whether loading was successfull
     * or not.
     * @since 1.3.0
     */
    public $isModelLoaded = false;

    /**
     * @var boolean Indicates whether the curent model is loaded AND sucessfull validated.
     * @since 1.4.2
     */
    public $isModelValidated = false;

    /**
     * @var ActiveForm
     */
    private $_form;

    private $_pjax;

    /**
     * @var Model
     */
    private $_model;

    public function beginForm(ActiveForm $form, String $model = 'luya\forms\Model')
    {
        $this->_form = $form;
        $this->_model = new $model();
        $this->sessionFormDataName = "__" . basename($model);
    }

    public function beginPjaxForm(Pjax $pjax)
    {
        $this->_pjax = $pjax;
    }

    public function getPjax()
    {
        return $this->_pjax;
    }
    /**
     * Active Form Getter
     *
     * @return ActiveForm
     */
    public function getForm()
    {
        return $this->_form;
    }

    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * Model Getter
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Clean up the session and destroy model and form
     */
  /*  public function cleanup()
    {
        Yii::$app->session->remove($this->sessionFormDataName);
        $this->_model = null;
        $this->_form = null;
        $this->isModelValidated = false;
        $this->isModelLoaded = false;
    }*/

    /**
     * Loads the data from the post request into the model, validates it and stores the data in the session.
     *
     * @return boolean Whether loading the model with data was successfull or not (if not a validation error may persists in the $model).
     */
    public function loadModel()
    {
       
       // if(!empty($this->model))
            $modelClass = basename(get_class($this->model));
            
        if ($this->isModelValidated) {
            return true;
        }
        /**TRIGGER BEFORE LOAD & VALIDATE EVENT : Chandra:1/11 **/
        if (method_exists($this->model, 'getBeforeLoadModelEvent')) {
            Yii::debug('before Model Load..' . $this->sessionFormDataName, __METHOD__);          
            $event = $this->model->getBeforeLoadModelEvent($this->model);
            $this->model->trigger(get_class($this->model)::EVENT_BEFORE_LOAD, $event);         
           // Yii::$app->session->set("__" . $modelClass, $this->model->attributes);
          
        }

        if (!Yii::$app->request->isPost || !$this->model) {
            return false;
        }
        $this->isModelLoaded = $this->model->load(Yii::$app->request->post());
        if ($this->isModelLoaded && $this->model->validate()) {
           // Yii::$app->session->set("__" . $modelClass, $this->model->attributes);
            Yii::debug('successfull loaded and validated order form', __METHOD__);
           
            $this->isModelValidated = true;
            /**TRIGGER AFTER LOAD & VALIDATE EVENT : Chandra:1/9 **/
            if (method_exists($this->model, 'getAfterSaveEvent')) {
                $event = $this->model->getAfterSaveEvent($this->model);
                $this->model->trigger(get_class($this->model)::EVENT_AFTER_VALID, $event);
            }
            return true;
        }
        return false;
    }

    /**
     * Get all form values which are stored trough {{loadModel()}}.
     *
     * @return array An array with attribute name and value
     */
    public function getFormData()
    {
        $modelClass = basename(get_class($this->model));
        $this->sessionFormDataName = "__" . $modelClass;
        return ArrayHelper::typeCast(Yii::$app->session->get($this->sessionFormDataName, []));
    }   
}

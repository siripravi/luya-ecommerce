<?php

namespace app\blocks;

use Yii;

use luya\Exception;
use luya\forms\blockgroups\FormGroup;
use luya\forms\Model;
use luya\forms\models\Form;

/**
 * Form Block.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 1.0.0
 */
class FormBlock extends \luya\forms\blocks\FormBlock
{
      public $module = "@app";
    public function name()
    {
        return 'My Forms';
    }   
    /**
     * @var boolean Choose whether block is a layout/container/segmnet/section block or not, Container elements will be optically displayed
     * in a different way for a better user experience. Container block will not display isDirty colorizing.
     */
    public $isContainer = true;

    public $review = false;

    //public $previewButtonsTemplate = '<div class="forms-preview-buttons-container">{{back}}<span class="forms-divider"> | </span>{{submit}}</div>';
    public $previewButtonsTemplate = '';
    
    /**
     * {@inheritDoc}
     */

    public function setup()
    {
        Yii::debug('from block setup invocation', __METHOD__);
        Yii::debug($this->getEnvOption('context', false));
        $object = Yii::$app->forms->activeFormClass;
        $model = 'luya\forms\Model';
       
        $begin = Yii::$app->forms->activeFormClassOptions;
        Yii::$app->forms->model = new $model();   //Yii::$app->forms->model->isPjax = false;
        if ($this->isFrontendContext()) {
            $model = Yii::$app->menu->current->getPropertyValue('model');
            Yii::$app->forms->model = new $model();
            $begin = Yii::$app->forms->model->activeFormClassOptions;
            Yii::$app->forms->pjaxClassOptions =  Yii::$app->forms->model->pjaxOptions;
           
        } 
        Yii::$app->forms->beginForm($object::begin($begin), $model);
        if((!(basename($model) == "Model")) && Yii::$app->forms->model->isPjax)
        \yii\widgets\Pjax::begin([]);
       
    }

    /**
     * Invokes the model submiting process and redirects the browsers if needed
     *
     * @return void
     */
    public function submitAndStore()
    {
        if ($this->isSubmit()) {
            // the data is only available if the isSubmit call was running, therefore for
            // first check for is submit
            // second get data from session
            $data = Yii::$app->forms->getFormData();
          
            if (!empty($data)) {              
                $model = Yii::$app->forms->model;
                $model->attributes = $data;
                // invisible attributes should not be validate in the second validation step.
                if (Yii::$app->forms->isModelValidated || $model->validate($model->getAttributesWithoutInvisible())) {
                  /*  if (!Yii::$app->forms->save(Form::findOne($this->getVarValue('formId')), $this->getCfgValue('doNotSaveData', false))) {
                        throw new Exception("Error while saving the form data, please try again later.");
                    }*/
                /*    if (method_exists(get_class($model), 'getAfterSaveEvent')) {
                        $event = $model->getAfterSaveEvent($model);
                        $model->trigger(get_class($model)::EVENT_AFTER_SAVE, $event);
                    }  */
                    Yii::$app->forms->cleanup();
                    // set flash, redirect and end app
                    Yii::$app->session->setFlash('formDataSuccess');
                    Yii::$app->response->redirect($model->redirectUrl);
                    // Yii::$app->response->redirect('/shopping-cart');
                    return Yii::$app->end();
                }
            }
        }
    }
}

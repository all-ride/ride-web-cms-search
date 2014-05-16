<?php

namespace ride\web\cms\search;

use \ride\library\form\FormBuilder;
use \ride\library\http\Request;
use \ride\library\template\TemplateFacade;
use \ride\library\widget\WidgetProperties;

/**
 * Abstract implementation of an engine for the search widgets
 */
abstract class AbstractSearchEngine implements SearchEngine {

    /**
     * Code of the current locale
     * @var string
     */
    protected $locale;

    /**
     * Instance of the template facade
     * @var \ride\library\template\TemplateFacade
     */
    protected $templateFacade;

    /**
     * Properties of the search result widget
     * @var \ride\library\widget\WidgetProperties
     */
    protected $resultWidgetProperties;

    /**
     * Sets the locale
     * @param string $locale Code of the current locale
     * @return null
     */
    public function setLocale($locale) {
        $this->locale = $locale;
    }

    /**
     * Sets the instance of the template facade
     * @param \ride\library\template\TemplateFacade $templateFacade
     * @return null
     */
    public function setTemplateFacade(TemplateFacade $templateFacade) {
        $this->templateFacade = $templateFacade;
    }

    /**
     * Sets the properties of the search result widget
     * @param \ride\library\widget\WidgetProperties $resultWidgetProperties
     * @return null
     */
    public function setResultWidgetProperties(WidgetProperties $resultWidgetProperties) {
        $this->resultWidgetProperties = $resultWidgetProperties;
    }

    /**
     * Prepares the properties form by adding row definitions
     * @param \ride\library\form\FormBuilder $builder
     * @param array $options
     * @return null
     */
    public function preparePropertiesForm(FormBuilder $builder, array $options) {

    }

    /**
     * Processes the submitted values of the properties form
     * @param array $data Submitted values of the form
     * @return null
     */
    public function processPropertiesForm(array $data) {

    }

}

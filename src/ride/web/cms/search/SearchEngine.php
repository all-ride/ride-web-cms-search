<?php

namespace ride\web\cms\search;

use \ride\library\form\FormBuilder;
use \ride\library\http\Request;
use \ride\library\http\Response;
use \ride\library\template\TemplateFacade;
use \ride\library\widget\WidgetProperties;

/**
 * Interface of an engine for the search widgets
 */
interface SearchEngine {

    /**
     * Sets the locale
     * @param string $locale Code of the current locale
     * @return null
     */
    public function setLocale($locale);

    /**
     * Sets the instance of the template facade
     * @param \ride\library\template\TemplateFacade $templateFacade
     * @return null
     */
    public function setTemplateFacade(TemplateFacade $templateFacade);

    /**
     * Sets the properties of the search result widget
     * @param \ride\library\widget\WidgetProperties $widgetProperties
     * @return null
     */
    public function setResultWidgetProperties(WidgetProperties $widgetProperties);

    /**
     * Gets the view for the form widget
     * @param \ride\library\http\Request
     * @return \ride\library\mvc\view\View
     */
    public function getFormView(Request $request);

    /**
     * Gets the view for the result widget
     * @param \ride\library\http\Request $request
     * @param \ride\library\http\Response $response
     * @return \ride\library\mvc\view\View
     */
    public function getResultView(Request $request, Response $response);

    /**
     * Prepares the properties form by adding row definitions
     * @param \ride\library\form\FormBuilder $builder
     * @param array $options
     * @return null
     */
    public function preparePropertiesForm(FormBuilder $builder, array $options);

    /**
     * Processes the submitted values of the properties form
     * @param array $data Submitted values of the form
     * @return null
     */
    public function processPropertiesForm(array $data);

}

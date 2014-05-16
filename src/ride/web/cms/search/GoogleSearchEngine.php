<?php

namespace ride\web\cms\search;

use \ride\library\form\FormBuilder;
use ride\library\http\Request;
use ride\library\http\Response;
use ride\library\template\TemplateFacade;
use ride\web\mvc\view\TemplateView;

/**
 * Google search engine
 */
class GoogleSearchEngine extends AbstractSearchEngine {

    /**
     * Gets the client id for the Google search
     * @return string
     */
    public function getClientId() {
        return $this->resultWidgetProperties->getWidgetProperty('google.client.id');
    }

    /**
     * Gets the view for the form widget
     * @param \ride\library\http\Request
     * @return \ride\library\mvc\view\View
     */
    public function getFormView(Request $request) {
        $template = $this->templateFacade->createTemplate('cms/widget/search/form.google');
        $template->set('clientId', $this->getClientId());
        $template->set('action', $this->resultWidgetProperties->getNode()->getUrl($this->locale, $request->getBaseScript()));

        return new TemplateView($template);
    }

    /**
     * Gets the view for the result widget
     * @param \ride\library\http\Request $request
     * @param \ride\library\http\Response $response
     * @return \ride\library\mvc\view\View
     */
    public function getResultView(Request $request, Response $response) {
        $template = $this->templateFacade->createTemplate("cms/widget/search/result.google");
        $template->set('clientId', $this->getClientId());

        return new TemplateView($template);
    }

    /**
     * Prepares the properties form by adding row definitions
     * @param \ride\library\form\FormBuilder $builder
     * @param array $options
     * @return null
     */
    public function preparePropertiesForm(FormBuilder $builder, array $options) {
        $builder->addRow('client-id', 'string', array(
            'label' => $options['translator']->translate('label.id.client.google.search'),
            'default' => $this->getClientId(),
            'validators' => array(
                'required' => array(),
            ),
        ));
    }

    /**
     * Processes the submitted values of the properties form
     * @param array $data Submitted values of the form
     * @return null
     */
    public function processPropertiesForm(array $data) {
        $this->resultWidgetProperties->setWidgetProperty('google.client.id', $data['client-id']);
    }

}

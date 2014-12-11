<?php

namespace ride\web\cms\controller\widget;

use ride\library\cms\node\NodeModel;
use ride\library\template\TemplateFacade;

/**
 * Widget for a search form
 */
class SearchFormWidget extends AbstractWidget implements StyleWidget {

    /*
     * Machine name of this widget
     * @var string
     */
    const NAME = 'search.form';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/search.form.png';

    /**
     * Action to show the search form
     * @return null
     */
    public function indexAction(NodeModel $nodeModel, TemplateFacade $templateFacade) {
        $nodeId = $this->properties->getWidgetProperty('result.node');
        $widgetId = $this->properties->getWidgetProperty('result.widget');

        if (!$nodeId || !$widgetId) {
            return;
        }

        $selfNode = $this->properties->getNode();
        $resultNode = $nodeModel->getNode($selfNode->getRootNodeId(), $selfNode->getRevision(), $nodeId);
        $resultWidgetProperties = $resultNode->getWidgetProperties($widgetId);

        $engine = $resultWidgetProperties->getWidgetProperty('engine');
        if (!$engine) {
            return;
        }

        $engine = $this->dependencyInjector->get('ride\\web\\cms\\search\\SearchEngine', $engine);
        $engine->setLocale($this->locale);
        $engine->setTemplateFacade($templateFacade);
        $engine->setResultWidgetProperties($resultWidgetProperties);

        $view = $engine->getFormView($this->request);
        if ($view) {
            $this->response->setView($view);
        }
    }

    /**
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $preview = '---';

        $nodeId = $this->properties->getWidgetProperty('result.node');
        if ($nodeId) {
            $translator = $this->getTranslator();

            $nodeModel = $this->dependencyInjector->get('ride\\library\\cms\\node\\NodeModel');
            $node = $this->properties->getNode();
            $node = $nodeModel->getNode($node->getRootNodeId(), $node->getRevision(), $nodeId);
            if ($node) {
                $preview = '<strong>' . $translator->translate('label.node.search.result') . '</strong>: ' . $node->getName($this->locale) . '<br />';
            }
        }

        return $preview;
    }

    /*
     * Action to manage the properties of thie widget
     * @param \ride\library\cms\node\NodeModel $nodeModel Instance of the node
     * model
     * @return null
     */
    public function propertiesAction(NodeModel $nodeModel) {
        $translator = $this->getTranslator();

        $data = array(
            'result' => $this->properties->getWidgetProperty('result.node') . '-' . $this->properties->getWidgetProperty('result.widget'),
        );

        $options = array('' => '---');
        $nodes = $nodeModel->getNodesForWidget('search.result', $this->properties->getNode()->getRootNodeId());
        foreach ($nodes as $node) {
            $options[$node->getId() . '-' . $node->getWidgetId()] = $node->getName($this->locale);
        }

        $form = $this->createFormBuilder($data);
        $form->addRow('result', 'select', array(
            'label' => $translator->translate('label.node.search.result'),
            'description' => $translator->translate('label.node.search.result.description'),
            'options' => $options,
            'validators' => array(
                'required' => array(),
            ),
        ));

        $form = $form->build();
        if ($form->isSubmitted()) {
            try {
                $form->validate();

                $data =  $form->getData();

                list($nodeId, $widgetId) = explode('-', $data['result']);

                $this->properties->setWidgetProperty('result.node', $nodeId);
                $this->properties->setWidgetProperty('result.widget', $widgetId);

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $this->setTemplateView('cms/widget/search/properties', array(
            'form' => $form->getView(),
        ));

        return false;
    }

    /**
     * Gets the options for the styles
     * @return array Array with the name of the option as key and the
     * translation key as value
     */
    public function getWidgetStyleOptions() {
        return array(
            'container' => 'label.style.container',
        );
    }

}

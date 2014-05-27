<?php

namespace ride\web\cms\controller\widget;

use ride\library\template\TemplateFacade;

use ride\web\cms\controller\widget\AbstractWidget;
use ride\web\cms\content\mapper\io\DependencyContentMapperIO;
use ride\web\cms\search\SearchEngine;

/**
 * Widget for search results
 */
class SearchResultWidget extends AbstractWidget {

	/*
	 * Machine name of this widget
	 * @var string
	 */
	const NAME = 'search.result';

	/**
	 * Path to the icon of this widget
	 * @var string
	 */
	const ICON = 'img/cms/widget/search.result.png';

	/*
	 * Widget properties form
	 */
	public function indexAction(TemplateFacade $templateFacade) {
        $engine = $this->properties->getWidgetProperty('engine');
        if (!$engine) {
            return;
        }

        $engine = $this->dependencyInjector->get('ride\\web\\cms\\search\\SearchEngine', $engine);
        $engine->setLocale($this->locale);
        $engine->setTemplateFacade($templateFacade);
        $engine->setResultWidgetProperties($this->properties);

        $view = $engine->getResultView($this->request, $this->response);
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

        $engine = $this->properties->getWidgetProperty('engine');
        if ($engine) {
            $translator = $this->getTranslator();

            $preview = '<strong>' . $translator->translate('label.engine.search') . '</strong>: ' . $engine;
        }

        return $preview;
    }

    /*
     * Action to manage the properties of thie widget
     * @param \ride\library\cms\node\NodeModel $nodeModel Instance of the node
     * model
     * @return null
     */
	public function propertiesAction() {
		$translator = $this->getTranslator();

        $engine = $this->properties->getWidgetProperty('engine', $this->request->getBodyParameter('engine'));
        $engines = $this->dependencyInjector->getAll('ride\\web\\cms\\search\\SearchEngine');
        $formOptions = array(
            'translator' => $translator,
        );
        $data = array(
            'current' => $engine,
            'engine' => $engine,
        );

        $form = $this->createPropertiesForm($data, $formOptions, $engines, $engine ? $engines[$engine] : null);
        if ($form->isSubmitted()) {
            $data = $form->getData();

            if ($data['engine'] && $data['engine'] == $data['current']) {
                try {
                    $form->validate();

                    $this->properties->setWidgetProperty('engine', $data['engine']);

                    $engines[$data['engine']]->processPropertiesForm($data);

                    return true;
                } catch (ValidationException $exception) {
                    $this->setValidationException($exception, $form);
                }
            } else {
                $engine = $data['engine'];

                $form = $this->createPropertiesForm($data, $formOptions, $engines, $engine ? $engines[$engine] : null);
                $form->getRow('current')->setData($engine);
            }
        }

        $view = $this->setTemplateView('cms/widget/search/properties', array(
            'form' => $form->getView(),
        ));
        $view->addJavascript('js/cms/search.js');

        return false;
	}

    protected function createPropertiesForm(array $data, array $formOptions, array $engines, SearchEngine $engine = null) {
        $translator = $this->getTranslator();

        $engineOptions = array('' => '---');
        foreach ($engines as $engineName => $engineInstance) {
            $engineOptions[$engineName] = $translator->translate('search.engine.' . $engineName);
        }

        $form = $this->createFormBuilder($data);
        $form->setId('form-search-result');
        $form->addRow('current', 'hidden');
        $form->addRow('engine', 'select', array(
            'label' => $translator->translate('label.engine.search'),
            'options' => $engineOptions,
            'validators' => array(
                'required' => array(),
            ),
        ));

        if ($engine) {
            $engine->setLocale($this->locale);
            $engine->setResultWidgetProperties($this->properties);
            $engine->preparePropertiesForm($form, $formOptions);
        }

        return $form->build();
    }

}

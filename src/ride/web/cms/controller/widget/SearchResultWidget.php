<?php

namespace ride\web\cms\controller\widget;

use ride\library\template\TemplateFacade;
use ride\web\cms\controller\widget\AbstractWidget;
use ride\web\cms\content\mapper\io\DependencyContentMapperIO;

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
        $engineOptions = array();

        $engines = $this->dependencyInjector->getAll('ride\\web\\cms\\search\\SearchEngine');
        foreach ($engines as $engineName => $engine) {
            $engineOptions[$engineName] = $translator->translate('search.engine.' . $engineName);
        }
        $engineOptions = array('' => '---') + $engineOptions;

        $engine = $this->properties->getWidgetProperty('engine');

        $formOptions = array(
            'translator' => $translator,
        );
        $data = array(
            'current' => $engine,
            'engine' => $engine,
        );

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
            $engines[$engine]->setLocale($this->locale);
            $engines[$engine]->setResultWidgetProperties($this->properties);
            $engines[$engine]->preparePropertiesForm($form, $formOptions);
        }

		$form = $form->build();
        if ($form->isSubmitted()) {
            $currentEngine = $form->getRow('current')->getData();
            $selectedEngine = $form->getRow('engine')->getData();
            if ($currentEngine == $selectedEngine) {
                try {
                    $form->validate();

                    $data = $form->getData();

                    $this->properties->setWidgetProperty('engine', $data['engine']);

                    if ($currentEngine) {
                        $engines[$currentEngine]->processPropertiesForm($data);
                    }

                    return true;
                } catch (ValidationException $exception) {
                    $this->setValidationException($exception, $form);
                }
            } else {
                $engine = $selectedEngine;

                $data['current'] = $engine;
                $data['engine'] = $engine;

                $form = $this->createFormBuilder($data);
                $form->addRow('current', 'hidden');
                $form->addRow('engine', 'select', array(
                    'label' => $translator->translate('label.engine.search'),
                    'options' => $engineOptions,
                    'validators' => array(
                        'required' => array(),
                    ),
                ));

                if ($engine) {
                    $engines[$engine]->setLocale($this->locale);
                    $engines[$engine]->setResultWidgetProperties($this->properties);
                    $engines[$engine]->preparePropertiesForm($form, $formOptions);
                }

                $form = $form->build();
            }
        }

        $view = $this->setTemplateView('cms/widget/search/properties', array(
            'form' => $form->getView(),
        ));
        $view->addJavascript('js/cms/search.js');

        return false;
	}

}

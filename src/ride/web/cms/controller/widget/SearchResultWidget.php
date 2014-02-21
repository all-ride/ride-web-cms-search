<?php

namespace ride\web\cms\controller\widget;

use ride\library\template\TemplateFacade;
use ride\web\cms\controller\widget\AbstractWidget;
use ride\web\cms\content\mapper\io\DependencyContentMapperIO;

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
	const ICON = 'img/cms/widget/text.png';


	/*
	 * Widget properties form
	 */
	public function indexAction(TemplateFacade $templateFacade) {
		$engine = $this->properties->getWidgetProperty('search.engine');
		if (!$engine) {
			return;
		}
		$engine = $this->dependencyInjector->get('ride\\web\\cms\\search\\SearchEngine', $engine);
		$view = $engine->getView($templateFacade, null, null);

        $this->response->setView($view);

        return $view;
	}

    public function getPropertiesPreview() {
        $translator = $this->getTranslator();

        return $translator->translate('preview.search.results');
    }

    /**
     * Gets the callback for the properties action
     * @return null|callback Null if the widget does not implement a properties
     * action, a callback for the action otherwise
     */
    public function getPropertiesCallback() {
        return array($this, 'propertiesAction');
    }

	public function propertiesAction() {
		$translator = $this->getTranslator();

		$data = array(
			'search_engine' => $this->properties->getWidgetProperty('search.engine'),
		);

        $engines = $this->dependencyInjector->getAll('ride\\web\\cms\\search\\SearchEngine');

		foreach ($engines as $engineName => $engine) {
			$engines[$engineName] = $translator->translate('search.engine.' . $engineName);
		}

		$form = $this->createFormBuilder($data);

		$form->addRow('search_engine', 'select', array(
            'options' => $engines,
		));

		$form->setRequest($this->request);
		$form = $form->build();

        if ($form->isSubmitted) {
            $data = $form->getData();

            if (isset($data['search_engine'])) {
                $this->properties->setWidgetProperty('search.engine', $data['search_engine']);
            }
            return true;
        }

        $this->setTemplateView('cms/widget/search/properties', array(
            'form' => $form->getView(),
        ));

        return true;
	}
}
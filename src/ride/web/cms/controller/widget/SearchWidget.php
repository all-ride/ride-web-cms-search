<?php

namespace ride\web\cms\controller\widget;

use ride\library\cms\node\NodeModel;
use ride\web\cms\controller\widget\AbstractWidget;

class SearchWidget extends AbstractWidget {

/*
 * Machine name of this widget
 * @var string
 */
	const NAME = 'search.form';

	/**
	 * Path to the icon of this widget
	 * @var string
	 */
	const ICON = 'img/cms/widget/text.png';

	public function indexAction() {
        $this->setTemplateView('search.form', array(
            'action' => strtolower($this->properties->getWidgetProperty('result.node')),
            'label' => $this->properties->getWidgetProperty('input.label'),
            'placeholder' => $this->properties->getWidgetProperty('input.placeholder'),
            'submit' => $this->properties->getWidgetProperty('submit.text'),
        ));
	}

	public function getPropertiesPreview() {
		$translator = $this->getTranslator();

		return $translator->translate('preview.search.form');
	}

	/**
	 * Gets the callback for the properties action
	 * @return null|callback Null if the widget does not implement a properties
	 * action, a callback for the action otherwise
	 */
	public function getPropertiesCallback() {
		return array($this, 'propertiesAction');
	}

	/*
	 * Widget properties form
	 */
	public function propertiesAction(NodeModel $nodeModel) {
		$translator = $this->getTranslator();

		$data = array(
			'result_node' => $this->properties->getWidgetProperty('result.node'),
            'input_label' => $this->properties->getWidgetProperty('input.label'),
            'input_placeholder' => $this->properties->getWidgetProperty('input.placeholder'),
            'submit_text' => $this->properties->getWidgetProperty('submit.text'),
		);

		$form = $this->createFormBuilder($data);

		$options = array();
        $nodes = $nodeModel->getNodesForWidget('search.result');

		foreach ($nodes as $node) {
			$route = $node->getRoute($this->locale);
			$name = $node->getName($this->locale) . ' (' . $route . ')';
			$options[$node->getName($this->locale)] = $name;
		}

		$form->addRow('result_node', 'select', array(
            'label' => $translator->translate('label.search_result_node'),
            'options' => $options,
        ));

        $form->addRow('input_label', 'string', array(
            'label' => $translator->translate('label.search_form_input_label'),
        ));

        $form->addRow('input_placeholder', 'string', array(
            'label' => $translator->translate('label.search_form_input_placeholder'),
        ));

        $form->addRow('submit_text', 'string', array(
            'label' => $translator->translate('label.search_form_submit_text'),
        ));

		$form->setRequest($this->request);

		$form = $form->build();

		if ($form->isSubmitted()) {
			try {
				$data =  $form->getData();

				if (!empty($data['result_node'])) {
					$this->properties->setWidgetProperty('result.node', $data['result_node']);
				}
                if (!empty($data['input_label'])) {
                    $this->properties->setWidgetProperty('input.label', $data['input_label']);
                }
                if (!empty($data['input_placeholder'])) {
                    $this->properties->setWidgetProperty('input.placeholder', $data['input_placeholder']);
                }
                if (!empty($data['submit_text'])) {
                    $this->properties->setWidgetProperty('submit.text', $data['submit_text']);
                }
				return TRUE;
			}
			catch (Exception $e) {
				var_dump($e->getMessage());
			}
		}

        $this->setTemplateView('cms/widget/search/properties', array(
            'form' => $form->getView(),
        ));

		return true;
	}
}
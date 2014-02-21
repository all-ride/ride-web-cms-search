<?php

namespace ride\web\cms\controller\widget;

use pallo\library\cms\node\NodeModel;
use pallo\web\cms\controller\widget\AbstractWidget;

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

		$form->setRequest($this->request);

		$form = $form->build();

		if ($form->isSubmitted()) {
			try {
				$data =  $form->getData();

				if (!empty($data['result_node'])) {
					$this->properties->setWidgetProperty('result.node', $data['result_node']);
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
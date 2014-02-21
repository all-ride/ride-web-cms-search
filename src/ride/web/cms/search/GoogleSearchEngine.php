<?php

namespace ride\web\cms\search;

use ride\web\cms\search\SearchEngine;
use pallo\library\template\TemplateFacade;
use pallo\web\mvc\view\TemplateView;

class GoogleSearchEngine implements SearchEngine {

	protected $clientId;

    public function __construct($clientId) {
        $this->setClientId($clientId);
    }

	public function getView(TemplateFacade $templateFacade, $query, $numItems, $types = null)	{
		$template = $templateFacade->createTemplate("search.results");
        $view = new TemplateView($template);
        $view->setTemplateFacade($templateFacade);

        return $view;
	}

	public function getClientId() {
		return $this->clientId;
	}

	public function setClientId($clientId) {
		$this->clientId = $clientId;
    }
}
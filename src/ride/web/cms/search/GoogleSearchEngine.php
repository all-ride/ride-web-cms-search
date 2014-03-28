<?php

namespace ride\web\cms\search;

use ride\library\template\TemplateFacade;
use ride\web\mvc\view\TemplateView;

class GoogleSearchEngine implements SearchEngine {

	protected $clientId;

    public function __construct($clientId) {
        $this->setClientId($clientId);
    }

	public function getView(TemplateFacade $templateFacade, $query, $numItems, $types = null)	{
		$template = $templateFacade->createTemplate("search.results.google");
        $template->set('client_id', $this->getClientId());
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
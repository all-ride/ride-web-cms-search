<?php

namespace ride\web\cms\search;

use \ride\library\template\TemplateFacade;

interface SearchEngine {

	public function getView(TemplateFacade $templateface, $query, $numItems, $types = null);

}
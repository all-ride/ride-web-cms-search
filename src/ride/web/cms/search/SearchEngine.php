<?php

namespace ride\web\cms\search;

use \pallo\library\template\TemplateFacade;

interface SearchEngine {

	public function getView(TemplateFacade $templatefacade, $query, $numItems, $types = null);

}
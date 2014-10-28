<?php

namespace ride\web\cms\search;

use ride\library\cms\content\mapper\SearchableContentMapper;
use ride\library\cms\content\ContentFacade;
use ride\library\form\FormBuilder;
use ride\library\http\Request;
use ride\library\http\Response;
use ride\library\template\TemplateFacade;
use ride\web\mvc\view\TemplateView;

/**
 * Content search engine
 */
class ContentSearchEngine extends AbstractSearchEngine {

    /**
     * Default number of entries per type on the search overview page
     * @var integer
     */
    const DEFAULT_ENTRIES_OVERVIEW = 3;

    /**
     * Default number of entries on the type detail page
     * @var integer
     */
    const DEFAULT_ENTRIES_TYPE = 20;

    /**
     * Name of the mappers property
     * @var string
     */
    const PROPERTY_MAPPERS = 'mappers';

    /**
     * Name of the entries overview property
     * @var string
     */
    const PROPERTY_ENTRIES_OVERVIEW = 'entries.overview';

    /**
     * Name of the entries type property
     * @var string
     */
    const PROPERTY_ENTRIES_TYPE = 'entries.page';

    /**
     * Instance of the content facade
     * @var \ride\library\cms\content\ContentFacade
     */
    protected $contentFacade;

    /**
     * Constructs a new search engine
     * @param \ride\library\cms\content\ContentFacade $contentFacade
     */
    public function __construct(ContentFacade $contentFacade) {
        $this->contentFacade = $contentFacade;
    }

    /**
     * Gets the view for the form widget
     * @param \ride\library\http\Request
     * @return \ride\library\mvc\view\View
     */
    public function getFormView(Request $request) {
        $template = $this->templateFacade->createTemplate('cms/widget/search/form.content');
        $template->set('action', $this->resultWidgetProperties->getNode()->getUrl($this->locale, $request->getBaseScript()));
        $template->set('query', $request->getQueryParameter('query'));

        return new TemplateView($template);
    }

    /**
     * Gets the view for the result widget
     * @param \ride\library\http\Request $request
     * @param \ride\library\http\Response $response
     * @return \ride\library\mvc\view\View
     */
    public function getResultView(Request $request, Response $response) {
        $result = null;

        // redirect post requests to get requests
        if ($request->isPost()) {
            $query = $request->getBodyParameter('query');

            $response->setRedirect($request->getUrl() . '?query=' . urlencode($query));

            return;
        }

        // get the request arguments
        $query = $request->getQueryParameter('query');
        $type = $request->getQueryParameter('type');
        $page = $request->getQueryParameter('page', 1);
        if ($type) {
            $entriesPerPage = $this->resultWidgetProperties->getWidgetProperty(self::PROPERTY_ENTRIES_TYPE, self::DEFAULT_ENTRIES_TYPE);
        } else {
            $entriesPerPage = $this->resultWidgetProperties->getWidgetProperty(self::PROPERTY_ENTRIES_OVERVIEW, self::DEFAULT_ENTRIES_OVERVIEW);
        }

        // perform the search
        $site = $this->resultWidgetProperties->getNode()->getRootNode()->getId();
        $result = array(
            'total' => 0,
            'types' => array(),
        );

        if ($query) {
            $mappers = $this->getSearchContentMappers();
            foreach ($mappers as $contentType => $contentMapper) {
                if ($type && $contentType != $type) {
                    continue;
                }

                $contentResult = $contentMapper->searchContent($site, $this->locale, $query, explode(' ', $query), (integer) $page, (integer) $entriesPerPage);

                $result['total'] += $contentResult->getTotal();
                $result['types'][$contentType] = $contentResult;
            }
        }

        // create and return the view
        $template = $this->templateFacade->createTemplate("cms/widget/search/result.content");
        $template->set('result', $result);
        $template->set('query', $query);
        $template->set('urlMore', $request->getUrl());

        return new TemplateView($template);
    }

    /**
     * Prepares the properties form by adding row definitions
     * @param \ride\library\form\FormBuilder $builder
     * @param array $options
     * @return null
     */
    public function preparePropertiesForm(FormBuilder $builder, array $options) {
        $mappers = $this->getAvailableContentMappers();
        foreach ($mappers as $type => $type) {
            $mappers[$type] = $type;
        }

        $defaultMappers = $this->resultWidgetProperties->getWidgetProperty(self::PROPERTY_MAPPERS);
        if ($defaultMappers) {
            $defaultMappers = array_flip(explode(',', $defaultMappers));
        } else {
            $defaultMappers = array();
        }

        $entriesOverview = $this->resultWidgetProperties->getWidgetProperty(self::PROPERTY_ENTRIES_OVERVIEW, self::DEFAULT_ENTRIES_OVERVIEW);
        $entriesType = $this->resultWidgetProperties->getWidgetProperty(self::PROPERTY_ENTRIES_TYPE, self::DEFAULT_ENTRIES_TYPE);
        $entriesOptions = array_combine(range(1,50), range(1,50));

        $builder->addRow('mappers', 'select', array(
            'label' => $options['translator']->translate('label.content.mappers'),
            'description' => $options['translator']->translate('label.content.mappers.search.description'),
            'default' => $defaultMappers,
            'multiple' => true,
            'options' => $mappers,
        ));
        $builder->addRow('entriesOverview', 'select', array(
            'label' => $options['translator']->translate('label.number.entries.overview'),
            'description' => $options['translator']->translate('label.number.entries.overview.description'),
            'default' => $entriesOverview,
            'options' => $entriesOptions,
        ));
        $builder->addRow('entriesType', 'select', array(
            'label' => $options['translator']->translate('label.number.entries.type'),
            'description' => $options['translator']->translate('label.number.entries.type.description'),
            'default' => $entriesType,
            'options' => $entriesOptions,
        ));
    }

    /**
     * Processes the submitted values of the properties form
     * @param array $data Submitted values of the form
     * @return null
     */
    public function processPropertiesForm(array $data) {
        $this->resultWidgetProperties->setWidgetProperty(self::PROPERTY_MAPPERS, implode(',', $data['mappers']));
        $this->resultWidgetProperties->setWidgetProperty(self::PROPERTY_ENTRIES_OVERVIEW, $data['entriesOverview']);
        $this->resultWidgetProperties->setWidgetProperty(self::PROPERTY_ENTRIES_TYPE, $data['entriesType']);
    }

    /**
     * Gets the search content mappers
     * @return array
     */
    protected function getSearchContentMappers() {
        $mappers = $this->getAvailableContentMappers();

        $searchMappers = $this->resultWidgetProperties->getWidgetProperty(self::PROPERTY_MAPPERS);
        if (!$searchMappers) {
            return $mappers;
        }

        $searchMappers = array_flip(explode(',', $searchMappers));
        foreach ($searchMappers as $type => $null) {
            $searchMappers[$type] = $mappers[$type];
        }

        return $searchMappers;
    }

    /**
     * Gets the available content mappers
     * @return array
     */
    protected function getAvailableContentMappers() {
        $mappers = $this->contentFacade->getContentMappers();
        foreach ($mappers as $index => $mapper) {
            if (!$mapper instanceof SearchableContentMapper) {
                unset($mappers[$index]);
            }
        }

        ksort($mappers);

        return $mappers;
    }

}
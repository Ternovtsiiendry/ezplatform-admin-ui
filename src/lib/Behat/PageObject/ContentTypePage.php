<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformAdminUi\Behat\PageObject;

use EzSystems\EzPlatformAdminUi\Behat\Helper\UtilityContext;
use EzSystems\EzPlatformAdminUi\Behat\PageElement\AdminList;
use EzSystems\EzPlatformAdminUi\Behat\PageElement\ElementFactory;

class ContentTypePage extends Page
{
    /** @var string Name by which Page is recognised */
    public const PAGE_NAME = 'Content Type';
    /** @var string Name of actual group */
    public $contentTypeName;

    /**
     * @var \EzSystems\EzPlatformAdminUi\Behat\PageElement\AdminList
     */
    public $globalPropertiesAdminList;
    /**
     * @var \EzSystems\EzPlatformAdminUi\Behat\PageElement\AdminList
     */
    public $contentAdminList;

    public function __construct(UtilityContext $context, string $contentTypeName)
    {
        parent::__construct($context);
        $this->groupName = $contentTypeName;
        $this->route = '/admin/contenttypegroup/';
        $this->globalPropertiesAdminList = ElementFactory::createElement(
            $this->context,
            AdminList::ELEMENT_NAME,
            'Global properties'
        );
        $this->contentAdminList = ElementFactory::createElement(
            $this->context,
            AdminList::ELEMENT_NAME,
            'Content'
        );
        $this->pageTitle = $contentTypeName;
    }

    public function verifyElements(): void
    {
        $this->globalPropertiesAdminList->verifyVisibility();
        $this->contentAdminList->verifyVisibility();
    }
}

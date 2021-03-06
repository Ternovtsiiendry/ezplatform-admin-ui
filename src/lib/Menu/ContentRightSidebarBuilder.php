<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformAdminUi\Menu;

use eZ\Publish\API\Repository\Exceptions as ApiExceptions;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzPlatformAdminUi\Menu\Event\ConfigureMenuEvent;
use EzSystems\EzPlatformAdminUi\Specification\ContentIsUser;
use InvalidArgumentException;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use eZ\Publish\API\Repository\UserService;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI Location View contextual sidebar menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
class ContentRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    /* Menu items */
    const ITEM__CREATE = 'content__sidebar_right__create';
    const ITEM__EDIT = 'content__sidebar_right__edit';
    const ITEM__SEND_TO_TRASH = 'content__sidebar_right__send_to_trash';
    const ITEM__COPY = 'content__sidebar_right__copy';
    const ITEM__MOVE = 'content__sidebar_right__move';
    const ITEM__DELETE = 'content__sidebar_right__delete';

    /** @var PermissionResolver */
    private $permissionResolver;

    /** @var ConfigResolverInterface */
    private $configResolver;

    /** @var UserService */
    private $userService;

    /**
     * @param MenuItemFactory $factory
     * @param EventDispatcherInterface $eventDispatcher
     * @param PermissionResolver $permissionResolver
     * @param ConfigResolverInterface $configResolver
     * @param UserService $userService
     */
    public function __construct(
        MenuItemFactory $factory,
        EventDispatcherInterface $eventDispatcher,
        PermissionResolver $permissionResolver,
        ConfigResolverInterface $configResolver,
        UserService $userService
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->permissionResolver = $permissionResolver;
        $this->configResolver = $configResolver;
        $this->userService = $userService;
    }

    /**
     * @return string
     */
    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::CONTENT_SIDEBAR_RIGHT;
    }

    /**
     * @param array $options
     *
     * @return ItemInterface
     *
     * @throws ApiExceptions\InvalidArgumentException
     * @throws ApiExceptions\BadStateException
     * @throws InvalidArgumentException
     */
    public function createStructure(array $options): ItemInterface
    {
        /** @var Location $location */
        $location = $options['location'];
        /** @var ContentType $contentType */
        $contentType = $options['content_type'];
        /** @var Content $content */
        $content = $options['content'];
        /** @var ItemInterface|ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');
        $canCreate = $this->permissionResolver->hasAccess('content', 'create')
            && $contentType->isContainer;
        $canEdit = $this->permissionResolver->canUser(
            'content',
            'edit',
            $location->getContentInfo()
        );
        $canDelete = $this->permissionResolver->canUser(
            'content',
            'remove',
            $content
        );
        $canTrashLocation = $this->permissionResolver->canUser(
            'content',
            'manage_locations',
            $location->getContentInfo(),
            [$location]
        );

        $createAttributes = [
            'class' => 'ez-btn--extra-actions ez-btn--create',
            'data-actions' => 'create',
            'data-focus-element' => '.ez-instant-filter__input',
        ];
        $editAttributes = [
            'class' => 'ez-btn--extra-actions ez-btn--edit',
            'data-actions' => 'edit',
        ];
        $deleteAttributes = [
            'data-toggle' => 'modal',
            'data-target' => '#delete-user-modal',
        ];
        $sendToTrashAttributes = [
            'data-toggle' => 'modal',
            'data-target' => '#trash-location-modal',
        ];

        $menu->setChildren([
            self::ITEM__CREATE => $this->createMenuItem(
                self::ITEM__CREATE,
                [
                    'extras' => ['icon' => 'create'],
                    'attributes' => $canCreate
                        ? $createAttributes
                        : array_merge($createAttributes, ['disabled' => 'disabled']),
                ]
            ),
            self::ITEM__EDIT => $this->createMenuItem(
                self::ITEM__EDIT,
                [
                    'extras' => ['icon' => 'edit'],
                    'attributes' => $canEdit
                        ? $editAttributes
                        : array_merge($editAttributes, ['disabled' => 'disabled']),
                ]
            ),
            self::ITEM__MOVE => $this->createMenuItem(
                self::ITEM__MOVE,
                [
                    'extras' => ['icon' => 'move'],
                    'attributes' => [
                        'class' => 'btn--udw-move',
                        'data-root-location' => $this->configResolver->getParameter(
                            'universal_discovery_widget_module.default_location_id'
                        ),
                    ],
                ]
            ),
            self::ITEM__COPY => $this->createMenuItem(
                self::ITEM__COPY,
                [
                    'extras' => ['icon' => 'copy'],
                    'attributes' => [
                        'class' => 'btn--udw-copy',
                        'data-root-location' => $this->configResolver->getParameter(
                            'universal_discovery_widget_module.default_location_id'
                        ),
                    ],
                ]
            ),
        ]);

        if ((new ContentIsUser($this->userService))->isSatisfiedBy($content)) {
            $menu->addChild(
                $this->createMenuItem(
                    self::ITEM__DELETE,
                    [
                        'extras' => ['icon' => 'trash'],
                        'attributes' => $canDelete
                            ? $deleteAttributes
                            : array_merge($deleteAttributes, ['disabled' => 'disabled']),
                    ]
                )
            );
        } else {
            $menu->addChild(
                $this->createMenuItem(
                    self::ITEM__SEND_TO_TRASH,
                    [
                        'extras' => ['icon' => 'trash-send'],
                        'attributes' => $canTrashLocation ?
                            $sendToTrashAttributes
                            : array_merge($sendToTrashAttributes, ['disabled' => 'disabled']),
                    ]
                )
            );
        }

        if (1 === $location->depth) {
            $menu[self::ITEM__SEND_TO_TRASH]->setAttribute('disabled', 'disabled');
            $menu[self::ITEM__MOVE]->setAttribute('disabled', 'disabled');
        }

        return $menu;
    }

    /**
     * @return Message[]
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__CREATE, 'menu'))->setDesc('Create'),
            (new Message(self::ITEM__EDIT, 'menu'))->setDesc('Edit'),
            (new Message(self::ITEM__SEND_TO_TRASH, 'menu'))->setDesc('Send to Trash'),
            (new Message(self::ITEM__COPY, 'menu'))->setDesc('Copy'),
            (new Message(self::ITEM__MOVE, 'menu'))->setDesc('Move'),
            (new Message(self::ITEM__DELETE, 'menu'))->setDesc('Delete'),
        ];
    }
}

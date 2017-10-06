<?php
declare(strict_types=1);

namespace EzPlatformAdminUi\Form\Type\Role;

use EzPlatformAdminUi\Form\Data\Role\RoleUpdateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'identifier',
                TextType::class,
                ['label' => /** @Desc("Name") */ 'role_update.name']
            )
            ->add(
                'save',
                SubmitType::class,
                ['label' => /** @Desc("Update") */ 'role_update.save']
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RoleUpdateData::class,
            'translation_domain' => 'forms',
        ]);
    }
}

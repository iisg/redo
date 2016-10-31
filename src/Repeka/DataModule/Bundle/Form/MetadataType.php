<?php
namespace Repeka\DataModule\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MetadataType extends AbstractType {
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'csrf_protection' => false, // TODO remove in REPEKA-66
        ]);
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('label')
            ->add('description')
            ->add('placeholder')
            ->add('control');
    }
}

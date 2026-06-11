<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Authentication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthenticationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options = $options;

        $builder
            ->add('login')
            ->add('password')
            ->add('domain');
    }

    /**
     * Configure the options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Authentication::class)
            ->setDefault('csrf_protection', false);
    }
}

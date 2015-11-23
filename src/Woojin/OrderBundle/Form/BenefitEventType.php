<?php

namespace Woojin\OrderBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BenefitEventType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('createAt', null)
            ->add('startAt', null, array(
                'label' => '開始時間'
            ))
            ->add('endAt', null, array(
                'label' => '結束時間'
            ))
            ->add('des', null, array(
                'label' => '活動描述'
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Woojin\OrderBundle\Entity\BenefitEvent'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'woojin_orderbundle_benefitevent';
    }
}

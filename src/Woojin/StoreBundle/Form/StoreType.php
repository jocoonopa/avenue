<?php

namespace Woojin\StoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StoreType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sn', null, array(
                'label' => '店碼',
                'read_only' => true
            ))
            ->add('name', null, array(
                'label' => '店名',
                'read_only' => true
            ))
            ->add('address', null, array(
                'label' => '地址'
            ))
            ->add('phone', null, array(
                'label' => '電話'
            ))
            ->add('openRegion', null, array(
                'label' => '營業時間'
            ))
            ->add('mail', null, array(
                'label' => '信箱'
            ))
            // ->add('isShow', null, array(
            //     'label' => '官網是否顯示'
            // ))
            //->add('exchange_rate')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Woojin\StoreBundle\Entity\Store'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'woojin_storebundle_store';
    }
}

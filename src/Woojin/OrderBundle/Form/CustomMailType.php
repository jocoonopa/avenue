<?php

namespace Woojin\OrderBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CustomMailType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array(
                'invalid_message' => '請輸入正確格式的信箱',
                'required' => true,
            ))
            ->add('captcha', 'captcha', array(
                'width' => 200,
                'height' => 50,
                'length' => 4,
                'reload' => true,
                'as_url' => true,          
                'invalid_message' => '驗證碼錯誤'
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'woojin_orderbundle_custom';
    }
}

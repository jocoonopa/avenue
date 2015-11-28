<?php

namespace Woojin\OrderBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CustomType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('sex', 'choice', array(
                'choices' => array(
                    '保密' => '保密',
                    '先生' => '先生', 
                    '女士' => '女士'
                ),
            ))
            ->add('birthday', 'birthday', array(
                'data' => new \DateTime('1984-07-01'),
                'view_timezone' => 'Asia/Taipei',
                'model_timezone' => 'Asia/Taipei'
            ))
            ->add('mobil')
            ->add('email', 'email')
            ->add('bkemail', 'email')
            ->add('address')
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => '兩次密碼輸入必需相同',
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
            'data_class' => 'Woojin\OrderBundle\Entity\Custom'
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

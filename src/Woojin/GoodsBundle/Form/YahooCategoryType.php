<?php

namespace Woojin\GoodsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class YahooCategoryType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('yahooId')
            ->add('brand')
            ->add('pattern')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Woojin\GoodsBundle\Entity\YahooCategory'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'woojin_goodsbundle_yahoocategory';
    }
}

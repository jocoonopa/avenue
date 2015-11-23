<?php

namespace Woojin\GoodsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BehalfType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paid')
            ->add('required')
            ->add('deposit')
            ->add('memo')
            ->add('createAt')
            ->add('updateAt')
            // ->add('got')
            // ->add('want')
            // ->add('status')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Woojin\GoodsBundle\Entity\Behalf'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'woojin_goodsbundle_behalf';
    }
}

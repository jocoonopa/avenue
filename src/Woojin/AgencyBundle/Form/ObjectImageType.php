<?php

namespace Woojin\AgencyBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class ObjectImageType extends AbstractType
{
    /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      //->add('filename')
      ->add('object')
      ->add('file', 'file', array(
        'constraints' => array(
          new Constraints\Image(array(
          'maxSize' => 6000000
          ))
        )
      ))
    ;
  }
  
  /**
   * @param OptionsResolverInterface $resolver
   */
  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Woojin\AgencyBundle\Entity\ObjectImage'
    ));
  }

  /**
   * @return string
   */
  public function getName()
  {
    return 'woojin_agencybundle_objectimage';
  }
}

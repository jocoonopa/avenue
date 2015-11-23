<?php

namespace Woojin\AgencyBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name')
			->add('sn')
			->add('brand')
			->add('custom_price')
			->add('contractor_price')
			->add('memo')
			->add('object_status')
			->add('object_location')
			->add('contractor')
			->add('agency_item')
		;
	}
	
	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Woojin\AgencyBundle\Entity\Object'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'woojin_agencybundle_object';
	}
}

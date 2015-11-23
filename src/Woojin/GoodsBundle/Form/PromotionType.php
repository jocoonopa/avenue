<?php

namespace Woojin\GoodsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PromotionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => '活動名稱'))
            ->add('description', null, array('label' => '描述'))
            //->add('createAt')
            //->add('updateAt')
            //->add('startAt')
            ->add('startAt', 'datetime', array('label' => '起始時間'))
            //->add('stopAt')
            ->add('stopAt', 'datetime', array('label' => '截止時間'))
            // ->add('isTimeliness', null, array('required' => false))
            ->add('isDisplay', null, array(
                'label' => '是否顯示',
                'required' => false
            ))
            //->add('type')
            ->add('thread', null, array(
                'label' => '滿',
                'attr' => array(
                    'data' => 0,
                    'placeholder' => '滿額贈門檻金額'
                )
            ))
            ->add('gift', null, array(
                'label' => '贈送金額',
                'attr' => array(
                    'data' => 0,
                    'placeholder' => '贈送金額'
                )
            ))
            ->add('isStack', null, array(
                'label' => '累計贈送(勾選表示累計贈送, 例如滿10000送1000, 則30000元商品會贈送3000元)',
                'required' => false
            ))
            ->add('discount', null, array(
                'label' => '折扣(不會和滿額贈同時作用，若要使用折扣設定，贈送金額務必設定為0)',
                'required' => true,
                'attr' => array(
                    'data' => 1,
                    'placeholder' => '請輸入折扣 @example: 0.9'
                )
            ))
            ->add('sort', null, array(
                'label' => '顯示順序',
                'required' => true,
                'attr' => array(
                    'data' => 10,
                    'placeholder' => '數字越大越前面'
                )
            ))
            ->add('file')
            
            //->add('user')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Woojin\GoodsBundle\Entity\Promotion'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'woojin_goodsbundle_promotion';
    }
}

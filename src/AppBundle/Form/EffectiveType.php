<?php
/**
 * Created by PhpStorm.
 * User: CoderCAE
 * Date: 08.03.2017
 * Time: 18:31
 */
namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EffectiveType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Boat', EntityType::class, array(
                'class' => 'AppBundle:Boat',
                'choice_label' => 'fullname',
                'attr'=> array('class'=>'form-control')))
            ->add('effective_date_from', DateTimeType::class, array(
                'label' => 'Start:',
                'widget' => 'single_text',
                'html5' => false,
                'attr'=> array('class'=>'form-control')));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\driving_effective'
        ));
    }
}
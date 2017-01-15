<?php

namespace SMARTASK\HomeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use SMARTASK\UserBundle\Repository\UserRepository;

class TaskType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$task=$builder->getData();
    	$user=$task->getManager();
    	$userid=$user->getId();
    	
    	
        $builder
        ->add('titre')
        ->add('localisation'      , TextType::class, array('required' => false))
        ->add('date')
        ->add('time')
        ->add('description'      , TextType::class, array('required' => false))
        ->add('resp'            , EntityType::class,array(
        		'class'    	    =>'SMARTASKUserBundle:User',
        		'choice_label'	=>'username',
        		'label'         =>'Responsable',
        		'multiple'	    =>false,
        		'query_builder' => function(UserRepository $repo) use($userid ) {
        		return $repo->getRegisteredContactBuilder($userid);
        		}
        		))
        ->add('group'            , EntityType::class,array(
        		'class'    	    =>'SMARTASKHomeBundle:Groupe',
        		'choices'       => $user->getGroupes(),
        		'choice_label'	=>'nom',
        		'label'         =>'Groupe',
        		'multiple'	    =>false,
        ))
        	
        ->add('enregistrer'      , SubmitType::class)
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SMARTASK\HomeBundle\Entity\Task'
        ));

    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'smartask_homebundle_task';
    }


}

<?php

namespace SMARTASK\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use SMARTASK\HomeBundle\Entity\Groupe;
use SMARTASK\HomeBundle\Form\TaskType;
use SMARTASK\HomeBundle\Form\ContactType;
use SMARTASK\HomeBundle\Form\GroupeType;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username')
        		->add('password')
        		->add('plainPassword')
        		->add('email', EmailType::class)
        		->add('groupes', CollectionType::class, [
        				'entry_type' => GroupeType::class,
        				'allow_add' => true,
        				'error_bubbling' => false,
        		])/*
        		->add('tasks', CollectionType::class, [
        				'entry_type' => TaskType::class,
        				'allow_add' => true,
        				'error_bubbling' => false,
        		])*/
        		->add('contacts', CollectionType::class, [
        				'entry_type' => ContactType::class,
        				'allow_add' => true,
        				'error_bubbling' => false,
        		]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SMARTASK\UserBundle\Entity\User',
        	'csrf_protection' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'smartask_userbundle_user';
    }


}

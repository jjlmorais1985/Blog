<?php

namespace App\Form;


use App\Entity\Categories;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class ArticleType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true
            ])
            ->add('image', FileType::class, [
                'label' => 'Télechargez une image',
                'required' => false,
                'mapped' => false,
                "constraints" => [
                    new File([
                        'maxSize' => '2048k',

                        'mimeTypesMessage' => 'Télechargez une image s\'il vous plaît',
                    ])
                ]

            ])
            ->add('content', TextareaType::class, [
                'required' => true
            ])
            ->add('categories', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'category',
                'expanded' => false,
                'multiple' => true,
                'label' => "Catégories"
                ])
            ->add('publish', CheckboxType::class, [
                'required' => false,
                'label' => 'Publier'
            ])
            ->add('Effacer', ResetType::class)
            ->add('Enregistrer', SubmitType::class);
    }
}
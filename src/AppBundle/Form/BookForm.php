<?php

namespace AppBundle\Form;

use AppBundle\Entity\Author;
use AppBundle\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Repository\AuthorRepository;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class BookForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Название'])
            ->add('year', TextType::class, ['label' => 'Год'])
            ->add('isbn', TextType::class, ['label' => 'ISBN'])
            ->add('count_pages', TextType::class, ['label' => 'Кол.во страниц'])
            ->add('authors', EntityType::class,
                [
                 'class' => Author::class,
                 'query_builder' => function (AuthorRepository $er) {
                     return $er->getAuthors();
                 },
                 'choice_label' => 'surname',
                 'multiple' => true,
                 'required' => false
             ])
            ->add('foto',HiddenType::class,
                [
                    'label' => 'Фото',
                    'required' => false,
                    'data_class' => null,
                ])
            ->add('attachment', FileType::class,
                [
                    'label' => 'attachment',
                    'required' => false,
                    'data_class' => null
                ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Book::class
        ]);
    }
}
<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Category name is required']),
                ],
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-gray-700 focus:border-gray-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1'
                ]
            ])
            ->add('image', TextType::class, [
                'label' => 'Image URL',
                'constraints' => [
                    new NotBlank(['message' => 'Image URL is required']),
                    new Url(['message' => 'Please enter a valid URL']),
                ],
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-gray-700 focus:border-gray-500',
                    'placeholder' => 'https://example.com/image.jpg'
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'attr' => ['class' => 'form-row'],
        ]);
    }
}
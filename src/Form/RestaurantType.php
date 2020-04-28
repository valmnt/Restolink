<?php

namespace App\Form;

use App\Entity\Restaurant;
use App\Utils\UploadService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\File\File;

class RestaurantType extends AbstractType
{
    private $uploadService;
    private $params;
    public function __construct(UploadService $uploadService, ParameterBagInterface $params)
    {
        $this->uploadService = $uploadService;
        $this->params = $params;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle')
            ->add('image', FileType::class, [
                'label' => 'Choisissez votre Image'
            ])
            ->add('description')
            ->add('Submit', SubmitType::class);

            $builder->get('image')
            ->addModelTransformer(new CallbackTransformer(
            function ($filepath) {
                if(!$filepath)
                {
                    return null;
                }
                $file = new File($this->params->get('kernel.project_dir').'/public'.$filepath);
                return $file;

            },
            function ($file) {
                return $this->uploadService->uploadImage($file, $this->params->get('upload_directory'));
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Restaurant::class,
        ]);
    }
}

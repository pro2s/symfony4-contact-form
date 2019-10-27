<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ApplicationStatusAttachmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildAttachment($builder);
        $this->buildAttachmentType($builder);
    }

    protected function buildAttachment(FormBuilderInterface $builder): void
    {
        $builder
            ->add('attachment', FileType::class, [
                'label' => ' ',
                'attr' => [
                    'class' => 'attachment-input-storage',
                ],
                'required' => false,
                'error_bubbling' => false,
            ]);
    }

    protected function buildAttachmentType(FormBuilderInterface $builder): void
    {
        $builder->add('attachmentType', ChoiceType::class, [
            //'constraints' => [new Assert\NotBlank()],
            'label' => ' ',
            'empty_data' => '',
            'error_bubbling' => false,
            'choices'  => [
                'Maybe' => null,
                'Yes' => true,
                'No' => false,
            ],
        ]);
    }
}

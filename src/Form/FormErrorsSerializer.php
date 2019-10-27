<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FormErrorsSerializer
{
    private const TOP_LEVEL_PROPERTY_PATH = 'unknown';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Serialize Form errors
     *
     * {
     *     "errors": [
     *         {
     *             "message": "This value is required.",
     *             "property_path": "property_path"
     *         },
     *      ]
     *}
     */
    public function serialize(FormInterface $form, string $topLevelPropertyPath = self::TOP_LEVEL_PROPERTY_PATH): array
    {
        $formErrors = $this->convertFormToArray($form);	
        return $formErrors + collect($form->getErrors(true))->map(function (FormError $error) use ($topLevelPropertyPath) {
            return [
                'message' => $this->getErrorMessage($error),
                'property_path' => $this->getPropertyPath($error, $topLevelPropertyPath),
                'validation_path' => $this->getReactFormPropertyPath($error, $topLevelPropertyPath),
            ];
        })->toArray();
    }

    private function getErrorMessage(FormError $error): string
    {
        if (null !== $error->getMessagePluralization()) {
            return $this->translator->transChoice(
                $error->getMessageTemplate(),
                $error->getMessagePluralization(),
                $error->getMessageParameters(),
                'validators'
            );
        }

        return $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
    }

    private function getPropertyPath(FormError $error, string $topLevelPropertyPath): string
    {
        $cause = $error->getCause();

        if (!$cause) {
            return $topLevelPropertyPath;
        }

        return str_replace('data.', '', $cause->getPropertyPath());
    }
    
    private function getReactFormPropertyPath(FormError $error, string $topLevelPropertyPath): string
    {
        $cause = $error->getCause();

        if (!$cause) {
            return $topLevelPropertyPath;
        }

        return str_replace('data.', '', $cause->getCode());
    }
    
    /**
     * This code has been taken from JMSSerializer.
     */
    private function convertFormToArray(FormInterface $data)
    {
        $form = $errors = [];
        foreach ($data->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        if ($errors) {
            $form['errors'] = $errors;
        }

        $children = [];
        foreach ($data->all() as $child) {
            if ($child instanceof FormInterface) {
                $children[$child->getName()] = $this->convertFormToArray($child);
            }
        }

        if ($children) {
            $form['children'] = $children;
        }

        return $form;
    }
}

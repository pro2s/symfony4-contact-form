<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactRequest
{
    private $fullName;
    private $fromEmail;
    private $message;
    
    /**
     * @Assert\Regex("/^-?(?:\d+|\d*\.\d+)$/")
     */
    private $number;

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;
        
        return $this;
    }
    
    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(?string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;
        
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message):self
    {
        $this->message = $message;
        
        return $this;       
    }
    
    public function getNumber(): ?float
    {
        return $this->number;
    }

    public function setNumber(?float $number)
    {
        $this->number = $number;
    }
    
    /**
     * Validation for each file inside validation callback
     *
     * @Assert\NotBlank()
     *
     * @var array
     */
     
    protected $attachments = [];

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function setAttachments(array $attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * To run UserPassword validation sequentially as GroupSequences were not helpful in this case
     *
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context): void
    {
        $validator = $context->getValidator();

        $validator
            ->inContext($context)
			->atPath('message')
			->validate($this->message, new NotBlank());
			
        $choice = [
            new Assert\NotBlank(),
            new Assert\Choice([
                'choices' => [null, true, false],
            ])
        ];
        
        foreach ($this->attachments as $id => $attachment) {
            $errorList = $validator
                ->validate($attachment['attachmentType'], $choice);
            
            if (\count($errorList) > 0) {
                $context->buildViolation($errorList[0]->getMessage())
                    ->atPath('attachments[' . $id . '][attachmentType]')
                    ->addViolation();
                $context->buildViolation($errorList[0]->getMessage())
                    ->atPath('attachmentType' . $id)
                    ->addViolation();                    
                return;
            }
        }
            
        $errorList = $validator->validate($this->number, new NotBlank());
        /* @var ConstraintViolation $violation */
        if (\count($errorList) > 0) {
            $context->buildViolation($errorList[0]->getMessage())
                ->atPath('number')
                ->addViolation();

            return;
        }
    }
}

<?php

/*
 * This file is part of the fabschurt/kontact package.
 *
 * (c) 2016 Fabien Schurter <fabien@fabschurt.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabSchurt\Kontact\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class KontactType extends AbstractType
{
    /**
     * @var string[]
     */
    private $requestParams;

    /**
     * @var int
     */
    private $maxMessageLength;

    /**
     * @param string[] $requestParams
     * @param int      $maxMessageLength
     */
    public function __construct(array $requestParams, int $maxMessageLength)
    {
        $this->requestParams    = $requestParams;
        $this->maxMessageLength = $maxMessageLength;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->requestParams as $fieldName) {
            switch ($fieldName) {
                case 'name':
                    $builder
                        ->add($fieldName, Type\TextType::class, [
                            'contstraints' => [
                                new Constraints\NotBlank(),
                                new Constraints\Length(['max' => 64]),
                            ],
                        ])
                    ;
                    break;
                case 'email_address':
                    $builder
                        ->add($fieldName, Type\EmailType::class, [
                            'contstraints' => [
                                new Constraints\NotBlank(),
                                new Constraints\Length(['max' => 128]),
                                new Constraints\Email(),
                            ],
                        ])
                    ;
                    break;
            }
        }
        $builder
            ->add('message', Type\TextareaType::class, [
                'contstraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Length(['max' => $this->maxMessageLength]),
                ],
            ])
        ;
    }
}

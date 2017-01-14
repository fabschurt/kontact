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

use FabSchurt\Silex\Provider\Captcha\Form\Type\CaptchaType;
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
     * @var bool
     */
    private $enableCaptcha;

    /**
     * @param string[] $requestParams
     * @param int      $maxMessageLength
     * @param bool     $enableCaptcha
     */
    public function __construct(array $requestParams, int $maxMessageLength, bool $enableCaptcha)
    {
        $this->requestParams    = $requestParams;
        $this->maxMessageLength = $maxMessageLength;
        $this->enableCaptcha    = $enableCaptcha;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach (array_keys($this->requestParams) as $fieldName) {
            switch ($fieldName) {
                case 'name':
                    $builder
                        ->add($fieldName, Type\TextType::class, [
                            'constraints' => [
                                new Constraints\NotBlank(),
                                new Constraints\Length(['max' => 64]),
                            ],
                        ])
                    ;
                    break;
                case 'address':
                    $builder
                        ->add($fieldName, Type\EmailType::class, [
                            'constraints' => [
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
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Length(['max' => $this->maxMessageLength]),
                ],
            ])
        ;
        if ($this->enableCaptcha) {
            $builder->add('captcha', CaptchaType::class);
        }
    }
}

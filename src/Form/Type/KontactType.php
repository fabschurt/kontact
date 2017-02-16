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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints;

/**
 * @author Fabien Schurter <fabien@fabschurt.com>
 */
final class KontactType extends AbstractType
{
    /**
     * @var string[]
     */
    const OPTIONAL_FIELDS = [
        'name',
        'address',
    ];

    /**
     * @var int
     */
    private $maxMessageLength;

    /**
     * @var bool
     */
    private $enableCaptcha;

    /**
     * @param int  $maxMessageLength
     * @param bool $enableCaptcha
     */
    public function __construct(int $maxMessageLength, bool $enableCaptcha)
    {
        $this->maxMessageLength = $maxMessageLength;
        $this->enableCaptcha    = $enableCaptcha;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Length(['max' => 64]),
                ],
            ])
            ->add('address', Type\EmailType::class, [
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Length(['max' => 128]),
                    new Constraints\Email(),
                ],
            ])
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

        // Handle optional fields
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            foreach (self::OPTIONAL_FIELDS as $optionalField) {
                if (!array_key_exists($optionalField, $event->getData())) {
                    $event->getForm()->remove($optionalField);
                }
            }
        });
    }
}

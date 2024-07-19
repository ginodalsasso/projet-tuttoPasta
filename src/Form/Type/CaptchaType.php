<?php

namespace App\Form\Type;

use App\Domain\AntiSpam\ChallengeInterface;
use App\Domain\AntiSpam\Puzzle\PuzzleChallenge;
use App\Validator\Challenge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CaptchaType extends AbstractType
{

    public function __construct(
        private readonly ChallengeInterface    $challenge,
        private readonly UrlGeneratorInterface $urlGenerator
    )
    {

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new NotBlank(),
                new Challenge()
            ],
            'route' => 'captcha'
        ]);
        parent::configureOptions($resolver);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('challenge', HiddenType::class, [
            'attr' => [
                'class' => 'captcha-challenge'
            ],
        ])
            ->add('answer', HiddenType::class, [
                    'attr' => [
                        'class' => 'captcha-anwser'
                    ]]
            );
        parent::buildForm($builder, $options);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {   
        // Génération d'une clé de challenge
        $key = $this->challenge->generateKey();
        $view->vars['attr'] = [
            'width' => PuzzleChallenge::WIDTH,
            'height' => PuzzleChallenge::HEIGHT,
            'piece-width' => PuzzleChallenge::PIECE_WIDTH,
            'piece-height' => PuzzleChallenge::PIECE_HEIGHT,

            // Génération de l'URL pour le captcha avec la clé de challenge
            'src' => $this->urlGenerator->generate($options['route'], ['challenge' => $key])
        ];
        $view->vars['challenge'] = $key;
        parent::buildView($view, $form, $options);
    }

}

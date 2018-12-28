<?php

namespace AppBundle\Renderer;

use AppBundle\Model\PhpQuiz;
use Twig_Environment;

class PhpQuizRenderer
{
    const TWIG_BASE_PATH = 'phpquiz';
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * PhpQuizRenderer constructor.
     *
     * @param Twig_Environment $twig
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param PhpQuiz $phpQuiz
     *
     * @return string
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function render(PhpQuiz $phpQuiz)
    {
        return $this->twig->render(static::TWIG_BASE_PATH . '/default.twig', ['phpquiz' => $phpQuiz]);
    }
}

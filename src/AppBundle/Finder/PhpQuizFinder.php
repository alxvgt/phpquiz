<?php

namespace AppBundle\Finder;

use AppBundle\Model\PhpQuiz;

class PhpQuizFinder
{
    /**
     * @var array
     */
    private $phpQuizzes;

    /**
     * PhpQuizFinder constructor.
     *
     * @param array $phpQuizzes
     */
    public function __construct(array $phpQuizzes)
    {
        $this->phpQuizzes = $phpQuizzes;
    }

    /**
     * @return array
     */
    public function getPhpQuizzes(): array
    {
        return $this->phpQuizzes;
    }

    /**
     * @param $reference
     *
     * @return PhpQuiz|bool
     */
    public function findOneByReference($reference)
    {
        /** @var PhpQuiz $phpQuizz */
        foreach ($this->getPhpQuizzes() as $phpQuizz) {
            if ($phpQuizz->getReference() == $reference) {
                return $phpQuizz;
            }
        }

        return false;
    }
}

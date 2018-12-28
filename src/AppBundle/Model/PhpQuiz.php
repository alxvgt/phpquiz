<?php

namespace AppBundle\Model;

class PhpQuiz
{
    /**
     * @var string
     */
    private $reference;
    /**
     * @var string
     */
    private $question;
    /**
     * @var array
     */
    private $choices;
    /**
     * @var array
     */
    private $goodChoices;
    /**
     * @var string
     */
    private $help;

    /**
     * PhpQuiz constructor.
     * @param string $reference
     * @param string $question
     * @param array $choices
     * @param array $goodChoices
     * @param string $help
     */
    public function __construct(string $reference, string $question, array $choices, array $goodChoices, string $help)
    {
        $this->reference = $reference;
        $this->question = $question;
        $this->choices = $choices;
        $this->goodChoices = $goodChoices;
        $this->help = $help;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getQuestion(): string
    {
        return $this->question;
    }

    /**
     * @return array
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    /**
     * @return array
     */
    public function getGoodChoices(): array
    {
        return $this->goodChoices;
    }

    /**
     * @return string
     */
    public function getHelp(): string
    {
        return $this->help;
    }

    /**
     * @param string $search
     * @param string $replace
     */
    public function replaceInQuestion(string $search, string $replace)
    {
        $this->question = str_replace($search, $replace, $this->question);
    }
}

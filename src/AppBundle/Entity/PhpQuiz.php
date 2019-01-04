<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Base\IdentifiableEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="phpquiz")
 */
class PhpQuiz
{
    use IdentifiableEntityTrait;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $reference;
    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $question;
    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $choices;
    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $goodChoices;
    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $help;

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
}

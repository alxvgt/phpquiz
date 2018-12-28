<?php

namespace AppBundle\Google\Converter;

use AppBundle\Model\PhpQuiz;

class GoogleSheetConverter
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * GoogleSheetConverter constructor.
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @param array $values
     * @return array
     * @throws \Exception
     */
    public function getPhpQuizzes(array $values)
    {
        $phpquizzes = [];
        foreach ($values as $row) {
            $reference = $this->searchRowValueFromMapping($this->mapping['reference'], $row);
            $question = $this->searchRowValueFromMapping($this->mapping['question'], $row);
            $choices = $this->searchRowValueFromMapping($this->mapping['choices'], $row);
            $goodChoices = $this->searchRowValueFromMapping($this->mapping['goodChoices'], $row);
            $help = $this->searchRowValueFromMapping($this->mapping['help'], $row);

            $goodChoices = $this->findGoodChoicesFromChoices($choices, $goodChoices);

            if (!$reference || !$question || !$choices || !$goodChoices || !$help) {
                continue;
            }

            $phpquizzes [] = new PhpQuiz(reset($reference), reset($question), $choices, $goodChoices, reset($help));
        }

        return $phpquizzes;
    }

    /**
     * @param $regexNeedle
     * @param $row
     * @return array|mixed
     */
    private function searchRowValueFromMapping($regexNeedle, $row)
    {
        $matches = preg_grep($regexNeedle, array_keys($row));
        $result = array_intersect_key($row, array_flip($matches));

        return array_values(array_unique(array_filter($result)));
    }

    /**
     * @param array $choices
     * @param array $goodChoices
     * @return array
     * @throws \Exception
     */
    private function findGoodChoicesFromChoices(array $choices, array $goodChoices)
    {
        $goodChoicesRef = array_intersect_key(array_flip($choices), array_flip($goodChoices));

        if (!empty($goodChoices) && !empty($choices) && empty($goodChoicesRef)) {
            throw new \Exception('Unable to find good choices into choices, keys mismatch. Searching [' . implode(',', array_values($goodChoices)) . '] into [' . implode(',', array_values($choices)) . ']');
        }

        return array_values($goodChoicesRef);
    }
}

<?php
declare(strict_types=1);

namespace FroshPluginUploader\Components\SBP;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FaqReader
{
    public function parseFaq(string $path): array
    {
        $questions = [];
        $currentQuestion = null;

        foreach ($this->parse($path) as $line) {
            switch ($line[0]) {
                case '#':
                    $currentQuestion = $this->parseTitle($line);
                    break;
                default:
                    if (!$currentQuestion) {
                        throw new \InvalidArgumentException(sprintf('FAQ in path "%s" is invalid', $path));
                    }

                    if (strlen(trim($line)) === 0) {
                        break;
                    }

                    if (!isset($questions[$currentQuestion])) {
                        $questions[$currentQuestion] = '';
                    }

                    $questions[$currentQuestion] .= $line;
                    break;
            }
        }

        $formattedQuestions = [];

        foreach ($questions as $question => $answer) {
            $formattedQuestions[] = ['question' => $question, 'answer' => $answer];
        }

        return $formattedQuestions;
    }

    private function parse(string $path): \Generator
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException(null, 0, null, $path);
        }

        $file = fopen($path, 'rb');

        while ($line = fgets($file)) {
            yield $line;
        }
        fclose($file);
    }

    private function parseTitle($line): string
    {
        return trim(substr($line, 1));
    }
}

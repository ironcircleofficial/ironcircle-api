<?php

declare(strict_types=1);

namespace App\AI\Infrastructure\Client;

use App\AI\Application\Service\SummaryGeneratorInterface;
use App\AI\Domain\Exception\AISummaryGenerationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class HuggingFaceSummaryGenerator implements SummaryGeneratorInterface
{
    private const MAX_INPUT_LENGTH = 4000;
    private const MAX_SUMMARY_LENGTH = 150;
    private const MIN_SUMMARY_LENGTH = 30;

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiToken,
        private string $model
    ) {
    }

    public function generateSummary(string $text): string
    {
        $sanitizedText = $this->sanitizeInput($text);

        if (mb_strlen($sanitizedText) < 50) {
            throw AISummaryGenerationException::failed('Post content is too short to summarize');
        }

        try {
            $response = $this->httpClient->request('POST', sprintf(
                'https://router.huggingface.co/hf-inference/models/%s',
                $this->model
            ), [
                'headers' => [
                    'Authorization' => sprintf('Bearer %s', $this->apiToken),
                ],
                'json' => [
                    'inputs' => $sanitizedText,
                    'parameters' => [
                        'max_length' => self::MAX_SUMMARY_LENGTH,
                        'min_length' => self::MIN_SUMMARY_LENGTH,
                    ],
                ],
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 503) {
                throw AISummaryGenerationException::failed(
                    'AI model is currently loading. Please try again in a few seconds.'
                );
            }

            if ($statusCode !== 200) {
                throw AISummaryGenerationException::failed(
                    sprintf('HuggingFace API returned status %d', $statusCode)
                );
            }

            $data = $response->toArray();

            if (!isset($data[0]['summary_text'])) {
                throw AISummaryGenerationException::failed(
                    'Unexpected response format from HuggingFace API'
                );
            }

            return trim($data[0]['summary_text']);
        } catch (AISummaryGenerationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw AISummaryGenerationException::failed($e->getMessage());
        }
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    private function sanitizeInput(string $text): string
    {
        $text = strip_tags($text);

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        $text = trim($text);

        if (mb_strlen($text) > self::MAX_INPUT_LENGTH) {
            $text = mb_substr($text, 0, self::MAX_INPUT_LENGTH);
        }

        return $text;
    }
}

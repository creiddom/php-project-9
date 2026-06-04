<?php

declare(strict_types=1);

namespace App\Http;

use App\SeoExtractor;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class PageChecker
{
    public const CONNECTION_ERROR = 'Произошла ошибка при проверке, не удалось подключиться';

    public function __construct(
        private readonly SeoExtractor $seoExtractor,
        private readonly ?Client $client = null,
    ) {
    }

    public function check(string $url): CheckResult
    {
        $response = $this->request($url);

        if ($response === null) {
            return CheckResult::failed(self::CONNECTION_ERROR);
        }

        return $this->buildResult($response);
    }

    private function request(string $url): ?ResponseInterface
    {
        $client = $this->client ?? new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'http_errors' => false,
            'allow_redirects' => false,
        ]);

        $response = null;

        try {
            $response = $client->request('GET', $url);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        } catch (ConnectException | GuzzleException) {
            $response = null;
        }

        return $response;
    }

    private function buildResult(ResponseInterface $response): CheckResult
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            return CheckResult::failed(self::CONNECTION_ERROR);
        }

        try {
            $seo = $this->seoExtractor->extract((string) $response->getBody());
        } catch (Throwable) {
            $seo = [
                'h1' => null,
                'title' => null,
                'description' => null,
            ];
        }

        return CheckResult::succeeded($statusCode, $seo);
    }
}

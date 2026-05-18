<?php

declare(strict_types=1);

namespace App\Http;

use App\SeoExtractor;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

final class PageChecker
{
    public const CONNECTION_ERROR = 'Произошла ошибка при проверке, не удалось подключиться';

    public function __construct(
        private readonly SeoExtractor $seoExtractor,
    ) {
    }

    public function check(string $url): CheckResult
    {
        $client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'http_errors' => false,
            'allow_redirects' => false,
        ]);

        try {
            $response = $client->request('GET', $url);
        } catch (ConnectException) {
            return CheckResult::failed(self::CONNECTION_ERROR);
        } catch (RequestException $e) {
            $response = $e->getResponse();

            if ($response === null) {
                return CheckResult::failed(self::CONNECTION_ERROR);
            }
        } catch (GuzzleException) {
            return CheckResult::failed(self::CONNECTION_ERROR);
        }

        return $this->buildResult($response);
    }

    private function buildResult(ResponseInterface $response): CheckResult
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            return CheckResult::failed(self::CONNECTION_ERROR);
        }

        $seo = $this->seoExtractor->extract((string) $response->getBody());

        return CheckResult::succeeded($statusCode, $seo);
    }
}

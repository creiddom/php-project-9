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

    /**
     * @return array{
     *     ok: bool,
     *     error: ?string,
     *     statusCode: ?int,
     *     seo: ?array{h1: ?string, title: ?string, description: ?string}
     * }
     */
    public function check(string $url): array
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
            return $this->connectionFailed();
        } catch (RequestException $e) {
            if (!$e->hasResponse()) {
                return $this->connectionFailed();
            }

            $response = $e->getResponse();
        } catch (GuzzleException) {
            return $this->connectionFailed();
        }

        return $this->buildResult($response);
    }

    /**
     * @return array{
     *     ok: bool,
     *     error: ?string,
     *     statusCode: ?int,
     *     seo: ?array{h1: ?string, title: ?string, description: ?string}
     * }
     */
    private function buildResult(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            return $this->connectionFailed();
        }

        $seo = $this->seoExtractor->extract((string) $response->getBody());

        return [
            'ok' => true,
            'error' => null,
            'statusCode' => $statusCode,
            'seo' => $seo,
        ];
    }

    /**
     * @return array{
     *     ok: bool,
     *     error: string,
     *     statusCode: null,
     *     seo: null
     * }
     */
    private function connectionFailed(): array
    {
        return [
            'ok' => false,
            'error' => self::CONNECTION_ERROR,
            'statusCode' => null,
            'seo' => null,
        ];
    }
}

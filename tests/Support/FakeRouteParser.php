<?php

declare(strict_types=1);

namespace Tests\Support;

use Psr\Http\Message\UriInterface;
use Slim\Interfaces\RouteParserInterface;

final class FakeRouteParser implements RouteParserInterface
{
    public function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        unset($routeName, $queryParams);

        return isset($data['id']) ? '/urls/' . $data['id'] : '/';
    }

    public function fullUrlFor(UriInterface $uri, string $routeName, array $data = [], array $queryParams = []): string
    {
        unset($uri, $routeName, $queryParams);

        return 'http://localhost' . $this->urlFor($routeName, $data, $queryParams);
    }

    public function relativeUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->urlFor($routeName, $data, $queryParams);
    }
}

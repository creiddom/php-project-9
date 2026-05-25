<?php

declare(strict_types=1);

namespace App\View;

use Slim\Interfaces\RouteParserInterface;

final class RoutePresenter
{
    public function __construct(
        private readonly RouteParserInterface $routeParser,
    ) {
    }

    /**
     * @param array<string, string|int> $data
     */
    public function for(string $name, array $data = []): string
    {
        /** @var array<string, string> $routeData */
        $routeData = array_map(static fn (string|int $value): string => (string) $value, $data);

        return $this->routeParser->urlFor($name, $routeData);
    }
}

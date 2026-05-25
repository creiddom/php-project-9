<?php

declare(strict_types=1);

namespace App\Validator;

use Valitron\Validator;

final class UrlFormValidator
{
    public const ERROR_EMPTY = 'URL не должен быть пустым';

    public const ERROR_TOO_LONG = 'URL превышает 255 символов';

    public const ERROR_INVALID = 'Некорректный URL';

    private const MAX_LENGTH = 255;

    private const MAX_HOST_LENGTH = 253;

    private const MIN_PORT = 1;

    private const MAX_PORT = 65535;

    private const ALLOWED_SCHEMES = ['http', 'https'];

    /**
     * @param array<string, mixed>|null $data
     */
    public function validate(?array $data): UrlValidationResult
    {
        $validator = new Validator($data ?? []);
        $validator->addInstanceRule('appUrl', $this->validateAppUrl(...), self::ERROR_INVALID);

        $validator->rule('required', 'url')->message(self::ERROR_EMPTY);
        $validator->rule('lengthMax', 'url', self::MAX_LENGTH)->message(self::ERROR_TOO_LONG);
        $validator->rule('url', 'url')->message(self::ERROR_INVALID);
        $validator->rule('appUrl', 'url');

        $url = trim((string) ($data['url'] ?? ''));

        if (!$validator->validate()) {
            return new UrlValidationResult(false, $this->collectErrors($validator), $url);
        }

        return new UrlValidationResult(true, [], $url);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $fields
     */
    private function validateAppUrl(string $field, mixed $value, array $params, array $fields): bool
    {
        unset($field, $params, $fields);

        if (!is_string($value)) {
            return false;
        }

        $url = trim($value);

        if ($url === '' || preg_match('/\s/u', $url) === 1) {
            return false;
        }

        return $this->isValidUrl($url);
    }

    /**
     * @return list<string>
     */
    private function collectErrors(Validator $validator): array
    {
        $errors = $validator->errors('url');

        if (!is_array($errors)) {
            return [];
        }

        return array_values($errors);
    }

    private function isValidUrl(string $url): bool
    {
        $parsed = parse_url($url);

        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && is_array($parsed)
            && $this->hasAllowedScheme($parsed)
            && !$this->hasCredentials($parsed)
            && $this->hasValidPort($parsed)
            && $this->isValidHost($parsed['host'] ?? '');
    }

    /**
     * @param array<string, mixed> $parsed
     */
    private function hasAllowedScheme(array $parsed): bool
    {
        if (!isset($parsed['scheme'])) {
            return false;
        }

        return in_array(strtolower((string) $parsed['scheme']), self::ALLOWED_SCHEMES, true);
    }

    /**
     * @param array<string, mixed> $parsed
     */
    private function hasCredentials(array $parsed): bool
    {
        return isset($parsed['user']) || isset($parsed['pass']);
    }

    /**
     * @param array<string, mixed> $parsed
     */
    private function hasValidPort(array $parsed): bool
    {
        if (!isset($parsed['port'])) {
            return true;
        }

        $port = (int) $parsed['port'];

        return $port >= self::MIN_PORT && $port <= self::MAX_PORT;
    }

    private function isValidHost(string $host): bool
    {
        return $host !== ''
            && strlen($host) <= self::MAX_HOST_LENGTH
            && preg_match('/[\s@\/\\\\]/', $host) !== 1
            && ($this->isValidIpHost($host) || $this->isValidDomainHost($host));
    }

    private function isValidIpHost(string $host): bool
    {
        $ip = $host;

        if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
            $ip = substr($host, 1, -1);
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6
        ) !== false;
    }

    private function isValidDomainHost(string $host): bool
    {
        $isValid = !str_starts_with($host, '-') && !str_ends_with($host, '-')
            && !str_contains($host, '..')
            && preg_match('/^[a-z0-9.-]+$/i', $host) === 1;

        if ($isValid) {
            foreach (explode('.', $host) as $label) {
                if (
                    $label === ''
                    || strlen($label) > 63
                    || str_starts_with($label, '-')
                    || str_ends_with($label, '-')
                ) {
                    $isValid = false;
                    break;
                }
            }
        }

        return $isValid;
    }
}

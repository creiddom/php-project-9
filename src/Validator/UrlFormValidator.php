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

    /**
     * @param array<string, mixed>|null $data
     */
    public function validate(?array $data): UrlValidationResult
    {
        $data = $data ?? [];

        if (array_key_exists('url', $data)) {
            $data['url'] = trim((string) $data['url']);
        }

        $validator = new Validator($data);
        $validator->labels(['url' => '']);
        $validator->stopOnFirstFail();

        $validator->rule('required', 'url')->message(self::ERROR_EMPTY);
        $validator->rule('lengthMax', 'url', self::MAX_LENGTH)->message(self::ERROR_TOO_LONG);
        $validator->rule('url', 'url')->message(self::ERROR_INVALID);

        $url = (string) ($data['url'] ?? '');

        if (!$validator->validate()) {
            return new UrlValidationResult(false, $this->collectErrors($validator), $url);
        }

        return new UrlValidationResult(true, [], $url);
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

        return array_values(array_unique(array_map(trim(...), $errors)));
    }
}

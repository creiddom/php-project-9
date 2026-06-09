<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UrlRepository;
use App\Support\UrlNormalizer;
use App\Validator\UrlFormValidator;
use Carbon\Carbon;

final class UrlStoreService
{
    public function __construct(
        private readonly UrlFormValidator $urlFormValidator,
        private readonly UrlNormalizer $urlNormalizer,
        private readonly UrlRepository $urlRepository,
    ) {
    }

    /**
     * @param array<string, mixed>|null $data
     */
    public function store(?array $data): UrlStoreResult
    {
        $validation = $this->urlFormValidator->validate($data);

        if (!$validation->valid) {
            return UrlStoreResult::failed($validation);
        }

        $normalizedUrl = $this->urlNormalizer->normalize($validation->url);
        $existingUrl = $this->urlRepository->findIdByName($normalizedUrl);

        if ($existingUrl !== null) {
            return UrlStoreResult::created((int) $existingUrl['id'], 'Страница уже существует');
        }

        $urlId = (int) $this->urlRepository->insert(
            $normalizedUrl,
            Carbon::now()->toDateTimeString(),
        );

        return UrlStoreResult::created($urlId, 'Страница успешно добавлена');
    }
}

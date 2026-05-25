<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\PhpRenderer;
use Throwable;

final class HttpErrorHandler
{
    public function __construct(
        private readonly PhpRenderer $renderer,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly bool $displayErrorDetails = false,
    ) {
    }

    public function __invoke(
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        unset($request, $logErrors, $logErrorDetails);

        $showDetails = $this->displayErrorDetails || $displayErrorDetails;

        if ($exception instanceof HttpNotFoundException || $exception instanceof HttpMethodNotAllowedException) {
            return $this->render(
                404,
                'Страница не найдена',
                'Запрашиваемая страница не существует.',
                $showDetails,
                $exception,
            );
        }

        if ($exception instanceof HttpException) {
            return $this->render(
                $exception->getCode(),
                'Ошибка',
                'Не удалось обработать запрос.',
                $showDetails,
                $exception,
            );
        }

        return $this->render(
            500,
            'Внутренняя ошибка сервера',
            'На сервере произошла ошибка. Попробуйте позже.',
            $showDetails,
            $exception,
        );
    }

    private function render(
        int $status,
        string $title,
        string $message,
        bool $showDetails,
        Throwable $exception,
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse()->withStatus($status);

        return $this->renderer->render($response, 'error.php', [
            'title' => $title,
            'statusCode' => $status,
            'message' => $message,
            'details' => $showDetails ? $exception->getMessage() : '',
        ]);
    }
}

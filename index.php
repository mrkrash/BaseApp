<?php declare(strict_types=1);

namespace Mrkrash\Estimate;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use Throwable;

(static function(): void {
    require __DIR__ . '/vendor/autoload.php';

    try {
        $app = App::bootstrap();
        $request = ServerRequestFactory::fromGlobals();
        $response = $app->handle($request);
    } catch (Throwable $exception) {
        logger()->error($exception, ['exception' => $exception, 'request' => $request ?? null]);
        $response = new JsonResponse(['error' => exception_to_array($exception)], 500);
    }

    (new SapiStreamEmitter())->emit($response);
})();

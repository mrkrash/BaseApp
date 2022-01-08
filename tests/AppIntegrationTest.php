<?php declare(strict_types=1);

namespace Mrkrash\Base;

use DI\DependencyException;
use DI\NotFoundException;
use JsonException;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertSame;

/**
 * @covers App::bootstrap
 * @covers App::handle
 */
class AppIntegrationTest extends TestCase
{
    /**
     * @throws DependencyException
     * @throws JsonException
     * @throws NotFoundException
     */
    public function testBootstrap(): void
    {
        $this->expectNotToPerformAssertions();

        App::bootstrap();
    }

    public function testRootRequest(): void
    {
        $app = App::bootstrap();
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/')->withHeader('accept', '*/*');
        $response = $app->handle($request);
        assertSame(200, $response->getStatusCode());
    }

    public function testContentNegotiation(): void
    {
        $app = App::bootstrap();
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/')->withHeader('accept', 'text/html');
        $response = $app->handle($request);
        assertSame(406, $response->getStatusCode());
    }
}

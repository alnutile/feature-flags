<?php

namespace YlsIdeas\FeatureFlags\Tests\Middlewares;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use YlsIdeas\FeatureFlags\Manager;
use YlsIdeas\FeatureFlags\Middlewares\GuardFeature;

class GuardFeatureTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_can_abort_requests_when_features_are_not_accessible(): void
    {
        $exception = new HttpException(403);

        $this->expectExceptionObject($exception);
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(403)
            ->once()
            ->andThrow($exception);

        $manager = \Mockery::mock(Manager::class);

        $manager->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(false);

        $request = new Request();

        $middleware = new GuardFeature($manager, $app);

        $middleware->handle($request, function () {
        }, 'my-feature', 'on');
    }

    public function test_it_can_abort_requests_when_features_are_not_accessible_and_expecting_to_be_off(): void
    {
        $exception = new HttpException(404);

        $this->expectExceptionObject($exception);
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(403)
            ->once()
            ->andThrow($exception);

        $manager = \Mockery::mock(Manager::class);

        $manager->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(true);

        $request = new Request();

        $middleware = new GuardFeature($manager, $app);

        $middleware->handle($request, function () {
        }, 'my-feature', 'off');
    }

    public function test_it_continues_the_chain_if_features_are_accessible(): void
    {
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(403)
            ->never();

        $manager = \Mockery::mock(Manager::class);

        $manager->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(true);

        $expectedRequest = new Request();

        $middleware = new GuardFeature($manager, $app);

        $this->assertTrue($middleware->handle(
            $expectedRequest,
            function ($request) use ($expectedRequest) {
                $this->assertSame($expectedRequest, $request);

                return true;
            },
            'my-feature'
        ));
    }

    public function test_it_can_abort_requests_with_a_specified_http_status_code(): void
    {
        $exception = new HttpException(404);

        $this->expectExceptionObject($exception);
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(404)
            ->once()
            ->andThrow($exception);

        $manager = \Mockery::mock(Manager::class);

        $manager->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(false);

        $request = new Request();

        $middleware = new GuardFeature($manager, $app);

        $middleware->handle($request, function () {
        }, 'my-feature', 'on', 404);
    }
}

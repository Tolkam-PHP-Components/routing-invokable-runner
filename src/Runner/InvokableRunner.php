<?php declare(strict_types=1);

namespace Tolkam\Routing\Runner;

use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InvokableRunner implements RunnerInterface
{
    use RouteHandlerAwareTrait;
    use AssertionsTrait;

    /**
     * @var InvokerInterface
     */
    protected InvokerInterface $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        if (!$container instanceof InvokerInterface) {
            throw new RunnerException(sprintf(
                'Container must implement %s (ex. PHP-DI).',
                InvokerInterface::class
            ));
        }

        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        if (is_array($this->routeHandler)) {
            [$class, $method] = $this->routeHandler;
            $isCallable = method_exists((string) $class, (string) $method);
        }
        else {
            $isCallable = is_callable($this->routeHandler);
        }

        if ($isCallable) {
            $response = $this->container->call($this->routeHandler, [$request]);
            $this->assertValidResponse($response, $this->routeName);

            return $response;
        }

        return $handler->handle($request);
    }
}

<?php declare(strict_types=1);

namespace Tolkam\Routing\Runner\Handler;

use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tolkam\Routing\Traits\AssertionsTrait;
use Tolkam\Routing\Traits\RouteHandlerAwareTrait;

class InvokableRunner implements HandlerRunnerInterface
{
    use RouteHandlerAwareTrait;
    use AssertionsTrait;
    
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        if (!$container instanceof InvokerInterface) {
            throw new HandlerRunnerException(sprintf(
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
        RequestHandlerInterface $requestHandler
    ): ResponseInterface {
        
        if (is_callable($this->routeHandler)) {
            $response = $this->container->call($this->routeHandler, [$request]);
            $this->assertValidResponse($response, $this->routeName);
            
            return $response;
        }
        
        return $requestHandler->handle($request);
    }
}

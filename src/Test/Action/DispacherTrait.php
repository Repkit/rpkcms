<?php

namespace Test\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait DispacherTrait
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $action = $request->getAttribute('action', 'index') . 'Action';

        if (! method_exists($this, $action)) {
            return $response->withStatus(404);
        }

        return $this->$action($request, $response, $next);
    }
}
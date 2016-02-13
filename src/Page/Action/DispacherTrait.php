<?php

namespace Page\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait DispacherTrait
{
    public function __invoke(
        ServerRequestInterface $Request,
        ResponseInterface $Response,
        callable $Next = null
    ) {
        $action = $Request->getAttribute('action', 'index') . 'Action';

        if (! method_exists($this, $action)) {
            return $Response->withStatus(404);
        }

        return $this->$action($Request, $Response, $Next);
    }
}
<?php
namespace Auth\Action;

class LogoutAction
{
    private $config;

    /**
     * Constructor
     *
     * @param array $config Configuration for the Opauth instance
     */
    public function __construct(array $authConfig)
    {
        $this->config   = $authConfig;
    }

    public function __invoke($request, $response, $next)
    {
        $_SESSION['auth']['user'] = null;
        return $this->redirect($request, $response);
    }

    private function redirect($request, $response)
    {
        $originalUri = $request->getOriginalRequest()->getUri();
        $redirectUri = $originalUri->withPath('/');

        return $response
            ->withStatus(302)
            ->withHeader('Location', (string) $redirectUri);
    }
}

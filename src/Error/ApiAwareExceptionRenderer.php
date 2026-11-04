<?php
declare(strict_types=1);

namespace App\Error;

use Cake\Error\Renderer\WebExceptionRenderer;
use Cake\Http\Exception\HttpException;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

/** Rend les exceptions API au contrat JSON tout en conservant les pages HTML. */
final class ApiAwareExceptionRenderer extends WebExceptionRenderer
{
    /**
     * Rend une réponse JSON pour les requêtes API/AJAX et délègue le HTML à CakePHP.
     *
     * @return \Psr\Http\Message\ResponseInterface Réponse d'erreur rendue.
     */
    public function render(): ResponseInterface
    {
        $request = $this->controller->getRequest();
        if (!$this->isJsonRequest($request)) {
            return parent::render();
        }

        $status = $this->getHttpCode($this->error);
        $response = $this->controller->getResponse()
            ->withType('application/json')
            ->withStatus($status);
        if ($this->error instanceof HttpException) {
            foreach ($this->error->getHeaders() as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }

        return $response->withStringBody((string)json_encode([
            'success' => false,
            'message' => $this->_message($this->error, $status),
            'errors' => [],
        ], JSON_UNESCAPED_UNICODE));
    }

    /** Détermine si la requête attend le contrat d'erreur JSON. */
    private function isJsonRequest(ServerRequest $request): bool
    {
        $path = $request->getPath();
        $isApiPath = $path === '/api' || str_starts_with($path, '/api/');
        $isAjax = $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        return $isApiPath || $isAjax || $request->accepts('application/json');
    }
}

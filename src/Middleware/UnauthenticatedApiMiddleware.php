<?php
declare(strict_types=1);

namespace App\Middleware;

use Authentication\Authenticator\UnauthenticatedException;
use Cake\Http\Response;
use Cake\Log\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/** Retourne le contrat JSON de perte de session pour les routes API. */
class UnauthenticatedApiMiddleware implements MiddlewareInterface
{
    /**
     * Transforme les demandes API non authentifiées en réponse JSON 401.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Requête courante.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler Gestionnaire suivant.
     * @return \Psr\Http\Message\ResponseInterface Réponse HTTP.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (UnauthenticatedException $exception) {
            if (!$this->isApiRequest($request)) {
                throw $exception;
            }

            Log::warning('Perte de session détectée.', [
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath(),
                'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? null,
            ]);

            return (new Response())
                ->withStatus(401)
                ->withType('application/json')
                ->withStringBody((string)json_encode([
                    'success' => false,
                    'message' => 'Votre session a expiré. Veuillez vous reconnecter.',
                ], JSON_UNESCAPED_UNICODE));
        }
    }

    /** Indique si la requête cible l'API applicative. */
    private function isApiRequest(ServerRequestInterface $request): bool
    {
        return str_starts_with($request->getUri()->getPath(), '/api/');
    }
}

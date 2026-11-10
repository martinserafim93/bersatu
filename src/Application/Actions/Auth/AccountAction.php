<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Domain\User\AccountConflictException;
use App\Domain\User\AccountNotActiveException;
use App\Domain\User\AccountService;
use App\Domain\User\AuthenticationFailedException;
use App\Domain\User\DuplicateUserException;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

abstract class AccountAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        protected AccountService $accounts
    ) {
        parent::__construct($logger);
    }

    final protected function action(): Response
    {
        try {
            return $this->handle();
        } catch (HttpBadRequestException $e) {
            return $this->respond(new ActionPayload(
                400,
                null,
                new ActionError(ActionError::BAD_REQUEST, $e->getMessage())
            ));
        } catch (ValidationException $e) {
            return $this->respondWithData(['errors' => $e->getErrors()], 422);
        } catch (AuthenticationFailedException $e) {
            return $this->respond(new ActionPayload(
                401,
                null,
                new ActionError(ActionError::UNAUTHENTICATED, $e->getMessage())
            ));
        } catch (AccountNotActiveException $e) {
            return $this->respond(new ActionPayload(
                403,
                null,
                new ActionError(ActionError::VERIFICATION_ERROR, $e->getMessage())
            ));
        } catch (UserNotFoundException $e) {
            return $this->respond(new ActionPayload(
                404,
                null,
                new ActionError(ActionError::RESOURCE_NOT_FOUND, $e->getMessage())
            ));
        } catch (AccountConflictException | DuplicateUserException $e) {
            return $this->respond(new ActionPayload(
                409,
                null,
                new ActionError(ActionError::NOT_ALLOWED, $e->getMessage())
            ));
        }
    }

    abstract protected function handle(): Response;

    /**
     * @return array<string, mixed>
     * @throws HttpBadRequestException
     */
    protected function bodyArray(): array
    {
        $body = $this->request->getParsedBody();
        if (!is_array($body)) {
            throw new HttpBadRequestException($this->request, 'Body harus berupa objek JSON.');
        }

        return $body;
    }
}

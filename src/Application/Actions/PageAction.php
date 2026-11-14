<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Auth\AuthSession;
use App\Application\Views\PhpRenderer;
use App\Domain\User\StatusAkun;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class PageAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        protected PhpRenderer $renderer,
        protected ContainerInterface $container
    ) {
        parent::__construct($logger);
    }

    /**
     * @param string $template
     * @param array<string, mixed> $data
     * @param string|null $layout
     * @return Response
     */
    protected function renderPage(
        string $template,
        array $data = [],
        ?string $layout = 'layouts/main.php'
    ): Response {
        if (!array_key_exists('currentUser', $data)) {
            $data['currentUser'] = $this->resolveCurrentUser();
        }

        return $this->renderer->render($this->response, $template, $data, $layout);
    }

    protected function resolveCurrentUser(): ?User
    {
        $attrUser = $this->request->getAttribute('user');
        if ($attrUser instanceof User && $attrUser->getStatusAkun() === StatusAkun::AKTIF) {
            return $attrUser;
        }

        $session = $this->request->getAttribute('session');
        if (!($session instanceof AuthSession) || $session->userId() === null) {
            return null;
        }

        try {
            /** @var UserRepository $users */
            $users = $this->container->get(UserRepository::class);
            $user = $users->findById($session->userId());

            return $user->getStatusAkun() === StatusAkun::AKTIF ? $user : null;
        } catch (Throwable) {
            return null;
        }
    }
}

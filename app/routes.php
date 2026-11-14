<?php

declare(strict_types=1);

use App\Application\Actions\Admin\AktifkanAkunAction;
use App\Application\Actions\Admin\CreateAkunAction;
use App\Application\Actions\Admin\ListAkunAction;
use App\Application\Actions\Admin\NonaktifkanAkunAction;
use App\Application\Actions\Admin\SetujuiAkunAction;
use App\Application\Actions\Admin\TolakAkunAction;
use App\Application\Actions\Auth\LoginAction;
use App\Application\Actions\Auth\LoginPageAction;
use App\Application\Actions\Auth\LogoutAction;
use App\Application\Actions\Auth\MeAction;
use App\Application\Actions\Auth\RegisterAction;
use App\Application\Actions\Auth\RegisterPageAction;
use App\Application\Actions\Home\BerandaSummaryApiAction;
use App\Application\Actions\Home\HomePageAction;
use App\Application\Actions\Home\OfflinePageAction;
use App\Application\Actions\Master\ListKategoriIsuAction;
use App\Application\Actions\Master\ListWilayahAction;
use App\Application\Middleware\AuthMiddleware;
use App\Application\Middleware\RequireJsonMiddleware;
use App\Application\Middleware\RoleMiddleware;
use App\Domain\User\Role;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', HomePageAction::class);
    $app->get('/offline', OfflinePageAction::class);
    $app->get('/auth/login', LoginPageAction::class);
    $app->get('/auth/register', RegisterPageAction::class);

    $app->get('/api/public/beranda', BerandaSummaryApiAction::class);

    $app->group('/api/auth', function (Group $group) {
        $group->post('/register', RegisterAction::class);
        $group->post('/login', LoginAction::class);
        $group->post('/logout', LogoutAction::class)->add(AuthMiddleware::class);
        $group->get('/me', MeAction::class)->add(AuthMiddleware::class);
    })->add(RequireJsonMiddleware::class);

    $app->group('/api/admin', function (Group $group) {
        $group->get('/akun', ListAkunAction::class);
        $group->post('/akun', CreateAkunAction::class);
        $group->post('/akun/{id:[0-9]+}/setujui', SetujuiAkunAction::class);
        $group->post('/akun/{id:[0-9]+}/tolak', TolakAkunAction::class);
        $group->post('/akun/{id:[0-9]+}/nonaktifkan', NonaktifkanAkunAction::class);
        $group->post('/akun/{id:[0-9]+}/aktifkan', AktifkanAkunAction::class);
    })->add(new RoleMiddleware([Role::SUPERADMIN]))
      ->add(AuthMiddleware::class)
      ->add(RequireJsonMiddleware::class);

    $app->group('/api/master', function (Group $group) {
        $group->get('/wilayah', ListWilayahAction::class);
        $group->get('/kategori-isu', ListKategoriIsuAction::class);
    });
};

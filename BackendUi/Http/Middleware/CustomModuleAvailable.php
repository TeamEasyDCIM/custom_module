<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Http\Middleware;

use App\BackendUi\Http\Responses\BackendUiResponse;
use Closure;
use Illuminate\Http\Request;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;
use Symfony\Component\HttpFoundation\Response;

/** Answers 404 on every 2.0 page and endpoint of the module while it is not active. */
final class CustomModuleAvailable
{
    public function __construct(private readonly CustomModuleAccess $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $this->access->available() ? $next($request) : BackendUiResponse::error(404, trans('backend/global.page_not_found'), 'not_found');
    }
}

<?php

declare(strict_types=1);

namespace Proovit\UrlWatcher\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface UrlWatchCapturePolicyInterface
{
    public function shouldCapture(Request $request, ?Throwable $throwable = null, ?Response $response = null): bool;

    public function ignoreReason(Request $request, ?Throwable $throwable = null, ?Response $response = null): ?string;
}

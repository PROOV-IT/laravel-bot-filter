<?php

declare(strict_types=1);

namespace Proovit\BotFilter\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface BotProbeCapturePolicyInterface
{
    public function shouldCapture(Request $request, ?Throwable $throwable = null, ?Response $response = null): bool;

    public function ignoreReason(Request $request, ?Throwable $throwable = null, ?Response $response = null): ?string;
}

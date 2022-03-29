<?php

declare(strict_types=1);

namespace App;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\StreamFactory;
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Serve
{
    private bool $isKwReq;

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        // Check if this is a KyuWeb request
        $kwVersStr     = $request->getHeaderLine('Accept-KyuWeb');
        $this->isKwReq = $kwVersStr !== '';

        if ($this->isKwReq) {
            // If a KW request, make sure we can handle the requested version.
            $kwVersStr = $request->getHeaderLine('Accept-KyuWeb');
            if (\preg_match('/^(\d)\.(\d)$/', $kwVersStr, $matches) === false) {
                return $this->sendError('Request specified invalid KyuWeb version', 400);
            }

            $majorVersion = (int) $matches[1];
            $minorVersion = (int) $matches[2];
            // Only expect a 0.1 version for now
            if ($majorVersion !== 0 && $minorVersion !== 1) {
                return $this->sendError('Cannot satisfy requested KyuWeb version', 406);
            }

            // We can only satisfy GET requests for now; also allow for HEAD.
            $method = $request->getMethod();
            if ($method !== 'GET' && $method !== 'HEAD') {
                return $this->sendError('Only GET requests accepted.', 405);
            }
        }

        // Can we handle the Accept header?
        $acceptLine  = $request->getHeaderLine('Accept');
        $negotiatior = new Negotiator();
        $types       = $this->isKwReq ? ['text/markdown', 'text/plain'] : ['text/html', 'text/plain'];
        $type        = $negotiatior->getBest($acceptLine, $types);
        if (! $type) {
            return $this->sendError('Cannot return a document in the requested type(s).', 415);
        }

        // Okay, so far so good. Try to find the file.
        $path    = $request->getRequestTarget();
        $docPath = \dirname(\dirname(__FILE__)) . '/doc' . $path;
        // Remove a trailing slash if present.
        $docPath = \rtrim($docPath, '/');
        if (\is_dir($docPath)) {
            $docPath .= '/index.md';
        }

        if (! \file_exists($docPath)) {
            return $this->sendError('The requested file was not found.', 404);
        }

        if (! \is_readable($docPath)) {
            return $this->sendError('The requested file is not accessible.', 403);
        }

        $ct      = $type->getType();
        $headers = [
            'KyuWeb' => '0.1',
            'Content-Type' => $ct,
        ];

        $sf = new StreamFactory();

        if ($this->isKwReq) {
            $stream = $sf->createStreamFromFile($docPath);
        } else {
            // Standard HTTP response.
            if ($ct === 'text/plain') {
                $stream = $sf->createStreamFromFile($docPath);
            } else {
                $mdContent = \file_get_contents($docPath);
                $content   = <<<END
<!doctype html>
<html>
    <head>
        <title>kyuWeb Document</title>
    </head>
    <body>
<!-- KyuWeb Doc Start /--><pre>
{$mdContent}
</pre><!-- KyuWeb Doc End /-->
    </body>
</html>

END;
                $stream    = $sf->createStream($content);
            }

            return new Response($stream, 200, $headers);
        }
    }

    protected function sendError(string $message, int $httpErrCode = 500): ResponseInterface
    {
        $response = new Response();
        $response->setStatus($httpErrCode);
        $statusMsg = $response->getReasonPhrase();
        if ($this->isKwReq) {
            $content =  <<<END
# {$httpErrCode} {$statusMsg}

{$message}

END;
        } else {
            $content = <<<END
<!doctype html>
<html>
    <head>
        <title>Error: {$statusMsg}</title>
    </head>
    <body>
        <h1>{$httpErrCode} {$statusMsg}</h1>
        <p>{$message}</p>
    </body>
</html>

END;
        }

        $response = $response->withHeader('KyuWeb', '0.1');
        $response->getBody()->write($content);

        return $response;
    }
}

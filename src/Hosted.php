<?php
declare(strict_types=1);
namespace ParagonIE\Hosted;

use FastRoute\{
    Dispatcher,
    RouteCollector
};
use ParagonIE\Hosted\Handler\{
    IndexPage
};
use Slim\Http\{
    Headers,
    Response,
    Stream
};

/**
 * Class Hosted
 * @package ParagonIE\Hosted
 */
final class Hosted
{
    /** @var \Twig_Environment */
    protected static $twig;

    /**
     * @param Response $response
     * @return void
     */
    public static function finalizeOutput(Response $response): void
    {
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                \header(\sprintf('%s: %s', $name, $value), false);
            }
        }
        echo $response->getBody();
        exit(0);
    }

    /**
     * @param int $statusCode
     * @return Response
     */
    public static function getErrorPage(int $statusCode = 500): Response
    {
        return self::renderResponse(
            'error.twig',
            ['status' => $statusCode],
            null,
            $statusCode
        );
    }

    /**
     * @return \FastRoute\Dispatcher
     */
    public static function getRouteDispatcher(): Dispatcher
    {
        return \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            $r->addRoute('GET', '/', IndexPage::class);
        });
    }

    /**
     * @return \Twig_Environment
     */
    public static function getTwig(): \Twig_Environment
    {
        if (!isset(self::$twig)) {
            self::$twig = new \Twig_Environment(
                new \Twig_Loader_Filesystem([
                    HOSTED_ROOT . '/templates'
                ])
            );
        }
        return self::$twig;
    }

    /**
     * All-in-one Response builder
     *
     * @param string $template
     * @param array $templateVariables
     * @param Headers|null $headers
     * @param int $statusCode
     * @return Response
     */
    public static function renderResponse(
        string $template,
        array $templateVariables = [],
        Headers $headers = null,
        int $statusCode = 200
    ): Response {
        return new Response(
            $statusCode,
            $headers,
            self::stringToStream(
                self::render($template, $templateVariables)
            )
        );
    }

        /**
     * @param string $template
     * @param array $args
     * @param array $headers
     * @return string
     */
    public static function render(string $template, array $args = []): string
    {
        return self::getTwig()->render(
            $template,
            $args
        );
    }

    /**
     * @param string $input
     * @return Stream
     * @throws \Error
     */
    public static function stringToStream(string $input): Stream
    {
        /** @var resource $stream */
        $stream = \fopen('php://temp', 'w+');
        if (!\is_resource($stream)) {
            throw new \Error('Could not create stream');
        }
        \fwrite($stream, $input);
        \rewind($stream);
        return new Stream($stream);
    }
}

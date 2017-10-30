<?php
declare(strict_types=1);
namespace ParagonIE\Hosted\Handler;

use ParagonIE\Hosted\HandlerInterface;
use ParagonIE\Hosted\Hosted;

/**
 * Class IndexPage
 * @package ParagonIE\Hosted\Handler
 */
class IndexPage implements HandlerInterface
{
    public function __invoke()
    {
        return Hosted::renderResponse('index.twig');
    }
}

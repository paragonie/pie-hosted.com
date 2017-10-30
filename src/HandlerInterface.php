<?php
declare(strict_types=1);
namespace ParagonIE\Hosted;

/**
 * Interface HandlerInterface
 * @package ParagonIE\Hosted
 */
interface HandlerInterface
{
    /**
     * @return mixed
     */
    public function __invoke();
}

<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * @SuppressWarnings("PHPMD.UnusedPrivateField")
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    // Add these to satisfy the Scrutinizer check
    /** @phpstan-ignore-next-line */
    private $instanceof = [];

    protected $name;
}

<?php

namespace Base\Press;

use Base\Bundle\AbstractBaseBundle;
use Base\Traits\SingletonTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The press kit an artist's agency asks for: what was written about them
 * (Quote), the pictures a programme may print (Photo), the biography in
 * three lengths (Bio) - one page, /press, and three Twig functions.
 */
class PressBundle extends AbstractBaseBundle
{
    use SingletonTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /** Modern layout: the class lives in src/, the bundle root is the package root. */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // App\-wins, as omnibase does for its own entities: an application may
        // declare App\Entity\Press\Quote extending ours and take over.
        $this->setMapping($this->getPath().'/src/Entity', 'Base\Press\Entity', 'App\Entity\Press');
        $this->setMapping($this->getPath().'/src/Repository', 'Base\Press\Repository', 'App\Repository\Press');
    }
}

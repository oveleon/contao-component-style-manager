<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\ContaoManager;

use Contao\CalendarBundle\ContaoCalendarBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
//use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use Oveleon\ContaoComponentStyleManager\ContaoComponentStyleManager;
//use Symfony\Component\Config\Loader\LoaderResolverInterface;
//use Symfony\Component\HttpKernel\KernelInterface;
//use Symfony\Component\Routing\RouteCollection;

class Plugin implements BundlePluginInterface //, RoutingPluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            new BundleConfig(ContaoComponentStyleManager::class)
                ->setLoadAfter([ContaoCoreBundle::class, ContaoCalendarBundle::class, ContaoNewsBundle::class]),
        ];
    }

    /**
     * @throws \Exception
     */
    /*public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): RouteCollection|null
    {
        return $resolver
            ->resolve('@ContaoComponentStyleManager/src/Controller')
            ->load('@ContaoComponentStyleManager/src/Controller')
        ;
    }*/
}

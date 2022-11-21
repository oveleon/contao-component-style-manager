<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\EventListener;

use Contao\CoreBundle\Exception\NoContentResponseException;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

/**
 * @Hook("executePostActions")
 */
class ExecutePostActionsListener
{
    /**
     * Saves the status of selected tabs
     */
    public function __invoke($strAction, DataContainer $dc)
    {
        if($strAction !== 'selectStyleManagerSection')
        {
            return;
        }

        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $fs = $objSessionBag->get('stylemanager_section_states');
        $fs[Input::post('groupAlias')] = Input::post('identifier');
        $objSessionBag->set('stylemanager_section_states', $fs);

        throw new NoContentResponseException();
    }
}

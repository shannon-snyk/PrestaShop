<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShop\PrestaShop\Core\Addon\Module\Exception;

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleInterface;
use PrestaShop\PrestaShop\Core\Exception\CoreException;

/**
 * This class is used for the module page, which allows to ask for a confirmation from the employee.
 */
class UnconfirmedModuleActionException extends CoreException
{
    /**
     * Concerned module by the exception.
     *
     * @var ModuleInterface
     */
    protected $module;

    /**
     * Action requested by the employee.
     *
     * @var string
     */
    protected $action;

    /**
     * Subject to send in order to confirm.
     *
     * @var string
     */
    protected $subject;

    /**
     * Module getter.
     *
     * @return ModuleInterface
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Action getter (install, uninstall, reset ...).
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Subject getter..
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Module setter.
     *
     * @param ModuleInterface $module
     *
     * @return $this
     */
    public function setModule(ModuleInterface $module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Action setter.
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Subject setter.
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }
}

<?php
/**
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * Discount by Bank Wire payment module by Krystian Podemski from PrestaHome.
 *
 * @author    Krystian Podemski <krystian@prestahome.com>
 * @copyright Copyright (c) 2016 Krystian Podemski - www.PrestaHome.com / www.Podemski.info
 * @license   You only can use module, nothing more!
 */
class BankwirePaymentModuleFrontControllerOverride extends BankwirePaymentModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency($cart)) {
            Tools::redirect('index.php?controller=order');
        }

        // Simulate a discount for using bankwire
        $total = $cart->getOrderTotal(true, Cart::BOTH);
        $discountByBankwire = Module::getInstanceByName('phdiscountbybankwire');
        if (Validate::isLoadedObject($discountByBankwire) && $discountByBankwire->active) {
            $codeFromTheModule = Configuration::get($discountByBankwire->options_prefix.'CODE');
            $voucherId = CartRule::getIdByCode($codeFromTheModule);
            if ($voucherId) {
                $cartRule = new CartRule((int) $voucherId);
                if (Validate::isLoadedObject($cartRule)) {
                    if (true === $cartRule->checkValidity($this->context, false, false)) {
                        $cart->addCartRule((int) $cartRule->id);
                        $total = $cart->getOrderTotal(true, Cart::BOTH);
                        $cart->removeCartRule((int) $cartRule->id);
                        $cart->update();
                    }
                }
            }
        }

        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),
            'total' => $total,
            'this_path' => $this->module->getPathUri(),
            'this_path_bw' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
        ));

        $this->setTemplate('payment_execution.tpl');
    }
}

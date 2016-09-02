<?php
/**
* 2007-2016 PrestaShop.
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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/vendor/autoload.php';

class PhDiscountByBankwire extends Module implements PrestaHomeConfiguratorInterface
{
    use PrestaHomeHelpers, PrestaHomeConfiguratorBase;

    public function __construct()
    {
        $this->name = 'phdiscountbybankwire';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'PrestaHome';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Discount by bankwire');
        $this->description = $this->l('You can setup discount available only within a payment by bankwire');

        $this->setOptionsPrefix('phdiscountbybankwire');
    }

    public function setOptionsPrefix($custom = false)
    {
        $this->options_prefix = Tools::strtoupper(($custom ? $custom : $this->name)).'_';

        return $this;
    }

    public function install()
    {
        $hooks = array(
            'displayBeforeShoppingCartBlock',
            'displayPaymentTop',
        );

        $this->renderConfigurationForm();
        $this->batchUpdateConfigs();

        if (file_exists(_PS_MODULE_DIR_.$this->name.'/init/my-install.php')) {
            require_once _PS_MODULE_DIR_.$this->name.'/init/my-install.php';
        }

        PhDiscountByBankwire::recurseCopy(dirname(__FILE__).'/override/modules/', _PS_OVERRIDE_DIR_.'modules/');

        if (!parent::install() || !$this->registerHook($hooks)) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        $this->renderConfigurationForm();
        $this->deleteConfigs();

        if (file_exists(_PS_MODULE_DIR_.$this->name.'/init/my-uninstall.php')) {
            require_once _PS_MODULE_DIR_.$this->name.'/init/my-uninstall.php';
        }

        return parent::uninstall();
    }

    public function getContent()
    {
        $this->renderConfigurationForm();
        $this->_html = '<h2>'.$this->displayName.'</h2>';

        if (Tools::isSubmit('save'.$this->name)) {
            $this->renderConfigurationForm();
            $this->batchUpdateConfigs();

            $this->_clearCache('*');
            $this->_html .= $this->displayConfirmation($this->l('Settings updated successfully.'));
        }

        return $this->_html.$this->renderForm();
    }

    public function renderConfigurationForm()
    {
        if ($this->fields_form) {
            return;
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),

                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Voucher code from "Cart Rules" page:'),
                        'name' => $this->options_prefix.'CODE',
                        'desc' => $this->l('Fill with a code of the generated voucher from the "Cart Rules" page, module will get information about discount based on this code.'),
                        'default' => '',
                        'validate' => 'isCleanHtml',
                    ),

                    array(
                        'type' => 'textarea',
                        'autoload_rte' => true,
                        'lang' => true,
                        'label' => $this->l('Message for customers:'),
                        'name' => $this->options_prefix.'MSG_SHOPPING_CART',
                        'desc' => $this->l('Displayed at the top of the shopping cart (displayBeforeShoppingCartBlock)'),
                        'default' => '<div class="alert alert-info">Get 2% discount by paying within a bankwire!</div>',
                        'validate' => 'isCleanHtml',
                    ),

                    array(
                        'type' => 'textarea',
                        'autoload_rte' => true,
                        'lang' => true,
                        'label' => $this->l('Message for customers:'),
                        'name' => $this->options_prefix.'MSG_PAYMENTS_PAGE',
                        'desc' => $this->l('Displayed at the top of the payments page (displayPaymentTop)'),
                        'default' => '<div class="alert alert-info">Get 2% discount by paying within a bankwire!</div>',
                        'validate' => 'isCleanHtml',
                    ),
                ),

                'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default', ),
            ),
        );

        $this->fields_form[] = $fields_form;
    }

    public function hookDisplayBeforeShoppingCartBlock($params)
    {
        $this->smarty->assign('message', Configuration::get($this->options_prefix.'MSG_SHOPPING_CART', $this->context->language->id));
        return $this->display(__FILE__, 'message.tpl');
    }

    public function hookDisplayPaymentTop($params)
    {
        $this->smarty->assign('message', Configuration::get($this->options_prefix.'MSG_PAYMENTS_PAGE', $this->context->language->id));
        return $this->display(__FILE__, 'message.tpl');
    }

    public static function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    PhDiscountByBankwire::recurseCopy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }
        closedir($dir);
    }
}

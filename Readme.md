# PrestaHome - Discount by `bankwire` module

The simplest way to apply discount for a `bankwire` payment.

## How to use?

1. Create voucher with an unique code, setup discount (percentage or amount) with all restrictions etc.
2. Install this module, provide generated code on the module Configuration page.
3. You can add proper message displayed in two places of the front-office to inform people about the discount.
4. Discount will only work if:
    - module is enabled
    - voucher validity is ok
    - Dispatcher.php is properly overriden, if not, controllers would not be overrided and module will not work

## Requirements

* PrestaShop 1.6.1.1+
* PHP 5.4

Tested on PrestaShop 1.6.1.6
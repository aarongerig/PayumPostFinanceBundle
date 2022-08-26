<?php

declare(strict_types=1);

/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
*/

namespace CoreShop\Payum\PostFinanceBundle\Extension;

use CoreShop\Component\Order\Model\OrderInterface;
use CoreShop\Component\Core\Model\PaymentInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\Convert;

final class ConvertPaymentExtension implements ExtensionInterface
{
    public function onPostExecute(Context $context): void
    {
        $action = $context->getAction();
        $previousActionClassName = \get_class($action);

        if (false === \stripos($previousActionClassName, 'ConvertPaymentAction')) {
            return;
        }

        /** @var Convert $request */
        $request = $context->getRequest();
        if (!$request instanceof Convert) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        if (!$payment instanceof PaymentInterface) {
            return;
        }

        $order = $payment->getOrder();
        if (!$order instanceof OrderInterface) {
            return;
        }

        $gatewayLanguage = 'en_EN';

        if (!empty($order->getLocaleCode())) {
            $orderLanguage = $order->getLocaleCode();

            // Postfinance always requires a full language ISO Code
            if (!str_contains($orderLanguage, '_')) {
                $gatewayLanguage = $orderLanguage . '_' . \mb_strtoupper($orderLanguage);
            } else {
                $gatewayLanguage = $orderLanguage;
            }
        }

        $result = ArrayObject::ensureArrayObject($request->getResult());
        $result['LANGUAGE'] = $gatewayLanguage;

        $request->setResult((array) $result);
    }

    /**
     * @inheritDoc
     */
    public function onPreExecute(Context $context): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onExecute(Context $context): void
    {
    }
}

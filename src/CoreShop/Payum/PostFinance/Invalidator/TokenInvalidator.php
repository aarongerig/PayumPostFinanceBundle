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

namespace CoreShop\Payum\PostFinanceBundle\Invalidator;

use CoreShop\Component\PayumPayment\Model\GatewayConfig;
use CoreShop\Component\PayumPayment\Model\PaymentSecurityToken;
use CoreShop\Component\Core\Model\PaymentProvider;
use CoreShop\Component\Payment\Model\Payment;
use Payum\Core\Model\Identity;
use Payum\Core\Payum;
use Doctrine\Persistence\ObjectManager;

final class TokenInvalidator implements TokenInvalidatorInterface
{
    public function __construct(private Payum $payum, private ObjectManager $objectManager)
    {
    }

    public function invalidate($days): void
    {
        $outdatedTokens = [];
        $now = new \DateTime();
        $repository = $this->objectManager->getRepository(PaymentSecurityToken::class);

        $tokens = $repository->findAll();
        if (empty($tokens)) {
            return;
        }

        /** @var PaymentSecurityToken $token */
        foreach ($tokens as $token) {
            $targetUrl = $token->getTargetUrl();

            if (empty($targetUrl)) {
                continue;
            }

            // Hacky: We only want to delete capture and after-pay tokens.
            if (!\str_contains($targetUrl, 'payment/capture') && !\str_contains($targetUrl, 'cs/after-pay')) {
                continue;
            }

            /** @var Identity $identity */
            $identity = $token->getDetails();

            $payment = $this->payum->getStorage($identity->getClass())->find($identity);
            if (!$payment instanceof Payment) {
                continue;
            }

            /** @var PaymentProvider $paymentProvider */
            $paymentProvider = $payment->getPaymentProvider();
            if (!$paymentProvider instanceof PaymentProvider) {
                continue;
            }

            /** @var GatewayConfig $gatewayConfig */
            $gatewayConfig = $paymentProvider->getGatewayConfig();
            if (!$gatewayConfig instanceof GatewayConfig) {
                continue;
            }

            // Now only tokens from Postfinance factory should get deleted!
            if ($gatewayConfig->getFactoryName() !== 'postfinance') {
                continue;
            }

            $creationDate = $payment->getCreationDate();
            if (!$creationDate instanceof \DateTime) {
                continue;
            }

            if ($creationDate->diff($now)->days >= $days) {
                $outdatedTokens[] = $token;
            }
        }

        // Cycle outdated and remove them.
        if (\count($outdatedTokens) === 0) {
            return;
        }

        foreach ($outdatedTokens as $outdatedToken) {
            $this->objectManager->remove($outdatedToken);
        }

        $this->objectManager->flush();
    }
}

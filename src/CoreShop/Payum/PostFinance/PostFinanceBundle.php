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

namespace CoreShop\Payum\PostFinanceBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

class PostFinanceBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    /**
     * @inheritDoc
     */
    protected function getComposerPackageName(): string
    {
        return 'coreshop/payum-postfinance-bundle';
    }
}

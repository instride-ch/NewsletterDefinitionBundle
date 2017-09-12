<?php
/**
 * Newsletter Definition.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2016-2017 W-Vision (http://www.w-vision.ch)
 * @license    https://github.com/w-vision/NewsletterDefinition/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace NewsletterDefinitionBundle;

use NewsletterDefinitionBundle\AddressSourceAdapater\DefinitionAddressSourceAdapter;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class NewsletterDefinitionBundle extends AbstractPimcoreBundle
{
    public function boot()
    {
        parent::boot();

        class_alias(DefinitionAddressSourceAdapter::class, '\\Pimcore\\Document\\Newsletter\\AddressSourceAdapter\\NewsletterDefinition');
    }


    /**
     * {@inheritdoc}
     */
    public function getInstaller()
    {
        return $this->container->get(Installer::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getJsPaths()
    {
        return [
            '/bundles/newsletterdefinition/js/pimcore/startup.js',
            '/bundles/newsletterdefinition/js/pimcore/newsletter/definition.js',
            '/bundles/newsletterdefinition/js/pimcore/newsletter/definitionDialog.js'
        ];
    }
}

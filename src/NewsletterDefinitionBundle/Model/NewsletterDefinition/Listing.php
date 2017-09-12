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

namespace NewsletterDefinitionBundle\Model\NewsletterDefinition;

use Pimcore\Model;
use ImportDefinitionsBundle\Model\DefinitionInterface;

class Listing extends Model\Listing\AbstractListing
{
    /**
     * Contains the results of the list. They are all an instance of Tool\Targeting\Rule
     *
     * @var array
     */
    public $definitions = [];

    /**
     * Tests if the given key is an valid order key to sort the results
     *
     * @param $key
     *
     * @return bool
     */
    public function isValidOrderKey($key)
    {
        return true;
    }

    /**
     * @param $definitions
     *
     * @return $this
     */
    public function setDefinitions($definitions)
    {
        $this->definitions = $definitions;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }
}

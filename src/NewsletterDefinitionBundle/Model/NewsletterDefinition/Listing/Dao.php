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

namespace NewsletterDefinitionBundle\Model\NewsletterDefinition\Listing;

use NewsletterDefinitionBundle\Model\NewsletterDefinition;
use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao
{
        /**
     * Loads a list of document-types for the specicifies parameters, returns an array of Document\DocType elements
     *
     * @return array
     */
    public function load()
    {
        $targetsData = $this->db->fetchCol('SELECT id FROM newsletter_definition' . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        $targets = [];
        foreach ($targetsData as $targetData) {
            $targets[] = NewsletterDefinition::getById($targetData);
        }

        $this->model->setDefinitions($targets);

        return $targets;
    }
}

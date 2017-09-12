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

namespace NewsletterDefinitionBundle\Model;

use Pimcore\Model;

class NewsletterDefinition extends Model\AbstractModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $class;

    /**
     * @var array
     */
    public $filters;

    /**
     * Static helper to retrieve an instance of Tool\Targeting\Rule by the given ID
     *
     * @param int $id
     *
     * @return null|static
     */
    public static function getById($id)
    {
        try {
            $definition = new self();
            $definition->setId(intval($id));
            $definition->getDao()->getById();

            return $definition;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param $name
     *
     * @return null|static
     */
    public static function getByName($name)
    {
        try {
            $definition = new self();
            $definition->setName($name);
            $definition->getDao()->getByName();

            return $definition;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param array $filters
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }
}
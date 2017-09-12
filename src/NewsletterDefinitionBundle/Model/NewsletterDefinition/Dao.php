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

use ImportDefinitionsBundle\Model\Mapping;
use Pimcore\Tool\Serialize;

class Dao extends Model\Dao\AbstractDao
{
    /**
     * @param null $id
     *
     * @throws \Exception
     */
    public function getById($id = null)
    {
        if ($id != null) {
            $this->model->setId($id);
        }

        $data = $this->db->fetchRow('SELECT * FROM newsletter_definition WHERE id = ?', $this->model->getId());

        if ($data['id']) {
            $data['filters'] = Serialize::unserialize($data['filters']);

            $this->assignVariablesToModel($data);
        } else {
            throw new \Exception('newsletter definition with id ' . $this->model->getId() . " doesn't exist");
        }
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     */
    public function getByName($name = null)
    {
        if ($name != null) {
            $this->model->setName($name);
        }

        $data = $this->db->fetchAll('SELECT id FROM newsletter_definition WHERE name = ?', [$this->model->getName()]);

        if (count($data) === 1) {
            $this->getById($data[0]['id']);
        } else {
            throw new \Exception('newsletter definition with name ' . $this->model->getId() . " doesn't exist or isn't unique");
        }
    }

    /**
     * Save object to database
     *
     * @return bool
     *
     * @todo: update and delete don't return anything
     */
    public function save()
    {
        if ($this->model->getId()) {
            return $this->model->update();
        }

        return $this->create();
    }

    /**
     * Deletes object from database
     */
    public function delete()
    {
        $this->db->delete('newsletter_definition', ['id' => $this->model->getId()]);
    }

    /**
     * @throws \Exception
     */
    public function update()
    {
        try {
            $type = get_object_vars($this->model);
            $data = [];

            foreach ($type as $key => $value) {
                if (in_array($key, $this->getValidTableColumns('newsletter_definition'))) {
                    if (is_array($value) || is_object($value)) {
                        $value = Serialize::serialize($value);
                    }
                    if (is_bool($value)) {
                        $value = (int) $value;
                    }
                    $data[$key] = $value;
                }
            }

            $this->db->update('newsletter_definition', $data, ['id' => $this->model->getId()]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create a new record for the object in database
     *
     * @return bool
     */
    public function create()
    {
        $this->db->insert('newsletter_definition', []);

        $this->model->setId($this->db->lastInsertId());

        return $this->save();
    }
}

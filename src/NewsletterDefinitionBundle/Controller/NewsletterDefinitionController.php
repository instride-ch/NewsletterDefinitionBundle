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

namespace NewsletterDefinitionBundle\Controller;

use NewsletterDefinitionBundle\Model\NewsletterDefinition;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\Request;
use Pimcore\Model\DataObject;

class NewsletterDefinitionController extends AdminController
{
    public function listAction(Request $request)
    {
        $list = new NewsletterDefinition\Listing();

        if ($request->get('className')) {
            $list->setCondition('class = ?', [$request->get('className')]);
        }

        $list->load();

        $result = [];

        foreach ($list->getDefinitions() as $item) {
            $result[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'filters' => $item->getFilters()
            ];
        }

        return $this->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function createAction(Request $request)
    {
        $definition = new NewsletterDefinition();
        $definition->setName($request->get('name'));
        $definition->setClass($request->get('className'));
        $definition->setFilters([]);
        $definition->save();

        return $this->json([
            'success' => true,
            'data' => get_object_vars($definition)
        ]);
    }

    public function deleteAction(Request $request)
    {
        $definition = NewsletterDefinition::getById($request->get('id'));

        if ($definition instanceof NewsletterDefinition) {
            $definition->delete();
        }

        return $this->json([
            'success' => true
        ]);
    }

    public function saveAction(Request $request)
    {
        $definition = NewsletterDefinition::getById($request->get('id'));
        $data = json_decode($request->get('data'), true);

        if ($definition instanceof NewsletterDefinition) {
            $definition->setFilters($data['filters']);
            $definition->save();

            return $this->json([
                'success' => true
            ]);
        }

        return $this->json([
            'success' => false
        ]);
    }

    public function fieldsAction(Request $request) {
        $definition = NewsletterDefinition::getById($request->get('id'));

        if ($definition instanceof NewsletterDefinition) {
            $class = $definition->getClass();
            $classDefinition = DataObject\ClassDefinition::getByName($class);

            return $this->json([
                'success' => true,
                'data' => $this->getClassDefinitionForFieldSelection($classDefinition)
            ]);
        }

        return $this->json([
            'success' => false
        ]);
    }

    public function getClassDefinitionForFieldSelection(DataObject\ClassDefinition $class)
    {
        $fields = $class->getFieldDefinitions();

        $systemColumns = [
            "published", "key", "parent", "type"
        ];

        $result = array();

        foreach ($systemColumns as $sysColumn) {
            $result[] = [
                'name' => $sysColumn,
                'identifier' => 'o_' . $sysColumn
            ];
        }

        foreach ($fields as $field) {
            if ($field instanceof DataObject\ClassDefinition\Data\Localizedfields) {
                //Currency not supported
            } elseif ($field instanceof DataObject\ClassDefinition\Data\Objectbricks) {
                //Currency not supported
            } elseif ($field instanceof DataObject\ClassDefinition\Data\Fieldcollections) {
                //Currency not supported
            } elseif ($field instanceof DataObject\ClassDefinition\Data\Classificationstore) {
                //Currency not supported
            } else {
                $result[] = [
                    'name' => $field->getTitle(),
                    'identifier' => $field->getName()
                ];
            }
        }

        return $result;
    }
}
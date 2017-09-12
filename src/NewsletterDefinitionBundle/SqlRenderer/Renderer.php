<?php

namespace NewsletterDefinitionBundle\SqlRenderer;

use NewsletterDefinitionBundle\Model\NewsletterDefinition;
use Pimcore\Db;
use Symfony\Component\Intl\Exception\NotImplementedException;

final class Renderer
{
    /**
     * @var Db\Connection
     */
    private $db;

    /**
     * @param Db\Connection $db
     */
    public function __construct(Db\Connection $db)
    {
        $this->db = $db;
    }

    public function renderDefinition(NewsletterDefinition $definition)
    {
        $filters = $definition->getFilters();
        $sqlString = '';
        $openBracketsCount = 0;

        foreach ($filters as $index => $filter) {
            $filterSqlString = '';

            if ($index > 0) {
                $filterSqlString .= " " . $filter['operator'] . " ";
            }

            if ($filter['bracketLeft']) {
                $filterSqlString .= '(';
                ++$openBracketsCount;
            }

            $filterSqlString .= $this->renderCondition($filter['filterOperator'], $filter['field'], $filter['value']);

            if ($filter['bracketRight'] && $openBracketsCount > 0) {
                $filterSqlString .= ')';
                --$openBracketsCount;
            }

            $sqlString .= ' ' . $filterSqlString;
        }

        return $sqlString;
    }

    private function renderCondition($condition, $field, $value) {
        switch($condition) {
            case 'equal':
                return $this->db->quoteIdentifier($field) . ' = ' . $this->db->quote($value);
                break;

            case 'notEqual':
                return $this->db->quoteIdentifier($field) . ' <> ' . $this->db->quote($value);
                break;

            case 'greater':
                return $this->db->quoteIdentifier($field) . ' > ' . $this->db->quote($value);
                break;

            case 'greaterEqual':
                return $this->db->quoteIdentifier($field) . ' >= ' . $this->db->quote($value);
                break;

            case 'lower':
                return $this->db->quoteIdentifier($field) . ' < ' . $this->db->quote($value);
                break;

            case 'lowerEqual':
                return $this->db->quoteIdentifier($field) . ' <= ' . $this->db->quote($value);
                break;

            case 'like':
                return $this->db->quoteIdentifier($field) . ' LIKE ' . $this->db->quote('%' . $value . '%');
                break;

            case 'startsWith':
                return $this->db->quoteIdentifier($field) . ' LIKE ' . $this->db->quote($value . '%');
                break;

            case 'endsWith':
                return $this->db->quoteIdentifier($field) . ' LIKE ' . $this->db->quote('%' . $value);
                break;

            default:
                throw new NotImplementedException(sprintf('%s not implemented', $condition));
        }
    }
}
<?php

namespace NewsletterDefinitionBundle\AddressSourceAdapater;

use NewsletterDefinitionBundle\Model\NewsletterDefinition;
use Pimcore\Document\Newsletter\AddressSourceAdapterInterface;
use Pimcore\Document\Newsletter\SendingParamContainer;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Listing;

final class DefinitionAddressSourceAdapter implements AddressSourceAdapterInterface
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var int
     */
    protected $definitionId;


    /**
     * @var int
     */
    protected $elementsTotal;

    /**
     * @var Listing
     */
    protected $list;

    /**
     * IAddressSourceAdapter constructor.
     *
     * @param $params
     */
    public function __construct($params)
    {
        $this->class = $params['class'];
        $this->definitionId = $params['definition'];
    }

    /**
     * @return Listing
     */
    protected function getListing()
    {
        if (empty($this->list)) {
            $objectList = '\\Pimcore\\Model\\DataObject\\' . ucfirst($this->class) . '\\Listing';
            $this->list = new $objectList();

            if ($this->definitionId) {
                $definition = NewsletterDefinition::getById($this->definitionId);

                $condition = \Pimcore::getContainer()->get('newsletter_definition.sql_renderer')->renderDefinition($definition);

                if ($condition) {
                    $condition .= ' AND ';
                }

                $condition .= ' (newsletterActive = 1 AND newsletterConfirmed = 1)';

                $this->list->setCondition($condition);
            }

            $this->list->setOrderKey('email');
            $this->list->setOrder('ASC');

            $this->elementsTotal = $this->list->getTotalCount();
        }

        return $this->list;
    }

    /**
     * returns array of email addresses for batch sending
     *
     * @return SendingParamContainer[]
     */
    public function getMailAddressesForBatchSending()
    {
        $listing = $this->getListing();
        $ids = $listing->loadIdList();

        $class = ClassDefinition::getByName($this->class);
        $tableName = 'object_' . $class->getId();

        $db = \Pimcore\Db::get();
        $emails = $db->fetchCol("SELECT email FROM $tableName WHERE o_id IN (" . implode(',', $ids) . ')');

        $containers = [];
        foreach ($emails as $email) {
            $containers[] = new SendingParamContainer($email, ['emailAddress' => $email]);
        }

        return $containers;
    }

    /**
     * returns params to be set on mail for test sending
     *
     * @param string $emailAddress
     *
     * @return SendingParamContainer
     */
    public function getParamsForTestSending($emailAddress)
    {
        $listing = $this->getListing();
        $listing->setOrderKey('RAND()', false);
        $listing->setLimit(1);
        $listing->setOffset(0);

        $object = current($listing->load());

        return new SendingParamContainer($emailAddress, [
            'object' => $object
        ]);
    }

    /**
     * returns total number of newsletter recipients
     *
     * @return int
     */
    public function getTotalRecordCount()
    {
        $this->getListing();

        return $this->elementsTotal;
    }

    /**
     * returns array of params to be set on mail for single sending
     *
     * @param $limit
     * @param $offset
     *
     * @return SendingParamContainer[]
     */
    public function getParamsForSingleSending($limit, $offset)
    {
        $listing = $this->getListing();
        $listing->setLimit($limit);
        $listing->setOffset($offset);
        $objects = $listing->load();

        $containers = [];

        foreach ($objects as $object) {
            $containers[] = new SendingParamContainer($object->getEmail(), [
                'gender' => method_exists($object, 'getGender') ? $object->getGender() : '',
                'firstname' => method_exists($object, 'getFirstname') ? $object->getFirstname() : '',
                'lastname' => method_exists($object, 'getLastname') ? $object->getLastname() : '',
                'email' => $object->getEmail(),
                'token' => $object->getProperty('token'),
                'object' => $object
            ]);
        }

        return $containers;
    }
}
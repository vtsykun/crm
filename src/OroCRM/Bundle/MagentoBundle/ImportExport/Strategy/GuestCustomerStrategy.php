<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class GuestCustomerStrategy extends AbstractImportStrategy
{
    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        if ($customer = $this->checkExistingCustomer($entity)) {
            return null;
        }

        $this->cachedEntities = array();
        $entity = $this->beforeProcessEntity($entity);
        $entity = $this->processEntity($entity, true, true, $this->context->getValue('itemData'));
        $entity = $this->afterProcessEntity($entity);
        if ($entity) {
            $entity = $this->validateAndUpdateContext($entity);
        }

        return $entity;
    }

    /**
     * @param $entity
     *
     * @return Customer
     */
    protected function checkExistingCustomer($entity)
    {
        $this->assertEnvironment($entity);

        $itemData = $this->context->getValue('itemData');
        $email = $itemData['customerEmail'];

        $existingCustomer = $this->databaseHelper->findOneBy(
            'OroCRM\Bundle\MagentoBundle\Entity\Customer',
            ['email' => $email]
        );

        return $existingCustomer;
    }

    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        $this->processChangeAttributes($entity);

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Customer $entity
     */
    protected function processChangeAttributes(Customer $entity)
    {
        // todo CRM-3211
        $itemData = $this->context->getValue('itemData');

        $entity->setGuest(true);
        !empty($itemData['customerEmail']) && $entity->setEmail($itemData['customerEmail']);
        if (!empty($itemData['addresses'])) {
            $address = array_pop($itemData['addresses']);
            !empty($address['firstName']) && !$entity->getFirstName() && $entity->setFirstName($address['firstName']);
            !empty($address['lastName']) && !$entity->getLastName() && $entity->setLastName($address['lastName']);
        }
    }
}

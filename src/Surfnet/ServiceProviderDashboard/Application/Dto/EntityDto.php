<?php

/**
 * Copyright 2018 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\ServiceProviderDashboard\Application\Dto;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity as DomainEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact as Contact;
use Symfony\Component\Routing\RouterInterface;

class EntityDto
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $entityId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $contact;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $environment;

    /**
     * @param string $id
     * @param string $entityId
     * @param string $name
     * @param string $contact
     * @param string $state
     * @param string $environment
     */
    public function __construct($id, $entityId, $name, $contact, $state, $environment)
    {
        $this->id = $id;
        $this->entityId = $entityId;
        $this->name = $name;
        $this->contact = $contact;
        $this->state = $state;
        $this->environment = $environment;
    }

    public static function fromEntity(DomainEntity $entity)
    {
        $contact = $entity->getAdministrativeContact();

        $formattedContact = '';

        if ($contact) {
            $formattedContact = self::formatDashboardContact($contact);
        }

        return new self(
            $entity->getId(),
            $entity->getEntityId(),
            $entity->getNameEn(),
            $formattedContact,
            $entity->getStatus(),
            $entity->getEnvironment()
        );
    }

    public static function fromManageTestResult(array $result)
    {
        $metadata = $result['data']['metaDataFields'];

        $formattedContact = self::formatManageContact($metadata);

        return new self(
            $result['id'],
            $result['data']['entityid'],
            $metadata['name:en'],
            $formattedContact,
            'published',
            'test'
        );
    }

    public static function fromManageProductionResult(array $result)
    {
        $metadata = $result['data']['metaDataFields'];

        $formattedContact = self::formatManageContact($metadata);

        // As long as the coin:exclude_from_push metadata is present, allow modifications to the entity by
        // copying it from manage and merging the changes. The view status text: requested is set when an entity
        // can still be edited.
        $status = 'published';
        if (isset($metadata['coin:exclude_from_push']) && $metadata['coin:exclude_from_push'] == 1) {
            $status = 'requested';
        }

        return new self(
            $result['id'],
            $result['data']['entityid'],
            $metadata['name:en'],
            $formattedContact,
            $status,
            'production'
        );
    }

    /**
     * @return string
     */
    private static function formatManageContact(array $metadata)
    {
        for ($i=0; $i<=2; $i++) {
            $attrPrefix = sprintf('contacts:%d:', $i);

            if (isset($metadata[$attrPrefix . 'contactType']) && $metadata[$attrPrefix . 'contactType'] === 'administrative') {
                return sprintf(
                    '%s %s (%s)',
                    $metadata[$attrPrefix . 'givenName'],
                    $metadata[$attrPrefix . 'surName'],
                    $metadata[$attrPrefix . 'emailAddress']
                );
            }
        }

        return '';
    }

    /**
     * @param Contact $contact
     * @return string
     */
    private static function formatDashboardContact(Contact $contact)
    {
        return sprintf(
            '%s %s (%s)',
            $contact->getFirstName(),
            $contact->getLastName(),
            $contact->getEmail()
        );
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return 'SAML';
    }
}

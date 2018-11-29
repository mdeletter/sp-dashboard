<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Ramsey\Uuid\Uuid;
use Surfnet\ServiceProviderDashboard\Application\Dto\EntityDto;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Provider\EntityQueryRepositoryProvider;
use Surfnet\ServiceProviderDashboard\Application\ViewObject;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException;
use Symfony\Component\Routing\RouterInterface;

class EntityService implements EntityServiceInterface
{
    /**
     * @var EntityQueryRepositoryProvider
     */
    private $queryRepositoryProvider;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        EntityQueryRepositoryProvider $entityQueryRepositoryProvider,
        RouterInterface $router
    ) {
        $this->queryRepositoryProvider = $entityQueryRepositoryProvider;
        $this->router = $router;
    }

    public function createEntityUuid()
    {
        return (string)Uuid::uuid1();
    }

    public function getEntityById($id)
    {
        return $this->queryRepositoryProvider->getEntityRepository()->findById($id);
    }

    public function getEntityListForService(Service $service)
    {
        $entities = $this->getEntitiesForService($service);

        $result = [];
        foreach ($entities as $entity) {
            $result[] = new ViewObject\Entity($entity, $this->router);
        }

        return new ViewObject\EntityList($result);
    }

    public function getEntitiesForService(Service $service)
    {
        $entities = [];

        $draftEntities = $this->findDraftEntitiesByServiceId($service->getId());
        foreach ($draftEntities as $entity) {
            $entities[] = EntityDto::fromEntity($entity);
        }

        $testEntities = $this->findPublishedTestEntitiesByTeamName($service->getTeamName());
        foreach ($testEntities as $result) {
            $entities[] = EntityDto::fromManageTestResult($result);
        }

        $productionEntities = $this->findPublishedProductionEntitiesByTeamName($service->getTeamName());
        foreach ($productionEntities as $result) {
            $entities[] = EntityDto::fromManageProductionResult($result);
        }

        return $entities;
    }

    /**
     * @param string $manageId
     * @param string $env
     *
     * @return array|null
     *
     * @throws InvalidArgumentException
     * @throws QueryServiceProviderException
     */
    public function getManageEntityById($manageId, $env = 'test')
    {
        return $this->queryRepositoryProvider
            ->fromEnvironment($env)
            ->findByManageId($manageId);
    }

    /**
     * @param $serivceid
     * @return Entity[]
     */
    private function findDraftEntitiesByServiceId($serivceid)
    {
        return $this->queryRepositoryProvider
            ->getEntityRepository()
            ->findByServiceId($serivceid);
    }

    /**
     * @param string $teamName
     * @return array|null
     * @throws QueryServiceProviderException
     */
    private function findPublishedTestEntitiesByTeamName($teamName)
    {
        return $this->queryRepositoryProvider
            ->getManageTestQueryClient()
            ->findByTeamName($teamName);
    }

    /**
     * @param $teamName
     * @return array|null
     * @throws QueryServiceProviderException
     */
    private function findPublishedProductionEntitiesByTeamName($teamName)
    {
        return $this->queryRepositoryProvider
            ->getManageProductionQueryClient()
            ->findByTeamName($teamName);
    }
}

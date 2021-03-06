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

namespace Surfnet\ServiceProviderDashboard\Domain\Repository;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

interface EntityRepository
{
    /**
     * @param Entity $entity
     */
    public function save(Entity $entity);

    public function delete(Entity $entity);

    /**
     * @param int $id
     *
     * @return bool
     */
    public function isUnique($id);

    /**
     * @param int $id
     *
     * @return Entity|null
     */
    public function findById($id);

    /**
     * @param int $serviceId
     *
     * @return Entity[]
     */
    public function findByServiceId($serviceId);

    /**
     * @param string $status
     * @param string $environment
     * @param int $limit
     *
     * @return Entity[]
     */
    public function findByState($status, $environment, $limit = 10);
}

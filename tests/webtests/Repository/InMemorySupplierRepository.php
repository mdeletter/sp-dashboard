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

namespace Surfnet\ServiceProviderDashboard\Webtests\Repository;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository as SupplierRepositoryInterface;

class InMemorySupplierRepository implements SupplierRepositoryInterface
{
    private static $memory = [];

    public function clear()
    {
        self:$memory = [];
    }

    /**
     * @param Supplier $supplier
     */
    public function save(Supplier $supplier)
    {
        self::$memory[] = $supplier;
    }

    /**
     * @param Supplier $supplier
     * @return bool
     */
    public function isUnique(Supplier $supplier)
    {
        return true;
    }

    /**
     * @return Supplier[]
     */
    public function findAll()
    {
        return self::$memory;
    }
}
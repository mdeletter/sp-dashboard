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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthorizationServiceTest extends MockeryTestCase
{
    /** @var Session|m\MockInterface */
    private $session;

    /** @var TokenStorageInterface|m\MockInterface */
    private $tokenStorage;

    public function setUp()
    {
        $this->session = m::mock(Session::class);
        $this->tokenStorage = m::mock(TokenStorageInterface::class);
        $this->service = new AuthorizationService($this->session, $this->tokenStorage);
    }

    /**
     * @group Service
     */
    public function test_service_writes_selected_service_to_session()
    {
        $this->session->shouldReceive('set')
            ->with('selected_service_id', 'test');

        $this->service->setSelectedServiceId('test');
    }

    /**
     * @group Service
     */
    public function test_service_reads_selected_service_from_session()
    {
        $this->session->shouldReceive('get')
            ->andReturn('test');

        $this->assertEquals('test', $this->service->getSelectedServiceId());
    }
}

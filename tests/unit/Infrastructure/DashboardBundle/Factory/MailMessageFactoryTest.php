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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Mailer;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\MailMessageFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Mailer\Message;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MailMessageFactoryTest extends MockeryTestCase
{
    /**
     * @var EngineInterface|Mock
     */
    private $templateEngine;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var MailMessageFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->templateEngine = m::mock(EngineInterface::class);
        $this->translator = m::mock(TranslatorInterface::class);

        $this->factory = new MailMessageFactory(
            'john@example.com',
            'doe@example.com',
            'no-reply@example.com',
            $this->translator,
            $this->templateEngine
        );
    }

    public function test_build_publish_to_production_message()
    {
        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getEntityId')
            ->once()
            ->andReturn('1123132-1231231-12312312123');

        $this->translator
            ->shouldReceive('trans');

        $this->templateEngine
            ->shouldReceive('render');

        $message = $this->factory->buildPublishToProductionMessage($entity);
        $this->assertInstanceOf(Message::class, $message);
    }
}

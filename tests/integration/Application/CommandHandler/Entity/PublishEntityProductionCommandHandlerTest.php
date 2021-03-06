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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Entity;

use League\Tactician\CommandBus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\PublishEntityProductionCommandHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Mailer\Message;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\MailMessageFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Token\SamlToken;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Identity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PublishEntityProductionCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var PublishEntityProductionCommandHandler
     */
    private $commandHandler;

    /**
     * @var EntityRepository|Mock
     */
    private $repository;

    /**
     * @var PublishEntityRepository|Mock
     */
    private $publishEntityClient;

    /**
     * @var CommandBus|Mock
     */
    private $commandBus;

    /**
     * @var MailMessageFactory|Mock
     */
    private $mailMessageFactory;

    /**
     * @var TokenStorageInterface|Mock
     */
    private $tokenStorage;

    /**
     * @var FlashBagInterface|Mock
     */
    private $flashBag;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    public function setUp()
    {

        $this->repository = m::mock(EntityRepository::class);
        $this->publishEntityClient = m::mock(PublishEntityRepository::class);
        $this->commandBus = m::mock(CommandBus::class);
        $this->mailMessageFactory = m::mock(MailMessageFactory::class);
        $this->tokenStorage = m::mock(TokenStorageInterface::class);
        $this->flashBag = m::mock(FlashBagInterface::class);
        $this->logger = m::mock(LoggerInterface::class);

        $this->commandHandler = new PublishEntityProductionCommandHandler(
            $this->repository,
            $this->publishEntityClient,
            $this->commandBus,
            $this->mailMessageFactory,
            $this->tokenStorage,
            $this->flashBag,
            $this->logger
        );


        parent::setUp();
    }

    public function test_it_can_publish()
    {
        $contact = new Contact('nameid', 'name@example.org', 'display name');
        $user = new Identity($contact);

        $token = new SamlToken([]);
        $token->setUser($user);

        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getNameNl')
            ->andReturn('Test Entity Name');

        $entity
            ->shouldReceive('setStatus')
            ->with(Entity::STATE_PUBLISHED);

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $this->repository
            ->shouldReceive('save')
            ->with($entity);

        $this->publishEntityClient
            ->shouldReceive('publish')
            ->once()
            ->with($entity)
            ->andReturn([
                'id' => 123,
            ]);

        $this->commandBus
            ->shouldReceive('handle')
            ->once();

        $this->tokenStorage->shouldReceive('getToken')
            ->andReturn($token);

        $message = m::mock(Message::class);

        $this->mailMessageFactory->shouldReceive('buildPublishToProductionMessage')
            ->with($entity, $contact)
            ->andReturn($message);

        $this->logger
            ->shouldReceive('info')
            ->times(3);


        $command = new PublishEntityProductionCommand('d6f394b2-08b1-4882-8b32-81688c15c489');
        $this->commandHandler->handle($command);
    }

    public function test_it_handles_failing_publish()
    {
        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getNameNl')
            ->andReturn('Test Entity Name');

        $entity
            ->shouldReceive('setStatus')
            ->with(Entity::STATE_PUBLISHED);

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $this->repository
            ->shouldReceive('save')
            ->with($entity);

        $this->logger
            ->shouldReceive('info')
            ->times(2);

        $this->logger
            ->shouldReceive('error')
            ->times(1);

        $this->publishEntityClient
            ->shouldReceive('publish')
            ->once()
            ->with($entity)
            ->andThrow(PublishMetadataException::class);

        $this->flashBag
            ->shouldReceive('add')
            ->with('error', 'entity.edit.error.publish');

        $command = new PublishEntityProductionCommand('d6f394b2-08b1-4882-8b32-81688c15c489');
        $this->commandHandler->handle($command);
    }
}

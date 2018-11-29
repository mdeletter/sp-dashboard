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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\Jira\Factory;

use PHPUnit_Framework_TestCase;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\IssueFieldFactory;

class IssueFieldFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var IssueFieldFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new IssueFieldFactory();
    }

    public function test_build_issue_field_from_ticket()
    {
        $ticket = new Ticket('Summary', 'Description', 'https://example.com', 'John Doe', 'john@example.com');
        $issueField = $this->factory->fromTicket($ticket);

        $this->assertEquals('Summary', $issueField->summary);
        $this->assertEquals('Description', $issueField->description);
        $this->assertEquals('https://example.com', $issueField->customFields['customfield_13018']);
        $this->assertEquals('John Doe', $issueField->customFields['customfield_11111']);
        $this->assertEquals('john@example.com', $issueField->customFields['customfield_22222']);
        $this->assertEquals('conext-beheer', $issueField->assignee->name);
        $this->assertEquals('spd-delete-production-entity', $issueField->getIssueType()->name);
        $this->assertEquals('Medium', $issueField->priority->name);
        $this->assertEquals('sp-dashboard', $issueField->reporter->name);
    }
}
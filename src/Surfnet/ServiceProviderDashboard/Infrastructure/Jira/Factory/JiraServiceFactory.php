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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory;

use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\IssueService;

class JiraServiceFactory
{
    /**
     * @var ArrayConfiguration
     */
    private $config;

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     */
    public function __construct($host, $username, $password)
    {
        // Create a IssueService with a Jira connection built in.
        $this->config = new ArrayConfiguration([
            'jiraHost' => $host,
            'jiraUser' => $username,
            'jiraPassword' => $password
        ]);
    }

    public function buildIssueService()
    {
        return new IssueService($this->config);
    }
}

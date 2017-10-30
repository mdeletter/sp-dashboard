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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client;

use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

/**
 * The QueryClient can be used to perform queries on the manage /manage/api/internal/search/saml20_sp endpoint. Queries
 * will return a hard coded return set per application.
 *
 * Example response (json formatted for readability)
 *  [{
 *      "_id": "db2e5c63-3c54-4962-bf4a-d6ced1e9cf33",
 *      "version": 0,
 *      "data": {
 *          "entityid": "https://example.com/saml/metadata",
 *          "state": "prodaccepted",
 *          "metaDataFields": {
 *              "name:en": "My example SP"
 *          }
 *      }
 *  }]
 */
class QueryClient implements QueryEntityRepository
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $entityId
     *
     * @return array|null
     *
     * @throws QueryServiceProviderException
     */
    public function findByEntityId($entityId)
    {
        try {
            // Queries the SP registry and asks for the English name in addition to the regular data
            $query = json_encode([
                'entityid' => $entityId,
                "REQUESTED_ATTRIBUTES" => ['metaDataFields.name:en']
            ]);
            $results = $this->client->post($query, '/manage/api/internal/search/saml20_sp');
            return $results;
        } catch (HttpException $e) {
            throw new QueryServiceProviderException(
                sprintf('Unable to find Service Provider with entityId: "%s"', $entityId)
            );
        }
    }
}
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

namespace Surfnet\ServiceProviderDashboard\Webtests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EditSupplierTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->getAuthorizationService()->setAdminSwitcherSupplierId(
            $this->getSupplierRepository()->findByName('SURFnet')->getId()
        );
    }

    public function test_can_edit_existing_supplier()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $formData = [
            'dashboard_bundle_edit_supplier_type' => [
                'guid' => 'f1af6b9e-2546-4593-a57f-6ca34d2561e9',
                'name' => 'The A Team',
                'teamName' => 'team-a',
            ]
        ];

        $crawler = $this->client->request('GET', '/supplier/edit');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        $supplier = $this->getSupplierRepository()->findAll()[0];

        $this->assertEquals('f1af6b9e-2546-4593-a57f-6ca34d2561e9', $supplier->getGuid());
        $this->assertEquals('The A Team', $supplier->getName());
        $this->assertEquals('team-a', $supplier->getTeamName());
    }
}
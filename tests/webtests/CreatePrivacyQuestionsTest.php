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

use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreatePrivacyQuestionsTest extends WebTestCase
{
    public function test_it_can_display_the_form()
    {
        $this->loadFixtures();

        $serviceRepository = $this->getServiceRepository();
        $service = $serviceRepository->findByName('SURFnet');

        $this->logIn('ROLE_USER', [$service]);

        $crawler = $this->client->request('GET', '/service/privacy');

        $this->assertEquals('GDPR related questions', $crawler->filter('h1')->first()->text());
        $formRows = $crawler->filter('div.form-row');
        $this->assertCount(14, $formRows);
    }

    public function test_it_can_submit_the_form()
    {
        $this->loadFixtures();

        $serviceRepository = $this->getServiceRepository();
        $service = $serviceRepository->findByName('SURFnet');

        $this->logIn('ROLE_USER', [$service]);

        $crawler = $this->client->request('GET', '/service/privacy');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $formData = [
            'dashboard_bundle_privacy_questions_type' => [
                'accessData' => 'Some data will be accessed',
                'country' => 'The Netherlands',
                'certification' => true,
                'certificationValidTo' => [
                    'year' => 2022,
                    'month' => 12,
                    'day' => 31,
                ],
                'privacyPolicyUrl' => 'http://example.org/privacy',
            ],
        ];

        $this->client->submit($form, $formData);

        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);

        $crawler = $this->client->followRedirect();

        $formRows = $crawler->filter('div.form-row');
        $this->assertCount(14, $formRows);

        $this->assertEquals(
            'Some data will be accessed',
            $crawler->filter('#dashboard_bundle_privacy_questions_type_accessData')->text()
        );

        $this->assertEquals(
            'The Netherlands',
            $crawler->filter('#dashboard_bundle_privacy_questions_type_country')->text()
        );

        $this->assertEquals(
            2022,
            $crawler
                ->filter('#dashboard_bundle_privacy_questions_type_certificationValidTo_year option:selected')
                ->first()
                ->text()
        );

        $this->assertEquals(
            'Dec',
            $crawler
                ->filter('select#dashboard_bundle_privacy_questions_type_certificationValidTo_month option:selected')
                ->first()
                ->text()
        );

        $this->assertEquals(
            31,
            $crawler
                ->filter('select#dashboard_bundle_privacy_questions_type_certificationValidTo_day option:selected')
                ->first()
                ->text()
        );
    }
}

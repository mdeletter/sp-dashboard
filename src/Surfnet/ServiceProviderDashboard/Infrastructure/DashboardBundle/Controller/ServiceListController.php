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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AdminSwitcherService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\SamlServiceService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ServiceListController extends Controller
{
    /**
     * @var AdminSwitcherService
     */
    private $adminSwitcherService;

    /**
     * @var SamlServiceService
     */
    private $samlServiceService;

    /**
     * @param AdminSwitcherService $adminSwitcherService
     * @param SamlServiceService $samlServiceService
     */
    public function __construct(AdminSwitcherService $adminSwitcherService, SamlServiceService $samlServiceService)
    {
        $this->adminSwitcherService = $adminSwitcherService;
        $this->samlServiceService = $samlServiceService;
    }

    /**
     * @Method("GET")
     * @Route("/", name="service_list")
     * @Template()
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function listAction()
    {
        $supplierOptions = $this->adminSwitcherService->getSupplierOptions();

        if (empty($supplierOptions)) {
            return $this->redirectToRoute('supplier_add');
        }

        $selectedSupplierId = $this->adminSwitcherService->getSelectedSupplier();

        return [
            'no_supplier_selected' => empty($selectedSupplierId),
            'service_list' => $this->samlServiceService->getServiceListForSupplier($selectedSupplierId),
        ];
    }
}

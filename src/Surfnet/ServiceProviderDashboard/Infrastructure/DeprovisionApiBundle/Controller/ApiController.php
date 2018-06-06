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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DeprovisionApiBundle\Controller;

use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Surfnet\ServiceProviderDashboard\Application\Service\DeprovisionService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DeprovisionApiBundle\Exception\ApiAccessDeniedHttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DeprovisionApiBundle\Exception\ApiMethodNotAllowedHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ApiController extends Controller
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var DeprovisionService
     */
    private $deprovisionService;

    /**
     * @var string
     */
    private $applicationName;

    public function __construct(
        LoggerInterface $logger,
        CommandBus $commandBus,
        DeprovisionService $deprovisionService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->logger = $logger;
        $this->commandBus = $commandBus;
        $this->deprovisionService = $deprovisionService;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @Route("/deprovision/{collabPersonId}", defaults={"collabPersonId" = null})
     * @Method({"GET", "DELETE"})
     *
     * @param Request $request
     * @param $collabPersonId
     * @return JsonResponse
     */
    public function userDataAction(Request $request, $collabPersonId)
    {
        $this->assertRequestMethod($request, [Request::METHOD_GET, Request::METHOD_DELETE]);
        $this->assertUserHasDeprovisionRole();

        $userData = $this->deprovisionService->read($collabPersonId);

        if ($request->isMethod(Request::METHOD_DELETE)) {
            $this->deprovisionService->delete($collabPersonId);
        }
        return $this->createResponse('OK', $userData);
    }

//    public function dryRunAction(Request $request, $collabPersonId)
//    {
//
//    }

    /**
     * @param string $status
     * @param array $userData
     * @param string|null $message
     * @return JsonResponse
     */
    private function createResponse($status, array $userData, $message = null)
    {
        $responseData = [
            'status'  => $status,
            'name'    => $this->applicationName,
            'data'    => $userData,
        ];

        if ($message !== null) {
            $responseData['message'] = $message;
        }

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    /**
     * @throws ApiAccessDeniedHttpException
     */
    private function assertUserHasDeprovisionRole()
    {
        if (!$this->authorizationChecker->isGranted('ROLE_API_USER_DEPROVISION')) {
            throw new ApiAccessDeniedHttpException(
                'Access to the deprovision API requires the role ROLE_API_USER_DEPROVISION'
            );
        }
    }

    /**
     * @param Request $request
     * @param array $expectedMethods
     *
     * @throws ApiMethodNotAllowedHttpException
     */
    private function assertRequestMethod(Request $request, array $expectedMethods)
    {
        foreach ($expectedMethods as $expectedMethod) {
            if ($request->isMethod($expectedMethod)) {
                return;
            }
        }

        throw ApiMethodNotAllowedHttpException::methodNotAllowed($request->getMethod(), $expectedMethods);
    }
}

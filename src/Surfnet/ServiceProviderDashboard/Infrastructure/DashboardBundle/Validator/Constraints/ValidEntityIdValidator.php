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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints;

use Pdp\Parser;
use Pdp\PublicSuffixListManager;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\EditEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryEntityRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class ValidEntityIdValidator extends ConstraintValidator
{
    /**
     * @var QueryEntityRepository
     */
    private $repository;

    /**
     * @param QueryEntityRepository $repository
     */
    public function __construct(QueryEntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string     $value
     * @param Constraint $constraint
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function validate($value, Constraint $constraint)
    {
        $root = $this->context->getRoot();

        if ($root instanceof EditEntityCommand) {
            $entityCommand = $root;
        } else {
            $entityCommand = $root->getData();
        }

        if (!$entityCommand->isDraft()) {
            return;
        }

        $metadataUrl = $entityCommand->getMetadataUrl();

        if (empty($metadataUrl) || empty($value)) {
            return;
        }

        $pslManager = new PublicSuffixListManager();
        $parser = new Parser($pslManager->getList());

        try {
            $parser->parseUrl($metadataUrl);
        } catch (\Exception $e) {
            $this->context->addViolation('Invalid metadataUrl.');
            return;
        }

        try {
            $parser->parseUrl($value);
        } catch (\Exception $e) {
            $this->context->addViolation('Invalid entityId.');
            return;
        }

        if ($entityCommand->isForProduction()) {
            return;
        }

        try {
            $entity = $this->repository->findByEntityId($value);
        } catch (\Exception $e) {
            $this->context->addViolation('Failed checking registry.');
            return;
        }

        if (empty($entity)) {
            return;
        }

        $this->context->addViolation('Entity has already been registered.');
    }
}
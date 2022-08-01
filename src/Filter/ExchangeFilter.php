<?php
// api/src/Filter/RegexpFilter.php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use App\Repository\GameRepository;
use Symfony\Component\PropertyInfo\Type;
use App\lib\IgdbBundle\IgdbWrapper;

final class ExchangeFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // otherwise filter is applied to order and page as well
        // if (
        //     !$this->isPropertyEnabled($property, $resourceClass) ||
        //     !$this->isPropertyMapped($property, $resourceClass)
        // ) {
        //     return;
        // }

        // if ($property !== 'search') {
        //     return;
        // }

        $alias = $queryBuilder->getRootAliases()[0];
        $parameterName = $queryNameGenerator->generateParameterName($property); // Generate a unique parameter name to avoid collisions with other filters
 

        if ($property  == 'pending') {
            $queryBuilder = $queryBuilder->where("$alias.confirmed IS NULL");  
        }

        if ($property  == 'answered') {
            $queryBuilder = $queryBuilder->where("$alias.confirmed IS FALSE")->orWhere("$alias.confirmed IS TRUE");;  
        }

    }

    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["filter_$property"] = [
                'property' => $property,
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'swagger' => [
                    'description' => 'Filter games. This will appear in the Swagger documentation!',
                    'name' => 'Custom name to use in the Swagger documentation',
                    'type' => 'Will appear below the name in the Swagger documentation',
                ],
            ];
        }

        return $description;
    }
}
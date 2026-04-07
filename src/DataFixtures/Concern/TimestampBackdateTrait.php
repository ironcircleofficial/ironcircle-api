<?php

declare(strict_types=1);

namespace App\DataFixtures\Concern;

use DateTimeImmutable;
use Doctrine\Instantiator\Instantiator;
use ReflectionClass;

trait TimestampBackdateTrait
{
    private function buildViaReflection(string $class, array $fields): object
    {
        $instantiator = new Instantiator();
        $obj = $instantiator->instantiate($class);
        $ref = new ReflectionClass($class);

        foreach ($fields as $fieldName => $value) {
            if ($fieldName === 'id' || !$ref->hasProperty($fieldName)) {
                continue;
            }
            $prop = $ref->getProperty($fieldName);
            $prop->setAccessible(true);
            $prop->setValue($obj, $value);
        }

        return $obj;
    }

    private function randomPastDate(int $maxDaysAgo): DateTimeImmutable
    {
        $secondsAgo = random_int(0, $maxDaysAgo * 86400);
        return new DateTimeImmutable("-{$secondsAgo} seconds");
    }
}

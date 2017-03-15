<?php
declare(strict_types = 1);

namespace BroadwaySerialization\Test\Performance;

use BroadwaySerialization\Hydration\HydrateUsingReflection;
use BroadwaySerialization\Reconstitution\ReconstituteUsingInstantiatorAndHydrator;
use BroadwaySerialization\Reconstitution\Reconstitution;
use Doctrine\Instantiator\Instantiator;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Groups;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;

final class ReconstitutionBench
{
    private $deserializationData = [
        'stringProperty' => 'foo',
        'integerProperty' => 20,
        'nullProperty' => null,
        'arrayProperty' => ['foo' => 'bar', 'bar' => 'baz'],
        'objectProperty' => [
            'foo' => 'foo',
        ],
        'objectsProperty' => [
            [
                'foo' => 'bar',
            ],
            [
                'foo' => 'baz',
            ],
        ]
    ];

    public function setup()
    {
        $reconstitute = new ReconstituteUsingInstantiatorAndHydrator(new Instantiator(), new HydrateUsingReflection());

        // test run, for calibration
        $reconstitute->objectFrom(SerializableClassUsingTrait::class, []);
        $reconstitute->objectFrom(SomeOtherSerializableClassUsingTrait::class, []);

        Reconstitution::reconstituteUsing($reconstitute);
    }

    /**
     * @Warmup(10)
     * @Revs(100000)
     * @Groups({"traditional"})
     */
    public function benchDeserializeTraditionalObject()
    {
        TraditionalSerializableClass::deserialize($this->deserializationData);
    }

    /**
     * @Warmup(10)
     * @Revs(100000)
     * @Groups({"trait"})
     * @BeforeMethods({"setup"})
     */
    public function benchDeserializeObjectUsingTrait()
    {
        SerializableClassUsingTrait::deserialize($this->deserializationData);
    }
}

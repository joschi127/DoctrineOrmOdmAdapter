<?php

namespace Doctrine\Tests\ORM\ODMAdapter\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\ORM\ODMAdapter\Mapping\ClassMetadata;

abstract class AbstractMappingDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    abstract protected function loadDriver();
    /**
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    abstract protected function loadDriverForTestMappingDocuments();

    protected function ensureIsLoaded($entityClassName)
    {
        new $entityClassName;
    }

    /**
     * Returns a ClassMetadata object for the given class,
     * loaded using the driver associated with a concrete child of this class.
     *
     * @param string $className
     * @return \Doctrine\ODM\PHPCR\Mapping\ClassMetadata
     */
    protected function loadMetadataForClassname($className)
    {
        $mappingDriver = $this->loadDriver();

        $class = new ClassMetadata($className);
        $class->initializeReflection(new RuntimeReflectionService());
        $mappingDriver->loadMetadataForClass($className, $class);

        return $class;
    }

    public function testLoadMetadataForNonDocumentThrowsException()
    {
        $cm = new ClassMetadata('stdClass');
        $cm->initializeReflection(new RuntimeReflectionService());

        $driver = $this->loadDriver();

        $this->setExpectedException('Doctrine\ORM\ODMAdapter\Mapping\MappingException');
        $driver->loadMetadataForClass('stdClass', $cm);
    }

    public function testGetAllClassNamesIsIdempotent()
    {
        $driver = $this->loadDriverForTestMappingDocuments();
        $original = $driver->getAllClassNames();

        $driver = $this->loadDriverForTestMappingDocuments();
        $afterTestReset = $driver->getAllClassNames();

        $this->assertEquals($original, $afterTestReset);
    }

    public function testGetAllClassNamesReturnsAlreadyLoadedClassesIfAppropriate()
    {
        $rightClassName = 'Doctrine\Tests\ORM\ODMAdapter\Mapping\Driver\Model\CommonFieldMappingObject';
        $this->ensureIsLoaded($rightClassName);

        $driver = $this->loadDriverForTestMappingDocuments();
        $classes = $driver->getAllClassNames();

        $this->assertContains($rightClassName, $classes);
    }

    public function testGetAllClassNamesReturnsOnlyTheAppropriateClasses()
    {
        $extraneousClassName = 'Doctrine\Tests\Models\ECommerce\ECommerceCart';
        $this->ensureIsLoaded($extraneousClassName);

        $driver = $this->loadDriverForTestMappingDocuments();
        $classes = $driver->getAllClassNames();

        $this->assertNotContains($extraneousClassName, $classes);
    }

    /**
     * @covers Doctrine\ODM\PHPCR\Mapping\Driver\XmlDriver::loadMetadataForClass
     */
    public function testLoadFieldMapping()
    {
        $className = 'Doctrine\Tests\ORM\ODMAdapter\Mapping\Driver\Model\CommonFieldMappingObject';

        return $this->loadMetadataForClassName($className);
    }

    /**
     * @depends testLoadFieldMapping
     * @param ClassMetadata $class
     * @return \Doctrine\ORM\ODMAdapter\Mapping\ClassMetadata
     */
    public function testFieldMappings($class)
    {
        $this->assertCount(3, $class->mappings);
        $this->assertCount(1, $class->commonFieldMappings);
        $this->assertTrue(isset($class->mappings['uuid']));
        $this->assertEquals('uuid', $class->mappings['uuid']['type']);
        $this->assertTrue(isset($class->mappings['document']));
        $this->assertEquals('document', $class->mappings['document']['type']);
        $this->assertTrue(isset($class->mappings['entityName']));
        $this->assertEquals('common-field', $class->mappings['entityName']['type']);

        return $class;
    }

    /**
     * @depends testLoadFieldMapping
     * @param   ClassMetadata $class
     */
    public function testUuidFieldMapping($class)
    {
        $this->assertEquals('uuid', $class->mappings['uuid']['property']);
        $this->assertEquals('uuid', $class->mappings['uuid']['type']);

    }

    /**
     * @depends testLoadFieldMapping
     * @param   ClassMetadata $class
     */
    public function testDocumentFieldMapping($class)
    {
        $this->assertEquals('document', $class->mappings['document']['property']);
        $this->assertEquals('document', $class->mappings['document']['type']);

    }

    /**
     * @depends testLoadFieldMapping
     * @param   ClassMetadata $class
     */
    public function testcommonFieldMapping($class)
    {
        $this->assertEquals('common-field', $class->mappings['entityName']['type']);
        $this->assertEquals('entityName', $class->mappings['entityName']['property']);
        $this->assertEquals('docName', $class->mappings['entityName']['document-name']);
    }
} 
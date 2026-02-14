<?php

namespace Dcat\Admin\Tests;

use PHPUnit\Framework\TestCase;

class PHP83CompatibilityTest extends TestCase
{
    /**
     * Test PHP version is at least 8.3
     */
    public function test_php_version_is_at_least_8_3()
    {
        $this->assertTrue(
            version_compare(PHP_VERSION, '8.3.0', '>='),
            'PHP version must be at least 8.3.0, current: ' . PHP_VERSION
        );
    }

    /**
     * Test AllowDynamicProperties attribute exists
     */
    public function test_allow_dynamic_properties_attribute_exists()
    {
        $this->assertTrue(
            class_exists(\AllowDynamicProperties::class),
            'AllowDynamicProperties attribute must exist (PHP 8.2+)'
        );
    }

    /**
     * Test ModelTree trait can be loaded
     */
    public function test_model_tree_trait_can_be_loaded()
    {
        $this->assertTrue(
            trait_exists(\Dcat\Admin\Traits\ModelTree::class),
            'ModelTree trait must be loadable'
        );
    }

    /**
     * Test ModelTree trait uses setAttribute/getAttribute methods
     */
    public function test_model_tree_uses_eloquent_attribute_methods()
    {
        $reflectionClass = new \ReflectionClass(\Dcat\Admin\Traits\ModelTree::class);
        $content = file_get_contents($reflectionClass->getFileName());

        // Should not contain dynamic property access patterns
        $dynamicPropertyPatterns = [
            '/\$this->\$[a-zA-Z_]+\s*=\s*/',  // $this->$var = 
            '/->where\([^,]+,\s*\$this->\$[a-zA-Z_]+\)/',  // ->where($col, $this->$var)
        ];

        foreach ($dynamicPropertyPatterns as $pattern) {
            $this->assertDoesNotMatchRegularExpression(
                $pattern,
                $content,
                'ModelTree trait should not use dynamic property access patterns'
            );
        }

        // Should use setAttribute/getAttribute methods
        $this->assertStringContainsString(
            'setAttribute',
            $content,
            'ModelTree trait should use setAttribute method'
        );
        $this->assertStringContainsString(
            'getAttribute',
            $content,
            'ModelTree trait should use getAttribute method'
        );
    }
}

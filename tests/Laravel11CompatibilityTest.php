<?php

namespace Dcat\Admin\Tests;

use PHPUnit\Framework\TestCase;

class Laravel11CompatibilityTest extends TestCase
{
    /**
     * Test Administrator model uses Authenticatable trait
     */
    public function test_administrator_uses_authenticatable_trait()
    {
        $this->assertTrue(
            in_array(
                \Illuminate\Auth\Authenticatable::class,
                class_uses(\Dcat\Admin\Models\Administrator::class)
            ),
            'Administrator model must use Authenticatable trait'
        );
    }

    /**
     * Test Administrator model implements Authenticatable contract
     */
    public function test_administrator_implements_authenticatable_contract()
    {
        $this->assertTrue(
            is_subclass_of(
                \Dcat\Admin\Models\Administrator::class,
                \Illuminate\Contracts\Auth\Authenticatable::class
            ),
            'Administrator model must implement Authenticatable contract'
        );
    }

    /**
     * Test Authenticatable trait has getAuthPasswordName method
     */
    public function test_authenticatable_trait_has_get_auth_password_name_method()
    {
        $reflection = new \ReflectionClass(\Illuminate\Auth\Authenticatable::class);
        $this->assertTrue(
            $reflection->hasMethod('getAuthPasswordName'),
            'Authenticatable trait must have getAuthPasswordName method (Laravel 11+)'
        );
    }

    /**
     * Test AdminServiceProvider extends Laravel ServiceProvider
     */
    public function test_admin_service_provider_extends_laravel_service_provider()
    {
        $this->assertTrue(
            is_subclass_of(
                \Dcat\Admin\AdminServiceProvider::class,
                \Illuminate\Support\ServiceProvider::class
            ),
            'AdminServiceProvider must extend Laravel ServiceProvider'
        );
    }

    /**
     * Test Extend ServiceProvider extends Laravel ServiceProvider
     */
    public function test_extend_service_provider_extends_laravel_service_provider()
    {
        $this->assertTrue(
            is_subclass_of(
                \Dcat\Admin\Extend\ServiceProvider::class,
                \Illuminate\Support\ServiceProvider::class
            ),
            'Extend ServiceProvider must extend Laravel ServiceProvider'
        );
    }
}

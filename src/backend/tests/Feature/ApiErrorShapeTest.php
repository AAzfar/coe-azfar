<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ApiErrorShapeTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_missing_model_returns_not_found_without_internal_class_names(): void
    {
        $this->getJson('/api/products/999')
            ->assertNotFound()
            ->assertExactJson(['error' => ['status' => 404, 'code' => 'not_found', 'message' => 'Product not found.']]);
    }

    public function test_unknown_api_route_returns_the_error_shape_even_without_json_accept_header(): void
    {
        $this->get('/api/nope')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found');
    }

    public function test_wrong_http_method_returns_method_not_allowed(): void
    {
        $this->putJson('/api/products')
            ->assertMethodNotAllowed()
            ->assertJsonPath('error.code', 'method_not_allowed');
    }

    public function test_unexpected_errors_hide_the_exception_and_stack_trace(): void
    {
        config(['app.debug' => true]);
        Route::get('/api/boom', fn () => throw new RuntimeException('database password is hunter2'));

        $this->getJson('/api/boom')
            ->assertInternalServerError()
            ->assertExactJson(['error' => [
                'status' => 500,
                'code' => 'server_error',
                'message' => 'Something went wrong on our side. Please try again.',
            ]]);
    }
}

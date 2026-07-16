<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use Illuminate\Support\Facades\Validator;
use MetaFramework\Http\Requests\ExecuteSqlQueryRequest;
use Tests\TestCase;

class ExecuteSqlQueryRequestTest extends TestCase
{
    public function test_only_developers_and_super_administrators_are_authorized(): void
    {
        $request = ExecuteSqlQueryRequest::create('/panel/sql-query', 'POST');
        $request->setUserResolver(static fn () => new class
        {
            public function hasRole(string $roles): bool
            {
                return $roles === 'dev|super-admin';
            }
        });

        $this->assertTrue($request->authorize());

        $request->setUserResolver(static fn () => new class
        {
            public function hasRole(string $roles): bool
            {
                return false;
            }
        });

        $this->assertFalse($request->authorize());
    }

    public function test_return_path_must_be_a_local_absolute_path(): void
    {
        $request = new ExecuteSqlQueryRequest;
        $validator = Validator::make([
            'sql_query' => 'SELECT 1',
            'return_path' => '//external.example/path',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('return_path', $validator->errors()->toArray());
    }
}

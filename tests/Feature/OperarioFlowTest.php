<?php

namespace Tests\Feature;

use Tests\TestCase;

class OperarioFlowTest extends TestCase
{
    public function test_cliente_requires_operario_session(): void
    {
        $response = $this->get('/cliente');

        $response->assertRedirect('/login');
    }
}

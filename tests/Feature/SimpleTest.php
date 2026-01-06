<?php
namespace Tests\Feature;
use Tests\TestCase;
class SimpleTest extends TestCase
{
    public function test_home_page()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}

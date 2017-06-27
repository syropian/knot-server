<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Knot\Models\User;
use Doorman;

class AuthenticatesUserTest extends TestCase
{
    use DatabaseMigrations;

    
    /** @test */
    function can_register_a_new_user()
    {
        $this->withExceptionHandling();
        
        $userData =  [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@janedoe.com',
            'password' => 'foobar',
            'code' => Doorman::generate()->for('jane@janedoe.com')->make()->first()->code
        ];
        $response = $this->json('POST', 'api/auth/user', $userData);

        $response->assertStatus(200);

        $this->assertEquals(1, User::count());
    }
    
    /** @test */
    function can_fetch_an_authenticated_user()
    {
        $this->authenticate();
        
        $response = $this->get('api/auth/user');

        $response->assertStatus(200);
    }
    
}
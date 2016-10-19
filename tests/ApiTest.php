<?php

use App\Jobs\UpdateBatteryState;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;

class ApiTest extends TestCase
{
    use DatabaseMigrations, DatabaseTransactions;

    /**
     * @var User
     */
    private $user;

    protected function setUp()
    {
        parent::setUp();

        $apiToken = str_random(60);
        $this->user = factory(User::class)->create([
            'api_token' => $apiToken,
        ]);
    }

    /**
     * @test
     */
    public function 用_post_呼叫更新電池狀態的_api_後應建立_UpdateBatteryState_的工作佇列()
    {
        Queue::fake();
        $payload = [
            'percent' => 23,
            'charging' => true,
        ];

        $this->post('/api/battery-state?api_token=' . $this->user->api_token, $payload, ['Accept' => 'application/json'])
            ->assertResponseOk();

        Queue::assertPushed(UpdateBatteryState::class, function ($job) use ($payload) {
            return $job->payload === $payload;
        });
    }
}

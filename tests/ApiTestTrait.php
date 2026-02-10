<?php

namespace Tests;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Customer;
use App\Models\Partner;
use Laravel\Sanctum\Sanctum;
use Laravel\Passport\Passport;

trait ApiTestTrait
{
    private $response;
    public static array $pagination = [
            'previous_page',
            'current_page',
            'next_page',
            'total_pages',
            'per_page',
            'total_items'
    ];

    // public function assertApiResponse(Collection $actualData): void 
    // {
    //     $this->assertApiSuccess();

    //     $response = json_decode($this->response->getContent(), true);
    //     $responseData = $response->data;

    //     $this->assertNotEmpty($responseData->id);
    //     $this->assertModelData($actualData, $responseData);
    // }

    public static function actingAsCustomer(){
        $customer = Customer::factory()->create();
        $user = $customer->user;
        Passport::actingAs($user);
        return $user;
    }

    public static function actingAsPartner(){
        $partner = Partner::factory()->create();
        $user = $partner->user;
        Passport::actingAs($user);
        return $user;
    }

    // Assert API response
    public function assertApiResponse(Collection $actualData): void
    {
        $this->assertApiSuccess();

        $responseData = collect(
            json_decode($this->response->getContent(), true)['data']
        );

        $this->assertTrue($responseData->has('id'));
        $this->assertNotEmpty($responseData->get('id'));

        $this->assertModelData($actualData, $responseData);
    }

    // Assert API success
    public function assertApiSuccess()
    {
        $this->response->assertStatus(200);

        $payload = collect(json_decode($this->response->getContent(), true));
        $this->assertTrue($payload->has('success'));
        $this->assertTrue($payload->get('success') === true);
    }

    // Assert API success data
    public function assertApiSuccessData()
    {
        $this->response->assertStatus(200);

        $payload = collect(json_decode($this->response->getContent(), true));
        $this->assertTrue($payload->has('success'));
        $this->assertTrue($payload->get('success') === true);
        $this->assertTrue($payload->has('data'));
    }

    // public function assertModelData(Array $actualData, Array $expectedData)
    // {
    //     foreach ($actualData as $key => $value) {
    //         if (in_array($key, ['created_at', 'updated_at'])) {
    //             continue;
    //         }
    //         $this->assertEquals($actualData[$key], $expectedData[$key]);
    //     }
    // }
    
    // Assert model data
    protected function assertModelData(Collection $expected, Collection $actual): void 
    {
        $expected->each(function ($value, $key) use ($actual) {
            $this->assertEquals(
                $value,
                $actual->get($key),
                "Mismatch on key [{$key}]"
            );
        });
    }

}
 
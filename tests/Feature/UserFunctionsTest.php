<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Tests\TestCase;

class UserFunctionsTest extends TestCase
{
    public function testGetMembers() {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('get', '/api/v1/members');
        
        // $apiResponse = $response->baseResponse->getData();
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testGetCaKycHash() {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('get', '/api/v1/members/ca-kyc-hash/AB10BC99');

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testGetMemberDetail() {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('get', '/api/v1/members/1');

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(404)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testCancelChangeEmail() {
        $this->addUser();
        
        $params = [
            'email' => 'testindividual@gmail.com',
            'code' => 'testcode'
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('post', '/api/v1/users/cancel-change-email', $params);

        // $apiResponse = $response->baseResponse->getData();
        
        $response->assertStatus(400)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testConfirmChangeEmail() {
        $this->addUser();
        
        $params = [
            'email' => 'testindividual@gmail.com',
            'code' => 'testcode'
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('post', '/api/v1/users/confirm-change-email', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(400)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testGetDonation() {
        $params = [
            'sessionId' => 'cs_test_a1DLPYuwW0FCjwvnH8eRr9hdHZk8yPAQT5JFL1sEYRrEoiwlKHsEywo9Mw'
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('get', '/api/v1/donation', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testSubmitDonation() {
        $params = [
            'first_name' => 'Test',
            'last_name' => 'Individual',
            'email' => 'testindividual@gmail.com',
            'amount' => 20,
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('post', '/api/v1/donation', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testSubmitContact() {
        $params = [
            'name' => 'Test Individual',
            'email' => 'testindividual@gmail.com',
            'message' => 'Test Message'
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('post', '/api/v1/contact-us', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testCheckValidatorAddress() {
        $params = [
            'public_address' => '01ebaebffebe63ee6e35b88697dd9d5bfab23dac47cbd61a45efc8ea8d80ec9c38'
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('post', '/api/v1/users/check-validator-address', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testChangeEmail() {
        $token = $this->getUserToken();

        $params = [
            'email' => 'testindividualnew@gmail.com',
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/change-email', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testChangePassword() {
        $token = $this->getUserToken();

        $params = [
            'new_password' => 'TestIndividual111New@',
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/change-password', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }
    
    public function testGetProfile() {
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('get', '/api/v1/users/profile');

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testLogout() {
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/logout');

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testHellosignRequest() {
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/hellosign-request');

        // $apiResponse = $response->baseResponse->getData();

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testSubmitPublicAddress() {
        $token = $this->getUserToken();

        $params = [
            'public_address' => '01eedfd20f75528c50aae557d15dff5ca6379ca8401bceb8e969cd0cb1ea52ec7f',
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/submit-public-address', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testCheckPublicAddress() {
        $token = $this->getUserToken();

        $params = [
            'public_address' => '01eedfd20f75528c50aae557d15dff5ca6379ca8401bceb8e969cd0cb1ea52ec7f',
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/check-public-address', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testVerifyFileCasperSigner() {
        $token = $this->getUserToken();

        $file = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');
        $params = [
            'file' => $file,
            'address' => '01eedfd20f75528c50aae557d15dff5ca6379ca8401bceb8e969cd0cb1ea52ec7f',
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/verify-file-casper-signer', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testVerifyFileCasperSigner2() {
        $token = $this->getUserToken();

        $file = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');
        $params = [
            'file' => $file,
            'address' => '01eedfd20f75528c50aae557d15dff5ca6379ca8401bceb8e969cd0cb1ea52ec7f',
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/verify-file-casper-signer-2', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testSubmitKYC() {
        $token = $this->getUserToken();

        $params = [
            'first_name' => 'Test',
            'last_name' => 'Individual',
            'dob' => '03/03/1992',
            'address' => 'New York',
            'city' => 'New York',
            'zip' => '10025',
            'country_citizenship' => 'United States',
            'country_residence' => 'United States',
            'type' => User::TYPE_INDIVIDUAL,
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/submit-kyc', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testVerifyOwnerNode() {
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/verify-owner-node', []);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testGetOwnerNodes() {
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('get', '/api/v1/users/owner-node');

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testResendInviteOwner() {
        $token = $this->getUserToken();

        $params = [
            'email' => 'testindividual@gmail.com',
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/resend-invite-owner', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testGetMessageContent() {
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('get', '/api/v1/users/message-content');

        $content = $response->streamedContent();

        $response->assertStatus(200);
    }

    public function testSaveShuftiproTemp() {
        $token = $this->getUserToken();

        $params = [
            'reference_id' => 'TestReferenceId'
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/shuftipro-temp', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testUpdateShuftiproTemp() {
        $token = $this->getUserToken();

        $params = [
            'reference_id' => 'TestReferenceId'
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('put', '/api/v1/users/shuftipro-temp', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testDeleteShuftiproTemp() {
        $token = $this->getUserToken();

        $params = [
            'reference_id' => 'TestReferenceId'
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('put', '/api/v1/users/shuftipro-temp/delete', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testUploadLetter() {
        $token = $this->getUserToken();

        $file = UploadedFile::fake()->create('letter.pdf', 10, 'application/pdf');
        $params = [
            'file' => $file,
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/v1/users/upload-letter', $params);

        // $apiResponse = $response->baseResponse->getData();

        $response->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }

    public function testListNodes() {
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('get', '/api/v1/users/list-node');
        
        // $apiResponse = $response->baseResponse->getData();
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }
    
    public function testInfoDashboard() {
        $token = $this->getUserToken();
        
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('get', '/api/v1/users/dashboard');
        
        // $apiResponse = $response->baseResponse->getData();
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                ]);
    }
}
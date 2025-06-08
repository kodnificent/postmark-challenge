<?php

namespace Tests\Feature;

use App\Jobs\StartReview;
use App\Models\Enums\ReviewSource;
use App\Models\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InboundEmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_processes_inbound_email_with_pdf_attachment()
    {
        Queue::fake();
        Storage::fake('local');

        $user = User::factory()->create([
            'username' => 'demo',
        ]);

        $pdfPath = base_path('tests/Fixtures/sample.pdf');

        $encodedPdf = base64_encode(file_get_contents($pdfPath));

        $payload = [
            'To' => 'demo@inbound.kodnificent.xyz',
            'Attachments' => [
                [
                    'Name' => 'contract.pdf',
                    'ContentType' => 'application/pdf',
                    'Content' => $encodedPdf,
                ],
            ],
        ];

        $response = $this->postJson('/api/postmark/inbound', $payload);

        $response->assertOk();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'source' => ReviewSource::EMAIL->value,
            'status' => ReviewStatus::PENDING->value,
        ]);

        $review = Review::where('user_id', $user->id)->first();

        $this->assertNotNull($review->content);
        Queue::assertPushed(StartReview::class, fn ($job) => $job->review->id === $review->id);
    }
}

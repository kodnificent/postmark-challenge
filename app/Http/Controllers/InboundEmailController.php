<?php

namespace App\Http\Controllers;

use App\Jobs\StartReview;
use App\Models\Enums\ReviewSource;
use App\Models\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\PdfToText\Pdf;

class InboundEmailController extends Controller
{
    protected function getUser(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    public function __invoke(Request $request)
    {
        $data = $request->all();
        $inbox = $data['To'] ?? '';

        if (!$inbox) return response('invalid data');

        $file = collect($data['Attachments'] ?? [])
            ->filter(static fn ($attachment) => $attachment['ContentType'] === 'application/pdf')
            ->first();

        $file_content = $file['Content'] ?? null;

        if (!$file_content) return response('no file content found');

        $username = explode('@', $inbox, 2)[0] ?? null;

        if (!$username || !($user = $this->getUser($username))) return response('user not found');

        $file_name = str()->random(60);
        $file_path = storage_path("app/private/{$file_name}.pdf");
        file_put_contents($file_path, base64_decode(($file_content)));

        $text = Pdf::getText($file_path);

        unlink($file_path);

        $review = new Review();
        $review->content = $text;
        $review->source = ReviewSource::EMAIL;
        $review->status = ReviewStatus::PENDING;
        $review->user_id = $user->id;
        $review->save();

        StartReview::dispatch($review);

        return response('ok');
    }
}

<?php

namespace Knot\Jobs;

use Notification;
use Illuminate\Http\File;
use Knot\Models\PhotoPost;
use Illuminate\Bus\Queueable;
use Knot\Notifications\AddedPost;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UploadPhotoPostImageToCloud implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;

    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(PhotoPost $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $authorFriends = $this->post->user->getFriends();

        $tmpPath = public_path($this->post->image_path);
        $file = new File($tmpPath);
        try {
            $url = Storage::cloud()->putFile('photo-posts', $file, 'public');
            $this->post->fill(['image_path' => $url, 'cloud' => true])->save();
            unlink($tmpPath);
            Notification::send($authorFriends, new AddedPost($this->post->post));
        } catch (Exception $e) {
            logger()->error('Cloud Upload Failed.', [
                'post' => $this->post->toArray(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}

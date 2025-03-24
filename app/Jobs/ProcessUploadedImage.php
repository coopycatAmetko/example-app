<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\Comment;
use Illuminate\Support\Str;
use Exception;

class ProcessUploadedImage implements ShouldQueue
{
    use Queueable;
    
    private $path;
    private $comment;
    
    /**
     * Create a new job instance.
     */
    public function __construct($path, Comment $comment)
    {
        $this->path = $path;
        $this->comment = $comment;
    }
    
    /**
     * Execute the job.
     */
    public function handle(): void
    {   
        if (!file_exists(public_path($this->path))) {
            throw new Exception("File does not exist at path: {$this->path}");
        }
        
        $mime = mime_content_type(public_path($this->path));
        if (!str_starts_with($mime, 'image/')) {
            throw new Exception("File is not an image. Mime type: {$mime}");
        }
        
        try {
            $img = Image::read(public_path($this->path));
            
            $path_info = pathinfo(public_path($this->path));
            $extension = $path_info['extension'] ?? 'jpg';
            
            $original_filename = $path_info['filename'];
            $new_filename = $original_filename . '_thumb' . '.' . $extension;
            $new_path = 'uploads/' . $new_filename;
            
            if ($img->width() > 320 || $img->height() > 240) {
                $img->resize(320, 240, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            $img->save(public_path($new_path));
                        
            $image = $this->comment->files()->where('path', $this->path)->first();
            if ($image) {
                $image->thumb_path = $new_path;
                $image->save();
            } else {
                $this->comment->files()->create([
                    'image' => $new_path,
                    'thumb_path' => $new_path,
                    'type' => $type
                ]);
            }
        } catch (Exception $e) {
            throw new Exception("Failed to process image: " . $e->getMessage());
        }
    }
}
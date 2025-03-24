<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Laravel\Facades\Image;

class CommentsController extends Controller
{
    public function index(Post $post, Request $request)
    {
        $sort_field = $request->input('sort_field', 'created_at');
        $sort_order = $request->input('sort_order', 'desc');
        
        $allowed_sort_fields = ['user_name', 'email', 'created_at'];
        if (!in_array($sort_field, $allowed_sort_fields)) {
            $sort_field = 'created_at';
        }
        
        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with(['files'])
            ->withCount('children as has_children')
            ->orderBy($sort_field, $sort_order)
            ->paginate(25);

        return response()->json($comments, 200);
    }

    public function getChildren(Post $post, Comment $comment, Request $request)
    {
        $comments = $comment->children()
            ->with(['files'])
            ->withCount('children as has_children')
            ->paginate(25);

        return response()->json($comments, 200,);
    }

    public function store(Post $post, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|alpha_num',
            'email' => 'required|email',
            'homepage' => 'nullable|url',
            'captcha' => 'required|captcha_api:' . request('captcha_key') . ',default',
            'text' => 'required|string',
            'file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,txt',
                function ($attribute, $value, $fail) {
                    if ($value->getClientOriginalExtension() == 'txt' && $value->getSize() > 102400) {
                        return $fail('The txt file must not be greater than 100KB.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $text = $this->processText($request->text);

        $comment = $post->comments()->create([
            'user_name' => $request->user_name,
            'email' => $request->email,
            'homepage' => $request->homepage,
            'text' => $text,
        ]);

        if ($request->hasFile('file')) {
            $this->processFile($request->file('file'), $comment);
        }

        return response()->json($comment->load('files'), 201);
    }

    public function reply(Comment $comment, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|alpha_num',
            'email' => 'required|email',
            'homepage' => 'nullable|url',
            'captcha' => 'required|captcha_api:' . request('captcha_key') . ',default',
            'text' => 'required|string',
            'file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,txt',
                function ($attribute, $value, $fail) {
                    if ($value->getClientOriginalExtension() == 'txt' && $value->getSize() > 102400) {
                        return $fail('The txt file must not be greater than 100KB.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $text = $this->processText($request->text);

        $reply = new Comment([
            'post_id' => $comment->post_id,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'homepage' => $request->homepage,
            'text' => $text,
        ]);

        $reply->appendToNode($comment)->save();

        if ($request->hasFile('file')) {
            $this->processFile($request->file('file'), $reply);
        }

        return response()->json($reply->load('files'), 201);
    }

    private function processText($text)
    {
        // Allow only specific HTML tags
        $allowedTags = '<a><code><i><strong>';
        $text = strip_tags($text, $allowedTags);
        
        // Validate and fix HTML structure to ensure proper tag closing
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        // Process <a> tags to ensure they have proper attributes
        $links = $dom->getElementsByTagName('a');
        for ($i = $links->length - 1; $i >= 0; $i--) {
            $link = $links->item($i);
            if (!$link->hasAttribute('href')) {
                $link->setAttribute('href', '#');
            }
        }
        
        return $dom->saveHTML();
    }

    private function processFile($file, Comment $comment)
    {
        $type = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();
        $path = 'uploads/' . uniqid() . '.' . $extension;
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {

            $img = Image::read($file);
            
            // Resize if larger than 320x240
            if ($img->width() > 320 || $img->height() > 240) {
                $img->resize(320, 240, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            $img->save(public_path($path));
        } else {
            $file->move(public_path('uploads'), basename($path));
        }
        
        $comment->files()->create([
            'path' => $path,
            'type' => $type
        ]);
    }
}
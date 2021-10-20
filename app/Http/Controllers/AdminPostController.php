<?php

namespace App\Http\Controllers;

use App\Events\PostEvent;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminPostController extends Controller
{
    public function index()
    {
        return view('admin.posts.index', [
            'posts' => Post::paginate(50)
        ]);
    }

    public function create()
    {
        return view('admin.posts.create');
    }

    public function store()
    {
        $imagePath = request()->file('thumbnail');

        if ($imagePath !== null) {
            $imagePath = $imagePath->store('thumbnails', 'public');
        }
            Post::create(array_merge($this->validatePost(), [
                'user_id' => request()->user()->id,
                'thumbnail' => $imagePath
            ]));
        return redirect('/')->with('success', 'Post Create!');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', ['post' => $post]);
    }

    public function update(Post $post)
    {
        $attributes = $this->validatePost($post);

        if ($attributes['thumbnail'] ?? false) {
            $attributes['thumbnail'] = request()->file('thumbnail')->store('thumbnails', 'public');
        }
        $post->update($attributes);

        event(new PostEvent($post));
        return back()->with('success', 'Post Updated!');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        event(new PostEvent($post));
        return back()->with('success', 'Post Deleted!');
    }

    /**
     * @param Post $post
     * @return array
     */
    protected function validatePost(?Post $post = null): array
    {
        $post ??= new Post();
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'thumbnail' => 'required|image',
//            'thumbnail' => $post->exists ? ['image'] : ['required', 'image'],
            'slug' => ['required', Rule::unique('posts', 'slug')->ignore($post)],
            'excerpt' => 'required',
            'body' => 'required',
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'published_at' => 'required'
        ]);

        return $validator->validated();

    }
}

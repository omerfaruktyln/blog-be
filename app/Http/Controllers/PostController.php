<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostView;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends Controller
{


    public function home()
    {
        
        // Son gönderiler
        $latestPost = Post::where('active', '=', 1)
            ->whereDate('published_at', '<', Carbon::now())
            ->orderBy('published_at', 'desc')
            ->limit(1)
            ->first();



        // Beğenilere göre en popüler 3 gönderiyi göster
        $popularPosts = Post::query()
            ->leftJoin('upvote_downvotes', 'posts.id', '=', 'upvote_downvotes.post_id')
            ->select('posts.*', DB::raw('COUNT(upvote_downvotes.id) as upvote_count'))
            ->where(function ($query) {
                $query->whereNull('upvote_downvotes.is_upvote')
                    ->orWhere('upvote_downvotes.is_upvote', '=', 1);
            })
            ->where('active', '=', 1)
            ->whereDate('published_at', '<', Carbon::now())
            ->orderByDesc('upvote_count')
            ->groupBy([
                'posts.id',
                'posts.title',
                'posts.slug',
                'posts.thumbnail',
                'posts.body',
                'posts.active',
                'posts.published_at',
                'posts.user_id',
                'posts.created_at',
                'posts.updated_at',
                'posts.meta_title',
                'posts.meta_description',
            ])
            ->limit(5)
            ->get();

      
    
            $recommendedPosts = Post::query()
                ->leftJoin('post_views', 'posts.id', '=', 'post_views.post_id')
                ->select('posts.*', DB::raw('COUNT(post_views.id) as view_count'))
                ->where('active', '=', 1)
                ->whereDate('published_at', '<', Carbon::now())
                ->orderByDesc('view_count')
                ->groupBy([
                    'posts.id',
                    'posts.title',
                    'posts.slug',
                    'posts.thumbnail',
                    'posts.body',
                    'posts.active',
                    'posts.published_at',
                    'posts.user_id',
                    'posts.created_at',
                    'posts.updated_at',
                    'posts.meta_title',
                    'posts.meta_description',
                ])
                ->limit(3)
                ->get();
        


        // En son kategorileri en son gönderileriyle birlikte göster
        $categories = Category::query()
//            ->with(['posts' => function ($query) {
//                $query->orderByDesc('published_at');
//            }])
            ->whereHas('posts', function ($query) {
                $query
                    ->where('active', '=', 1)
                    ->whereDate('published_at', '<', Carbon::now());
            })
            ->select('categories.*')
            ->selectRaw('MAX(posts.published_at) as max_date')
            ->leftJoin('category_post', 'categories.id', '=', 'category_post.category_id')
            ->leftJoin('posts', 'posts.id', '=', 'category_post.post_id')
            ->orderByDesc('max_date')
            ->groupBy([
                'categories.id',
                'categories.title',
                'categories.slug',
                'categories.created_at',
                'categories.updated_at',
            ])
            ->limit(5)
            ->get();

            return response()->json([
                'latest_post' => $latestPost,
                'popular_posts' => $popularPosts,
                'recommended_posts' => $recommendedPosts,
                'categories' => $categories,
            ]);
    
    }

    public function show(Request $request)
    {
        $postData = $request->input('post');
        $post = new Post($postData);
        
        $next = Post::query()
            ->where('active', true)
            ->whereDate('published_at', '<=', Carbon::now())
            ->whereDate('published_at', '<', $post->published_at)
            ->orderBy('published_at', 'desc')
            ->limit(1)
            ->first();

        $prev = Post::query()
            ->where('active', true)
            ->whereDate('published_at', '<=', Carbon::now())
            ->whereDate('published_at', '>', $post->published_at)
            ->orderBy('published_at', 'asc')
            ->limit(1)
            ->first();

        return response()->json([
                'prev' => $prev,
                'next' =>  $next,
            ]);
    }

    public function byCategory(Request $request )
    {
       
        $categoryData = $request->input('category');
       
        $posts = Post::query()
            ->join('category_post', 'posts.id', '=', 'category_post.post_id')
            ->where('category_post.category_id', '=', $categoryData['id'])
            ->where('active', '=', true)
            ->whereDate('published_at', '<=', Carbon::now())
            ->orderBy('published_at', 'desc')
            ->paginate(10);

            return response()->json([
                'posts' => $posts,
                'category' =>  $categoryData,
            ]);
    }

    public function search(Request $request)
    {
 
        $searchData = $request->input('request');
      
        $posts = Post::query()
            ->where('active', '=', true)
            ->whereDate('published_at', '<=', Carbon::now())
            ->orderBy('published_at', 'desc')
            ->where(function ($query) use ($searchData) {
                $query->where('title', 'like', "%$searchData%")
                    ->orWhere('body', 'like', "%$searchData%");
            })
            ->paginate(10);

            return response()->json([
                'posts' => $posts,
              
            ]);
    }
}

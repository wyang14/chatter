<?php

namespace Wyang14\Chatter\Controllers;

use Auth;
use Carbon\Carbon;
use Wyang14\Chatter\Events\ChatterAfterNewDiscussion;
use Wyang14\Chatter\Events\ChatterBeforeNewDiscussion;
use Wyang14\Chatter\Models\Models;
use Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Validator;

class ChatterDiscussionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /*$total = 10;
        $offset = 0;
        if ($request->total) {
            $total = $request->total;
        }
        if ($request->offset) {
            $offset = $request->offset;
        }
        $discussions = Models::discussion()->with('user')->with('post')->with('postsCount')->with('category')->orderBy('created_at', 'ASC')->take($total)->offset($offset)->get();*/

        // Return an empty array to avoid exposing user data to the public.
        // This index function is not being used anywhere.
        return response()->json([]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Models::category()->all();

        return view('chatter::discussion.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->request->add(['body_content' => strip_tags($request->body)]);

        $validator = Validator::make($request->all(), [
            'title'               => 'required|min:5|max:255',
            'body_content'        => 'required|min:10',
            'chatter_category_id' => 'required',
        ]);

        Event::fire(new ChatterBeforeNewDiscussion($request, $validator));
        if (function_exists('chatter_before_new_discussion')) {
            chatter_before_new_discussion($request, $validator);
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user_id = Auth::user()->id;

        if (config('chatter.security.limit_time_between_posts')) {
            if ($this->notEnoughTimeBetweenDiscussion()) {
                $minute_copy = (config('chatter.security.time_between_posts') == 1) ? ' minute' : ' minutes';
                $chatter_alert = [
                    'chatter_alert_type' => 'danger',
                    'chatter_alert'      => '为防止恶意灌水, 发帖请间隔'.config('chatter.security.time_between_posts').$minute_copy.'.',
                    ];

                return redirect('/'.config('chatter.routes.home'))->with($chatter_alert)->withInput();
            }
        }

        $new_discussion = [
            'title'               => $request->title,
            'chatter_category_id' => $request->chatter_category_id,
            'user_id'             => $user_id,
            'slug'                => uniqid($user_id),
            'color'               => $request->color,
            ];

        $category = Models::category()->find($request->chatter_category_id)->first();
        
        $discussion = Models::discussion()->create($new_discussion);

        $new_post = [
            'chatter_discussion_id' => $discussion->id,
            'user_id'               => $user_id,
            'body'                  => $request->body,
            ];

        if (config('chatter.editor') == 'simplemde'):
           $new_post['markdown'] = 1;
        endif;

        // add the user to automatically be notified when new posts are submitted
        $discussion->users()->attach($user_id);

        $post = Models::post()->create($new_post);

        if ($post->id) {
            Event::fire(new ChatterAfterNewDiscussion($request, $discussion, $post));
            if (function_exists('chatter_after_new_discussion')) {
                chatter_after_new_discussion($request);
            }

            $chatter_alert = [
                'chatter_alert_type' => 'success',
                'chatter_alert'      => '成功创建新的'.config('chatter.titles.discussion').'.',
                ];

            return redirect('/'.config('chatter.routes.home').'/'.config('chatter.routes.discussion').'/'.$category->id.'/'.$discussion->id)->with($chatter_alert);
        } else {
            $chatter_alert = [
                'chatter_alert_type' => 'danger',
                'chatter_alert'      => '糟糕 :( 在创建您的'.config('chatter.titles.discussion').'时出问题咯.',
                ];

            return redirect('/'.config('chatter.routes.home').'/'.config('chatter.routes.discussion').'/'.$category->id.'/'.$discussion->id)->with($chatter_alert);
        }
    }

    private function notEnoughTimeBetweenDiscussion()
    {
        $user = Auth::user();

        $past = Carbon::now()->subMinutes(config('chatter.security.time_between_posts'));

        $last_discussion = Models::discussion()->where('user_id', '=', $user->id)->where('created_at', '>=', $past)->first();

        if (isset($last_discussion)) {
            return true;
        }

        return false;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($category_id, $discussion_id = null)
    {
        if (!isset($category_id) || !isset($discussion_id)) {
            return redirect(config('chatter.routes.home'));
        }

        $discussion = Models::discussion()->where('id', '=', $discussion_id)->first();
        if (is_null($discussion)) {
            abort(404);
        }

        $discussion_category = Models::category()->find($discussion->chatter_category_id);
        if ($category_id != $discussion_category->id) {
            return redirect(config('chatter.routes.home').'/'.config('chatter.routes.discussion').'/'.$discussion_category->id.'/'.$discussion->id);
        }
        $posts = Models::post()->with('user')->where('chatter_discussion_id', '=', $discussion->id)->orderBy('created_at', 'ASC')->paginate(10);

        $chatter_editor = config('chatter.editor');

        if ($chatter_editor == 'simplemde') {
            // Dynamically register markdown service provider
            \App::register('GrahamCampbell\Markdown\MarkdownServiceProvider');
        }

        $discussion->increment('views');
        
        return view('chatter::discussion', compact('discussion', 'posts', 'chatter_editor'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function sanitizeContent($content)
    {
        libxml_use_internal_errors(true);
        // create a new DomDocument object
        $doc = new \DOMDocument();

        // load the HTML into the DomDocument object (this would be your source HTML)
        $doc->loadHTML($content);

        $this->removeElementsByTagName('script', $doc);
        $this->removeElementsByTagName('style', $doc);
        $this->removeElementsByTagName('link', $doc);

        // output cleaned html
        return $doc->saveHtml();
    }

    private function removeElementsByTagName($tagName, $document)
    {
        $nodeList = $document->getElementsByTagName($tagName);
        for ($nodeIdx = $nodeList->length; --$nodeIdx >= 0;) {
            $node = $nodeList->item($nodeIdx);
            $node->parentNode->removeChild($node);
        }
    }

    public function toggleEmailNotification($category, $slug = null)
    {
        if (!isset($category) || !isset($slug)) {
            return redirect(config('chatter.routes.home'));
        }

        $discussion = Models::discussion()->where('slug', '=', $slug)->first();

        $user_id = Auth::user()->id;

        // if it already exists, remove it
        if ($discussion->users->contains($user_id)) {
            $discussion->users()->detach($user_id);

            return response()->json(0);
        } else { // otherwise add it
             $discussion->users()->attach($user_id);

            return response()->json(1);
        }
    }
}

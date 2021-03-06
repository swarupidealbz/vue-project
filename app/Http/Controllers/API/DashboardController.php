<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comments;
use App\Models\Content;
use App\Models\Notifications;
use App\Models\SideMenus;
use App\Models\Topics;
use App\Models\User;
use App\Models\Websites;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseController
{
    public function dashboard(Request $request)
    {
        $result = [
            'side_menus' => (blank($request->parts) || ($request->parts == 'side_menu')) ? $this->sideMenus() : [],
            'websites' => (blank($request->parts) || ($request->parts == 'top_bar')) ? $this->websites() : [],
            'languages' => (blank($request->parts) || ($request->parts == 'top_bar')) ? $this->languages() : [],
            'notifications' => (blank($request->parts) || ($request->parts == 'top_bar')) ? $this->notifications() : [],
            'statistics' => (blank($request->parts) || ($request->parts == 'data')) ? $this->contents($request) : [],
			'topic_lists' => (blank($request->parts) || ($request->parts == 'data')) ? $this->topics($request) : [],
			'leaders' => (blank($request->parts) || ($request->parts == 'data')) ? $this->leaders($request) : []
        ];

        return $this->handleResponse($result, 'Dashboard content is ready');
    }

    private function sideMenus()
    {
        $loginUser = Auth::user();
        $rec = SideMenus::select('id', 'parent_id', 'name', 'access')->whereNull('parent_id')->get()->filter(function($item) use($loginUser){
            $access = json_decode($item->access, true);
            return $access[$loginUser->role];
        })->map(function($menu) use($loginUser){
			$item = [
				'id' => $menu->id,
				'parent_id' => $menu->parent_id,
				'name' => $menu->name,
				'access' => $menu->access
			];
			$children = $menu->sub_menus->filter(function($item) use($loginUser){
				$access = json_decode($item->access, true);
				return $access[$loginUser->role];
			})->map(function($sub) {
				return [
					'id' => $sub->id,
					'parent_id' => $sub->parent_id,
					'name' => $sub->name,
					'access' => $sub->access
				];
			});
			if($children->count()) {
				$item['sub_menus'] = $children;
			}			
			return $item;
		});

        return $rec;

    }

    private function websites()
    {
        $loginUser = Auth::user();
        $result = collect([]);
        if($loginUser->role == 'writer') {
            $result = collect([
                [
                    'id' => 0,
                    'name' => 'All'
                ]
            ]);
        }
        $rec = Websites::all()->filter(function($item) use($loginUser){
            $owners = explode(',', $item->owners);
            return in_array($loginUser->id, $owners);
        })->map(function($web) use(&$result){
            $result->push($web);
        });

        return $result;
    }

    private function languages()
    {
        return [
            [
                'name' => 'English',
                'sort_name' => 'EN'
            ],
            // [
            //     'name' => 'French',
            //     'sort_name' => 'FR'
            // ]
        ];
    }

    private function notifications()
    {
        $loginUser = Auth::user();
        $rec = Notifications::where('recipient_user_id', $loginUser->id)
        ->where('is_read', 0);
        $response = [
            'count' => $rec->count(),
            'data' => $rec->latest()->take(10)->get()
        ];

        return $response;
    }

    private function contents($request)
    {
        $loginUser = Auth::user();
        $rec = Websites::all()->filter(function($item) use($loginUser){
            $owners = explode(',', $item->owners);
            return in_array($loginUser->id, $owners);
        })->first();
        $websiteId = optional($rec)->id;
        if($request->website == 0) {
            $websiteId = '';
        }
        elseif($request->website) {
            $websiteId = $request->website;
        }
        $startOfMonth = Carbon::now()->startOfMonth()->toDateTimeString();
        $currentDate = Carbon::now()->toDateTimeString();

        $firstDayofPreviousMonth = Carbon::now()->startOfMonth()->subMonthsNoOverflow()->toDateTimeString();
        $lastDayofPreviousMonth = Carbon::now()->subMonthsNoOverflow()->endOfMonth()->toDateTimeString();

        $topics = $this->topicRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth);
        $articles = $this->articleRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth);
        $outlines = $this->outlineRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth);
        $comments = $this->commentRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth);    
        $selfTopicCount = $this->selfTopicCount($websiteId, $startOfMonth, $currentDate);    

        return [
            'topics' => $topics,
            'articles' => $articles,
            'outlines' => $outlines,
            'comments' => $comments,
            'self_topics_count' => $selfTopicCount,
            'monthly_goal' => $loginUser->monthly_goal,
            'unit_cost' => $loginUser->unit_cost
        ];       

    }

    public function selfTopicCount($websiteId, $startOfMonth, $currentDate)
    {
        $user = Auth::user();
        $topics = Topics::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->when(($websiteId == '') && ($user->role == 'client'), function($q) use($user){
            $webIds = Websites::all()->filter(function($item) use($user){
                $owners = explode(',', $item->owners);
                return in_array($user->id, $owners);
            })->pluck('id')->toArray();
            return $q->whereIn('website_id', $webIds);
        })
        ->whereBetween('created_at',[$startOfMonth, $currentDate]);

        if($user->role == 'writer') {
            $topics = $topics->where('assignee_id', $user->id);
        }
        else {
            $topics = $topics->where('created_by_id', $user->id);
        }
        
        return $topics->count();
    }

    private function topicRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth)
    {
        $user = Auth::user();
        //getting topic
        $currentMonthTopics = Topics::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->when(($websiteId == '') && ($user->role == 'client'), function($q) use($user){
            $webIds = Websites::all()->filter(function($item) use($user){
                $owners = explode(',', $item->owners);
                return in_array($user->id, $owners);
            })->pluck('id')->toArray();
            return $q->whereIn('website_id', $webIds);
        })
        ->whereBetween('created_at',[$startOfMonth, $currentDate])->count();
        $lastMonthTopics = Topics::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->whereBetween('created_at',[$firstDayofPreviousMonth, $lastDayofPreviousMonth])->count();

        $firstTopic = Topics::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })->oldest()->get()->first();
        $topicStartText = 'Since The Start';
        $topicInc = '100%';
        $incText = '--';
        if($firstTopic && optional(optional($firstTopic)->created_at)->toDateTimeString() < $startOfMonth) {
            $topicStartText = 'Since Last Month';
            $diffLastCurrent = $currentMonthTopics - $lastMonthTopics;
            $incText = $diffLastCurrent > 0 ? 'inc' : 'dec';
            $lastMonthTopics = $lastMonthTopics == 0 ? 1 : $lastMonthTopics;
            $inc = (abs($diffLastCurrent) / $lastMonthTopics) * 100;
            $topicInc = number_format($inc, 2).'%';
        }
        $topicContent = [
            'text' => 'Topics',
            'count' => $currentMonthTopics,
            'stat' => $topicInc,
            'stat_text' => $incText,
            'stat_review_text' => $topicStartText,
			'color' => 'light-primary',
			'icon' => 'TrendingUpIcon'
        ];

        return $topicContent;
    }

    private function articleRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth)
    {
        $user = Auth::user();
        //getting content
        $currentMonthContent = Content::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->when(($websiteId == '') && ($user->role == 'client'), function($q) use($user){
            $webIds = Websites::all()->filter(function($item) use($user){
                $owners = explode(',', $item->owners);
                return in_array($user->id, $owners);
            })->pluck('id')->toArray();
            return $q->whereIn('website_id', $webIds);
        })
        ->where('content_type', 'like', 'article')
        ->whereBetween('created_at',[$startOfMonth, $currentDate])->count();
        $lastMonthContent = Content::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->where('content_type', 'like', 'article')
        ->whereBetween('created_at',[$firstDayofPreviousMonth, $lastDayofPreviousMonth])->count();

        $firstContent = Content::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->where('content_type', 'like', 'article')
        ->oldest()->get()->first();
        $contentStartText = 'Since The Start';
        $contentInc = '100%';
        $incText = '--';
        if($firstContent && optional(optional($firstContent)->created_at)->toDateTimeString() < $startOfMonth) {
            $contentStartText = 'Since Last Month';
            $diffLastCurrent = $currentMonthContent - $lastMonthContent;
            $incText = $diffLastCurrent > 0 ? 'inc' : 'dec';
            $lastMonthContent = $lastMonthContent == 0 ? 1 : $lastMonthContent;
            $inc = (abs($diffLastCurrent) / $lastMonthContent) * 100;
            $contentInc = number_format($inc, 2).'%';
        }
        $articleContent = [
            'text' => 'Articles',
            'count' => $currentMonthContent,
            'stat' => $contentInc,
            'stat_text' => $incText,
            'stat_review_text' => $contentStartText,
			'color' => 'light-info',
			'icon' => 'UserIcon'
        ];

        return $articleContent;
    }

    private function outlineRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth)
    {
        $user = Auth::user();
        //getting outline
        $currentMonthOutline = Content::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->when(($websiteId == '') && ($user->role == 'client'), function($q) use($user){
            $webIds = Websites::all()->filter(function($item) use($user){
                $owners = explode(',', $item->owners);
                return in_array($user->id, $owners);
            })->pluck('id')->toArray();
            return $q->whereIn('website_id', $webIds);
        })
        ->where('content_type', 'like', 'outline')
        ->whereBetween('created_at',[$startOfMonth, $currentDate])->count();
        $lastMonthOutline = Content::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->where('content_type', 'like', 'outline')
        ->whereBetween('created_at',[$firstDayofPreviousMonth, $lastDayofPreviousMonth])->count();

        $firstOutline = Content::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->where('content_type', 'like', 'outline')
        ->oldest()->get()->first();
        $outlineStartText = 'Since The Start';
        $outlineInc = '100%';
        $incText = '--';
        if($firstOutline && optional(optional($firstOutline)->created_at)->toDateTimeString() < $startOfMonth) {
            $outlineStartText = 'Since Last Month';
            $diffLastCurrent = $currentMonthOutline - $lastMonthOutline;
            $incText = $diffLastCurrent > 0 ? 'inc' : 'dec';
            $lastMonthOutline = $lastMonthOutline == 0 ? 1 : $lastMonthOutline;
            $inc = (abs($diffLastCurrent) / $lastMonthOutline) * 100;
            $outlineInc = number_format($inc, 2).'%';
        }
        $outlineContent = [
            'text' => 'Outlines',
            'count' => $currentMonthOutline,
            'stat' => $outlineInc,
            'stat_text' => $incText,
            'stat_review_text' => $outlineStartText,
			'color' => 'light-danger',
			'icon' => 'BoxIcon'
        ];

        return $outlineContent;
    }

    private function commentRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth)
    {
        $user = Auth::user();
        $currentMonthComment = Comments::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->when(($websiteId == '') && ($user->role == 'client'), function($q) use($user){
            $webIds = Websites::all()->filter(function($item) use($user){
                $owners = explode(',', $item->owners);
                return in_array($user->id, $owners);
            })->pluck('id')->toArray();
            return $q->whereIn('website_id', $webIds);
        })
        ->whereBetween('created_at',[$startOfMonth, $currentDate])->count();
        $lastMonthComment = Comments::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->whereBetween('created_at',[$firstDayofPreviousMonth, $lastDayofPreviousMonth])->count();

        $firstComment = Comments::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })->oldest()->get()->first();
        $commentStartText = 'Since The Start';
        $commentInc = '100%';
        $incText = '--';
        if($firstComment && optional(optional($firstComment)->created_at)->toDateTimeString() < $startOfMonth) {
            $commentStartText = 'Since Last Month';
            $diffLastCurrent = $currentMonthComment - $lastMonthComment;
            $incText = $diffLastCurrent > 0 ? 'inc' : 'dec';
            $lastMonthComment = $lastMonthComment == 0 ? 1 : $lastMonthComment;
            $inc = (abs($diffLastCurrent) / $lastMonthComment) * 100;
            $commentInc = number_format($inc, 2).'%';
        }
        $commentContent = [
            'text' => 'Comments',
            'count' => $currentMonthComment,
            'stat' => $commentInc,
            'stat_text' => $incText,
            'stat_review_text' => $commentStartText,
			'color' => 'light-success',
			'icon' => 'DollarSignIcon'
        ];

        return $commentContent;
    }
	
	private function topics($request)
	{
		$loginUser = Auth::user();
        $rec = Websites::all()->filter(function($item) use($loginUser){
            $owners = explode(',', $item->owners);
            return in_array($loginUser->id, $owners);
        })->first();
        $websiteId = optional($rec)->id;
        if($request->website == 0) {
            $websiteId = '';
        }
        elseif($request->website) {
            $websiteId = $request->website;
        }
		
		$topics = Topics::when($websiteId, function($q) use($websiteId){
            $q->where('website_id', $websiteId);
        })
        ->when(($websiteId == '') && ($loginUser->role == 'client'), function($q) use($loginUser){
            $webIds = Websites::all()->filter(function($item) use($loginUser){
                $owners = explode(',', $item->owners);
                return in_array($loginUser->id, $owners);
            })->pluck('id')->toArray();
            return $q->whereIn('website_id', $webIds);
        })
        ->latest()->take(10)->get();
		
		return $topics;
	}
	
	private function leaders($request)
	{
		$loginUser = Auth::user();
        $rec = Websites::all()->filter(function($item) use($loginUser){
            $owners = explode(',', $item->owners);
            return in_array($loginUser->id, $owners);
        })->first();
        $websiteId = optional($rec)->id;
        if($request->website == 0) {
            $websiteId = '';
        }
        elseif($request->website) {
            $websiteId = $request->website;
        }
		
		$users = User::where('role', 'writer')->latest('job_units')->get()
        ->map(function($user) {
            return [
                'profile_image' => $user->profile_image ?? '/images/account.png',
                'full_name' => $user->name,
                'level' => 'Level '.$this->getLevel($user->job_units),
                'job_units' => $user->job_units,
                'monthly_goal' => $user->monthly_goal,
                'unit_cost' => $user->unit_cost,
                'role' => $user->role,
                'email' => $user->email,
            ];
        });
		
		return $users;
	}

    public function getLevel($units) {
        if($units <= 100) {
          return 1;
        }
        else if($units > 100 && $units <= 300) {
          return 2;
        }
        else if($units > 300 && $units <= 500) {
          return 3;
        }
    }

    public function allNotifications(Request $request)
    {
        $loginUser = Auth::user();
        $rec = Notifications::where('recipient_user_id', $loginUser->id);
        if($request->type == 'read') {
            $rec = $rec->where('is_read', 1);
        }
        elseif($request->type == 'unread') {
            $rec = $rec->where('is_read', 0);
        }

        $response = [
            'count' => $rec->count(),
            'data' => $rec->latest()->get()
        ];

        return $response;
    }

    public function updateNotification(Request $request)
    {
        $loginUser = Auth::user();
        Notifications::where('id', $request->id)->update(['updated_by_id' => $loginUser->id, 'is_read' => $request->is_read]);

        $rec = Notifications::where('recipient_user_id', $loginUser->id);
        if($request->type == 'read') {
            $rec = $rec->where('is_read', 1);
        }
        elseif($request->type == 'unread') {
            $rec = $rec->where('is_read', 0);
        }

        $response = [
            'count' => $rec->count(),
            'data' => $rec->latest()->get()
        ];

        return $response;
    }
}

<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Content;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Topics;
use App\Models\Comments;
use App\Models\Notifications;
use App\Models\User;
use App\Models\Websites;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContentController extends BaseController
{
    //approved, rejected, open, work in progress
    //outline, article

    public function index()
    {
        try {                
            $list = Content::all();

            return $this->handleResponse($list, 'Fetched all list');
        }
        catch(Exception $e) 
        {
            logger('content list error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function show(Content $content)
    {
        try {                
            if(empty($content)) {
                return $this->handleError([], 'Data not found', 404);
            }
            return $this->handleResponse($content, 'Success');
        }
        catch(Exception $e) 
        {
            logger('content show error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function contentByStatus(Request $request)
    {
        try {
            $lists = Content::query();
            if($request->website) {
                $lists = $lists->whereWebsiteId(trim($request->website));
            }
            if($request->primary_topic) {
                $lists = $lists->where('primary_topic_id', trim($request->primary_topic));
            }
            if($request->child_topic) {
                $lists = $lists->where('child_topic_id', trim($request->child_topic));
            }
            if($request->user) {
                $lists = $lists->where('user_id', trim($request->user));
            }
            if($request->content_type) {
                $lists = $lists->where('content_type', trim($request->content_type));
            }
            if($request->status) {
                $lists = $lists->where('status', trim($request->status));
            }
            $lists = $lists->get();

            
            return $this->handleResponse($lists, 'Success');            
        }
        catch(Exception $e) 
        {
            logger('content list by status error');
            return $this->handleError('Something went wrong', [], 500);
        }

    }

    public function updateStatus(Request $request)
    {
        try {
                
            $input = $request->only('content', 'status');
                
            $validator = Validator::make($input,[
                'content' => 'required|integer', 
                'status' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
            $content = Content::find($request->content);
            $status = strtolower(trim($request->status));

            if(empty($content)) {
                return $this->handleError('Invalid content', $validator->errors()->all(), 400);
            }
            $accept = false;
            $reject = false;
            $content_user = $content->createdUser;
            if(in_array($content->status, [Content::STATUS_REJECTED, Content::STATUS_OPEN, Content::STATUS_WORKIN_PROGRESS]) && ($status == Content::STATUS_APPROVED)) {
                $accept = true;
                $content_user->job_units += 1;
                $content_user->save();
            }
            elseif(($status == Content::STATUS_REJECTED) && ($content->status == Content::STATUS_APPROVED)) {
                $reject = true;
                $content_user->job_units -= 1;
                $content_user->save();
            }

            $content->status = $status;
            $content->save();

            $loginUser = Auth::user();
            $website = Websites::find($content->website_id);
            $owners = explode(',', $website->owners);
			$time = Carbon::now()->toDateTimeString();
            $notify = [];
            $notify[] = [
                'recipient_user_id' => $content->created_by_id,
                'sender_user_id' => $loginUser->id,
                'website_id' => $website->id,
                'heading' => 'Status updated',
                'details' => sprintf('%s has been %s by %s.', $content->title, $content->fresh()->status, $loginUser->name),
                'object_from_type' => Notifications::CONTENT,
                'object_from_id' => $content->id,
                'object_to_type' => Notifications::PRIMARY_TOPICS,
                'object_to_id' => $content->primary_topic_id,
                'created_by_id' => $loginUser->id,
                'updated_by_id' => $loginUser->id,
                'created_at' => $time,
                'updated_at' => $time
            ];
            if(count($notify)) {
                Notifications::insert($notify);
            }

            return $this->handleResponse($content->fresh(), 'Content status updated to '.ucwords($status).' successfully');
        }
        catch(Exception $e) 
        {
            logger('update status error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function contentForTimeline(Request $request)
    {
        try {

            $input = $request->only('website', 'primary_topic');
                
            $validator = Validator::make($input,[
                'website' => 'required|integer', 
                'primary_topic' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
            if($request->website == 0) {
                $topic = Topics::find($request->primary_topic);
                $request->website = $topic->website_id;
            }

            $limit = 1*2;
            if ($request->limit) {
                $limit = $request->limit * 2;
            }

            $contentALists = Content::select('id','title', 'created_at')->where('primary_topic_id', trim($request->primary_topic))
            ->where('website_id',trim($request->website));
            if($request->child_topic) {
                $contentALists = $contentALists
                ->where('child_topic_id', trim($request->child_topic));
            }
            $contentALists = $contentALists->get();
            
            $commentALists = Comments::select('id','created_at')->where('primary_topic_id', trim($request->primary_topic))
            ->where('website_id',trim($request->website));
            if($request->child_topic) {
                $commentALists = $commentALists
                ->where('child_topic_id', trim($request->child_topic));
            }
            $commentALists = $commentALists->get();
           
            $contentIds = [];
            $commentIds = [];

            $allData = $contentALists->merge($commentALists)->sortByDesc('created_at')->take($limit)
            ->each(function($item) use(&$contentIds, &$commentIds){
                if(array_key_exists('title', $item->toArray())) {
                    array_push($contentIds,$item->id);
                }
                else {
                    array_push($commentIds,$item->id);
                }
            });

            $contentLists = Content::whereIn('id', $contentIds)->get();
            
            $commentLists = Comments::whereIn('id', $commentIds)->get();

            $allData = $contentLists->merge($commentLists)->sortBy('created_at');

            $timeline = [
                'contents' => $contentLists,
                'comments' => $commentLists,
                'content_comment' => $allData,
                'show_more' => ($contentALists->count() + $commentALists->count()) > $allData->count(),
                'primary_topic' => Topics::find($request->primary_topic),
            ];
            
            return $this->handleResponse($timeline, 'Fetched matched record.');            
        }
        catch(Exception $e) 
        {
            logger('content list for timeline error');
            return $this->handleError('Something went wrong', [], 500);
        }

    }

    public function contentShowByRole(Content $content)
    {
        try {   
            $loginUser = Auth::user();             
            if(empty($content)) {
                return $this->handleError('Data not found', [], 404);
            }
            if(($loginUser->role == User::ROLE_WRITER) && !in_array($content->status, [Content::STATUS_OPEN, Content::STATUS_WORKIN_PROGRESS])) {
                return $this->handleError('You are not permitted to view this content', [], 403);
            }
            elseif(($loginUser->role == User::ROLE_CLIENT) && !in_array($content->status, [Content::STATUS_APPROVED])) {
                return $this->handleError('You are not permitted to view this content', [], 403);
            }
            return $this->handleResponse($content, 'Success');
        }
        catch(Exception $e) 
        {
            logger('content show error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function reviewContent(Request $request)
    {
        try {
            $loginUser = Auth::user();
            $input = $request->only('website', 'primary_topic', 'child_topic', 'content_type', 'action', 'content_id');
                
            $validator = Validator::make($input,[
                'website' => 'required|integer',
                'primary_topic' => 'required|integer',
                'child_topic' => 'required|integer',
                'content_type' => 'required',
                'action' => 'required',
                'content_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }

            $childTopic = Topics::where('is_primary_topic', 0)->where('website_id', $request->website)
            ->where('primary_topic_id', $request->primary_topic)
            ->where('id', $request->child_topic)
            ->first();

            $contentDetails = Content::find($request->content_id);

            if(($childTopic->content()->latest()->first()->id != $contentDetails->id) && ($loginUser->role != User::ROLE_CLIENT)) {
                return $this->handleError('You are not permitted to update status to this content.', [], 403);
            }

            if($request->action == 'approve') {
                $contentDetails->status = Content::STATUS_APPROVED;
                $contentDetails->updated_by_id = $loginUser->id;
                $contentDetails->updated_at = Carbon::now()->toDateTimeString();
                $contentDetails->save();
            }
            elseif($request->action == 'reject') {
                $contentDetails->status = Content::STATUS_APPROVED;
                $contentDetails->updated_by_id = $loginUser->id;
                $contentDetails->updated_at = Carbon::now()->toDateTimeString();
                $contentDetails->save();
            }
            else {
                return $this->handleError('content can not update, Wrong action', [], 500);
            }

            $website = Websites::find($request->website);
            $owners = explode(',', $website->owners);
			$time = Carbon::now()->toDateTimeString();
            $notify = [];
            if($loginUser->role == 'writer') {
                foreach($owners as $owner) {
                    $notify[] = [
                        'recipient_user_id' => $owner,
                        'sender_user_id' => $loginUser->id,
						'website_id' => $website->id,
                        'heading' => 'Record updated',
                        'details' => sprintf('%s for %s has been updated.', $contentDetails->title, $website->name),
                        'object_from_type' => Notifications::CONTENT,
                        'object_from_id' => $contentDetails->id,
                        'object_to_type' => Notifications::PRIMARY_TOPICS,
                        'object_to_id' => $contentDetails->primary_topic_id,
						'created_by_id' => $loginUser->id,
						'updated_by_id' => $loginUser->id,
						'created_at' => $time,
						'updated_at' => $time
                    ];
                }
            }
            elseif($loginUser->role == 'client') {
                $notify[] = [
                    'recipient_user_id' => $contentDetails->created_by_id,
                    'sender_user_id' => $loginUser->id,
					'website_id' => $website->id,
                    'heading' => 'Status updated',
                    'details' => sprintf('%s has been %s by %s.', $contentDetails->title, $contentDetails->fresh()->status, $loginUser->name),
                    'object_from_type' => Notifications::CONTENT,
                    'object_from_id' => $contentDetails->id,
                    'object_to_type' => Notifications::PRIMARY_TOPICS,
                    'object_to_id' => $contentDetails->primary_topic_id,
					'created_by_id' => $loginUser->id,
					'updated_by_id' => $loginUser->id,
					'created_at' => $time,
					'updated_at' => $time
                ];
            }
            if(count($notify)) {
                Notifications::insert($notify);
            }

            return $this->handleResponse($contentDetails->fresh(), 'Content updated successfully');            
            
        }
        catch(Exception $e) 
        {
            logger('review content: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }

    }

    public function create(Request $request)
    {
        try {
            $loginUser = Auth::user();
            $input = $request->only('website', 'primary_topic', 'child_topic', 'content_type', 'title', 'description');
                
            $validator = Validator::make($input,[
                'website' => 'required|integer',
                'primary_topic' => 'required|integer',
                'child_topic' => 'required|integer',
                'content_type' => 'required|in:'.Content::CONTENT_TYPE_ARTICLE.','.Content::CONTENT_TYPE_OUTLINE,
                'title' => 'required',
                'description' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
            $website = $request->website;
            if($website == 0) {
                $primary = Topics::find($request->primary_topic);
                $website = $primary->website_id;
            }

            $time = Carbon::now()->toDateTimeString();
            $data = [
                'website_id' => $website,
                'primary_topic_id' => $request->primary_topic,
                'child_topic_id' => $request->child_topic,
                'content_type' => $request->content_type,
                'title' => $request->title,
                'description' => $request->description,
                'user_id' => $loginUser->id,
                'created_by_id' => $loginUser->id,
                'updated_by_id' => $loginUser->id,
                'created_at' => $time,
                'updated_at' => $time
            ];

            $content = Content::create($data);
                      

            if($content) {
                $website = Websites::find($website);
                $owners = explode(',', $website->owners);
                $notify = [];
                foreach($owners as $owner) {
                    $notify[] = [
                        'recipient_user_id' => $owner,
                        'sender_user_id' => $loginUser->id,
						'website_id' => $website->id,
                        'heading' => 'New record Created',
                        'details' => sprintf('New %s has been added to %s, %s.', $request->content_type, $content->topic->topic, $website->name),
                        'object_from_type' => Notifications::CONTENT,
                        'object_from_id' => $content->id,
                        'object_to_type' => Notifications::PRIMARY_TOPICS,
                        'object_to_id' => $content->primary_topic_id,
						'created_by_id' => $loginUser->id,
						'updated_by_id' => $loginUser->id,
						'created_at' => $time,
						'updated_at' => $time
                    ];
                }
                Notifications::insert($notify);
                return $this->handleResponse($content, 'Content created successfully');
            }
            
            return $this->handleError('content can not create', [], 500);            
            
        }
        catch(Exception $e) 
        {
            logger('create content: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }

    }
}

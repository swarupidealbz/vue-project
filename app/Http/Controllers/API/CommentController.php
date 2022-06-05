<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Comments;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Notifications;
use App\Models\Topics;
use App\Models\User;
use App\Models\Websites;
use App\Notifications\ContentAddedNotify;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class CommentController extends BaseController
{
    public function index()
    {
        try {                
            $list = Comments::all();

            return $this->handleResponse($list, 'Fetched all list');
        }
        catch(Exception $e) 
        {
            logger('comment list error:'.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function show(Comments $Comment)
    {
        try {                
            if(empty($Comment)) {
                return $this->handleError([], 'Data not found', 404);
            }

            return $this->handleResponse($Comment, 'Success');
        }
        catch(Exception $e) 
        {
            logger('Comment show error:'.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function commentByUser(Request $request)
    {
        try {
            $lists = Comments::query();
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

            $lists = $lists->get();
            
            return $this->handleResponse($lists, 'Success');            
        }
        catch(Exception $e) 
        {
            logger('Comment list by user error:'.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }

    }

    public function addEditComment(Request $request)
    {
        try {
            $loginUser = Auth::user();
            $input = $request->only('website', 'primary_topic', 'content_type', 'comment', 'action');
                
            $validator = Validator::make($input,[
                'website' => 'required|integer',
                'primary_topic' => 'required|integer',
                'content_type' => 'required',
                'comment' => 'required',
                'action' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }

            // $childTopic = Topics::where('is_primary_topic', 0)->where('website_id', $request->website)
            // ->where('primary_topic_id', $request->primary_topic)
            // ->where('id', $request->child_topic)
            // ->first();

            // if(($childTopic->status != Topics::STATUS_APPROVED) && !in_array($loginUser->role, [User::ROLE_CLIENT, User::ROLE_WRITER])) {
            //     return $this->handleError('You are not permitted to comment.', [], 403);
            // }
			
            $primaryTopic = Topics::when(in_array($loginUser->role, [User::ROLE_CLIENT, User::ROLE_REVIEWER]), function($q) use($request) {
				return $q->where('website_id', $request->website);
			})			
            ->where('id', $request->primary_topic)
            ->first();
			
            if(($primaryTopic->status != Topics::STATUS_APPROVED) && !in_array($loginUser->role, [User::ROLE_CLIENT, User::ROLE_WRITER, User::ROLE_REVIEWER])) {
                return $this->handleError('You are not permitted to comment.', [], 403);
            }

            $message = 'success';
			$website = filled($request->website) && ($request->website != 0) ? $request->website : $primaryTopic->website_id;
			if($request->action == 'edit') {
                $comment_id = $request->comment_id;
                $commentDetails = Comments::find($comment_id);                
                if(!$commentDetails || !$comment_id) {
                    return $this->handleError('Comment not found to update', $commentDetails, 403);
                }
                if($commentDetails->user_id != $loginUser->id) {
                    return $this->handleError('You are not permitted to edit this comment.', $commentDetails, 403);
                }
                $commentDetails->website_id =  $website ;
                $commentDetails->primary_topic_id = $request->primary_topic;
                $commentDetails->child_topic_id = $request->child_topic;
                $commentDetails->comment = $request->comment;
                $commentDetails->updated_by_id = $loginUser->id;
                $commentDetails->updated_at = Carbon::now()->toDateTimeString();
                $commentDetails->save();
                $message = 'Comment updated successfully';
            }
            elseif($request->action == 'add') {
                $time = Carbon::now()->toDateTimeString();
                $insertData = [
                    'website_id' => $website,
                    'primary_topic_id' => $request->primary_topic,
                    'child_topic_id' => $request->child_topic,
                    'user_id' => $loginUser->id,
                    'comment' => $request->comment,
                    'created_by_id' => $loginUser->id,
                    'updated_by_id' => $loginUser->id,
                    'created_at' => $time,
                    'updated_at' => $time
                ];
                $commentDetails = Comments::create($insertData);
                $message = 'Comment added successfully';
            }
            else {
                return $this->handleError('Comment can not save, Wrong action', [], 500);
            }

            if($commentDetails) {
                $websites = Websites::find($website);
                $owners = explode(',', $websites->owners);
                $notify = [];
                foreach($owners as $owner) {
                    if($owner != $loginUser->id) {                            
                        $notify[] = [
                            'recipient_user_id' => $owner,
                            'sender_user_id' => $loginUser->id,
                            'website_id' => $websites->id,
                            'heading' => 'New comment added by '.$loginUser->name,
                            'details' => sprintf('New comment has been added to %s by %s.', $primaryTopic->topic, $loginUser->name),
                            'object_from_type' => Notifications::COMMENT,
                            'object_from_id' => $commentDetails->id,
                            'object_to_type' => Notifications::PRIMARY_TOPICS,
                            'object_to_id' => $commentDetails->primary_topic_id,
                            'created_by_id' => $loginUser->id,
                            'updated_by_id' => $loginUser->id,
                            'created_at' => $time,
                            'updated_at' => $time
                        ];
                        $msg = sprintf('New comment has been added to %s by %s.', $primaryTopic->topic, $loginUser->name);
                        $to = User::find($owner);
                        $url = url('/topic/timeline/'.$commentDetails->primary_topic_id);
                        Notifications::insert($notify);
                        if($to->email) {
                            Notification::send($to, new ContentAddedNotify($msg, $url, $to->first_name));
                        }
                    }
                }
            }

            $contentLists = Content::where('primary_topic_id', trim($request->primary_topic))
            ->where('website_id',$website);
            if($request->child_topic) {
                $contentLists = $contentLists
                ->where('child_topic_id', trim($request->child_topic));
            }
            $contentLists = $contentLists->get();
            
            $commentLists = Comments::where('primary_topic_id', trim($request->primary_topic))
            ->where('website_id',$website);
            if($request->child_topic) {
                $commentLists = $commentLists
                ->where('child_topic_id', trim($request->child_topic));
            }
            $commentLists = $commentLists->get();

            $allData = $contentLists->merge($commentLists)->sortByDesc('created_at')->take(2);

            $timeline = [
                'contents' => $contentLists,
                'comments' => $commentLists,
                'content_comment' => $allData,
                'show_more' => ($contentLists->count() + $commentLists->count()) > $allData->count(),
            ];

            return $this->handleResponse($timeline, $message);            
            
        }
        catch(Exception $e) 
        {
            logger('Comment add edit error:'.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }

    }
}

<?php

namespace App\Resources;

use App\Models\Swap;
use App\Models\Space;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Resources\SwapCollectionResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $spaces_liked_by_other_users = [];
        $has_subscription = null;

        //fetch all swaps for current loggedIn user
        $user_swaps = Auth::user()->swaps->where('status', 'paid');
        $upcoming_swap = [];

        if (!Auth::user()->has_welcome_video_seen) {
            $welcome_clip = asset('videos/welcome_clip.mp4');
        }

        if (!empty($user_swaps)) {
            foreach ($user_swaps as $swap) {
                $upcoming_swap[] = new SwapCollectionResource($swap);
            }
        }


        //fetch loggedin user's spaces
        $spaces = Space::where('user_id', Auth::id())->get();

        foreach ($spaces as $space) {
            $space_id[] = $space->id;
        }


        if (!empty($space)) {
            $fav_spaces =  DB::table('users_favourite_spaces')
                ->select('user_id')
                ->whereIn('space_id', $space_id)
                ->get();

            if (count($fav_spaces)) {
                foreach ($fav_spaces as $fav_space) {
                    $fav_user_id[] = $fav_space->user_id;
                }
                $spaces_liked_by_other_users = User::whereIn('id', $fav_user_id)->get();
            }
        }

        $has_subscription = Subscription::where('user_id',Auth::id())->latest()->first();

        return [
            'age' => $this->getAge(), // null if hidden
            'bio' => $this->bio,
            'date_registered' => $this->created_at->timestamp,
            'gender' => $this->gender,
            'fcm_token' => $this->fcm_token,
            'id' => $this->id,
            'image_url' => $this->getImageUrl(),
            'video_url' => $welcome_clip ?? null,
            'industry' => new CategoryResource($this->industry()),
            'languages' => CategoryResource::collection($this->languages()),
            'last_active_at' => $this->last_active_at->diffForHumans(),
            'lifestyles' => CategoryResource::collection($this->lifestyles()),
            'location' => $this->location,
            'first_name' => $this->trashed()
                ? 'Deactivated User'
                : $this->first_name,
            'personalities' => CategoryResource::collection($this->personalities()),
            'countries' => CategoryResource::collection($this->countries()),
            'is_verified' => $this->isVerified(),
            $this->mergeWhen((int) Auth::id() === (int) $this->id, [
                'completed_percentage' => $this->getCompletedPercentage(),
                'tokens' => (int) $this->getCreditsAmount(),
                'subscription' => new SubscriptionResource($this->activeSubscription),
                'has_subscription' => $has_subscription,
                'date_of_birth' => optional($this->date_of_birth)->timestamp,
                'email' => $this->email,
                'upcoming_swap' => $upcoming_swap,
                'is_age_hidden' => $this->is_age_hidden,
                'last_name' => $this->last_name,
                'favourited' => SpaceCollectionResource::collection($this->favourites),
                'spaces_liked_by_other_users' => SpacesLikedByOtherUsers::collection($spaces_liked_by_other_users),
                'verifications' => UserVerificationResource::collection($this->verifications),
                'has_proposed_before' => Swap::where('user_id', Auth::id())->exists(),
            ]),
        ];
    }
}

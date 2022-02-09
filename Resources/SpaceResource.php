<?php

namespace App\Resources;

use App\Models\Space;
use App\Resources\SpaceReviewResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SpaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $showAddress = $this->showFullAddress($this);

        return [
            $this->mergeWhen($showAddress, [
                'address' => new AddressResource($this->address),
            ]),
            $this->mergeWhen(!$showAddress, [
                'address' => new HiddenAddressResource($this->address),
            ]),

            'accessibilities' => CategoryResource::collection($this->accessibilities()),
            'amenities' => CategoryResource::collection($this->amenities()),
            'atmospheres' => CategoryResource::collection($this->atmospheres()),
            'bathrooms' => $this->bathrooms,
            'bedrooms' => $this->bedrooms,
            'created_at' => $this->created_at->timestamp,
            'is_complete_space_popup_seen'=> Space::find($this->id)->is_space_details_completed,
            'description' => $this->description,
            'guests' => $this->guests,
            'hosting_tokens' => $this->hosting_tokens,
            'id' => $this->id,
            'images' => SpaceImageResource::collection($this->images),
            'is_favourited' => $this->hasBeenFavourited(),
            'is_primary' => $this->is_primary,
            'landscapes' => CategoryResource::collection($this->landscapes()),
            'next_available_date' => $this->getNextAvailableDate()->timestamp,
            'purpose' => new CategoryResource($this->purpose),
            'reviews' => [
                'average' => $this->getAverageReviewRating(),
                'data' => SpaceReviewResourceCollection::collection($this->reviews),
                'total_reviews' => $this->reviews->count(),
            ],
            'rules' => CategoryResource::collection($this->rules()),
            'safeties' => CategoryResource::collection($this->safeties()),
            'status' => $this->status,
            'title' => $this->getTitle(),
            'type' => new CategoryResource($this->type),
            'unavailable_dates' => SpaceUnavailabilityResource::collection($this->unavailability),
            'user' => new UserSubCardResource($this->user),
            $this->mergeWhen((int) Auth::id() === (int) $this->user_id, [
                'completed_percentage' => $this->getCompletedPercentage(),
                'moderator_comments' => $this->moderator_comments,
            ]),
        ];
    }

    public function showFullAddress($space)
    {
        return (int) Auth::id() === (int) $space->user_id
            || Auth::user()
                    ->swaps()
                    ->where('space_id', $space->id)
                    ->paid()
                    ->exists();
    }
}

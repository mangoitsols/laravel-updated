<?php

namespace App\Models;

use App\Models\Traits\ImageTrait;
use App\Helpers\Status;
use App\Models\Traits\PaginatableTrait;
use App\Models\User;
use App\Models\Category;
use Auth;
use Facades\Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Space extends Model
{
    use HasFactory, ImageTrait, SoftDeletes, PaginatableTrait;

    protected $dispatchesEvents = [
        // 'saved' => SpaceSaved::class,
    ];

    protected $fillable = [
        'bathrooms',
        'bedrooms',
        'description',
        'guests',
        'purpose_id',
        'title',
        'type_id',
        'user_id',
        'is_primary',
        'old_id',
        'moderator_comments',
        'is_space_percentage',
        'hosting_tokens',
    ];

    protected $casts = [
        'bathrooms' => 'int',
        'bedrooms' => 'int',
        'guests' => 'int',
        'is_primary' => 'boolean',
        'user_id' => 'int',
        'is_space_percentage' => 'int',
        
    ];

    ///////////////  Helper /////////////////////

    public function isFeatured() : bool
    {
       $this->loadExists('featured');

       return $this->featured_exists;
    }

    public function isLive() : bool
    {
        return in_array($this->status, [Status::LIVE, Status::MODERATED]);
    }

    public function getAverageReviewRating()
    {
        if (!$this->reviews_avg_rating) {
            $this->loadAvg('reviews','rating');
        }

        return (float) $this->reviews_avg_rating;
    }

    public function getReviewCount()
    {
        if (!$this->reviews_count) {
            $this->loadCount('reviews');
        }

        return $this->reviews_count;
    }

    public function getNextAvailableDate()
    {
        $this->loadMissing('unavailability');

        $dates = $this->unavailability;

        if (count($dates) === 0) {
            return now()->startOfDay();
        }

        // @todo candidate for refactoring perhaps
        for ($i = 0; $i < count($dates); $i++) {

            if ($i == 0 && $dates[$i]->started_at > now() && now()->diffInDays($dates[$i]->started_at) > 0) {
                return now()->startOfDay();
            }

            if (!isset($dates[$i+1])) {
                return $dates[$i]->ended_at->addDay()->startOfDay();
            }

            if ($dates[$i]->ended_at->diffInDays($dates[$i+1]->started_at) === 0) {
                continue;
            }

            return $dates[$i]->ended_at->addDay()->startOfDay();
        }
    }

    public function isComplete() : bool
    {
        return $this->getCompletedPercentage() === 100;
    }

    public function getCompletedPercentage() : int
    {
        // 7
        $requiredAttributes = [
            'bathrooms',
            'bedrooms',
            'description',
            'guests',
            'purpose_id',
            'title',
            'type_id',
        ];

        $count = 0;
        foreach ($requiredAttributes as $attribute) {
           if (!is_null($this->$attribute)) {
                $count++;
            }
        }

        $this->loadMissing('categories', 'address')->loadCount('images');

        if ($this->address && $this->address->isComplete()) {
            $count++;
        }

        if ($this->images_count >= 3) {
            $count++;
        }

        // if (count($this->accessibilities()) > 0) {
        //     $count++;
        // }

        if (count($this->amenities()) > 0) {
            $count++;
        }

        if (count($this->atmospheres()) > 0) {
            $count++;
        }

        if (count($this->landscapes()) > 0) {
            $count++;
        }

        // if (count($this->rules()) > 0) {
        //     $count++;
        // }

        // if (count($this->safeties()) > 0) {
        //     $count++;
        // }

        return (int) round($count / 12 * 100);
    }

    public function getCoverImage()
    {
        $this->loadMissing('images');

        if (count($this->images) === 0) {
           return $this->getPlaceholderImage();
        }

        $coverImage = $this->images->where('is_cover', 1)->first();

        return $coverImage
            ? $coverImage->getImageUrl()
            : $this->images->first()->getImageUrl();
    }

    public function getTitle()
    {
        if ($this->title) {
            return $this->title;
        }

        if ($this->trashed()) {
            return null;
        }

        $this->loadMissing('address', 'type');

        if (!$this->address || !$this->address->city || !$this->type) {
            return null;
        }

        $return = $this->type->name ?? 'Nice place';
        $return .= ' in ';
        $return .= $this->address->city;

        return $return;
    }

    public function hasBeenFavourited($user = null)
    {
        if (!$user && !Auth::check()) {
            return false;
        }

        if (!$user) {
            $user = Auth::user();
        }

        return $user->favourites()
            ->whereSpaceId($this->id)
            ->exists();
    }

    ///////////////   RELATIONSHIPS /////////////////////

    public function address()
    {
        return $this->morphOne(Address::class, 'entity');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'spaces_categories');
    }

    public function type()
    {
        return $this->belongsTo(Category::class);
    }

    public function purpose()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(SpaceImage::class)
            ->oldest('order');
    }
    public function viewspace()
    {
        return $this->hasMany(UserSeenSpace::class);
    }

    public function accessibilities()
    {
        return $this->categories
            ->where('type_id', CategoryType::ACCESSIBILITY);
    }

    public function amenities()
    {
        return $this->categories
            ->where('type_id', CategoryType::AMENITY);
    }

    public function atmospheres()
    {
        return $this->categories
            ->where('type_id', CategoryType::ATMOSPHERE);
    }

    public function landscapes()
    {
        return $this->categories
            ->where('type_id', CategoryType::LANDSCAPE);
    }

    public function reviews()
    {
        return $this->hasMany(SpaceReview::class);
    }

    public function rules()
    {
        return $this->categories
            ->where('type_id', CategoryType::RULE);
    }

    public function safeties()
    {
        return $this->categories
            ->where('type_id', CategoryType::SAFETY);
    }

    public function unavailability()
    {
        // view only gets future/current dates
        return $this->hasMany(SpaceUnavailabilityView::class)
            ->orderBy('started_at', 'asc');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function favouritedBy()
    {
        return $this->belongsToMany(User::class, 'users_favourite_spaces');
    }

    public function featured()
    {
        return $this->morphOne(Featured::class, 'entity');
    }

    public function moderatedHistory()
    {
        return $this->morphMany(ModeratedHistory::class, 'entity')
            ->latest();
    }

    /// SCOPES

    public function scopeLive($q)
    {
        return $q->whereStatus(Status::LIVE);
    }

    public function scopeLiveOrModerated($q)
    {
        return $q->whereIn('status', [Status::LIVE, Status::MODERATED]);
    }

    public function scopeWithFeaturedOrderColumn($q)
    {
        $q->addSelect(['order' => Featured::select('order')
            ->whereColumn('entity_id', $this->getTable() . '.id')
            ->where('entity_type', ucfirst(Str::singular($this->getTable())))
            ->orderBy('created_at', 'desc')
            ->limit(1)]);
    }

    public function scopeSearchWithinRadius($query, $location, $radius = 5)
    {
        // 3961 = M
        // 6371 = KM
        $haversine = "(3961 * acos(cos(radians(". $location['lat']. "))
                        * cos(radians(addresses.latitude))
                        * cos(radians(addresses.longitude)
                        - radians(". $location['lng'] . "))
                        + sin(radians(". $location['lat'] . "))
                        * sin(radians(addresses.latitude))))";
        return $query->whereHas('address', function($q) use ($haversine, $radius) {
            $q->selectRaw("{$haversine} AS distance")
                ->whereRaw("{$haversine} < ?", [$radius]);
        });
   }
}

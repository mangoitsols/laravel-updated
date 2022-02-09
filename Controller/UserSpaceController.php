<?php

namespace App\Http\Controllers;

use App\Actions\Spaces\UpdateSpaceAction;
use App\Events\FreeTokenForInviter;
use App\Events\SpaceViewed;
use App\Http\Requests\SpaceCreateRequest;
use App\Http\Requests\SpaceUpdateRequest;
use App\Http\Requests\UserViewSpaceRequest;
use App\Models\Space;
use App\Models\UsersSeenSpace;
use App\Models\UserInvitation;
use App\Resources\SpaceCollectionResource;
use App\Resources\SpaceResource;
use App\Resources\SpaceResourceWithIdsOnly;
use Auth;
use Stevebauman\Location\Facades\Location;
use DB;

class UserSpaceController extends Controller
{
    public function index($userId = null)
    {
        if (!$userId) {
            $userId = Auth::id();
        }

        $query = Space::query()
            ->whereUserId($userId);

        if ((int) $userId !== Auth::id()) {
           $query->liveOrModerated();
        }

        $spaces = $query
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->with(
                'user',
                'address',
                'images',
                'purpose',
                'type'
            )->paginate();

        return SpaceCollectionResource::collection($spaces);
    }

    public function show(int $spaceId)
    {
        $space = $this->getFreshSpace($spaceId, Auth::id());

        if (!$space || (int) $space->user_id !== (int) Auth::id()) {
            return abort(404);
        }

        if ((int) Auth::id() !== (int) $space->user_id) {
            SpaceViewed::dispatch($space);
        }

        // @todo TDD..
        return new SpaceResourceWithIdsOnly($space);
    } 

     public function showviewspace(int $spaceId)
    {
        $space = $this->getViewSpace($spaceId, Auth::id());

        if (!$space || (int) $space->user_id !== (int) Auth::id()) {
            return abort(404);
        }

        // if ((int) Auth::id() !== (int) $space->user_id) {
        //     SpaceViewed::dispatch($space);
        // }

        // @todo TDD..
        return $space;
        // return new SpaceResourceWithIdsOnly($space);
    }

    public function store(SpaceCreateRequest $request)
    {
        if($request->purpose_id != 521){
            if(empty($request->hosting_tokens)){
                abort(403, 'hosting tokens are required');
            }
        }
      
        $space = Space::create([
            'user_id' => Auth::id(),
        ]);


        if(!empty($space)){

            $invitation = UserInvitation::where([['invitee_id', '=', $space->user_id],['token_expired', '=', 0]])->first();

            if(!empty($invitation)){
                event(new FreeTokenForInviter($invitation));
            }
        }

        (new UpdateSpaceAction())
            ->handle($space, $request->all());

        return (new SpaceResource($this->getFreshSpace($space->id)))
            ->response()
            ->setStatusCode(201);
    }

    public function update(int $spaceId, SpaceUpdateRequest $request)
    {
        $space = Space::whereUserId(Auth::id())->findOrFail($spaceId);

        (new UpdateSpaceAction())
            ->handle($space, $request->all());

        return (new SpaceResource($this->getFreshSpace($space->id)))
            ->response()
            ->setStatusCode($space->wasRecentlyCreated ? 201 : 200);
    }

    // @todo remove..
    public function destroy()
    {
        // if (isProduction()) {
        //     abort(403, 'Sorry not in production');
        // }

        Space::whereUserId(Auth::id())->delete();

        return $this->okResponse();
    }

    public function storeViewSpace(UserViewSpaceRequest $request)
    {
        $ip = $request->ip();
        // $ip = '162.159.24.227';
        $spaceId = $request->space_id;
        $userId = $request->user_id;
        $currentUserInfo = Location::get($ip);
        $country = $currentUserInfo->countryName;
        return $this->addViewSpace($spaceId,$userId,$country);
    }

    private function getFreshSpace($id, $userId = null)
    {
        $space = Space::with(
            'address',
            'categories',
            'images',
            'purpose',
            'type',
            'unavailability',
            'user'
        )->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->withTrashed();

        return $id
            ? $space->find($id)
            : $space->whereUserId($userId)->first();
    }

     private function addViewSpace($id, $userId = null,$country = null)
    {
       $views = UsersSeenSpace::whereSpaceId($id)->whereUserId($userId)->first();
        if(!$views){
            UsersSeenSpace::create([
            'user_id' => $userId,
            'space_id' => $id,
            'country' => $country,
        ]);

            return true;
        }
        else{
            return false;
        }
    }

    private function getViewSpace($id, $userId = null)
    {
        $space = UsersSeenSpace::with('user','space')->whereSpaceId($id)->whereUserId($userId)->get();

        return  $space;
    }

    public function spaceDetailsCompleted(int $spaceId)
    {
       $space=Space::find($spaceId);
       $space->is_space_details_completed=1;
       $space->update();

       return $this->okResponse();
    }

    public function spacePercentage(UserViewSpaceRequest $request)
    {
        // return $request->id;
       $space=DB::table('spaces')->where('id','=', $request->id)->update(['is_space_percentage' => $request->percentage]);
       dd($space);
       

       return $this->okResponse();
    }

}

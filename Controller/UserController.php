<?php

namespace App\Http\Controllers;

use App\Actions\Users\UpdateUserAction;
use App\Events\UserViewed;
use App\Http\Requests\UserUpdateRequest;
use App\Jobs\DeactivateUserJob;
use App\Resources\UserResource;
use App\Models\User;
use App\Models\UserInvitation;
use App\Models\UserUninterestedSpace;
use Facades\Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Notification;
use App\Notifications\SendPushNotification;

class UserController extends Controller
{
    public function show(?User $user)
    {
        // for some reason $user is still coming back as an object in users/me
        if (!$user->id) {
            $user = Auth::user();
        }

        // user can still do users/my-id to view their own profile so
        // separate check here as opposed to adding else to above if
        if ((int) $user->id !== Auth::id()) {
            UserViewed::dispatch($user);
        }

        $user = $this->loadUserRelations($user);

        return new UserResource($user);
    }

    // @todo if updating EMAIL/Name, also update on stripe.
    public function update(UserUpdateRequest $request)
    {
        (new UpdateUserAction())
            ->handle($request->all(), Auth::user());

        $user = $this->loadUserRelations(Auth::user()->fresh());

        return new UserResource($user);
    }

    public function destroy()
    {
        DeactivateUserJob::dispatch(User::find(Auth::id()));

        return $this->okResponse(__('Your account is now in a queue to be removed. If we encounter any problems, we will email you.'));
    }

    private function loadUserRelations($user)
    {
        if ($user->id === Auth::id()) {
            $user->loadSum('creditHistory', 'amount');
            $user->load('activeSubscription');

            // @todo maybe remove the with DEFAULT option from active subscription
            if (!$user->activeSubscription->source) {
                $user->setRelation('activeSubscription', null);
            }

            $user->setRelation('favourites', $user->favourites()->paginate(5));
        }

        $user->load('categories');

        return $user;
    }

    public function storeUninterestedSpace(Request $request)
    {
       $user_uninterested_space = new UserUninterestedSpace;
       $user_uninterested_space->user_id = $request->user_id;
       $user_uninterested_space->space_id = $request->space_id;
       $user_uninterested_space->save();

       return response()->json($user_uninterested_space, 200);
    }

    public function createInvitationCode()
    {
        $user_invite_code = User::where('id',Auth::id())->first();

        if(empty($user_invite_code->invite_code)){
            $invite_code = Str::random();
            $user = User::find(Auth::id());
            $user->invite_code = $invite_code;
            $user->update();

            return response(['invite_code' => $invite_code]);
        }

        $invite_code = User::where('id', Auth::id())->select('invite_code')->first();
        return response($invite_code);
    }

    public function updateToken(Request $request){
        try{
            User::where('id',Auth::id())->update(['fcm_token'=>$request->token]);
            return response()->json([
                'success'=>true
            ]);
        }catch(\Exception $e){
            report($e);
            return response()->json([
                'success'=>false
            ],500);
        }
	}

}

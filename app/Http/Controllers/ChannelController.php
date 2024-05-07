<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Itv;
use App\Models\TvGenre;
use App\Services\ItvService;

class ChannelController extends Controller
{
    protected $itvService;

    /**
     * Constructor method for ChannelController.
     * Applies middleware to specified controller actions.
     *
     * @param  \App\Services\ItvService  $itvService
     * @return void
     */
    public function __construct(ItvService $itvService)
    {
        $this->itvService = $itvService;
        $this->middleware('permission:channel_view')->only(['index', 'show']);
        $this->middleware('permission:channel_add')->only(['create', 'store']);
        $this->middleware('permission:channel_edit')->only(['edit', 'update', 'updateStatus']);
        $this->middleware('permission:channel_delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $channels = Itv::orderBy('number', 'asc')->get();
        return view('channels.index', compact('channels'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $genres = TvGenre::all();
        return view('channels.create', compact('genres'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'number' => 'required|integer|min:0|max:999|unique:itv',
            'name' => 'required|string|max:128|unique:itv',
            'genre' => 'required|integer',
            'logo' => 'image|max:1024',     // mimes:png,  Add dimensions rule for logo:-  dimensions:width=96,height=96'
            'enable_tv_archive' => 'nullable|boolean',
            'mc_cmd' => 'nullable|url|max:128',
            'tv_archive_duration' => 'nullable|min:0',
            'xmltv_id' => 'nullable|string|max:128',
            'epg_offset' => 'nullable|min:0',
            'censored' => 'nullable|boolean',
            'links.url.*' => 'required|url',
            'links' => 'nullable'
        ], [
            'number.required' => 'The channel number is required',
            'name.required' => 'The channel name is required',
            'mc_cmd.url' => 'The TV archive must be a valid URL address',
            'links.url.*.required' => 'The URL is required.',
            'links.url.*.url' => 'The URL must be a valid URL.',
        ]);

        $response = $this->itvService->storeChannel($validatedData);

        if ($response) {
            session()->flash('success', 'Channel created successfully.');
            return response()->json(['message' => 'Channel created successfully.'], 200);
        } else {
            session()->flash('error', 'Failed to create channel.');
            return response()->json(['error' => 'Failed to create channel.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(string $id)
    {
        $itv = Itv::findOrFail($id);
        $genres = TvGenre::all();
        return view('channels.edit', compact('itv', 'genres'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        $itv = Itv::findOrFail($id);
        $validatedData = $request->validate([
            'number' => 'required|integer|min:0|max:999|unique:itv,number,' . $itv->id,
            'name' => 'required|string|max:128|unique:itv,name,' . $itv->id,
            'genre' => 'required|integer',
            'logo' => 'image|max:1024', // mimes:png,  Add dimensions rule for logo:- dimensions:width=96,height=96'
            'enable_tv_archive' => 'nullable|boolean',
            'mc_cmd' => 'nullable|url|max:128',
            'tv_archive_duration' => 'nullable|min:0',
            'xmltv_id' => 'nullable|string|max:128',
            'epg_offset' => 'nullable|min:0',
            'censored' => 'nullable|boolean',
            'links.url.*' => 'required|url',
            'links' => 'nullable'
        ], [
            'number.required' => 'The channel number is required',
            'name.required' => 'The channel name is required',
            'mc_cmd.url' => 'The TV archive must be a valid URL address',
            'links.url.*.required' => 'The URL is required.',
            'links.url.*.url' => 'The URL must be a valid URL.',
        ]);

        $response = $this->itvService->updateChannel($itv, $validatedData, $request);

        if ($response) {
            session()->flash('success', 'Channel updated successfully.');
            return response()->json(['message' => 'Channel updated successfully.'], 200);
        } else {
            session()->flash('error', 'Failed to update channel.');
            return response()->json(['error' => 'Failed to update channel.']);
        }
    }

    /**
     * Update the status of a channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $status = $request->input('status');

        $channel = Itv::findOrFail($request->id);
        $channel->status = $status;
        $channel->save();

        return response()->json(['message' => 'Channel status updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        $itv = Itv::findOrFail($id);

        $response = $this->itvService->deleteChannel($itv);
        if ($response) {
            return redirect()->route('channels.index')
                ->with('success', 'Channel deleted successfully');
        } else {
            return redirect()->route('channels.index')
                ->with('error', 'Failed to delete channel.');
        }
    }
}

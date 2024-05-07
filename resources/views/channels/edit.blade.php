@extends('layouts.app')

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Edit Channel'])
    <div class="row mt-4 mx-4">
        <div class="col-12">

            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Edit Channel</h6>
                </div>
                @if (session('error'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                        <span class="alert-text">{{ session('error') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="container">
                        <form id="editChannelForm" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="edit_channel_id" value="{{ $itv->id }}" name="channel_id">
                            <!-- Basic Section -->
                            <div class="container m-2 p-2">
                                <div id="basic-section">
                                    <h6>Basic</h6>
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="number">Channel Number <span class="required">*</span></label>
                                            <input type="number" class="form-control" id="number" name="number"
                                                value="{{ $itv->number }}">
                                            <span class="invalid-feedback text-sm text-danger"></span>
                                            <p class="form-text text-muted comment">Number in channel list must contain only
                                                numbers
                                                from 0
                                                to
                                                999</p>
                                        </div>
                                        <div class="form-group">
                                            <label for="name">Channel Name <span class="required">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                value="{{ $itv->name }}">
                                            <span class="invalid-feedback text-sm text-danger"></span>
                                            <p class="form-text text-muted comment">You can use Latin letters, digits and
                                                symbols
                                                from
                                                the
                                                list
                                                ! @ # $ % ^ & * ( ) _ - + : ; ,</p>
                                        </div>
                                        <div class="form-group">
                                            <label for="genre">Genre <span class="required">*</span></label>
                                            <select class="form-control" id="genre" name="genre">
                                                <option value="">Select a genre</option>
                                                @foreach ($genres as $genre)
                                                    <option value="{{ $genre->id }}"
                                                        {{ $itv->tv_genre_id == $genre->id ? 'selected' : '' }}>
                                                        {{ $genre->title }}</option>
                                                @endforeach
                                            </select>
                                            <span class="invalid-feedback text-sm text-danger"></span>
                                        </div>

                                        <div class="form-check form-switch mb-4 mt-4">
                                            <input class="form-check-input" type="checkbox" id="status" name="status"
                                                value="1" {{ $itv->status == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status">Status</label>
                                        </div>

                                        <div class="form-group flex-0">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <label for="channel_logo">Channel Logo</label>
                                                    <input type="file" class="form-control-file" id="channel_logo"
                                                        name="logo" accept="image/*">
                                                    <span class="invalid-feedback text-sm text-danger"></span>
                                                    <p class="form-text text-muted comment">Recommended format - png, no
                                                        smaller
                                                        than
                                                        96*96
                                                        pixels,
                                                        maximum
                                                        size of the file - 1 MB.</p>
                                                </div>
                                                <div class="col-md-4">
                                                    @if ($itv->logo)
                                                        <img class="mx-auto" src="{{ asset($itv->logo) }}"
                                                            alt="Channel Logo" height="60" width="60">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Streaming link</label>
                                            @include('channels.links.links-table')
                                            <a class="btn btn-primary" id="addStreamingLink"><i
                                                    class="bi bi-plus-circle"></i>
                                                Add Link</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="container m-2 p-2">
                                <!-- TV Archive Section -->
                                <div id="tv-archive-section">
                                    <h6>TV Archive</h6>
                                    <div class="container">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="enable_tv_archive"
                                                    value="1" name="enable_tv_archive"
                                                    {{ $itv->enable_tv_archive ? 'checked' : '' }}>
                                                <label class="form-check-label" for="enable_tv_archive">Enable TV
                                                    archive</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="mc_cmd">TV archive address</label>
                                            <input type="text" class="form-control" id="mc_cmd" name="mc_cmd"
                                                value="{{ $itv->mc_cmd }}">
                                            <span class="invalid-feedback text-sm text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="tv_archive_duration">TV archive length</label>
                                            <input type="number" min='0' class="form-control"
                                                id="tv_archive_duration" name="tv_archive_duration"
                                                value="{{ $itv->tv_archive_duration }}">
                                            <span class="invalid-feedback text-sm text-danger"></span>
                                            <p class="form-text text-muted comment">Measured in hours</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="container m-2 p-2">
                                <!-- TV Archive Section -->
                                <div id="epg-section">
                                    <h6>EPG</h6>
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="xmltv_id">XMLTV ID</label>
                                            <input type="text" class="form-control " id="xmltv_id" name="xmltv_id"
                                                value="{{ $itv->xmltv_id }}">
                                            <span class="invalid-feedback text-sm text-danger"></span>
                                            <p class="form-text text-muted comment">XMLTV Id of channel in EPG file. If the
                                                EPG
                                                source
                                                has
                                                the
                                                prefix state it in _ XMLTV ID format</p>
                                        </div>
                                        <div class="form-group">
                                            <label for="epg_offset">Time correction for EPG</label>
                                            <input type="number" min='0' class="form-control" id="epg_offset"
                                                name="epg_offset" value="{{ $itv->epg_offset }}">
                                            <span class="invalid-feedback text-sm text-danger"></span>
                                            <p class="form-text text-muted comment">Measured in minutes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="container m-2 p-2">
                                <!-- Parental Control Section -->
                                <div id="parental-control-section">
                                    <h6>Parental Control</h6>
                                    <div class="container">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="censored"
                                                    value="1" name="censored" {{ $itv->censored ? 'checked' : '' }}>
                                                <label class="form-check-label" for="censored">Age restriction</label>
                                            </div>
                                            <p class="form-text text-muted comment">Sets the password retriction on the
                                                channel.
                                                Default
                                                password
                                                0000</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('channels.index') }}" class="btn btn-primary backButton">Back</a>
                        </form>
                    </div>
                </div>

                @include('channels.modals')
            </div>
        </div>
    </div>
@endsection
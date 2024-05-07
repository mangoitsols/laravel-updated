@extends('layouts.app')

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Channels'])

    <div class="row mt-4 mx-4">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div>CHANNELS</div>
                    @if (app('UtilityService')->checkPermission('channel_add'))
                        <a href="{{ route('channels.create') }}" class="btn btn-primary btn-sm my-2" id="addChannelButton">
                            <i class="bi bi-plus-circle"></i> Add New Channel
                        </a>
                    @endif
                </div>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                        <span class="alert-text">{{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
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
                    <div class="table-responsive p-0">
                        <div class="container">
                            <table id="channelTable" class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Number
                                        </th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Logo
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Title
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Genre
                                        </th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Archive
                                        </th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status
                                        </th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($channels as $channel)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <p class="mx-auto text-sm font-weight-bold mb-0">{{ $channel->number }}
                                                    </p>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($channel->logo)
                                                    <div class="d-flex align-items-center">
                                                        <img class="mx-auto" src="{{ asset($channel->logo) }}"
                                                            alt="Channel Logo" height="50" width="50">
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <h6 class="mb-0 text-sm">{{ $channel->name }}</h6>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">
                                                    @if ($channel->tvGenre)
                                                        {{ $channel->tvGenre->title }}
                                                    @endif
                                                </p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-sm font-weight-bold mb-0">
                                                    @if ($channel->enable_tv_archive == 1)
                                                        Yes
                                                    @else
                                                        No
                                                    @endif
                                                </p>
                                            </td>
                                            <td class="text-center">
                                                <div
                                                    class="form-check form-switch d-flex justify-content-center align-items-center mb-0">
                                                    @csrf
                                                    <input class="form-check-input status-toggle" type="checkbox"
                                                        name="status" value="1" data-id="{{ $channel->id }}"
                                                        {{ $channel->status == 1 ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center">
                                                    @if (app('UtilityService')->checkPermission('channel_edit'))
                                                        <a href={{ route('channels.edit', $channel->id) }}
                                                            class="btn btn-link edit-channel-button mb-0">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                    @endif
                                                    @if (app('UtilityService')->checkPermission('channel_delete'))
                                                        <button type="button" class="btn btn-link ml-3 delete-button mb-0"
                                                            data-toggle="modal" data-id="{{ $channel->id }}">
                                                            <i class="fas fa-trash-alt"></i> Delete
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
                aria-labelledby="confirmDeleteModal" aria-hidden="true">
                @include('channels.delete-channel')
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Tables'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Print Activity List</h6>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Issue</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Occurred At</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>

                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse ($activities as $activity)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        <img src="/img/team-2.jpg" class="avatar avatar-sm me-3"
                                                            alt="user1">
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">#{{ $activity->id }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $activity->description }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $activity->created_at }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @if ($activity->isSuccess )
                                                <span
                                                class="badge badge-sm bg-gradient-success">Success</span>
                                                    @else
                                                    <span
                                                class="badge badge-sm bg-gradient-warning">Failed</span>
                                                @endif
                                               
                                            </td>

                                            {{-- <td class="align-middle">
                                            <a href="javascript:;" class="text-secondary font-weight-bold text-xs"
                                                data-toggle="tooltip" data-original-title="Edit user">
                                                Edit
                                            </a>
                                        </td> --}}
                                        </tr>
                                    @empty
                                    @endforelse

                                </tbody>
                            </table>
                            <div class="mt-1 ">
                                {{-- {{$activities->links()}} --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth.footer')
    </div>
@endsection

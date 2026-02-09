@php use App\Enums\HideRatingsOption; @endphp
@extends('layouts.app')

@section('content')
    <h1>ratings</h1>
    <h3 class="mb-3">{{ $user->link }}</h3>
    @if ($user->hide_ratings != HideRatingsOption::ALL->value || Auth::id() == $user->id)
        <div class="card mb-3">
            <div class="card-body">
                {{ html()->form('GET', route('users.ratings', ['id' => $user->id]))->open() }}
                <div class="row mb-3 g-4">
                    <div class="col-md-3">
                        <div class="mb-3">
                            {{ html()->label('score', 'score')->class('form-label') }}
                            {{ html()->select('score', $ratingOptions, $score)->class('form-select form-select-sm') }}
                        </div>
                        <div>
                            {{ html()->label('sort', 'sort')->class('form-label') }}
                            <div class="d-flex">
                                {{ html()->select('sort', $sortOptions, $sort)->class('form-select form-select-sm me-2') }}
                                {{ html()->select('sort_dir', $sortDirectionOptions, $sortDirection)->class('form-select form-select-sm') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        {{ html()->label('star rating', 'sr')->class('form-label') }}
                        <div class="d-flex">
                            {{ html()->text('sr_min', $srMin)->class('form-control form-control-sm')->placeholder('min') }}
                            <span class="mx-2">to</span>
                            {{ html()->text('sr_max', $srMax)->class('form-control form-control-sm')->placeholder('max') }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        {{ html()->label('ranked year', 'year')->class('form-label') }}
                        <div class="d-flex">
                            {{ html()->select('year_min', $yearOptions, $yearMin)->class('form-select form-select-sm') }}
                            <span class="mx-2">to</span>
                            {{ html()->select('year_max', $yearOptions, $yearMax)->class('form-select form-select-sm') }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        {{ html()->label('mapper', 'mapper')->class('form-label') }}
                        {{ html()->text('mapper', $mapperNameOrId)->class('form-control form-control-sm')->placeholder('username or id') }}
                    </div>
                </div>
                {{ html()->submit('filter')->class('btn btn-primary float-end') }}
                {{ html()->form()->close() }}
            </div>
        </div>
        {{ $ratings->links() }}
        <div class="list-group mb-3">
            @forelse ($ratings as $rating)
                <x-ratings.rating_list_group :rating="$rating"/>
            @empty
                <div class="text-muted">no ratings found.</div>
            @endforelse
        </div>
        {{ $ratings->links() }}
    @else
        <div class="alert alert-sm alert-primary" data-bs-theme="dark">
            this user's ratings are private.
        </div>
    @endif
@endsection

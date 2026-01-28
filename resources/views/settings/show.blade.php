@extends('layouts.app')

@section('content')
    <h1>settings</h1>
    <div class="row">
        <div class="col-md-6">
            <h3>general</h3>
            <div class="card mt-3">
                <div class="card-header"><b>content filtering</b></div>
                <div class="card-body">
                    {{ html()->form('POST', route('settings.enabled_modes'))->class('')->open() }}
                        {{ html()->label('enabled modes:')->class('form-label') }}
                        <div class="form-check">
                            {{ html()->checkbox('osu', Auth::user()->hasModeEnabled(\App\Enums\BeatmapMode::OSU))->class('form-check-input') }}
                            {{ html()->label('osu', 'osu')->class('form-check-label') }}
                        </div>
                        <div class="form-check">
                            {{ html()->checkbox('taiko', Auth::user()->hasModeEnabled(\App\Enums\BeatmapMode::TAIKO))->class('form-check-input') }}
                            {{ html()->label('taiko', 'taiko')->class('form-check-label') }}
                        </div>
                        <div class="form-check">
                            {{ html()->checkbox('fruits', Auth::user()->hasModeEnabled(\App\Enums\BeatmapMode::FRUITS))->class('form-check-input') }}
                            {{ html()->label('fruits', 'fruits')->class('form-check-label') }}
                        </div>
                        <div class="form-check">
                            {{ html()->checkbox('mania', Auth::user()->hasModeEnabled(\App\Enums\BeatmapMode::MANIA))->class('form-check-input') }}
                            {{ html()->label('mania', 'mania')->class('form-check-label') }}
                        </div>
                        {{ html()->submit('submit')->class('btn btn-primary float-end') }}
                    {{ html()->form()->close() }}
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header"><b>privacy</b></div>
                <div class="card-body">
                    {{ html()->form('POST', route('settings.hide_ratings'))->open() }}
                        <div class="mb-3">
                            {{ html()->label('hide ratings:', 'hide_ratings')->class('form-label') }}
                            {{ html()->select('hide_ratings', $hideRatingsOptions, Auth::user()->hide_ratings->value)->class('form-select') }}
                        </div>
                        {{ html()->submit('submit')->class('btn btn-primary float-end') }}
                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <h3>profile</h3>
            <small>coming soon!</small>
        </div>
    </div>
@endsection

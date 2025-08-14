@extends('layouts.app')

@section('content')
    <h1 class="mb-3">lists - new</h1>
    <form href="{{ route('lists.new.post') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">name</label>
            <input class="form-control" name="name">
        </div>
        <div class="mb-3">
            <label class="form-label">description</label>
            <textarea class="form-control" name="description"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">submit</button>
    </form>
@endsection

<ul id="ratings-list" class="list-group mb-3">
    @foreach ($ratings as $rating)
        <div class="list-group-item d-flex align-items-center ps-1 pe-2">
            <a href="{{ url("/users/".$rating->user->id) }}" class="d-flex align-items-start flex-nowrap ms-1">
                <img src="{{ $rating->user->avatar_url }}" width="16" height="16" alt="Avatar">
                <small class="ms-2">{{ $rating->user->name }}</small>
            </a>
            <small class="ms-1">rated <strong>[{{ $rating->beatmap->difficulty_name }}]</strong></small>
            <span class="ms-auto badge bg-main fs-6"><small>{{ number_format($rating->score / 2, 1) }}</small></span>
            <small class="ms-2 text-nowrap" title="{{ $rating->updated_at }}">{{ $rating->updated_at->diffForHumans() }}</small>
        </div>
    @endforeach
</ul>

<div class="float-end">
    {{ $ratings->links() }}
</div>

@section('scripts')
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.pagination a')) {
                e.preventDefault();

                const list = document.getElementById('ratings-list');
                list.style.opacity = '0.5';

                const url = e.target.closest('.pagination a').href;

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            console.error('network response was not ok');
                        }
                        return response.text();
                    })
                    .then(html => {
                        const container = document.getElementById('ratings');
                        container.innerHTML = html;
                        list.style.opacity = '1';
                    })
                    .catch(error => {
                        console.error('error:', error);
                        list.style.opacity = '1';
                    });
            }
        });
    </script>
@endsection

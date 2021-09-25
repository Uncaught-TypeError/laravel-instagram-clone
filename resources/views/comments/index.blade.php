@if(request()->routeIs('posts.index') && $post->comments_count > 2 )
    <a href="{{ route('posts.show', $post) }}" class="card-blockquote text-sm px-4 py-2">
        See all {{ $post->comments_count }} comments..
    </a>
@endif

<div class="card-body p-0 {{ request()->routeIs('posts.index') ? '' : 'overflow-hidden rounded-bottom' }}">
    <ul class="list-group list-group-flush" id="commentList-{{ $post->slug }}">
        @forelse($post->comments as $comment)
            @include('comments._single-comment')
        @empty
        @endforelse
    </ul>
</div>

<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>{{ $entity->name }}</title>
        <link>{{ url('/' . $entity->slug) }}</link>
        <description>آخرین خبرها و مقالات {{ $entity->name }}</description>
        <language>fa-ir</language>
        <pubDate>{{ now()->toRfc2822String() }}</pubDate>
        @foreach($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ url('/' . $entity->slug . '/' . $post->slug) }}</link>
                <description><![CDATA[{!! $post->excerpt !!}]]></description>
                <pubDate>{{ $post->published_at?->toRfc2822String() ?? $post->created_at->toRfc2822String() }}</pubDate>
                <guid>{{ url('/' . $entity->slug . '/' . $post->slug) }}</guid>
            </item>
        @endforeach
    </channel>
</rss>

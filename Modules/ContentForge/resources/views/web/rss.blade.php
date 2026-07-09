<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>{{ $entity->name }}</title>
        <link>{{ $entity->url }}</link>
        <description>آخرین خبرها و مقالات {{ $entity->name }}</description>
        <language>fa-ir</language>
        <pubDate>{{ now()->toRfc2822String() }}</pubDate>
        @foreach($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ $post->url }}</link>
                <description><![CDATA[{!! $post->excerpt !!}]]></description>
                <pubDate>{{ $post->published_at?->toRfc2822String() ?? $post->created_at->toRfc2822String() }}</pubDate>
                <guid>{{ $post->url }}</guid>
            </item>
        @endforeach
    </channel>
</rss>

@extends('layouts.store-pwa')

@section('seo')
  <link rel="canonical" href="{{ url('/revista/' . $post->slug) }}">
  <meta property="og:type" content="article">
  <meta property="og:title" content="{{ $post->title }} | Revista Digital">
  <meta property="og:description" content="{{ Str::limit(strip_tags($post->excerpt), 160) }}">
  <meta property="article:published_time" content="{{ optional($post->published_at)->toIso8601String() }}">
  <meta property="article:author" content="{{ $post->author_name ?? 'Editorial Torzo' }}">

  <!-- Schema.org JSON-LD para Artículo Editorial -->
  <script type="application/ld+json">
  {!! json_encode([
    '@context' => 'https://schema.org/',
    '@type' => 'Article',
    'headline' => $post->title,
    'description' => strip_tags($post->excerpt),
    'author' => [
      '@type' => 'Person',
      'name' => $post->author_name ?? 'Editorial Torzo',
    ],
    'publisher' => [
      '@type' => 'Organization',
      'name' => $currentStorefront['brandName'] ?? 'TORZO',
      'logo' => [
        '@type' => 'ImageObject',
        'url' => asset('user-avatar.png'),
      ],
    ],
    'datePublished' => optional($post->published_at)->toIso8601String() ?? now()->toIso8601String(),
    'mainEntityOfPage' => [
      '@type' => 'WebPage',
      '@id' => url('/revista/' . $post->slug),
    ],
  ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
  </script>
@endsection

@push('styles')
<style>
  .mag-post-wrapper {
    max-width: 840px;
    margin: 0 auto;
    padding: 24px 16px 80px;
  }
  .mag-breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    color: var(--text-muted, #71717a);
    margin-bottom: 24px;
    flex-wrap: wrap;
  }
  .mag-breadcrumb-nav a {
    color: var(--text-muted, #71717a);
    text-decoration: none;
  }
  .mag-post-article {
    background: #ffffff;
    border: 1px solid #e4e4e7;
    border-radius: 16px;
    padding: 32px 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  }
  @media (min-width: 768px) {
    .mag-post-article {
      padding: 48px;
    }
  }
  .mag-post-header {
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f4f4f5;
  }
  .mag-category-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    background: #09090b;
    color: #ffffff;
    padding: 3px 10px;
    border-radius: 4px;
    margin-bottom: 12px;
  }
  .mag-post-headline {
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-size: 1.85rem;
    font-weight: 800;
    line-height: 1.25;
    color: var(--text-primary, #09090b);
    margin: 0 0 16px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
  }
  @media (min-width: 768px) {
    .mag-post-headline {
      font-size: 2.2rem;
    }
  }
  .mag-meta-row {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.78rem;
    color: #71717a;
    flex-wrap: wrap;
  }
  .mag-post-content {
    font-size: 0.95rem;
    line-height: 1.8;
    color: #3f3f46;
  }
  .mag-post-content h2, .mag-post-content h3 {
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-weight: 800;
    color: #09090b;
    margin: 32px 0 16px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
  }
  .mag-post-content p {
    margin-bottom: 20px;
  }
  .mag-post-content ul, .mag-post-content ol {
    margin-bottom: 20px;
    padding-left: 24px;
  }
  .mag-post-content li {
    margin-bottom: 8px;
  }

  .mag-tags-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 36px;
    padding-top: 20px;
    border-top: 1px solid #f4f4f5;
  }
  .mag-tag-item {
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 6px;
    background: #f4f4f5;
    color: #52525b;
    font-weight: 600;
  }

  /* Artículos Relacionados */
  .mag-related-section {
    margin-top: 48px;
  }
  .mag-related-title {
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-size: 1.15rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #09090b;
    margin-bottom: 20px;
  }
  .mag-related-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
  }
  @media (min-width: 600px) {
    .mag-related-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }
  .mag-related-card {
    background: #ffffff;
    border: 1px solid #e4e4e7;
    border-radius: 12px;
    padding: 16px;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    transition: all 0.2s ease;
  }
  .mag-related-card:hover {
    border-color: #09090b;
    transform: translateY(-2px);
  }
  .mag-related-card-title {
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-size: 0.9rem;
    font-weight: 700;
    line-height: 1.35;
    color: #18181b;
    margin: 8px 0;
  }
</style>
@endpush

@section('content')
<div class="mag-post-wrapper">
  
  <nav class="mag-breadcrumb-nav" aria-label="Ruta de navegación">
    <a href="{{ route('store.home', ['universo' => $activeUniverse]) }}">INICIO</a>
    <span>/</span>
    <a href="{{ route('store.magazine') }}">REVISTA DIGITAL</a>
    <span>/</span>
    <span style="color: var(--text-primary); font-weight: 600;">{{ $post->category_name }}</span>
  </nav>

  <article class="mag-post-article">
    <header class="mag-post-header">
      <span class="mag-category-badge">{{ $post->category_name }}</span>
      <h1 class="mag-post-headline">{{ $post->title }}</h1>
      <div class="mag-meta-row">
        <span>Por <strong>{{ $post->author_name ?? 'Editorial Torzo' }}</strong></span>
        <span>•</span>
        <span>{{ $post->reading_time_minutes ?? 4 }} min de lectura</span>
        @if($post->published_at)
          <span>•</span>
          <span>{{ $post->published_at->format('d M, Y') }}</span>
        @endif
      </div>
    </header>

    <div class="mag-post-content">
      {!! $post->body !!}
    </div>

    @if(!empty($post->tags) && is_array($post->tags))
      <div class="mag-tags-row">
        @foreach($post->tags as $tag)
          <span class="mag-tag-item">#{{ $tag }}</span>
        @endforeach
      </div>
    @endif
  </article>

  @if(isset($relatedPosts) && $relatedPosts->isNotEmpty())
    <section class="mag-related-section">
      <h3 class="mag-related-title">Artículos Relacionados</h3>
      <div class="mag-related-grid">
        @foreach($relatedPosts as $rel)
          <a href="{{ route('store.magazine.post', $rel->slug) }}" class="mag-related-card">
            <span style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">{{ $rel->category_name }}</span>
            <h4 class="mag-related-card-title">{{ $rel->title }}</h4>
            <span style="font-size: 0.72rem; color: #71717a; margin-top: auto;">{{ $rel->reading_time_minutes ?? 3 }} min lectura</span>
          </a>
        @endforeach
      </div>
    </section>
  @endif

</div>
@endsection

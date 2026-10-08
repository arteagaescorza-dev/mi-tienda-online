@extends('layouts.store-pwa')

@section('seo')
  <link rel="canonical" href="{{ url('/revista') }}">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Revista Digital | {{ $currentStorefront['brandName'] ?? 'TORZO' }}">
  <meta property="og:description" content="Artículos editoriales sobre tecnología textil, ergonomía corporal, bienestar y tendencias de moda íntima contemporánea.">
@endsection

@push('styles')
<style>
  .mag-index-wrapper {
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px 16px 80px;
  }
  .mag-hero-card {
    background: #09090b;
    color: #ffffff;
    border-radius: 16px;
    padding: 36px 24px;
    margin-bottom: 32px;
    position: relative;
    overflow: hidden;
  }
  @media (min-width: 768px) {
    .mag-hero-card {
      padding: 48px 40px;
    }
  }
  .mag-hero-tag {
    display: inline-block;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #a1a1aa;
    margin-bottom: 10px;
  }
  .mag-hero-title {
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-size: 1.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin: 0 0 12px;
  }
  @media (min-width: 768px) {
    .mag-hero-title {
      font-size: 2.3rem;
    }
  }
  .mag-hero-desc {
    font-size: 0.88rem;
    line-height: 1.6;
    color: #d4d4d8;
    max-width: 600px;
    margin: 0;
  }

  /* Categorías Pills */
  .mag-cat-row {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 12px;
    margin-bottom: 28px;
  }
  .mag-cat-pill {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border: 1px solid #d4d4d8;
    background: #ffffff;
    color: #3f3f46;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s ease;
  }
  .mag-cat-pill:hover, .mag-cat-pill.active {
    background: #09090b;
    color: #ffffff;
    border-color: #09090b;
  }

  /* Grid de Artículos */
  .mag-posts-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
  }
  @media (min-width: 680px) {
    .mag-posts-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  @media (min-width: 1000px) {
    .mag-posts-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  .mag-post-card {
    background: #ffffff;
    border: 1px solid #e4e4e7;
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .mag-post-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
  }
  .mag-post-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex: 1;
  }
  .mag-post-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.72rem;
    color: #71717a;
    margin-bottom: 12px;
  }
  .mag-post-cat-badge {
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--brand-primary, #09090b);
  }
  .mag-post-title {
    font-family: var(--font-heading, 'Outfit', sans-serif);
    font-size: 1.15rem;
    font-weight: 800;
    line-height: 1.35;
    color: #18181b;
    margin: 0 0 10px;
  }
  .mag-post-title a {
    color: inherit;
    text-decoration: none;
  }
  .mag-post-excerpt {
    font-size: 0.82rem;
    line-height: 1.55;
    color: #52525b;
    margin-bottom: 20px;
    flex: 1;
  }
  .mag-post-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid #f4f4f5;
    font-size: 0.75rem;
  }
  .mag-post-read-link {
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--brand-primary, #09090b);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
</style>
@endpush

@section('content')
<div class="mag-index-wrapper">

  <section class="mag-hero-card">
    <span class="mag-hero-tag">EDICIÓN DIGITAL EDITORIAL</span>
    <h1 class="mag-hero-title">REVISTA DIGITAL // MULTIVERSO</h1>
    <p class="mag-hero-desc">
      Artículos sobre innovación en ingeniería textil, ergonomía, bienestar corporal y la cultura contemporánea del estilo íntimo.
    </p>
  </section>

  @if($categories->isNotEmpty())
    <div class="mag-cat-row">
      <a href="{{ route('store.magazine') }}" class="mag-cat-pill {{ !request()->has('categoria') ? 'active' : '' }}">Todos los artículos</a>
      @foreach($categories as $cat)
        <a href="{{ route('store.magazine', ['categoria' => $cat]) }}" class="mag-cat-pill {{ request()->query('categoria') === $cat ? 'active' : '' }}">
          {{ $cat }}
        </a>
      @endforeach
    </div>
  @endif

  <div class="mag-posts-grid">
    @forelse($posts as $post)
      <article class="mag-post-card">
        <div class="mag-post-body">
          <div class="mag-post-meta">
            <span class="mag-post-cat-badge">{{ $post->category_name }}</span>
            <span>{{ $post->reading_time_minutes ?? 4 }} min de lectura</span>
          </div>

          <h2 class="mag-post-title">
            <a href="{{ route('store.magazine.post', $post->slug) }}">{{ $post->title }}</a>
          </h2>

          <p class="mag-post-excerpt">
            {{ Str::limit(strip_tags($post->excerpt), 130) }}
          </p>

          <div class="mag-post-footer">
            <span style="color: #71717a;">{{ $post->author_name ?? 'Editorial Torzo' }}</span>
            <a href="{{ route('store.magazine.post', $post->slug) }}" class="mag-post-read-link">
              <span>LEER</span>
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
          </div>
        </div>
      </article>
    @empty
      <div style="grid-column: 1 / -1; text-align: center; padding: 48px; background: #fafafa; border-radius: 12px; color: #71717a;">
        <strong style="display: block; font-size: 1rem; color: #18181b; margin-bottom: 4px;">Próximamente nuevos artículos</strong>
        <span>Estamos redactando las próximas ediciones de la revista digital.</span>
      </div>
    @endforelse
  </div>

</div>
@endsection

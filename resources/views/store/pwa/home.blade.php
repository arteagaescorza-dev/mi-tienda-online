@extends('layouts.store-pwa')

@section('content')
  <!-- VISTA 1: INICIO (HOME FEED // NUESTRA REVISTA DIGITAL OFICIAL) -->
  <div id="viewHome" style="{{ ($initialTab ?? 'home') === 'home' ? 'display: block;' : 'display: none;' }}">
    
    @if(isset($sections) && $sections->isNotEmpty())
      @foreach($sections as $sec)
        @php
          $secSettings = is_array($sec->settings) ? $sec->settings : (json_decode($sec->settings ?? '{}', true) ?: []);
          $isVitrinaSec = in_array($sec->type, ['curated_products', 'categories_grid', 'family_showcase', 'tags_cloud', 'catalog_section', 'featured_products', 'catalog_grid', 'subcategories_slider']);
          $secSpacingTop = $isVitrinaSec ? 12 : (isset($secSettings['spacing_top']) && $secSettings['spacing_top'] !== '' ? (int)$secSettings['spacing_top'] : 0);
          $secSpacingBottom = $isVitrinaSec ? 12 : (isset($secSettings['spacing_bottom']) && $secSettings['spacing_bottom'] !== '' ? (int)$secSettings['spacing_bottom'] : 0);
          $secSpacingStyle = $isVitrinaSec
              ? "margin-top: 12px !important; margin-bottom: 12px !important; padding-top: 12px !important; padding-bottom: 12px !important;"
              : "margin-top: {$secSpacingTop}px !important; margin-bottom: {$secSpacingBottom}px !important;";
        @endphp
        @if(in_array($sec->type, ['hero_carousel', 'hero', 'carousel']))
          @php
            $settings = $secSettings;
            $isCarousel = $sec->type === 'carousel' || $sec->type === 'hero_carousel';
            $heroMode = $settings['hero_mode'] ?? (!empty($sec->custom_html) && empty($sec->title) ? 'custom' : 'structured');
            $isFullWidth = !empty($settings['full_width']);
            $isFullWidthH = !empty($settings['full_width_horizontal']);
            $heroHasButtons = !isset($settings['hero_has_buttons']) || $settings['hero_has_buttons'] == '1' || $settings['hero_has_buttons'] === true || $settings['hero_has_buttons'] === 1;
            $heroClickUrl = $settings['hero_click_url'] ?? '';
            $bgImage = $settings['background_image'] ?? null;
            $enableGradient = !empty($settings['enable_gradient']);
            $gradStart = $settings['gradient_color_start'] ?? '#0f172a';
            $gradEnd = $settings['gradient_color_end'] ?? '#1e1b4b';
            $gradDir = $settings['gradient_direction'] ?? 'to bottom';
            $gradOpacityStart = isset($settings['gradient_opacity_start']) ? ((int)$settings['gradient_opacity_start'] / 100) : 0.85;
            $gradOpacityEnd = isset($settings['gradient_opacity_end']) ? ((int)$settings['gradient_opacity_end'] / 100) : 0.85;

            $hexToRgba = function ($hex, $alpha = 1.0) {
                $hex = ltrim((string)$hex, '#');
                if (strlen($hex) === 3) {
                    $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
                    $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
                    $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
                } elseif (strlen($hex) >= 6) {
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b = hexdec(substr($hex, 4, 2));
                } else {
                    return $hex;
                }
                return sprintf('rgba(%d, %d, %d, %s)', $r, $g, $b, rtrim(rtrim(sprintf('%.2f', $alpha), '0'), '.'));
            };

            $imageFocalPoint = !empty($settings['image_focal_point']) ? trim($settings['image_focal_point']) : 'center center';
            $bgImageUrl = !empty($bgImage) ? asset(ltrim($bgImage, '/')) : null;
            $cssDeclarations = [];

            if (!empty($bgImageUrl) && $enableGradient) {
                $rgbaStart = $hexToRgba($gradStart, $gradOpacityStart);
                $rgbaEnd = $hexToRgba($gradEnd, $gradOpacityEnd);
                $cssDeclarations[] = "background-image: linear-gradient({$gradDir}, {$rgbaStart}, {$rgbaEnd}), url('{$bgImageUrl}') !important";
                $cssDeclarations[] = "background-size: cover, cover !important";
                $cssDeclarations[] = "background-position: center, {$imageFocalPoint} !important";
                $cssDeclarations[] = "background-repeat: no-repeat, no-repeat !important";
            } elseif (!empty($bgImageUrl)) {
                $cssDeclarations[] = "background-image: url('{$bgImageUrl}') !important";
                $cssDeclarations[] = "background-size: cover !important";
                $cssDeclarations[] = "background-position: {$imageFocalPoint} !important";
                $cssDeclarations[] = "background-repeat: no-repeat !important";
            } elseif ($enableGradient) {
                $rgbaStart = $hexToRgba($gradStart, $gradOpacityStart);
                $rgbaEnd = $hexToRgba($gradEnd, $gradOpacityEnd);
                $cssDeclarations[] = "background-image: linear-gradient({$gradDir}, {$rgbaStart}, {$rgbaEnd}) !important";
                $cssDeclarations[] = "background-size: cover !important";
                $cssDeclarations[] = "background-position: center !important";
            }
            if (!empty($settings['parallax_scrolling']) && !$isCarousel) {
                $cssDeclarations[] = "background-attachment: fixed !important";
            }
            if (!empty($settings['hero_height_value'])) {
                $hHeightVal = trim($settings['hero_height_value']);
                $hHeightUnit = trim($settings['hero_height_unit'] ?? 'px');
                $cssDeclarations[] = "min-height: {$hHeightVal}{$hHeightUnit} !important";
                $cssDeclarations[] = "height: {$hHeightVal}{$hHeightUnit} !important";
            }
            $heroBgStyle = !empty($cssDeclarations) ? implode('; ', $cssDeclarations) . ';' : '';

            $ctaPrimaryText = $settings['cta_primary_text'] ?? $settings['cta_text'] ?? $sec->query_rules['cta_text'] ?? 'EXPLORAR CATÁLOGO';
            $ctaPrimaryUrl = $settings['cta_primary_url'] ?? $settings['cta_url'] ?? $sec->query_rules['cta_url'] ?? null;
            $ctaSecondaryText = $settings['cta_secondary_text'] ?? 'GUÍA DE TALLAS TORZO';
            $ctaSecondaryUrl = $settings['cta_secondary_url'] ?? null;
          @endphp

          <!-- Hero Carousel / Editorial Banner -->
          <section id="section-{{ $sec->id }}" class="hero-editorial-section {{ $isFullWidth ? 'hero-fullpage-v' : '' }} {{ $isFullWidthH ? 'hero-full-width-h' : '' }} {{ ($isFullWidth && $isFullWidthH) ? 'hero-full-width' : '' }}" style="{{ $secSpacingStyle }} padding-top: 0 !important; padding-bottom: 0 !important;" data-section-id="{{ $sec->id }}">
            @if($heroMode === 'custom')
              {{-- MODO CÓDIGO PERSONALIZADO (AISLADO, NO DUPLICA EL HERO) --}}
              <div class="hero-custom-container" style="{!! $heroBgStyle !!} {{ (!$heroHasButtons && !empty($heroClickUrl)) ? 'cursor: pointer;' : '' }}" @if(!$heroHasButtons && !empty($heroClickUrl)) onclick="if(!event.target.closest('a, button')) { window.location.href='{{ $heroClickUrl }}'; }" @endif>
                @if(!$heroHasButtons && !empty($heroClickUrl))
                  <a href="{{ $heroClickUrl }}" class="hero-full-click-link" style="position: absolute; inset: 0; z-index: 15; width: 100%; height: 100%; text-decoration: none;" aria-label="{{ $sec->title ?? 'Hero' }}"></a>
                @endif
                @if(!empty($sec->custom_css))
                  <style>{!! $sec->custom_css !!}</style>
                @endif
                @if(!empty($sec->custom_html))
                  {!! $sec->custom_html !!}
                @endif
                @if(!empty($sec->custom_js))
                  <script>{!! $sec->custom_js !!}</script>
                @endif
              </div>
            @else
              {{-- MODO VISUAL ESTRUCTURADO (FORMULARIO DINÁMICO) --}}
              <div class="hero-banner-card" style="{!! $heroBgStyle !!} {{ $isCarousel ? 'position: relative !important; overflow: hidden !important; padding: 0 !important; margin: 0 !important;' : '' }} {{ ($isCarousel && empty($settings['hero_height_value']) && !$isFullWidth) ? 'min-height: 440px;' : '' }} {{ ($isCarousel && $isFullWidth) ? 'min-height: calc(100vh - 56px) !important; height: calc(100vh - 56px) !important;' : '' }} {{ (!$isCarousel && !$heroHasButtons && !empty($heroClickUrl)) ? 'cursor: pointer;' : '' }}" aria-label="{{ $settings['background_image_alt'] ?? '' }}" @if(!$isCarousel && !$heroHasButtons && !empty($heroClickUrl)) onclick="if(!event.target.closest('a, button')) { window.location.href='{{ $heroClickUrl }}'; }" @endif>
                @if(!$isCarousel && !$heroHasButtons && !empty($heroClickUrl))
                  <a href="{{ $heroClickUrl }}" class="hero-full-click-link" style="position: absolute; inset: 0; z-index: 15; width: 100%; height: 100%; text-decoration: none;" aria-label="{{ $sec->title ?? 'Hero' }}"></a>
                @endif
                @if(!$isCarousel && ($settings['background_type'] ?? 'image') === 'video' && !empty($settings['background_video']))
                  <video src="{{ asset(ltrim($settings['background_video'], '/')) }}" 
                         style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: {{ $imageFocalPoint }}; z-index: 0;" 
                         {{ !empty($settings['video_autoplay']) ? 'autoplay' : '' }} 
                         {{ !empty($settings['video_loop']) ? 'loop' : '' }} 
                         {{ !empty($settings['video_muted']) ? 'muted' : '' }} 
                         playsinline></video>
                  @if($enableGradient)
                    @php
                      $rgbaStart = $hexToRgba($gradStart, $gradOpacityStart);
                      $rgbaEnd = $hexToRgba($gradEnd, $gradOpacityEnd);
                    @endphp
                    <div style="position: absolute; inset: 0; z-index: 1; background-image: linear-gradient({{ $gradDir }}, {{ $rgbaStart }}, {{ $rgbaEnd }});"></div>
                  @endif
                @endif
                
                @if($isCarousel && !empty($settings['carousel_slides']))
                  @php
                      $slidesList = array_values(is_array($settings['carousel_slides']) ? $settings['carousel_slides'] : []);
                      $carouselId = 'carousel-' . $sec->id . '-' . uniqid();
                      $autoplaySeconds = isset($settings['autoplay_time']) && (int)$settings['autoplay_time'] > 0 ? (int)$settings['autoplay_time'] : 5;
                      $autoplayEnabled = (!isset($settings['autoplay']) && !isset($settings['carousel_autoplay'])) || !empty($settings['autoplay']) || !empty($settings['carousel_autoplay']);
                      $isCarouselParallax = !empty($settings['parallax_scrolling']);

                      $resolveSlideImg = function ($rawUrl) {
                          $rawUrl = trim((string)$rawUrl);
                          if ($rawUrl === '') return '';
                          if (preg_match('/^(https?:\/\/|data:|blob:)/i', $rawUrl)) {
                              return $rawUrl;
                          }
                          return asset(ltrim($rawUrl, '/'));
                      };
                  @endphp
                  <div id="{{ $carouselId }}-viewport" class="store-carousel-viewport" data-parallax="{{ $isCarouselParallax ? '1' : '0' }}" style="position: absolute; inset: 0; z-index: 2; width: 100%; height: 100%; overflow: hidden;">
                      <div id="{{ $carouselId }}" class="carousel-container" data-autoplay="{{ $autoplaySeconds }}" data-autoplay-enabled="{{ $autoplayEnabled ? '1' : '0' }}" style="width: 100%; height: 100%; {{ $isCarouselParallax ? 'position: relative; overflow: hidden;' : 'display: flex; transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1); will-change: transform;' }}">
                          @foreach($slidesList as $sIdx => $slide)
                              @php
                                  $slideImgSrc = $resolveSlideImg($slide['image_url'] ?? '');
                                  $slideFocal = !empty($slide['image_focal_point']) ? trim($slide['image_focal_point']) : 'center center';

                                  $sEnableGrad = !isset($slide['enable_gradient']) || !empty($slide['enable_gradient']);
                                  $sGradStart = $slide['gradient_color_start'] ?? '#0f172a';
                                  $sGradEnd = $slide['gradient_color_end'] ?? '#1e1b4b';
                                  $sGradDir = $slide['gradient_direction'] ?? 'to bottom';
                                  $sGradOpStart = isset($slide['gradient_opacity_start']) ? ((int)$slide['gradient_opacity_start'] / 100) : 0.65;
                                  $sGradOpEnd = isset($slide['gradient_opacity_end']) ? ((int)$slide['gradient_opacity_end'] / 100) : 0.85;
                                  $sRgbaStart = $hexToRgba($sGradStart, $sGradOpStart);
                                  $sRgbaEnd = $hexToRgba($sGradEnd, $sGradOpEnd);

                                  $sAlignRaw = $slide['text_align'] ?? 'text-center';
                                  $sFlexAlign = 'center';
                                  $sTextAlign = 'center';
                                  if ($sAlignRaw === 'text-left') {
                                      $sFlexAlign = 'flex-start';
                                      $sTextAlign = 'left';
                                  } elseif ($sAlignRaw === 'text-right') {
                                      $sFlexAlign = 'flex-end';
                                      $sTextAlign = 'right';
                                  }

                                  $sTitleColor = !empty($slide['title_color']) ? $slide['title_color'] : '#ffffff';
                                  $sSubColor = !empty($slide['subtitle_color']) ? $slide['subtitle_color'] : '#e2e8f0';

                                  $bWidthVal = $slide['button_border_width'] ?? 'border-0';
                                  $bPx = '0';
                                  if ($bWidthVal === 'border') $bPx = '1';
                                  elseif ($bWidthVal === 'border-2') $bPx = '2';
                                  elseif ($bWidthVal === 'border-4') $bPx = '4';

                                  $sBtnShadow = '';
                                  $rawShadow = $slide['button_shadow'] ?? '';
                                  if ($rawShadow === 'shadow-sm') $sBtnShadow = 'box-shadow: 0 1px 2px 0 rgba(0,0,0,0.12);';
                                  elseif ($rawShadow === 'shadow-md') $sBtnShadow = 'box-shadow: 0 4px 10px -1px rgba(0,0,0,0.22);';
                                  elseif ($rawShadow === 'shadow-lg') $sBtnShadow = 'box-shadow: 0 10px 20px -3px rgba(0,0,0,0.3);';
                                  elseif ($rawShadow === 'shadow-xl') $sBtnShadow = 'box-shadow: 0 20px 28px -5px rgba(0,0,0,0.4);';
                              @endphp
                              <div class="carousel-slide" data-slide-index="{{ $sIdx }}" style="{{ $isCarouselParallax ? 'position: absolute; inset: 0; width: 100%; height: 100%; z-index: ' . ($sIdx === 0 ? '2' : '0') . '; visibility: ' . ($sIdx === 0 ? 'visible' : 'hidden') . '; will-change: clip-path;' : 'flex: 0 0 100%; min-width: 100%; width: 100%; height: 100%; position: relative;' }} overflow: hidden; display: flex; align-items: center; justify-content: center; box-sizing: border-box; padding: 36px max(24px, calc((100vw - 1280px) / 2 + 24px));">
                                  @if(!empty($slideImgSrc))
                                      <img src="{{ $slideImgSrc }}" class="carousel-slide-bg-img" alt="{{ $slide['alt_text'] ?? ($slide['title'] ?? 'Slide ' . ($sIdx + 1)) }}" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: {{ $slideFocal }}; z-index: 0; pointer-events: none; display: block;" loading="{{ $sIdx === 0 ? 'eager' : 'lazy' }}">
                                  @else
                                      <div style="position: absolute; inset: 0; background: radial-gradient(circle at center, #1e293b 0%, #09090b 100%); z-index: 0;"></div>
                                  @endif

                                  @if($sEnableGrad)
                                      <div style="position: absolute; inset: 0; z-index: 1; background-image: linear-gradient({{ $sGradDir }}, {{ $sRgbaStart }}, {{ $sRgbaEnd }}); pointer-events: none;"></div>
                                  @endif

                                  @if(($slide['link_target'] ?? 'button') === 'image' && !empty($slide['button_url']))
                                      <a href="{{ $slide['button_url'] }}" style="position: absolute; inset: 0; z-index: 12; text-decoration: none;" aria-label="{{ $slide['title'] ?? 'Ver colección' }}"></a>
                                  @endif

                                  <div class="carousel-slide-content" style="position: relative; z-index: 10; width: 100%; max-width: 960px; display: flex; flex-direction: column; align-items: {{ $sFlexAlign }}; text-align: {{ $sTextAlign }}; pointer-events: none; transition: opacity 0.35s ease, transform 0.35s ease;">
                                      @if(!empty($slide['badge']))
                                        <div class="hero-tag-pill" style="margin-bottom: 12px;">
                                          <span class="hero-pulse-dot"></span>
                                          <span>{{ $slide['badge'] }}</span>
                                        </div>
                                      @endif
                                      @if(!empty($slide['title']))
                                        <h2 class="hero-title" style="color: {{ $sTitleColor }} !important; margin-bottom: 10px;">{{ $slide['title'] }}</h2>
                                      @endif
                                      @if(!empty($slide['subtitle']))
                                        <p class="hero-desc" style="color: {{ $sSubColor }} !important; margin-bottom: 20px;">{{ $slide['subtitle'] }}</p>
                                      @endif

                                      @if(!empty($slide['button_text']))
                                        <div style="margin-top: 6px; pointer-events: auto; position: relative; z-index: 14;">
                                            <a href="{{ $slide['button_url'] ?? '#' }}" class="btn-hero-primary" style="
                                                display: inline-flex;
                                                align-items: center;
                                                gap: 8px;
                                                padding: 12px 24px;
                                                background-color: {{ $slide['button_bg'] ?? '#ffffff' }} !important;
                                                color: {{ $slide['button_color'] ?? '#0f172a' }} !important;
                                                border: {{ $bPx }}px solid {{ $slide['button_border_color'] ?? '#ffffff' }} !important;
                                                text-decoration: none;
                                                font-weight: 800;
                                                font-size: 12px;
                                                letter-spacing: 0.08em;
                                                text-transform: uppercase;
                                                border-radius: 9999px;
                                                transition: transform 0.2s ease, opacity 0.2s ease;
                                                {{ $sBtnShadow }}
                                            ">
                                                <span>{{ $slide['button_text'] }}</span>
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                            </a>
                                        </div>
                                      @endif
                                  </div>
                              </div>
                          @endforeach
                      </div>

                      @if(count($slidesList) > 1)
                          <button type="button" data-carousel-prev="{{ $carouselId }}" aria-label="Slide anterior" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 20; width: 40px; height: 40px; border-radius: 9999px; background: rgba(9, 9, 11, 0.55); color: #ffffff; border: 1px solid rgba(255,255,255,0.22); display: flex; align-items: center; justify-content: center; cursor: pointer; backdrop-filter: blur(6px); transition: background 0.2s ease;">
                              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
                          </button>
                          <button type="button" data-carousel-next="{{ $carouselId }}" aria-label="Siguiente slide" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); z-index: 20; width: 40px; height: 40px; border-radius: 9999px; background: rgba(9, 9, 11, 0.55); color: #ffffff; border: 1px solid rgba(255,255,255,0.22); display: flex; align-items: center; justify-content: center; cursor: pointer; backdrop-filter: blur(6px); transition: background 0.2s ease;">
                              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
                          </button>
                          <div data-carousel-dots="{{ $carouselId }}" style="position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%); z-index: 20; display: flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 9999px; background: rgba(9, 9, 11, 0.45); backdrop-filter: blur(6px);">
                              @foreach($slidesList as $sIdx => $slide)
                                  <button type="button" data-carousel-dot="{{ $sIdx }}" aria-label="Ir al slide {{ $sIdx + 1 }}" style="height: 8px; width: {{ $sIdx === 0 ? '24px' : '8px' }}; border-radius: 9999px; border: none; padding: 0; cursor: pointer; background: {{ $sIdx === 0 ? '#ffffff' : 'rgba(255,255,255,0.45)' }}; transition: all 0.3s ease;"></button>
                              @endforeach
                          </div>
                      @endif
                  </div>
                  <script>
                      (function() {
                          const container = document.getElementById('{{ $carouselId }}');
                          const viewport = document.getElementById('{{ $carouselId }}-viewport');
                          if (!container || !viewport) return;

                          const isRevealMode = viewport.getAttribute('data-parallax') === '1';
                          const slides = container.querySelectorAll('.carousel-slide');
                          const total = slides.length;
                          if (total <= 1) return;

                          let current = 0;
                          let timer = null;
                          let isAnimating = false;
                          const autoplayEnabled = container.getAttribute('data-autoplay-enabled') === '1';
                          const intervalMs = Math.max(1500, (parseInt(container.getAttribute('data-autoplay')) || 5) * 1000);
                          const dotsWrap = viewport.querySelector('[data-carousel-dots="{{ $carouselId }}"]');
                          const dots = dotsWrap ? dotsWrap.querySelectorAll('[data-carousel-dot]') : [];
                          const prevBtn = viewport.querySelector('[data-carousel-prev="{{ $carouselId }}"]');
                          const nextBtn = viewport.querySelector('[data-carousel-next="{{ $carouselId }}"]');

                          // Inicialización de capas para Efecto Revelado (Cortina)
                          if (isRevealMode) {
                              slides.forEach(function(s, idx) {
                                  s.style.position = 'absolute';
                                  s.style.inset = '0';
                                  s.style.width = '100%';
                                  s.style.height = '100%';
                                  s.style.zIndex = idx === 0 ? '2' : '0';
                                  s.style.visibility = idx === 0 ? 'visible' : 'hidden';
                                  s.style.opacity = '1';
                                  s.style.clipPath = 'none';
                              });
                          }

                          function goToSlide(targetIdx, direction) {
                              if (isAnimating) return;
                              targetIdx = ((targetIdx % total) + total) % total;
                              if (targetIdx === current && isRevealMode) return;

                              if (!direction) {
                                  direction = targetIdx > current ? 'next' : 'prev';
                                  if (current === total - 1 && targetIdx === 0) direction = 'next';
                                  if (current === 0 && targetIdx === total - 1) direction = 'prev';
                              }

                              if (isRevealMode) {
                                  isAnimating = true;
                                  const currentSlide = slides[current];
                                  const targetSlide = slides[targetIdx];

                                  // Preparar slide destino debajo en z-index 1
                                  targetSlide.style.zIndex = '1';
                                  targetSlide.style.visibility = 'visible';
                                  targetSlide.style.opacity = '1';
                                  targetSlide.style.clipPath = 'none';
                                  targetSlide.style.transition = 'none';

                                  // Slide actual por encima en z-index 3
                                  currentSlide.style.zIndex = '3';
                                  currentSlide.style.visibility = 'visible';
                                  currentSlide.style.clipPath = 'inset(0 0 0 0)';
                                  currentSlide.style.transition = 'none';

                                  const curContent = currentSlide.querySelector('.carousel-slide-content');
                                  const tgtContent = targetSlide.querySelector('.carousel-slide-content');

                                  // Forzar reflow para registrar estado inicial
                                  void currentSlide.offsetHeight;

                                  currentSlide.style.transition = 'clip-path 0.75s cubic-bezier(0.25, 1, 0.5, 1)';
                                  if (direction === 'next') {
                                      currentSlide.style.clipPath = 'inset(0 100% 0 0)';
                                  } else {
                                      currentSlide.style.clipPath = 'inset(0 0 0 100%)';
                                  }

                                  if (curContent) {
                                      curContent.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
                                      curContent.style.opacity = '0';
                                      curContent.style.transform = direction === 'next' ? 'translateX(-24px)' : 'translateX(24px)';
                                  }
                                  if (tgtContent) {
                                      tgtContent.style.opacity = '0';
                                      tgtContent.style.transform = direction === 'next' ? 'translateX(24px)' : 'translateX(-24px)';
                                      setTimeout(function() {
                                          tgtContent.style.transition = 'opacity 0.45s ease, transform 0.45s ease';
                                          tgtContent.style.opacity = '1';
                                          tgtContent.style.transform = 'translateX(0)';
                                      }, 80);
                                  }

                                  setTimeout(function() {
                                      targetSlide.style.zIndex = '2';
                                      currentSlide.style.zIndex = '0';
                                      currentSlide.style.visibility = 'hidden';
                                      currentSlide.style.clipPath = 'none';
                                      currentSlide.style.transition = 'none';
                                      if (curContent) {
                                          curContent.style.opacity = '1';
                                          curContent.style.transform = 'none';
                                          curContent.style.transition = 'none';
                                      }
                                      slides.forEach(function(s, idx) {
                                          if (idx !== targetIdx) {
                                              s.style.zIndex = '0';
                                              s.style.visibility = 'hidden';
                                          }
                                      });
                                      current = targetIdx;
                                      isAnimating = false;
                                  }, 760);
                              } else {
                                  current = targetIdx;
                                  container.style.transform = 'translateX(-' + (current * 100) + '%)';
                              }

                              dots.forEach(function(dot, dIdx) {
                                  if (dIdx === targetIdx) {
                                      dot.style.width = '24px';
                                      dot.style.background = '#ffffff';
                                  } else {
                                      dot.style.width = '8px';
                                      dot.style.background = 'rgba(255,255,255,0.45)';
                                  }
                              });
                          }

                          function startAutoplay() {
                              stopAutoplay();
                              if (!autoplayEnabled) return;
                              timer = setInterval(function() {
                                  goToSlide(current + 1, 'next');
                              }, intervalMs);
                          }

                          function stopAutoplay() {
                              if (timer) {
                                  clearInterval(timer);
                                  timer = null;
                              }
                          }

                          if (prevBtn) {
                              prevBtn.addEventListener('click', function(e) {
                                  e.preventDefault();
                                  e.stopPropagation();
                                  goToSlide(current - 1, 'prev');
                                  startAutoplay();
                              });
                          }
                          if (nextBtn) {
                              nextBtn.addEventListener('click', function(e) {
                                  e.preventDefault();
                                  e.stopPropagation();
                                  goToSlide(current + 1, 'next');
                                  startAutoplay();
                              });
                          }
                          dots.forEach(function(dot) {
                              dot.addEventListener('click', function(e) {
                                  e.preventDefault();
                                  e.stopPropagation();
                                  const targetIdx = parseInt(dot.getAttribute('data-carousel-dot')) || 0;
                                  goToSlide(targetIdx, targetIdx >= current ? 'next' : 'prev');
                                  startAutoplay();
                              });
                          });

                          let touchStartX = 0;
                          viewport.addEventListener('touchstart', function(e) {
                              if (e.touches && e.touches.length > 0) {
                                  touchStartX = e.touches[0].clientX;
                              }
                          }, { passive: true });
                          viewport.addEventListener('touchend', function(e) {
                              if (!e.changedTouches || e.changedTouches.length === 0) return;
                              const diffX = e.changedTouches[0].clientX - touchStartX;
                              if (Math.abs(diffX) > 40) {
                                  if (diffX < 0) goToSlide(current + 1, 'next');
                                  else goToSlide(current - 1, 'prev');
                                  startAutoplay();
                              }
                          }, { passive: true });

                          viewport.addEventListener('mouseenter', stopAutoplay);
                          viewport.addEventListener('mouseleave', startAutoplay);

                          startAutoplay();
                      })();
                  </script>
                @endif
                
                @php
                  $titleColor = $settings['title_color'] ?? '';
                  $titleFont = $settings['title_font'] ?? $settings['font_family'] ?? '';
                  $subtitleColor = $settings['subtitle_color'] ?? '';
                  $subtitleFont = $settings['subtitle_font'] ?? '';
                  $textAlign = $settings['text_align'] ?? 'text-center';
                  
                  $alignCss = 'text-center';
                  $flexAlign = 'center';
                  if ($textAlign === 'text-left') {
                      $alignCss = 'text-left';
                      $flexAlign = 'flex-start';
                  } elseif ($textAlign === 'text-right') {
                      $alignCss = 'text-right';
                      $flexAlign = 'flex-end';
                  }

                  $titleStyle = '';
                  if (!empty($titleColor)) $titleStyle .= "color: {$titleColor} !important; ";
                  if (!empty($titleFont) && !in_array(strtolower($titleFont), ['inherit', 'inherit / vitrina', 'font-sans', 'font-serif', 'font-mono'])) {
                      $titleStyle .= "font-family: '{$titleFont}', sans-serif !important; ";
                  }

                  $subtitleStyle = '';
                  if (!empty($subtitleColor)) $subtitleStyle .= "color: {$subtitleColor} !important; ";
                  if (!empty($subtitleFont) && !in_array(strtolower($subtitleFont), ['inherit', 'inherit / vitrina', 'font-sans', 'font-serif', 'font-mono'])) {
                      $subtitleStyle .= "font-family: '{$subtitleFont}', sans-serif !important; ";
                  }
                @endphp
                <div style="position: relative; z-index: 10; width: 100%; display: flex; flex-direction: column; align-items: {{ $flexAlign }}; text-align: {{ str_replace('text-', '', $alignCss) }}; {{ $isCarousel ? 'display: none !important;' : '' }}">
                @if(!empty($sec->badge) || !empty($currentStorefront['heroTag']))
                  <div class="hero-tag-pill">
                    <span class="hero-pulse-dot"></span>
                    <span id="heroBrandTag">{{ $sec->badge ?? $currentStorefront['heroTag'] ?? 'MARTE // COLECCIÓN OFICIAL TORZO' }}</span>
                  </div>
                @endif

                <h1 class="hero-title" style="{{ $titleStyle }}">{{ $sec->title ?? $currentStorefront['heroTitle'] ?? 'MODA CONTEMPORÁNEA & ROPA INTERIOR ERGONÓMICA' }}</h1>

                @if(!empty($sec->subtitle) || !empty($currentStorefront['heroSubtitle']))
                  <p class="hero-desc" style="{{ $subtitleStyle }}">
                    {{ $sec->subtitle ?? $currentStorefront['heroSubtitle'] ?? 'Diseño textil anatómico con modal pima, costuras planas y microfibras de alta transpirabilidad. Envíos express a todo México.' }}
                  </p>
                @endif

                @if($heroHasButtons)
                <div class="hero-actions-row" style="justify-content: {{ $flexAlign }};">
                  @php
                    $primaryBg = $settings['cta_primary_bg'] ?? '';
                    $primaryColor = $settings['cta_primary_color'] ?? '';
                    $primaryBorderWidth = $settings['cta_primary_border_width'] ?? 'border-0';
                    $primaryBorderColor = $settings['cta_primary_border_color'] ?? '';
                    $primaryShadow = $settings['cta_primary_shadow'] ?? '';
                    $bPx = '0';
                    if ($primaryBorderWidth === 'border') $bPx = '1';
                    elseif ($primaryBorderWidth === 'border-2') $bPx = '2';
                    elseif ($primaryBorderWidth === 'border-4') $bPx = '4';
                    
                    $primaryStyle = '';
                    if (!empty($primaryBg)) $primaryStyle .= "background-color: {$primaryBg} !important; ";
                    if (!empty($primaryColor)) $primaryStyle .= "color: {$primaryColor} !important; ";
                    if (!empty($primaryBorderColor) && $bPx !== '0') $primaryStyle .= "border: {$bPx}px solid {$primaryBorderColor} !important; ";
                    if ($primaryShadow === 'shadow-sm') $primaryStyle .= "box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05) !important; ";
                    elseif ($primaryShadow === 'shadow-md') $primaryStyle .= "box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important; ";
                    elseif ($primaryShadow === 'shadow-lg') $primaryStyle .= "box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important; ";
                    elseif ($primaryShadow === 'shadow-xl') $primaryStyle .= "box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1) !important; ";
                  @endphp
                  @if(!empty($ctaPrimaryUrl))
                    <a href="{{ $ctaPrimaryUrl }}" class="btn-hero-primary" style="text-decoration: none; {{ $primaryStyle }}">
                      <span>{{ $ctaPrimaryText }}</span>
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                  @else
                    <button type="button" class="btn-hero-primary" data-action="switch-dock-tab" data-dock-tab="catalog" style="{{ $primaryStyle }}">
                      <span>{{ $ctaPrimaryText }}</span>
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                  @endif

                  @if(!empty($ctaSecondaryUrl))
                    <a href="{{ $ctaSecondaryUrl }}" class="btn-hero-secondary" style="text-decoration: none;">
                      <span>{{ $ctaSecondaryText }}</span>
                    </a>
                  @else
                    <button type="button" class="btn-hero-secondary" data-action="open-size-guide">
                      <span>{{ $ctaSecondaryText }}</span>
                    </button>
                  @endif
                </div>
                @endif
                </div>
              </div>
            @endif
          </section>
        @elseif($sec->type === 'brand_pillars')
          <!-- Pilares de Marca & Beneficios (Courier Exclusivo) -->
          <section id="section-{{ $sec->id }}" class="brand-pillars-bar" style="{{ $secSpacingStyle }}" data-section-id="{{ $sec->id }}">
            <div class="pillar-item-card">
              <div class="pillar-icon-box">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
              </div>
              <div>
                <div class="pillar-title">ENVÍOS EXPRESS NACIONALES</div>
                <div class="pillar-sub">Paquetería Express (${{ number_format((float)($shippingRule['flatRate'] ?? 149), 0) }} MXN / Gratis desde ${{ number_format((float)($shippingRule['freeShippingThreshold'] ?? 999), 0) }})</div>
              </div>
            </div>

            <div class="pillar-item-card">
              <div class="pillar-icon-box">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M7 15h3M15 15h2"/></svg>
              </div>
              <div>
                <div class="pillar-title">TRANSFERENCIAS Tarjetas BBVA</div>
                <div class="pillar-sub">Sin comisión bancaria directa</div>
              </div>
            </div>

            <div class="pillar-item-card">
              <div class="pillar-icon-box">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              </div>
              <div>
                <div class="pillar-title">EMPAQUE 100% DISCRETO</div>
                <div class="pillar-sub">Precinto higiénico termosellado</div>
              </div>
            </div>
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_html))
              {!! $sec->custom_html !!}
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </section>
        @elseif(in_array($sec->type, ['editorial_banner', 'carousel']))
          <!-- Banner Promocional de Campañas Dir A (Convenios & Aliados Hub 7 TASK-STR-04) -->
          <section id="section-{{ $sec->id }}" class="lead-magnet-banner-card" style="{{ $secSpacingStyle }}" data-section-id="{{ $sec->id }}" data-card-id="leadMagnetHomeCard_{{ $sec->id }}">
            <div class="lead-magnet-content">
              <div class="lead-magnet-tag-row">
                <span class="lead-magnet-tag">{{ $sec->badge ?? 'CONVENIOS MULTIVERSO // HUB 7' }}</span>
                <span class="badge-lead-magnet">CORTESÍA EXCLUSIVA</span>
              </div>
              <h3 class="lead-magnet-title">{{ $sec->title ?? 'Cortesía Especial en Paradiso Club & Circuit CDMX' }}</h3>
              <p class="lead-magnet-desc">
                {{ $sec->subtitle ?? 'Obtén tu voucher digital con acceso preferencial y trago de cortesía en los mejores venues aliados de la comunidad.' }}
              </p>
              @php
                $edCtaText = $sec->settings['cta_text'] ?? $sec->query_rules['cta_text'] ?? 'OBTENER MI VOUCHER DIGITAL';
                $edCtaUrl = $sec->settings['cta_url'] ?? $sec->query_rules['cta_url'] ?? null;
              @endphp
              @if(!empty($edCtaUrl))
                <a href="{{ $edCtaUrl }}" class="btn-lead-magnet-action" style="text-decoration: none;">
                  <span>{{ $edCtaText }}</span>
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
              @else
                <button type="button" class="btn-lead-magnet-action" data-action="open-lead-magnet" data-campaign="{{ $sec->settings['campaign'] ?? 'PARADISO_VIP' }}">
                  <span>{{ $edCtaText }}</span>
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
              @endif
            </div>
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_html))
              {!! $sec->custom_html !!}
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </section>
        @elseif(in_array($sec->type, ['curated_products', 'categories_grid', 'family_showcase', 'tags_cloud', 'catalog_section']))
          <!-- Feed de Prendas Destacadas / Colección Curada ({{ $sec->type }}) -->
          @php
            $sectionProducts = $sec->resolved_products ?? $products;
          @endphp
          <section id="section-{{ $sec->id }}" style="{{ $secSpacingStyle }}" data-section-id="{{ $sec->id }}" data-section-type="{{ $sec->type }}">
            <div class="catalog-section-header">
              <div>
                <h2 class="section-headline">{{ $sec->title ?? 'PRENDAS DESTACADAS' }}</h2>
                <span style="font-size: 0.72rem; color: var(--text-muted);">{{ $sec->subtitle ?? 'Curaduría activa para tu universo' }}</span>
              </div>
              <div style="display: flex; align-items: center; gap: 8px;">
                @if(!empty($sec->badge))
                  <span class="badge-lead-magnet" style="font-size: 0.65rem;">{{ $sec->badge }}</span>
                @endif
                <button type="button" class="section-action-btn" data-action="open-filter-drawer">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                  <span>Filtros</span>
                </button>
              </div>
            </div>

            <div id="{{ $sec->type === 'curated_products' ? 'homeProductsGrid' : 'sectionProductsGrid_' . $sec->id }}" class="product-feed-grid">
              @forelse($sectionProducts as $prod)
                <div class="product-card" data-product-id="{{ $prod['id'] }}">
                  <div class="product-image-wrap">
                    <button type="button" class="btn-wishlist-toggle" data-action="toggle-wishlist" data-product-id="{{ $prod['id'] }}" title="Guardar en mis deseos" aria-label="Guardar en favoritos">
                      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </button>
                    <span class="product-stock-badge {{ ($prod['stockType'] ?? '') === 'ready' ? 'ready' : 'on_demand' }}">
                      {{ $prod['stockLabel'] ?? 'ENVÍO EXPRESS' }}
                    </span>
                    <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%; cursor: pointer; text-decoration: none; position: relative;" title="{{ $prod['name'] }}">
                      @if(!empty($prod['image']) && $prod['image'] !== '/user-avatar.png')
                        <img src="{{ $prod['image'] }}" alt="{{ $prod['name'] }}" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        <div class="product-fallback-silhouette" style="display: none; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; position: absolute; inset: 0;">
                          <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                          <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] ?? 'TORZO' }}</span>
                        </div>
                      @else
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%;">
                          <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                          <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] ?? 'TORZO' }}</span>
                        </div>
                      @endif
                    </a>
                  </div>
                  <div class="product-body">
                    <div>
                      <div class="product-category-label">{{ $prod['categoryName'] ?? 'General' }}</div>
                      <h3 class="product-title">
                        <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="cursor: pointer; color: inherit; text-decoration: none;">{{ $prod['name'] }}</a>
                      </h3>
                      <div class="product-pricing-row">
                        <span class="product-price">{{ $prod['price_formatted'] ?? ('MXN $' . number_format((float)($prod['price'] ?? 0), 2)) }}</span>
                        <span style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600;">IVA incluido</span>
                      </div>
                      @if(!empty($prod['colors']))
                        <div class="product-card-colors">
                          @foreach($prod['colors'] as $c)
                            @php
                              $isMulti = str_contains(strtolower($c['name'] ?? ''), 'multicolor');
                              $dotBg = $isMulti
                                ? 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)'
                                : ($c['hex'] ?? '#0f172a');
                            @endphp
                            <span class="product-card-color-dot {{ $isMulti ? 'color-dot-multicolor' : '' }}" data-color-name="{{ $c['name'] }}" title="{{ $c['name'] }}" style="background: {{ $dotBg }};"></span>
                          @endforeach
                        </div>
                      @endif
                    </div>
                    <div>
                      <div class="product-sizes-selector">
                        @foreach(($prod['sizes'] ?? ['CH', 'M', 'G']) as $sz)
                          <button type="button" class="size-pill-btn {{ $loop->first ? 'selected' : '' }}" data-action="select-size" data-product-id="{{ $prod['id'] }}" data-size="{{ $sz }}">{{ $sz }}</button>
                        @endforeach
                      </div>
                      <div style="display: flex; gap: 6px; margin-top: 8px;">
                        <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" class="btn-hero-secondary" style="padding: 8px 10px; font-size: 0.7rem; border-radius: var(--radius-sm); text-decoration: none; text-align: center;" title="Ver ficha técnica completa">
                          FICHA
                        </a>
                        <button type="button" class="btn-add-cart" data-action="add-to-cart" data-product-id="{{ $prod['id'] }}" style="flex: 1;">
                          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                          <span>AGREGAR</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--text-muted);">
                  <strong style="font-family: var(--font-heading); color: var(--text-primary); display: block; font-size: 0.95rem;">Sin prendas en esta selección</strong>
                  <small style="font-size: 0.8rem;">Prueba cambiando el universo o la categoría en los filtros.</small>
                </div>
              @endforelse
            </div>
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_html))
              {!! $sec->custom_html !!}
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </section>
        @elseif($sec->type === 'subcategories_slider')
          <!-- Slider Horizontal de Subcategorías CMS -->
          @php
            $sliderProducts = $sec->resolved_products ?? $products;
          @endphp
          <section id="section-{{ $sec->id }}" style="{{ $secSpacingStyle }}" data-section-id="{{ $sec->id }}" data-section-type="subcategories_slider">
            <div class="catalog-section-header">
              <div>
                <h2 class="section-headline">{{ $sec->title ?? 'EXPLORAR COLECCIÓN' }}</h2>
                <span style="font-size: 0.72rem; color: var(--text-muted);">{{ $sec->subtitle ?? 'Desliza para ver más prendas' }}</span>
              </div>
              @if(!empty($sec->badge))
                <span class="badge-lead-magnet" style="font-size: 0.65rem;">{{ $sec->badge }}</span>
              @endif
            </div>

            <div style="display: flex; gap: 14px; overflow-x: auto; padding-bottom: 12px; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: thin;">
              @forelse($sliderProducts as $prod)
                <div class="product-card" data-product-id="{{ $prod['id'] }}" style="min-width: 220px; max-width: 240px; flex-shrink: 0; scroll-snap-align: start;">
                  <div class="product-image-wrap" style="height: 180px;">
                    <button type="button" class="btn-wishlist-toggle" data-action="toggle-wishlist" data-product-id="{{ $prod['id'] }}" title="Guardar en mis deseos" aria-label="Guardar en favoritos">
                      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </button>
                    <span class="product-stock-badge {{ ($prod['stockType'] ?? '') === 'ready' ? 'ready' : 'on_demand' }}">
                      {{ $prod['stockLabel'] ?? 'EXPRESS' }}
                    </span>
                    <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%; cursor: pointer; text-decoration: none; position: relative;" title="{{ $prod['name'] }}">
                      @if(!empty($prod['image']) && $prod['image'] !== '/user-avatar.png')
                        <img src="{{ $prod['image'] }}" alt="{{ $prod['name'] }}" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        <div class="product-fallback-silhouette" style="display: none; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; position: absolute; inset: 0;">
                          <svg width="36" height="36" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                          <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] ?? 'TORZO' }}</span>
                        </div>
                      @else
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%;">
                          <svg width="36" height="36" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                          <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] ?? 'TORZO' }}</span>
                        </div>
                      @endif
                    </a>
                  </div>
                  <div class="product-body" style="padding: 10px;">
                    <div>
                      <div class="product-category-label">{{ $prod['categoryName'] ?? 'General' }}</div>
                      <h3 class="product-title" style="font-size: 0.78rem;">
                        <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="cursor: pointer; color: inherit; text-decoration: none;">{{ $prod['name'] }}</a>
                      </h3>
                      <div class="product-pricing-row">
                        <span class="product-price" style="font-size: 0.85rem;">{{ $prod['price_formatted'] ?? ('MXN $' . number_format((float)($prod['price'] ?? 0), 2)) }}</span>
                      </div>
                      @if(!empty($prod['colors']))
                        <div class="product-card-colors">
                          @foreach($prod['colors'] as $c)
                            @php
                              $isMulti = str_contains(strtolower($c['name'] ?? ''), 'multicolor');
                              $dotBg = $isMulti
                                ? 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)'
                                : ($c['hex'] ?? '#0f172a');
                            @endphp
                            <span class="product-card-color-dot {{ $isMulti ? 'color-dot-multicolor' : '' }}" data-color-name="{{ $c['name'] }}" title="{{ $c['name'] }}" style="background: {{ $dotBg }};"></span>
                          @endforeach
                        </div>
                      @endif
                    </div>
                    <div style="margin-top: 8px;">
                      <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" class="btn-hero-secondary" style="display: block; padding: 6px 10px; font-size: 0.68rem; border-radius: var(--radius-sm); text-decoration: none; text-align: center;" title="Ver ficha técnica">
                        VER PRENDA
                      </a>
                    </div>
                  </div>
                </div>
              @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted); width: 100%;">
                  <small>No hay prendas disponibles para esta subcategoría.</small>
                </div>
              @endforelse
            </div>
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_html))
              {!! $sec->custom_html !!}
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </section>
        @elseif(in_array($sec->type, ['promo_modal', 'pop_up']))
          @php
            $st = $sec->settings ?? [];
            if (is_string($st)) {
              $st = json_decode($st, true) ?? [];
            }
            $popupAction = $st['popup_action'] ?? 'ally_discount';
            $allyCampaignId = $st['popup_ally_campaign_id'] ?? null;
            $allyCampaign = null;
            if ($popupAction === 'ally_discount' && $allyCampaignId) {
              $allyCampaign = \App\Models\AllyCampaignDirA::with('ally')->find($allyCampaignId);
            }
            
            $allyImgSource = $st['popup_ally_image_source'] ?? 'ally_poster';
            $bgImage = '';
            if ($popupAction === 'ally_discount' && $allyImgSource === 'ally_poster' && $allyCampaign && !empty($allyCampaign->banner_image)) {
              $bgImage = $allyCampaign->banner_image;
            } else {
              $bgImage = $st['popup_image'] ?? ($st['popup_image_url'] ?? ($sec->image_url ?? ''));
            }
            
            $focalPoint = $st['popup_image_focal_point'] ?? 'center center';
            $enableGrad = !isset($st['popup_enable_gradient']) || $st['popup_enable_gradient'] == '1' || $st['popup_enable_gradient'] === 1 || $st['popup_enable_gradient'] === true;
            $gradStart = $st['popup_gradient_color_start'] ?? '#0f172a';
            $gradEnd = $st['popup_gradient_color_end'] ?? '#000000';
            $gradDir = $st['popup_gradient_direction'] ?? 'to bottom';
            $opStart = ($st['popup_gradient_opacity_start'] ?? 85) / 100;
            $opEnd = ($st['popup_gradient_opacity_end'] ?? 95) / 100;
            
            $hexToRgb = function($hex) {
              $hex = str_replace('#', '', $hex);
              if (strlen($hex) == 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
              }
              $r = hexdec(substr($hex, 0, 2));
              $g = hexdec(substr($hex, 2, 2));
              $b = hexdec(substr($hex, 4, 2));
              return "$r, $g, $b";
            };
            $rgbStart = $hexToRgb($gradStart);
            $rgbEnd = $hexToRgb($gradEnd);
            $gradientCss = "linear-gradient({$gradDir}, rgba({$rgbStart}, {$opStart}) 0%, rgba({$rgbEnd}, {$opEnd}) 100%)";
            
            $closeBehavior = $st['popup_close_behavior'] ?? 'outside';
            $delaySeconds = (int)($st['popup_delay_seconds'] ?? 3);
            $frequency = $st['popup_frequency'] ?? 'once_per_session';
            $ctaText = $st['popup_cta_text'] ?? ($popupAction === 'presale' ? 'Acceder a la Preventa' : ($popupAction === 'newsletter' ? 'Suscribirme' : 'Obtener mi Descuento'));
            $displayTitle = !empty($sec->title) ? $sec->title : ($allyCampaign->title ?? 'Beneficio Exclusivo');
            $displaySubtitle = !empty($sec->subtitle) ? $sec->subtitle : ($allyCampaign->benefit ?? 'Regístrate para recibir tu código único y guardarlo en tu cuenta.');
            $displayBadge = !empty($sec->badge) ? $sec->badge : ($allyCampaign ? 'ALIANZA CON ' . strtoupper($allyCampaign->ally->name ?? 'OFICIAL') : 'BENEFICIO EXCLUSIVO');
            $titleColor = $st['popup_title_color'] ?? ($settings['title_color'] ?? '#ffffff');
            $subtitleColor = $st['popup_subtitle_color'] ?? ($settings['subtitle_color'] ?? '#e2e8f0');
          @endphp

          <!-- Modal Pop-Up Full Background CMS (Hub 2 / Hub 7 / Hub 3) -->
          <div id="promoModal_{{ $sec->id }}" class="promo-modal-backdrop" data-close-behavior="{{ $closeBehavior }}" data-delay="{{ $delaySeconds }}" data-frequency="{{ $frequency }}" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0,0,0,0.75); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 16px;">
            <div class="popup-modal-card" onclick="event.stopPropagation();" style="position: relative; width: 100%; max-width: 440px; min-height: 480px; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6); border: 1px solid rgba(255, 255, 255, 0.15); display: flex; flex-direction: column; justify-content: flex-end; color: #ffffff; background-color: #0f172a;">
              <!-- Background Image -->
              @if(!empty($bgImage))
                <div style="position: absolute; inset: 0; background-image: url('{{ asset($bgImage) }}'); background-size: cover; background-repeat: no-repeat; background-position: {{ $focalPoint }}; z-index: 1;"></div>
              @endif
              <!-- Gradient Overlay -->
              @if($enableGrad)
                <div style="position: absolute; inset: 0; background: {{ $gradientCss }}; z-index: 2;"></div>
              @endif
              
              <!-- Close Button -->
              <button type="button" class="btn-popup-close-x" onclick="event.stopPropagation(); window.closeStorePopup('{{ $sec->id }}');" onmousedown="event.stopPropagation(); window.closeStorePopup('{{ $sec->id }}');" ontouchstart="event.stopPropagation(); window.closeStorePopup('{{ $sec->id }}');" style="position: absolute; top: 14px; right: 14px; z-index: 999999; pointer-events: auto !important; background: rgba(0,0,0,0.65); border: 1.5px solid rgba(255,255,255,0.4); width: 36px; height: 36px; border-radius: 50%; color: #ffffff; font-size: 1.4rem; font-weight: bold; cursor: pointer !important; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(6px); transition: all 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.4);" aria-label="Cerrar ventana emergente">&times;</button>
              
              <!-- Content Floating on top -->
              <div style="position: relative; z-index: 5; padding: 24px; display: flex; flex-direction: column; gap: 10px;">
                @if(!empty($displayBadge))
                  <span style="align-self: flex-start; padding: 3px 10px; border-radius: 9999px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(99, 102, 241, 0.85); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.3); color: #ffffff;">
                    {{ $displayBadge }}
                  </span>
                @endif
                
                <h3 style="margin: 0; color: {{ $titleColor }} !important; font-family: var(--font-heading); font-size: 1.35rem; font-weight: 800; line-height: 1.2; text-shadow: 0 2px 8px rgba(0,0,0,0.6);">
                  {{ $displayTitle }}
                </h3>
                
                @if(!empty($displaySubtitle))
                  <p style="margin: 0; color: {{ $subtitleColor }} !important; font-size: 0.8rem; line-height: 1.4; text-shadow: 0 1px 4px rgba(0,0,0,0.6);">
                    {{ $displaySubtitle }}
                  </p>
                @endif
                
                <!-- Form Container -->
                <div id="popupFormContainer_{{ $sec->id }}" style="margin-top: 6px;">
                  <form id="popupSubscribeForm_{{ $sec->id }}" onsubmit="window.handleStorePopupSubmit(event, '{{ $sec->id }}')" style="display: flex; flex-direction: column; gap: 8px;">
                    <input type="hidden" name="section_id" value="{{ $sec->id }}">
                    <input type="hidden" name="popup_action" value="{{ $popupAction }}">
                    <input type="hidden" name="ally_campaign_id" value="{{ $allyCampaignId ?? '' }}">
                    <input type="hidden" name="universe" value="{{ $activeUniverse ?? 'marte' }}">
                    
                    <input type="text" name="name" required placeholder="Tu Nombre Completo" style="width: 100%; padding: 8px 12px; border-radius: 8px; font-size: 0.8rem; background: rgba(255,255,255,0.92); border: 1px solid rgba(255,255,255,0.3); color: #0f172a; outline: none; box-sizing: border-box;">
                    
                    <div style="display: flex; gap: 6px;">
                      <span style="padding: 8px 10px; font-size: 0.75rem; font-weight: 700; background: #059669; color: #ffffff; border-radius: 8px; display: flex; align-items: center; gap: 4px; white-space: nowrap; border: 1px solid rgba(255,255,255,0.2);">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 6.46 17.5 2 12.04 2M12.05 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 14.99 3.81 13.47 3.81 11.91C3.81 7.37 7.5 3.69 12.05 3.69C16.6 3.69 20.28 7.37 20.28 11.92C20.28 16.46 16.6 20.15 12.05 20.15Z"/></svg>
                        +52
                      </span>
                      <input type="tel" name="whatsapp" required pattern="[0-9]{10}" maxlength="10" placeholder="WhatsApp (10 dígitos)" style="flex: 1; min-width: 0; padding: 8px 12px; border-radius: 8px; font-size: 0.8rem; background: rgba(255,255,255,0.92); border: 1px solid rgba(255,255,255,0.3); color: #0f172a; outline: none; box-sizing: border-box;">
                    </div>
                    
                    <input type="email" name="email" required placeholder="Correo Electrónico" style="width: 100%; padding: 8px 12px; border-radius: 8px; font-size: 0.8rem; background: rgba(255,255,255,0.92); border: 1px solid rgba(255,255,255,0.3); color: #0f172a; outline: none; box-sizing: border-box;">
                    
                    <button type="submit" id="popupSubmitBtn_{{ $sec->id }}" style="width: 100%; padding: 10px; background: #4f46e5; color: #ffffff; border: none; border-radius: 8px; font-size: 0.82rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);">
                      {{ $ctaText }}
                    </button>
                    
                    <div style="display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 0.65rem; color: #cbd5e1; text-align: center; margin-top: 2px;">
                      <svg width="10" height="10" fill="none" stroke="#34d399" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                      <span>Código único de un solo uso &bull; Envío instantáneo a WhatsApp</span>
                    </div>
                  </form>
                </div>
                
                <!-- Success State Container (Hidden initially) -->
                <div id="popupSuccessContainer_{{ $sec->id }}" style="display: none; flex-direction: column; gap: 10px; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; padding: 16px; text-align: center;">
                  <div style="font-size: 0.72rem; font-weight: 800; color: #34d399; text-transform: uppercase;">Cuenta Creada & Beneficio Desbloqueado</div>
                  <div id="popupVoucherDisplay_{{ $sec->id }}" style="font-family: monospace; font-size: 1.25rem; font-weight: 800; letter-spacing: 0.1em; color: #ffffff; background: rgba(0,0,0,0.4); padding: 8px; border-radius: 8px; border: 1px dashed #6366f1;">
                    ------
                  </div>
                  <div id="popupSuccessMsg_{{ $sec->id }}" style="font-size: 0.75rem; color: #e2e8f0; line-height: 1.35;"></div>
                  <div style="display: flex; gap: 6px; margin-top: 4px;">
                    <button type="button" id="popupCopyBtn_{{ $sec->id }}" style="flex: 1; padding: 8px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: #ffffff; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer;">Copiar</button>
                    <button type="button" id="popupApplyBtn_{{ $sec->id }}" style="flex: 1; padding: 8px; background: #059669; border: none; color: #ffffff; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer;">Aplicar al Carrito</button>
                  </div>
                  <button type="button" onclick="event.stopPropagation(); window.closeStorePopup('{{ $sec->id }}');" style="background: none; border: none; color: #94a3b8; font-size: 0.7rem; cursor: pointer; text-decoration: underline; margin-top: 2px;">Cerrar ventana</button>
                </div>
              </div>
            </div>
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </div>
          <script>
            window.closeStorePopup = window.closeStorePopup || function(secId) {
              var m = document.getElementById('promoModal_' + secId);
              if (m) {
                m.style.setProperty('display', 'none', 'important');
                m.classList.remove('active');
                m.setAttribute('aria-hidden', 'true');
              }
              try {
                sessionStorage.setItem('store_popup_seen_' + secId, '1');
                localStorage.setItem('store_popup_closed_' + secId, Date.now().toString());
              } catch(e) {}
            };

            document.addEventListener('DOMContentLoaded', function() {
              var modalId = 'promoModal_{{ $sec->id }}';
              var m = document.getElementById(modalId);
              if (!m) return;
              
              var freq = m.dataset.frequency || 'once_per_session';
              var modalKey = 'store_popup_seen_{{ $sec->id }}';
              var shouldShow = true;
              
              if (freq === 'once_per_session' && sessionStorage.getItem(modalKey)) {
                shouldShow = false;
              } else if (freq === 'once_per_day') {
                var lastSeen = localStorage.getItem(modalKey);
                if (lastSeen && (Date.now() - parseInt(lastSeen, 10)) < 86400000) {
                  shouldShow = false;
                }
              }
              
              if (shouldShow) {
                var delay = (parseInt(m.dataset.delay, 10) || 3) * 1000;
                setTimeout(function() {
                  m.style.display = 'flex';
                  if (freq === 'once_per_session') sessionStorage.setItem(modalKey, '1');
                  if (freq === 'once_per_day') localStorage.setItem(modalKey, Date.now().toString());
                }, delay);
              }
              
              var closeBehavior = m.dataset.closeBehavior || 'outside';
              if (closeBehavior === 'outside') {
                m.addEventListener('click', function(ev) {
                  if (ev.target === m) {
                    window.closeStorePopup('{{ $sec->id }}');
                  }
                });
              }

              var closeBtn = m.querySelector('.btn-popup-close-x');
              if (closeBtn) {
                closeBtn.addEventListener('click', function(ev) {
                  ev.preventDefault();
                  ev.stopPropagation();
                  window.closeStorePopup('{{ $sec->id }}');
                });
              }
            });
          </script>
        @elseif(in_array($sec->type, ['custom_html_css_js', 'buttons']))
          <!-- Bloque Custom Operativo (HTML/CSS/JS Scoped) -->
          <section id="section-{{ $sec->id }}" class="custom-cms-section" data-section-id="{{ $sec->id }}" style="{{ $secSpacingStyle }}">
            @if(!empty($sec->custom_css))
              <style>{!! $sec->custom_css !!}</style>
            @endif
            @if(!empty($sec->custom_html))
              {!! $sec->custom_html !!}
            @endif
            @if(!empty($sec->custom_js))
              <script>{!! $sec->custom_js !!}</script>
            @endif
          </section>
        @endif
      @endforeach
    @else
      <!-- Hero Carousel / Editorial Banner (Fallback) -->
      <section class="hero-editorial-section" style="margin-top: 0px !important; margin-bottom: 0px !important; padding-top: 0 !important; padding-bottom: 0 !important;">
        <div class="hero-banner-card">
          <div class="hero-tag-pill">
            <span class="hero-pulse-dot"></span>
            <span id="heroBrandTag">{{ $currentStorefront['heroTag'] ?? 'MARTE // COLECCIÓN OFICIAL TORZO' }}</span>
          </div>
          <h1 class="hero-title">{{ $currentStorefront['heroTitle'] ?? 'MODA CONTEMPORÁNEA & ROPA INTERIOR ERGONÓMICA' }}</h1>
          <p class="hero-desc">
            {{ $currentStorefront['heroSubtitle'] ?? 'Diseño textil anatómico con modal pima, costuras planas y microfibras de alta transpirabilidad. Envíos express a todo México.' }}
          </p>
          <div class="hero-actions-row">
            <button type="button" class="btn-hero-primary" data-action="switch-dock-tab" data-dock-tab="catalog">
              <span>EXPLORAR CATÁLOGO</span>
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
            <button type="button" class="btn-hero-secondary" data-action="open-size-guide">
              <span>GUÍA DE TALLAS TORZO</span>
            </button>
          </div>
        </div>
      </section>

      <!-- Pilares de Marca & Beneficios (Fallback) -->
      <section class="brand-pillars-bar" style="margin-top: 0px !important; margin-bottom: 0px !important;">
        <div class="pillar-item-card">
          <div class="pillar-icon-box">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          </div>
          <div>
            <div class="pillar-title">ENVÍOS EXPRESS NACIONALES</div>
            <div class="pillar-sub">Paquetería Express (${{ number_format((float)($shippingRule['flatRate'] ?? 149), 0) }} MXN / Gratis desde ${{ number_format((float)($shippingRule['freeShippingThreshold'] ?? 999), 0) }})</div>
          </div>
        </div>

        <div class="pillar-item-card">
          <div class="pillar-icon-box">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M7 15h3M15 15h2"/></svg>
          </div>
          <div>
            <div class="pillar-title">TRANSFERENCIAS Tarjetas BBVA</div>
            <div class="pillar-sub">Sin comisión bancaria directa</div>
          </div>
        </div>

        <div class="pillar-item-card">
          <div class="pillar-icon-box">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          </div>
          <div>
            <div class="pillar-title">EMPAQUE 100% DISCRETO</div>
            <div class="pillar-sub">Precinto higiénico termosellado</div>
          </div>
        </div>
      </section>

      <!-- Banner Promocional de Campañas Dir A (Fallback) -->
      <section class="lead-magnet-banner-card" id="leadMagnetHomeCard" style="margin-top: 0px !important; margin-bottom: 0px !important;">
        <div class="lead-magnet-content">
          <div class="lead-magnet-tag-row">
            <span class="lead-magnet-tag">CONVENIOS MULTIVERSO // HUB 7</span>
            <span class="badge-lead-magnet">CORTESÍA EXCLUSIVA</span>
          </div>
          <h3 class="lead-magnet-title" id="leadMagnetTitle">Cortesía Especial en Paradiso Club & Circuit CDMX</h3>
          <p class="lead-magnet-desc" id="leadMagnetDesc">
            Obtén tu voucher digital con acceso preferencial y trago de cortesía en los mejores venues aliados de la comunidad.
          </p>
          <button type="button" class="btn-lead-magnet-action" data-action="open-lead-magnet" data-campaign="PARADISO_VIP">
            <span>OBTENER MI VOUCHER DIGITAL</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </div>
      </section>

      <!-- Feed de Prendas Destacadas (Fallback) -->
      <section style="margin-top: 12px !important; margin-bottom: 12px !important; padding-top: 12px !important; padding-bottom: 12px !important;">
        <div class="catalog-section-header">
          <div>
            <h2 class="section-headline">PRENDAS DESTACADAS</h2>
            <span style="font-size: 0.72rem; color: var(--text-muted);">Curaduría activa para tu universo</span>
          </div>
          <button type="button" class="section-action-btn" data-action="open-filter-drawer">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
            <span>Filtros</span>
          </button>
        </div>

        <div id="homeProductsGrid" class="product-feed-grid">
          @forelse($products as $prod)
            <div class="product-card" data-product-id="{{ $prod['id'] }}">
              <div class="product-image-wrap">
                <button type="button" class="btn-wishlist-toggle" data-action="toggle-wishlist" data-product-id="{{ $prod['id'] }}" title="Guardar en mis deseos" aria-label="Guardar en favoritos">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </button>
                <span class="product-stock-badge {{ ($prod['stockType'] ?? '') === 'ready' ? 'ready' : 'on_demand' }}">
                  {{ $prod['stockLabel'] }}
                </span>
                <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%; cursor: pointer; text-decoration: none; position: relative;" title="{{ $prod['name'] }}">
                  @if(!empty($prod['image']) && $prod['image'] !== '/user-avatar.png')
                    <img src="{{ $prod['image'] }}" alt="{{ $prod['name'] }}" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    <div class="product-fallback-silhouette" style="display: none; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; position: absolute; inset: 0;">
                      <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] }}</span>
                    </div>
                  @else
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%;">
                      <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] }}</span>
                    </div>
                  @endif
                </a>
              </div>
              <div class="product-body">
                <div>
                  <div class="product-category-label">{{ $prod['categoryName'] }}</div>
                  <h3 class="product-title">
                    <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="cursor: pointer; color: inherit; text-decoration: none;">{{ $prod['name'] }}</a>
                  </h3>
                  <div class="product-pricing-row">
                    <span class="product-price">{{ $prod['price_formatted'] }}</span>
                    <span style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600;">IVA incluido</span>
                  </div>
                  @if(!empty($prod['colors']))
                    <div class="product-card-colors">
                      @foreach($prod['colors'] as $c)
                        @php
                          $isMulti = str_contains(strtolower($c['name'] ?? ''), 'multicolor');
                          $dotBg = $isMulti
                            ? 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)'
                            : ($c['hex'] ?? '#0f172a');
                        @endphp
                        <span class="product-card-color-dot {{ $isMulti ? 'color-dot-multicolor' : '' }}" data-color-name="{{ $c['name'] }}" title="{{ $c['name'] }}" style="background: {{ $dotBg }};"></span>
                      @endforeach
                      <span style="font-size: 0.65rem; color: var(--text-muted); margin-left: 2px;">{{ count($prod['colors']) }} {{ count($prod['colors']) === 1 ? 'color' : 'colores' }}</span>
                    </div>
                  @endif
                </div>
                <div>
                  <div class="product-sizes-selector">
                    @foreach($prod['sizes'] as $sz)
                      <button type="button" class="size-pill-btn {{ $loop->first ? 'selected' : '' }}" data-action="select-size" data-product-id="{{ $prod['id'] }}" data-size="{{ $sz }}">{{ $sz }}</button>
                    @endforeach
                  </div>
                  <div style="display: flex; gap: 6px; margin-top: 8px;">
                    <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" class="btn-hero-secondary" style="padding: 8px 10px; font-size: 0.7rem; border-radius: var(--radius-sm); text-decoration: none; text-align: center;" title="Ver ficha técnica completa">
                      FICHA
                    </a>
                    <button type="button" class="btn-add-cart" data-action="add-to-cart" data-product-id="{{ $prod['id'] }}" style="flex: 1;">
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                      <span>AGREGAR</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--text-muted);">
              <strong style="font-family: var(--font-heading); color: var(--text-primary); display: block; font-size: 0.95rem;">Sin prendas en esta selección</strong>
              <small style="font-size: 0.8rem;">Prueba cambiando el universo o la categoría en los filtros.</small>
            </div>
          @endforelse
        </div>
      </section>
    @endif

  </div>

  <!-- VISTA: EXPLORAR (BOTONES CON IMAGEN 4:5, OVERLAY CON DEGRADADO & ACCIÓN DIRECTA) -->
  <div id="viewExplore" style="{{ ($initialTab ?? '') === 'explore' ? 'display: block;' : 'display: none;' }} padding-top: 16px;">
    <div class="catalog-section-header">
      <div>
        <h2 class="section-headline">EXPLORAR UNIVERSOS &amp; COLECCIONES</h2>
        <span style="font-size: 0.72rem; color: var(--text-muted);">Páginas temáticas, curadurías de estilo y guías de selección</span>
      </div>
    </div>

    <style>
      .explore-buttons-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 16px;
        margin-top: 16px;
        margin-bottom: 40px;
      }
      @media (max-width: 640px) {
        .explore-buttons-grid {
          grid-template-columns: repeat(2, 1fr);
          gap: 12px;
        }
      }
      .explore-button-card {
        aspect-ratio: 4 / 5;
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 16px;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s ease;
        text-align: left;
        width: 100%;
        background: #09090b;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
      }
      .explore-button-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.25);
      }
      .explore-button-card:active {
        transform: scale(0.98);
      }
      .explore-carousel-wrap {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
      }
      .explore-button-card .explore-card-bg-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
        transition: opacity 0.8s ease-in-out, transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
      }
      .explore-carousel-wrap .explore-card-bg-img {
        opacity: 0;
        pointer-events: none;
      }
      .explore-carousel-wrap .explore-card-bg-img.active {
        opacity: 1;
      }
      .explore-button-card:hover .explore-card-bg-img {
        transform: scale(1.05);
      }
      .explore-carousel-dots {
        position: absolute;
        bottom: 8px;
        right: 12px;
        display: flex;
        gap: 4px;
        z-index: 4;
        pointer-events: none;
      }
      .explore-dot {
        width: 5px;
        height: 5px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.4);
        transition: all 0.3s ease;
      }
      .explore-dot.active {
        background: #ffffff;
        width: 14px;
      }
      .explore-button-card .explore-card-overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        pointer-events: none;
      }
      .explore-button-card .explore-card-top {
        position: relative;
        z-index: 3;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        pointer-events: none;
      }
      .explore-button-card .explore-card-content {
        position: relative;
        z-index: 3;
        display: flex;
        flex-direction: column;
        pointer-events: none;
      }
    </style>

    <div class="explore-buttons-grid">
      @forelse($explorePages ?? [] as $ep)
        @php
          $hasFilters = $ep->hasCatalogFilters();
          $overlayCss = $ep->getOverlayGradientCss();
          $resolvedImages = $ep->getResolvedImages();
          $textColor = $ep->getTextColor();
          $hasCarousel = count($resolvedImages) > 1;
          $catalogTargetUrl = $ep->getCatalogUrl($activeUniverse);
          $effectiveCategory = $ep->getEffectiveCategory();
        @endphp

        @if($hasFilters)
          <a href="{{ $catalogTargetUrl }}" 
             class="explore-button-card"
             data-action="explore-filter-catalog"
             data-universe="{{ $ep->filter_universe ?? 'all' }}"
             data-category="{{ $effectiveCategory ?? '' }}"
             data-supplier="{{ $ep->filter_supplier ?? '' }}"
             data-tags="{{ $ep->filter_tags ?? '' }}"
             title="{{ $ep->title }}">
        @else
          <a href="{{ url('/pagina/' . $ep->slug) }}" 
             class="explore-button-card"
             role="button"
             title="{{ $ep->title }}">
        @endif

          {{-- 1. IMAGEN DE FONDO / CARRUSEL AUTOMÁTICO (REGLA 1.2) --}}
          @if($hasCarousel)
            <div class="explore-carousel-wrap" data-carousel-explore="{{ $ep->id }}">
              @foreach($resolvedImages as $imgIndex => $imgUrl)
                <img src="{{ $imgUrl }}" alt="{{ $ep->title }}" class="explore-card-bg-img {{ $imgIndex === 0 ? 'active' : '' }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
              @endforeach
              <div class="explore-carousel-dots">
                @foreach($resolvedImages as $dotIndex => $dot)
                  <span class="explore-dot {{ $dotIndex === 0 ? 'active' : '' }}"></span>
                @endforeach
              </div>
            </div>
          @elseif(!empty($resolvedImages[0]))
            <img src="{{ $resolvedImages[0] }}" alt="{{ $ep->title }}" class="explore-card-bg-img" loading="lazy">
          @else
            <div class="explore-card-bg-img" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #18181b, #09090b); color: #71717a;">
              <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
            </div>
          @endif

          {{-- 2. OVERLAY CON DEGRADADO (REGLA 1.1) --}}
          <div class="explore-card-overlay" style="background: {!! $overlayCss !!};"></div>

          {{-- 3. CABECERA / INSIGNIA SUPERIOR --}}
          <div class="explore-card-top">
            @if($hasFilters)
              <span style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: {{ $textColor }}; font-size: 0.62rem; font-weight: 800; padding: 3px 9px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid rgba(255,255,255,0.25);">
                {{ strtoupper($ep->filter_universe ?? 'CATÁLOGO') }} {{ !empty($effectiveCategory) ? '// ' . strtoupper($effectiveCategory) : '' }}
              </span>
            @elseif(in_array($ep->slug, ['aliados', 'convenios', 'convenios-con-aliados']) || str_contains(strtolower($ep->title), 'aliados') || str_contains(strtolower($ep->title), 'convenios'))
              <span style="background: rgba(79, 70, 229, 0.85); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: #ffffff; font-size: 0.62rem; font-weight: 800; padding: 3px 9px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid rgba(255,255,255,0.25);">
                CONVENIOS // EVENTOS HUB 7
              </span>
            @else
              <span style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: {{ $textColor }}; font-size: 0.62rem; font-weight: 800; padding: 3px 9px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid rgba(255,255,255,0.25);">
                EDITORIAL
              </span>
            @endif

            @if($hasFilters)
              <span class="explore-card-corner-link" 
                 style="background: rgba(255, 255, 255, 0.18); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: {{ $textColor }}; border-radius: 9999px; padding: 4px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.3); transition: background 0.2s;"
                 title="Ver catálogo de {{ $ep->title }}"
                 aria-hidden="true">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
              </span>
            @endif
          </div>

          {{-- 4. CONTENIDO INFERIOR INTEGRADO AL BOTÓN (CONTRASTE GARANTIZADO) --}}
          <div class="explore-card-content">
            <h3 style="font-family: var(--font-heading, sans-serif); font-size: 1.05rem; font-weight: 800; color: {{ $textColor }}; margin: 0 0 4px; line-height: 1.25; text-shadow: 0 2px 8px rgba(0,0,0,0.7);">
              {{ $ep->title }}
            </h3>
            @if(!empty($ep->seo_description))
              <p style="font-size: 0.72rem; color: {{ $textColor }}; opacity: 0.9; margin: 0 0 10px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-shadow: 0 1px 4px rgba(0,0,0,0.7);">
                {{ \Illuminate\Support\Str::limit($ep->seo_description, 80) }}
              </p>
            @endif

            <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
              <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $textColor }}; background: rgba(255,255,255,0.22); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); padding: 5px 12px; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.35);">
                @if($hasFilters)
                  <span>Ver Catálogo</span>
                  <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                @elseif(in_array($ep->slug, ['aliados', 'convenios', 'convenios-con-aliados']) || str_contains(strtolower($ep->title), 'aliados') || str_contains(strtolower($ep->title), 'convenios'))
                  <span>Ver Convenios & Eventos</span>
                  <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                @else
                  <span>Ver Página</span>
                  <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                @endif
              </span>
            </div>
          </div>

          </a>
      @empty
        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: var(--bg-secondary); border-radius: var(--radius-md); border: 1px dashed var(--border-subtle);">
          <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">No hay páginas de exploración configuradas aún. Créalas desde el Hub 2 en la sección "Páginas de Explorar".</p>
        </div>
      @endforelse
    </div>

    {{-- Script del carrusel automático para tarjetas de explorar --}}
    <script>
      (function() {
        function initExploreCarousels() {
          const carousels = document.querySelectorAll('[data-carousel-explore]');
          carousels.forEach(carousel => {
            const imgs = carousel.querySelectorAll('.explore-card-bg-img');
            const dots = carousel.querySelectorAll('.explore-dot');
            if (imgs.length <= 1) return;
            let current = 0;
            setInterval(() => {
              imgs[current].classList.remove('active');
              if (dots[current]) dots[current].classList.remove('active');
              current = (current + 1) % imgs.length;
              imgs[current].classList.add('active');
              if (dots[current]) dots[current].classList.add('active');
            }, 3500);
          });
        }
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', initExploreCarousels);
        } else {
          initExploreCarousels();
        }
      })();
    </script>
  </div>

  <!-- VISTA 2: CATÁLOGO COMPLETO -->
  <div id="viewCatalog" style="{{ ($initialTab ?? '') === 'catalog' ? 'display: block;' : 'display: none;' }} padding-top: 16px;">
    <div class="catalog-section-header">
      <div>
        <h2 class="section-headline">CATÁLOGO COMPLETO</h2>
        <span style="font-size: 0.72rem; color: var(--text-muted);">Prendas de diseño, ropa interior y cuidado</span>
      </div>
      <button type="button" class="section-action-btn" data-action="open-filter-drawer">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
        <span>Filtros & Universos</span>
      </button>
    </div>

    <div id="catalogProductsGrid" class="product-feed-grid">
      @forelse($products as $prod)
        <div class="product-card" data-product-id="{{ $prod['id'] }}">
          <div class="product-image-wrap">
            <button type="button" class="btn-wishlist-toggle" data-action="toggle-wishlist" data-product-id="{{ $prod['id'] }}" title="Guardar en mis deseos" aria-label="Guardar en favoritos">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </button>
            <span class="product-stock-badge {{ ($prod['stockType'] ?? '') === 'ready' ? 'ready' : 'on_demand' }}">
              {{ $prod['stockLabel'] }}
            </span>
            <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%; cursor: pointer; text-decoration: none; position: relative;" title="{{ $prod['name'] }}">
              @if(!empty($prod['image']) && $prod['image'] !== '/user-avatar.png')
                <img src="{{ $prod['image'] }}" alt="{{ $prod['name'] }}" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                <div class="product-fallback-silhouette" style="display: none; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%; position: absolute; inset: 0;">
                  <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] }}</span>
                </div>
              @else
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #a1a1aa; width: 100%; height: 100%;">
                  <svg width="40" height="40" viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="4" fill="#18181b"/><path d="M8 22V10L16 16L24 10V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  <span style="font-family: var(--font-heading); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; color: #71717a;">{{ $prod['sku'] }}</span>
                </div>
              @endif
            </a>
          </div>
          <div class="product-body">
            <div>
              <div class="product-category-label">{{ $prod['categoryName'] }}</div>
              <h3 class="product-title">
                <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" style="cursor: pointer; color: inherit; text-decoration: none;">{{ $prod['name'] }}</a>
              </h3>
              <div class="product-pricing-row">
                <span class="product-price">{{ $prod['price_formatted'] }}</span>
                <span style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600;">IVA incluido</span>
              </div>
              @if(!empty($prod['colors']))
                <div class="product-card-colors">
                  @foreach($prod['colors'] as $c)
                    @php
                      $isMulti = str_contains(strtolower($c['name'] ?? ''), 'multicolor');
                      $dotBg = $isMulti
                        ? 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)'
                        : ($c['hex'] ?? '#0f172a');
                    @endphp
                    <span class="product-card-color-dot {{ $isMulti ? 'color-dot-multicolor' : '' }}" data-color-name="{{ $c['name'] }}" title="{{ $c['name'] }}" style="background: {{ $dotBg }};"></span>
                  @endforeach
                  <span style="font-size: 0.65rem; color: var(--text-muted); margin-left: 2px;">{{ count($prod['colors']) }} {{ count($prod['colors']) === 1 ? 'color' : 'colores' }}</span>
                </div>
              @endif
            </div>
            <div>
              <div class="product-sizes-selector">
                @foreach($prod['sizes'] as $sz)
                  <button type="button" class="size-pill-btn {{ $loop->first ? 'selected' : '' }}" data-action="select-size" data-product-id="{{ $prod['id'] }}" data-size="{{ $sz }}">{{ $sz }}</button>
                @endforeach
              </div>
              <div style="display: flex; gap: 6px; margin-top: 8px;">
                <a href="{{ url('/productos/' . ($prod['slug'] ?? $prod['id'])) }}" class="btn-hero-secondary" style="padding: 8px 10px; font-size: 0.7rem; border-radius: var(--radius-sm); text-decoration: none; text-align: center;" title="Ver ficha técnica completa">
                  FICHA
                </a>
                <button type="button" class="btn-add-cart" data-action="add-to-cart" data-product-id="{{ $prod['id'] }}" style="flex: 1;">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  <span>AGREGAR</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--text-muted);">
          <strong style="font-family: var(--font-heading); color: var(--text-primary); display: block; font-size: 0.95rem;">Sin prendas en esta selección</strong>
          <small style="font-size: 0.8rem;">Prueba cambiando el universo o la categoría en los filtros.</small>
        </div>
      @endforelse
    </div>
  </div>
@endsection

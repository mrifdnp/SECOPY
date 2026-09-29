<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Homepage -->
    <url>
        <loc>{{ route('frontend.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Main Categories -->
    @foreach ($mainCategories as $mainCategory)
    <url>
        <loc>{{ route('frontend.productByMainCategory', ['main_slug' => $mainCategory->slug]) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    <!-- Sub Categories -->
    @foreach ($categories as $category)
    @if($category->mainCategory)
    <url>
        <loc>{{ route('frontend.product', ['main_slug' => $category->mainCategory->slug, 'sub_slug' => $category->slug]) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endif
    @endforeach

    <!-- Products -->
    @foreach ($products as $product)
    <url>
        <loc>{{ route('frontend.product.detail', $product->seo_url_param) }}</loc>
        @if($product->updated_at)
        <lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>
        @else
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        @endif
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    @endforeach
</urlset>

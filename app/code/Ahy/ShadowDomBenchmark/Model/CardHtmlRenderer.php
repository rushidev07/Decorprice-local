<?php
declare(strict_types=1);

namespace Ahy\ShadowDomBenchmark\Model;

/**
 * Builds the product-card markup once, shared by both server-rendering variants
 * (SSR-plain and SSR+Declarative-Shadow-DOM), so the two are visually identical
 * and the only difference under test is the shadow-DOM wrapping itself.
 */
class CardHtmlRenderer
{
    /**
     * @param array<int, array{id:int, name:string, price:float, image:string, url:string}> $products
     */
    public function render(array $products): string
    {
        $html = '';
        foreach ($products as $p) {
            $name = htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8');
            $price = number_format($p['price'], 2);
            $img = htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars($p['url'], ENT_QUOTES, 'UTF-8');

            $html .= '<a class="card" href="' . $url . '">'
                . '<div class="card-img"><img src="' . $img . '" alt="' . $name . '" loading="lazy"></div>'
                . '<div class="card-name">' . $name . '</div>'
                . '<div class="card-price">$' . $price . '</div>'
                . '</a>';
        }
        return $html;
    }

    public function sharedCss(): string
    {
        return <<<CSS
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px}
.card{display:block;text-decoration:none;color:#111;border:1px solid #eee;border-radius:8px;overflow:hidden;font-family:system-ui,sans-serif}
.card-img{aspect-ratio:1;background:#f5f5f5;overflow:hidden}
.card-img img{width:100%;height:100%;object-fit:cover;display:block}
.card-name{padding:8px 10px 2px;font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.card-price{padding:0 10px 10px;font-size:13px;color:#555}
CSS;
    }
}

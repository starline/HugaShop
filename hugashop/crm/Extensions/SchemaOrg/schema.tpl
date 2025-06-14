<!-- Schema.org -->
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "{$settings->company_name} {$settings->company_description}",
        "url": "{$config->root_url}",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "{$config->root_url}/s/{literal}{search_term_string}{/literal}",
            "query-input": "required name=search_term_string"
        }
    }
</script>

<script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "Organization",
        "url": "{$config->root_url}",
        "logo": "{'images/logo.png'|asset}",
        "name": "{$settings->company_name}"
    }
</script>

{if $route == 'Product' and !$product->name|empty}
    <script type="application/ld+json">
        {
            "@context": "https://schema.org/",
            "@type": "Product",
            "name": "{$product->name}",
            "image": [
                {foreach $product->images as $image}
                    "{$image->filename|resize:1080:1080:w}"{if !$image@last},{/if}
                {/foreach}
            ],
            "description": "Цена: {$product->variant->price} {$currency->sign}. {$product->annotation|strip_tags}",
            "sku": "{$product->variant->sku}",
            "brand": {
                "@type": "Brand",
                "name":"{$settings->domain}"
            },
            "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": 5,
                "reviewCount": {if $comments_total->count>0}{$comments_total->count}{else}1{/if}
            },
            "offers": {
                "@type": "Offer",
                "url": "{$config->root_url}/tovar-{$product->url}",
                "priceCurrency": "{$currency->code}",
                "price": "{$product->variant->price}",
                "priceValidUntil": "{'+30 days'|date:'Y-m-d'}",
                "itemCondition": "https://schema.org/NewCondition",
                "availability": {if $product->variant->stock > 0 || $product->variant->stock|is_null}"https://schema.org/InStock"{else}"https://schema.org/OutOfStock"{/if},
                "seller": {
                    "@type": "Organization",
                    "name": "{$settings->company_name}"
                },
                "shippingDetails": {
                    "@type": "OfferShippingDetails",
                    "shippingRate": {
                        "@type": "MonetaryAmount",
                        "value": {$SchemaOrg->shipping_cost},
                        "currency": "{$currency->code}"
                    },
                    "shippingDestination": {
                        "@type": "DefinedRegion",
                        "addressCountry": "{$SchemaOrg->country_code}"
                    },
                    "deliveryTime": {
                        "@type": "ShippingDeliveryTime",
                        "handlingTime": {
                            "@type": "QuantitativeValue",
                            "minValue": 0,
                            "maxValue": 1,
                            "unitCode": "DAY"
                        },
                        "transitTime": {
                            "@type": "QuantitativeValue",
                            "minValue": 1,
                            "maxValue": 2,
                            "unitCode": "DAY"
                        }
                    }
                },
                "hasMerchantReturnPolicy": {
                    "@type": "MerchantReturnPolicy",
                    "applicableCountry": "{$SchemaOrg->country_code}",
                    "returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",
                    "merchantReturnDays": {$SchemaOrg->return_days},
                    "returnMethod": "https://schema.org/ReturnByMail",
                    "returnFees": "https://schema.org/ReturnFeesCustomerResponsibility"
                }
            }
        }
    </script>
{/if}
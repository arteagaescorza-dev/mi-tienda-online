<?php

namespace App\Http\Controllers;

use App\Models\SupplierProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\CmsStorefrontConfig;
use App\Models\CmsSection;
use App\Models\CmsPage;
use App\Models\CmsBlogPost;
use App\Models\CmsCatalogFilter;
use App\Models\CmsOfficialDocument;
use App\Models\CmsTrackingSetting;
use App\Models\Cart;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Lead;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseKardex;
use App\Models\Supplier;
use App\Models\SupplierSyncRun;
use App\Models\OrderFinancialMargin;
use App\Models\PayoutBatch;
use App\Models\CustomerSubscription;
use App\Models\DunningRetryLog;
use App\Models\VendorBill;
use App\Models\ProductBundle;
use App\Models\CycleCount;
use App\Models\OperationalTask;
use App\Models\ShiftLog;
use App\Models\SupportCase;
use App\Models\ShippingReconciliation;
use App\Models\ShippingReconciliationItem;
use App\Models\ShippingDiscrepancyClaim;
use App\Models\ThreeWayMatching;
use App\Models\TaxLedgerEntry;
use App\Services\Finance\ShippingInvoiceAuditorService;
use App\Services\Finance\ThreeWayMatchingService;
use App\Services\Finance\TaxCalculationEngineService;
use App\Services\Inventory\VariantDimensionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PortalInventoryController extends Controller
{
    public function portalInventory(Request $request, VariantDimensionService $dimensionService)
    {
        $dimensionService->ensureBootstrapDefaults();
        $portalSizeCurves = $dimensionService->getAllCurves(true);
        $portalFragranceVolumes = $dimensionService->getAllVolumes(true);
        $portalCategories = $dimensionService->getAllCategories(true);

        $productsQuery = Product::query()
            ->with([
                'variants.inventories',
                'supplierProducts.supplier',
            ])
            ->where('is_archived', false)
            ->where('visible', true)
            ->where(function ($q) {
                $q->whereNull('meta->approval_status')
                  ->orWhere('meta->approval_status', 'approved');
            })
            ->orderBy('id');

        $draftProductsQuery = Product::query()
            ->with([
                'variants.inventories',
                'supplierProducts.supplier',
            ])
            ->where('is_archived', false)
            ->where('meta->approval_status', 'draft')
            ->orderByDesc('id');

        $portalDraftProducts = $draftProductsQuery->get()->map(function (Product $product) {
            $mainImage = data_get($product->meta, 'image')
                ?: (data_get($product->meta, 'images.0') ?: 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80');
            $variants = $product->variants;
            $isScrapedProduct = $product->isScraped() || $product->type === 'dropship';
            $totalStock = $isScrapedProduct ? 0 : (int) $variants->sum(fn ($v) => $v->inventories->where('location', 'warehouse-1')->sum('quantity'));
            return [
                'id' => $product->id,
                'dbId' => $product->id,
                'sku' => $product->sku,
                'name' => $product->title,
                'brand' => $product->brand ?: 'ModaIntima Textil MX',
                'category' => data_get($product->meta, 'category', 'Boxers'),
                'subcategory' => data_get($product->meta, 'subcategory', ''),
                'fabricType' => data_get($product->meta, 'fabric_type', $product->fabric),
                'colorName' => data_get($product->meta, 'color_name', 'Negro'),
                'colorHex' => data_get($product->meta, 'color_hex', '#0f172a'),
                'colorType' => data_get($product->meta, 'color_type', 'solid'),
                'designType' => data_get($product->meta, 'design_type', 'sin_diseno'),
                'websiteSection' => data_get($product->meta, 'website_section', 'basicos'),
                'description' => $product->description,
                'pvp' => (float)$product->price,
                'cost' => (float)$product->cost,
                'image' => $mainImage,
                'images' => data_get($product->meta, 'gallery', []),
                'localStock' => $totalStock,
                'approvalStatus' => 'draft',
                'isApproved' => false,
                'createdAt' => $product->created_at ? $product->created_at->format('d/m/Y H:i') : 'Reciente',
            ];
        })->values();

        $archivedProductsQuery = Product::query()
            ->with([
                'variants.inventories',
                'supplierProducts.supplier',
            ])
            ->where(function ($q) {
                $q->where('is_archived', true)
                  ->orWhere(function ($sq) {
                      $sq->where('visible', false)
                         ->where(function ($ssq) {
                             $ssq->whereNull('meta->approval_status')
                                ->orWhere('meta->approval_status', '!=', 'draft');
                         });
                  });
            })
            ->orderByDesc('id');

        $portalArchivedProducts = $archivedProductsQuery->get()->map(function (Product $product) {
            $mainImage = data_get($product->meta, 'image')
                ?: (data_get($product->meta, 'images.0') ?: 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80');
            return [
                'id' => $product->id,
                'dbId' => $product->id,
                'sku' => $product->sku,
                'name' => $product->title,
                'brand' => $product->brand ?: 'Torzo',
                'category' => data_get($product->meta, 'category', 'Ropa Interior'),
                'price' => (float)$product->price,
                'cost' => (float)$product->cost,
                'image' => $mainImage,
                'isArchived' => (bool)$product->is_archived,
                'isVisible' => (bool)$product->visible,
                'discontinuedReason' => $product->discontinued_reason ?: ($product->is_archived ? 'Archivado por operador' : 'Retirado de vitrina'),
                'archivedAt' => $product->archived_at ? $product->archived_at->format('d/m/Y H:i') : ($product->updated_at ? $product->updated_at->format('d/m/Y H:i') : 'Reciente'),
            ];
        })->values();

        $dbSuppliers = Supplier::query()->withCount('supplierProducts')->with(['users', 'supplierProducts.product'])->orderBy('name')->get();
        $brandToSupplierMap = [];
        foreach ($dbSuppliers as $s) {
            $brandToSupplierMap[mb_strtolower(trim($s->name))] = $s;
            if (is_array($s->brands)) {
                foreach ($s->brands as $b) {
                    $brandToSupplierMap[mb_strtolower(trim($b))] = $s;
                }
            }
        }

        $portalProviders = $dbSuppliers->map(function ($s) {
            $hasExtranet = $s->users->where('role', 'supplier')->isNotEmpty();
            return [
                'id' => 'prov_' . $s->id,
                'dbId' => $s->id,
                'vendorCode' => $s->vendor_code ?: ('VND-' . strtoupper(Str::slug($s->name)) . '-001'),
                'name' => $s->name,
                'tradeName' => $s->name,
                'legalName' => $s->legal_name ?: $s->name,
                'taxId' => $s->tax_id ?: '',
                'fiscalRegime' => $s->fiscal_regime ?: '',
                'fiscalAddress' => $s->fiscal_address ?: '',
                'fiscalZip' => $s->fiscal_zip ?: '',
                'invoiceEmail' => $s->invoice_email ?: '',
                'website' => $s->website ?: '',
                'origin' => $s->origin ?: ($s->type === 'dropship_scrape' ? 'scraping' : 'manual'),
                'isManual' => ($s->origin === 'manual' || $s->type !== 'dropship_scrape'),
                'brands' => is_array($s->brands) ? $s->brands : [],
                'brandsTree' => $s->getBrandsTree(),
                'type' => $s->type,
                'typeLabel' => match ($s->type) {
                    'dropship_scrape' => 'Fabricante Mayorista & Scraping B2B',
                    'dropship_api' => 'Proveedor API B2B Integrado',
                    'local' => 'Taller Local & Stock Propio',
                    default => 'Distribuidor Mayorista',
                },
                'city' => $s->city ?: 'CDMX',
                'contactName' => $s->contact_name ?: 'Atencion a Clientes',
                'phone' => $s->phone ?: '+52 55 0000 0000',
                'email' => $s->contact_email ?: ('b2b@' . Str::slug($s->name) . '.com'),
                'extractionMethod' => $s->extraction_method ?: 'Headless Scraper (Puppeteer)',
                'scrape_frequency' => $s->scrape_frequency ?: 'every_3_days',
                'frequency' => $s->scrape_frequency ?: 'every_3_days',
                'lastScrapeTime' => 'Hace 18 min',
                'syncedItemsCount' => (int) $s->supplier_products_count,
                'status' => $s->status ?: 'connected',
                'leadTimeDays' => (int) ($s->lead_time_days ?: 3),
                'paymentTerms' => $s->payment_terms ?: 'contado',
                'slaOnTimePct' => (float) ($s->sla_on_time_pct ?: 97.5),
                'slaFillRatePct' => (float) ($s->sla_fill_rate_pct ?: 98.0),
                'totalOrders' => 12,
                'completedOrders' => 11,
                'avgDelayDays' => (float) ($s->avg_delay_days ?: 0.3),
                'isSyncing' => false,
                'notes' => $s->notes ?: 'Proveedor oficial sincronizado.',
                'hasExtranetAccess' => $hasExtranet,
            ];
        })->values();

        $dbProducts = $productsQuery->get();

        $allVariantIds = $dbProducts->pluck('variants')->flatten()->pluck('id')->filter()->unique()->all();
        $allActiveLocks = !empty($allVariantIds)
            ? \App\Models\StockLock::whereIn('product_variant_id', $allVariantIds)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->get()
                ->groupBy('product_variant_id')
            : collect();

        $portalProducts = $dbProducts->map(function (Product $product) use ($brandToSupplierMap, $allActiveLocks) {
            $variants = $product->variants;
            $hasVariants = $variants->isNotEmpty();
            $isScrapedProduct = $product->isScraped() || $product->type === 'dropship';

            if ($hasVariants) {
                $totalStock = $isScrapedProduct ? 0 : (int) $variants->sum(fn ($v) => $v->inventories->where('location', 'warehouse-1')->sum('quantity'));
                $totalReserved = $isScrapedProduct ? 0 : (int) $variants->sum(fn ($v) => $v->inventories->where('location', 'warehouse-1')->sum('reserved'));

                $sizes = [];
                $physicalSizes = [];
                $committedSizes = [];
                $lockedSizes = [];
                $totalLocked = 0;

                foreach ($variants as $v) {
                    $rawSize = data_get($v->attributes, 'size', 'u');
                    $sizeKey = strtolower(trim((string) $rawSize));
                    $vQty = $isScrapedProduct ? 0 : (int) $v->inventories->where('location', 'warehouse-1')->sum('quantity');
                    $vRes = $isScrapedProduct ? 0 : (int) $v->inventories->where('location', 'warehouse-1')->sum('reserved');
                    $vLocks = $isScrapedProduct ? 0 : (int) ($allActiveLocks->get($v->id)?->sum('quantity') ?? 0);
                    $totalLocked += $vLocks;

                    $physicalSizes[$sizeKey] = $vQty;
                    $committedSizes[$sizeKey] = $vRes;
                    $lockedSizes[$sizeKey] = $vLocks;
                    $sizes[$sizeKey] = max(0, $vQty - $vRes - $vLocks);
                }

                $availableStock = $isScrapedProduct ? 0 : max(0, $totalStock - $totalReserved - $totalLocked);
            } else {
                $rawMetaSizes = data_get($product->meta, 'sizes', []);
                $sizes = [];
                if (is_array($rawMetaSizes)) {
                    foreach ($rawMetaSizes as $k => $v) {
                        $sizes[strtolower(trim((string) $k))] = $isScrapedProduct ? 0 : (int) $v;
                    }
                }
                $physicalSizes = $sizes;
                $committedSizes = [];
                $lockedSizes = [];
                $totalReserved = 0;
                $totalLocked = 0;
                $totalStock = $isScrapedProduct ? 0 : (!empty($sizes) ? array_sum($sizes) : (int) data_get($product->meta, 'local_stock', 0));
                $availableStock = $totalStock;
            }

            $sp = $product->supplierProducts->first();
            $supplierStock = $sp && $sp->availability === 'in_stock' ? 50 : (int) data_get($product->meta, 'supplier_stock', ($isScrapedProduct ? 50 : 0));

            $rawImg = data_get($product->meta, 'image')
                ?: (data_get($product->meta, 'images.0') ?: (data_get($product->meta, 'gallery.0.url') ?: 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80'));
            if (is_array($rawImg)) {
                $rawImg = data_get($rawImg, 'url') ?: (data_get($rawImg, 'src') ?: '');
            }
            $mainImage = is_string($rawImg) ? str_replace('/./', '/', $rawImg) : '';

            $rawGallery = data_get($product->meta, 'gallery') ?: data_get($product->meta, 'images', []);
            $images = [];
            $imageColorMap = data_get($product->meta, 'images_by_color') ?: data_get($product->meta, 'image_color_map', []);
            if (is_array($rawGallery)) {
                foreach ($rawGallery as $gItem) {
                    if (is_string($gItem) && !empty($gItem)) {
                        $images[] = $gItem;
                    } elseif (is_array($gItem) && !empty($gItem['url'])) {
                        $images[] = $gItem['url'];
                        if (!empty($gItem['color_name']) && empty($imageColorMap[$gItem['url']])) {
                            $imageColorMap[$gItem['url']] = $gItem['color_name'];
                        }
                    }
                }
            }
            if (empty($images) && !empty($mainImage)) {
                $images = [$mainImage];
            }

            $cost = (float) ($product->cost ?: ($product->price * 0.5));
            $pvp = (float) $product->price;
            $regularPrice = (float) (data_get($product->meta, 'regular_price') ?: $pvp);
            $discountPct = (float) data_get($product->meta, 'discount_pct', 0);

            $rawBrand = $product->brand ?: data_get($product->meta, 'brand', '');
            $matchedSupplier = null;
            if ($sp?->supplier) {
                $matchedSupplier = $sp->supplier;
            } elseif ($rawBrand && isset($brandToSupplierMap[mb_strtolower(trim($rawBrand))])) {
                $matchedSupplier = $brandToSupplierMap[mb_strtolower(trim($rawBrand))];
            } elseif (data_get($product->meta, 'provider') && isset($brandToSupplierMap[mb_strtolower(trim((string) data_get($product->meta, 'provider')))])) {
                $matchedSupplier = $brandToSupplierMap[mb_strtolower(trim((string) data_get($product->meta, 'provider')))];
            }

            $supplierName = $matchedSupplier?->name ?: (data_get($product->meta, 'provider') ?: ($rawBrand ?: 'Torzo'));
            $supplierId = $matchedSupplier ? $matchedSupplier->id : (data_get($product->meta, 'supplier_id') ?: ($sp?->supplier_id ?: 4));
            $supplierSku = data_get($product->meta, 'supplier_sku') ?: ($sp?->supplier_sku ?: '');
            $supplierUrl = data_get($product->meta, 'provider_url')
                ?: (data_get($product->meta, 'supplier_url')
                ?: (data_get($product->meta, 'origin_url')
                ?: (data_get($product->meta, 'source_url')
                ?: (data_get($product->meta, 'supplierUrl')
                ?: ($sp?->source_url ?: '')))));

            $colorName = data_get($product->meta, 'color_name') ?: data_get($product->meta, 'colorName', '');
            $colorHex = data_get($product->meta, 'color_hex') ?: data_get($product->meta, 'colorHex', '');
            $colorType = data_get($product->meta, 'color_type') ?: data_get($product->meta, 'colorType', 'solid');
            $fabricType = $product->fabric ?: (data_get($product->meta, 'fabric') ?: data_get($product->meta, 'fabric_type', data_get($product->meta, 'fabricType', '')));

            // Sincronización fidedigna con curaduría de scraping aprobada si existe desfase
            if ($product->sku) {
                $scrapedItem = \App\Models\ScrapedPendingItem::where('external_sku', $product->sku)->where('curation_status', 'approved')->first();
                if ($scrapedItem) {
                    if (($colorName === '' || $colorName === 'Negro Profundo') && $scrapedItem->curated_color && $scrapedItem->curated_color !== 'Negro Profundo') {
                        $colorName = $scrapedItem->curated_color;
                        if ($scrapedItem->detected_color_hex) {
                            $colorHex = $scrapedItem->detected_color_hex;
                        }
                    }
                    if (($fabricType === '' || $fabricType === 'Modal Seamless') && $scrapedItem->curated_fabric && $scrapedItem->curated_fabric !== 'Modal Seamless') {
                        $fabricType = $scrapedItem->curated_fabric;
                    }
                }
            }

            // Resolver Hex Cromático a través del catálogo maestro ApparelColor si está ausente o es genérico oscuro
            if (strtolower(trim((string)$colorName)) === 'multicolor') {
                $colorHex = 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)';
                $colorType = 'iridescent';
            } elseif (empty($colorHex) || $colorHex === '#0f172a' || $colorHex === '#111827') {
                if (!empty($colorName)) {
                    $matchedCol = \App\Models\ApparelColor::where('name', $colorName)
                        ->orWhere('name', 'like', '%' . explode(' ', $colorName)[0] . '%')
                        ->first();
                    if ($matchedCol) {
                        $colorHex = $matchedCol->hex;
                        $colorType = $matchedCol->type;
                    }
                }
                $colorHex = $colorHex ?: '#0f172a';
            }

            $designType = data_get($product->meta, 'design_type') ?: data_get($product->meta, 'designType', 'sin_diseno');
            $websiteSection = $product->section_name ?: (data_get($product->meta, 'section_name') ?: (data_get($product->meta, 'website_section') ?: data_get($product->meta, 'websiteSection', 'basicos')));
            $location = data_get($product->meta, 'location', 'Rack A-01 / Nivel 1');
            $brand = $rawBrand ?: ($product->brand ?: data_get($product->meta, 'brand', $supplierName));
            $category = data_get($product->meta, 'category', 'Ropa Interior');
            $subcategory = data_get($product->meta, 'subcategory', '');

            $variantDimension = data_get($product->meta, 'variant_dimension', 'size');
            $selectedSizeCurveId = data_get($product->meta, 'selected_size_curve_id', '');
            $shades = data_get($product->meta, 'shades', []);
            $volumes = data_get($product->meta, 'volumes', []);

            if ($isScrapedProduct) {
                $status = 'Sincronizado';
            } elseif ($availableStock > 0) {
                $status = 'Saludable';
            } elseif ($supplierStock > 0) {
                $status = 'Solo proveedor';
            } else {
                $status = 'Agotado';
            }

            return [
                'id' => $product->id,
                'dbId' => $product->id,
                'sku' => $product->sku,
                'weightedAverageCost' => (float) data_get($product->meta, 'weighted_average_cost', $cost),
                'name' => $product->title,
                'brand' => $brand,
                'provider' => $supplierName,
                'supplier_id' => $supplierId,
                'supplierId' => $supplierId,
                'supplierSku' => $supplierSku,
                'supplierUrl' => $supplierUrl,
                'providerUrl' => $supplierUrl,
                'originUrl' => $supplierUrl,
                'category' => $category,
                'subcategory' => $subcategory,
                'type' => ($isScrapedProduct || $product->type === 'dropship') ? 'dropship' : ($product->type === 'physical' ? 'propio' : 'mixto'),
                'isScraped' => (bool) $isScrapedProduct,
                'is_scraped' => (bool) $isScrapedProduct,
                'supplierType' => ($isScrapedProduct || $product->type === 'dropship') ? 'dropship' : 'local',
                'localStock' => $isScrapedProduct ? 0 : $availableStock,
                'physicalStock' => $isScrapedProduct ? 0 : $totalStock,
                'physicalSizes' => $physicalSizes,
                'committedStock' => $isScrapedProduct ? 0 : $totalReserved,
                'committedSizes' => $committedSizes,
                'lockedStock' => $isScrapedProduct ? 0 : $totalLocked,
                'lockedSizes' => $lockedSizes,
                'supplierStock' => $supplierStock,
                'location' => $location,
                'status' => $status,
                'pvp' => $pvp,
                'cost' => $cost,
                'regularPrice' => $regularPrice,
                'discountPct' => $discountPct,
                'sizes' => $sizes,
                'shades' => $shades,
                'volumes' => $volumes,
                'variantDimension' => $variantDimension,
                'selectedSizeCurveId' => $selectedSizeCurveId,
                'image' => $mainImage,
                'images' => $images,
                'gallery' => $rawGallery,
                'imageColorMap' => $imageColorMap,
                'colorName' => $colorName,
                'colorHex' => $colorHex,
                'colorType' => $colorType,
                'fabricType' => $fabricType,
                'designType' => $designType,
                'websiteSection' => $websiteSection,
                'description' => $product->description ?: data_get($product->meta, 'description', ''),
                'isPublished' => (bool) $product->visible,
                'adjustType' => 'add',
                'adjustQty' => '',
                'adjustReason' => '',
            ];
        })->values();

        $syncRuns = SupplierSyncRun::query()
            ->with('supplier')
            ->when($request->filled('supplier_id'), fn ($query) => $query->where('supplier_id', $request->integer('supplier_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest('started_at')
            ->limit(20)
            ->get();

        $totalLocalStock = (int) $portalProducts->sum('localStock');
        $totalDropshipRefs = (int) $portalProducts->where('supplierStock', '>', 0)->count();
        $totalSkus = (int) $portalProducts->count();
        $lastScrape = SupplierProduct::max('last_checked_at')
            ? now()->parse(SupplierProduct::max('last_checked_at'))->format('d/m/Y H:i')
            : 'Hace 14 min';


        $dbPurchaseOrders = PurchaseOrder::query()->with(['supplier', 'items.product', 'items.variant'])->latest()->get();
        $portalPurchaseOrders = $dbPurchaseOrders->map(function ($po) {
            $firstItem = $po->items->first();
            $sizes = ['ch' => 0, 'm' => 0, 'g' => 0, 'xl' => 0, '2xl' => 0, '3xl' => 0];
            foreach ($po->items as $it) {
                $szKey = strtolower(trim((string) $it->size_name));
                if (array_key_exists($szKey, $sizes)) {
                    $sizes[$szKey] += $it->quantity_ordered;
                } else {
                    $sizes[$szKey] = $it->quantity_ordered;
                }
            }
            $statusLabel = match ($po->status) {
                'received' => 'Recibida en Almacén',
                'in_transit' => 'En Camino',
                'partially_received' => 'Parcialmente Recibida',
                'confirmed' => 'Por Despachar',
                'cancelled' => 'Cancelada',
                default => 'Borrador',
            };

            return [
                'id' => $po->po_number,
                'dbId' => $po->id,
                'poNumber' => $po->po_number,
                'supplier' => $po->supplier?->name ?? 'Proveedor',
                'supplier_id' => $po->supplier_id,
                'carrier' => $po->carrier ?: ($po->notes ?: 'Paquetería Express (Guía Pendiente)'),
                'trackingNumber' => $po->tracking_number,
                'priority' => $po->priority ?: 'media',
                'eta' => $po->estimated_delivery_at ? $po->estimated_delivery_at->format('d M, Y') : 'En preparación',
                'sku' => $firstItem?->product?->sku ?? 'LOTE-COMPRA',
                'productName' => $firstItem?->product?->title ?? 'Lote de Reabastecimiento',
                'qty' => (int) $po->total_units,
                'sizes' => $sizes,
                'unitCost' => (float) ($firstItem?->unit_cost ?? 0),
                'subtotal' => (float) $po->subtotal,
                'tax' => (float) $po->tax,
                'creditDeductionAmount' => (float) ($po->credit_deduction_amount ?? 0),
                'creditAgreementId' => $po->credit_agreement_id,
                'totalCost' => (float) $po->total_amount,
                'date' => $po->created_at ? $po->created_at->format('d M, Y') : now()->format('d M, Y'),
                'status' => $statusLabel,
                'linkedCartId' => null,
                'linkedCustomer' => null,
                'poSnapshot' => $po->po_snapshot,
                'items' => $po->items->map(fn ($it) => [
                    'id' => $it->id,
                    'product_id' => $it->product_id,
                    'variant_id' => $it->product_variant_id,
                    'size' => $it->size_name,
                    'quantity_ordered' => $it->quantity_ordered,
                    'quantity_received' => $it->quantity_received,
                    'unit_cost' => (float) $it->unit_cost,
                    'total_cost' => (float) $it->total_cost,
                ])->values(),
            ];
        })->values();

        $portalFuturePurchaseOrders = $portalProducts->map(function ($p, $idx) {
            $needed = max(24, 60 - (int) $p['localStock']);
            $cost = (float) $p['cost'];
            $ch = (int) round($needed * 0.15);
            $m = (int) round($needed * 0.35);
            $g = (int) round($needed * 0.30);
            $xl = (int) round($needed * 0.15);
            $xxl = max(0, $needed - ($ch + $m + $g + $xl));

            return [
                'id' => 'FP-2026-0' . ($idx + 1),
                'productId' => $p['id'],
                'sku' => $p['sku'],
                'productName' => $p['name'],
                'supplier' => $p['provider'] ?: 'Torzo',
                'supplier_id' => $p['supplier_id'] ?: 4,
                'brand' => $p['brand'] ?: ($p['provider'] ?: 'Torzo'),
                'qty' => $needed,
                'sizes' => ['ch' => $ch, 'm' => $m, 'g' => $g, 'xl' => $xl, '2xl' => $xxl, '3xl' => 0],
                'costUnit' => $cost,
                'totalCost' => round($needed * $cost, 2),
                'priority' => $p['localStock'] <= 0 ? 'alta' : 'media',
                'status' => 'Planificada',
                'date' => now()->subDays(2 - ($idx % 5))->format('d M, Y'),
                '_editingSizes' => false,
            ];
        })->values();

        $portalPurchaseKardex = PurchaseKardex::query()
            ->with(['supplier', 'purchaseOrder'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($k) => [
                'id' => 'KDX-PUR-' . str_pad((string) $k->id, 4, '0', STR_PAD_LEFT),
                'timestamp' => $k->created_at ? \Carbon\Carbon::parse($k->created_at)->format('d M, Y H:i') : now()->format('d M, Y H:i'),
                'type' => $k->movement_type,
                'typeLabel' => match ($k->movement_type) {
                    'PO_CREATED' => 'Emisión de PO',
                    'INBOUND_RECEIPT' => 'Recepción en Rampa',
                    default => 'Movimiento Compras',
                },
                'badgeClass' => $k->movement_type === 'INBOUND_RECEIPT' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'poNumber' => $k->purchaseOrder?->po_number ?? 'PO-GRAL',
                'supplier' => $k->supplier?->name ?? 'Mayorista',
                'sku' => $k->purchaseOrder?->items?->first()?->product?->sku ?? 'MULTIPLE',
                'productName' => $k->purchaseOrder?->items?->first()?->product?->title ?? 'Lote de Compra',
                'deltaQty' => '+' . ($k->purchaseOrder?->total_units ?? 0) . ' uds',
                'totalAmount' => (float) $k->total_amount,
                'user' => 'Elena Compras',
                'notes' => $k->description,
            ])->values();

        $portalCreditAgreements = \App\Models\SupplierCreditAgreement::query()
            ->with(['supplier', 'purchaseOrder', 'appliedToPurchaseOrder'])
            ->latest()
            ->get()
            ->map(fn ($ca) => [
                'id' => $ca->id,
                'supplier_id' => $ca->supplier_id,
                'supplier' => $ca->supplier?->name ?? 'Proveedor',
                'ref' => $ca->reference_folio ?: ('NC-' . $ca->id),
                'amount' => (float) $ca->amount,
                'reason' => $ca->resolution_notes ?: 'Acuerdo compensatorio por discrepancia',
                'agreement_type' => $ca->agreement_type,
                'discrepancy_type' => $ca->discrepancy_type,
                'missing_units' => $ca->missing_units_count,
                'damaged_units' => $ca->damaged_units_count,
                'unit_cost' => (float) $ca->unit_cost_applied,
                'authorized_by' => $ca->authorized_by_vendor_contact,
                'minutes' => $ca->discrepancy_minutes,
                'status' => match ($ca->status) {
                    'pending' => 'Pendiente de Aplicar',
                    'applied' => 'Aplicada',
                    'void', 'cancelled' => 'Anulada',
                    default => ucfirst($ca->status),
                },
                'raw_status' => $ca->status,
                'applied_to_po_id' => $ca->applied_to_po_id,
                'date' => $ca->created_at ? $ca->created_at->format('d/m/Y') : now()->format('d/m/Y'),
            ])->values();

        $portalProductBundles = ProductBundle::query()
            ->with(['items.product', 'items.variant.inventories'])
            ->get()
            ->map(function ($bundle) {
                $components = $bundle->items->map(function ($item) {
                    $prod = $item->product;
                    $var = $item->variant;
                    $sku = $var?->sku ?? ($prod?->sku ?? 'SKU-COMP');
                    $name = $prod?->title ?? 'Prenda Componente';
                    $size = $var ? (data_get($var->attributes, 'size') ?? 'm') : 'm';
                    return [
                        'sku' => $sku,
                        'name' => $name,
                        'size' => strtolower((string) $size),
                        'qty' => (int) $item->quantity,
                        'location' => 'A-01-B',
                    ];
                })->values()->toArray();

                $subPrice = (float) $bundle->bundle_price;
                $regPrice = (float) $bundle->regular_price;
                $disc = ($regPrice > $subPrice && $regPrice > 0)
                    ? (int) round((($regPrice - $subPrice) / $regPrice) * 100)
                    : 0;

                return [
                    'id' => (string) ($bundle->slug ?: $bundle->id),
                    'dbId' => $bundle->id,
                    'sku' => (string) ($bundle->slug ?: ('BUNDLE-' . $bundle->id)),
                    'name' => $bundle->name,
                    'type' => $bundle->bundle_type === 'box_subscription' ? 'subscription' : ($bundle->bundle_type === 'pack_multiple' ? 'promo' : $bundle->bundle_type),
                    'tierLevel' => 'custom_pack',
                    'tierName' => $bundle->name,
                    'subscriptionPrice' => $subPrice,
                    'regularPrice' => $regPrice,
                    'discountPct' => $disc,
                    'isActive' => (bool) $bundle->is_active,
                    'badgeColor' => $bundle->bundle_type === 'box_subscription' ? 'indigo' : 'emerald',
                    'description' => 'Receta BOM sincronizada con almacén.',
                    'components' => $components,
                ];
            })->values();

        $portalCycleCounts = CycleCount::query()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($cc) {
                return [
                    'id' => $cc->id,
                    'date' => $cc->count_date ? \Carbon\Carbon::parse($cc->count_date)->format('d/m/Y') : now()->format('d/m/Y'),
                    'skuCount' => (int) $cc->sample_sku_count,
                    'totalCounted' => (int) $cc->total_units_counted,
                    'totalVariance' => (int) $cc->total_variance_units,
                    'iraScore' => (float) $cc->ira_accuracy_score,
                    'auditedBy' => $cc->audited_by,
                    'status' => $cc->status,
                    'details' => $cc->details_json,
                ];
            })->values();

        $portalOnDemandRequests = OrderItem::query()
            ->with(['order.user', 'variant.product.supplierProducts.supplier', 'supplier'])
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['pending', 'processing', 'confirmed', 'paid']);
            })
            ->whereHas('variant.product', function ($q) {
                $q->whereIn('type', ['dropship', 'both']);
            })
            ->where('fulfillment_status', '!=', 'fulfilled')
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($item) {
                $product = $item->variant?->product;
                $order = $item->order;
                $supplier = $item->supplier ?: ($product?->supplierProducts?->first()?->supplier);
                $customerName = $order?->shipping_address['name'] ?? ($order?->user?->name ?? 'Cliente');
                $customerPhone = $order?->shipping_address['phone'] ?? ($order?->user?->phone ?? 'S/T');
                $cartType = ($order?->order_type === 'mixed') ? 'mixto_linea_proveedor' : 'solicitud_pedido';
                $cartLabel = ($order?->order_type === 'mixed') 
                    ? "Carrito Mixto #{$order?->order_number} (Linea On-Demand)"
                    : "Pedido #{$order?->order_number} (Bajo Demanda)";

                $costUnit = (float) ($item->variant?->cost ?? ($product?->cost ?? 0));
                $qty = (int) $item->quantity;

                return [
                    'id' => 'REQ-' . ($order?->order_number ?? $item->id),
                    'order_id' => $order?->id,
                    'customerName' => $customerName,
                    'customerPhone' => $customerPhone,
                    'cartType' => $cartType,
                    'cartLabel' => $cartLabel,
                    'sku' => $product?->sku ?? 'SKU-N/A',
                    'productName' => $product?->title ?? 'Producto Bajo Demanda',
                    'size' => $item->variant?->attributes['size'] ?? ($item->variant?->attributes['volume'] ?? 'M'),
                    'qty' => $qty,
                    'supplier' => $supplier?->name ?? 'Torzo B2B',
                    'costUnit' => $costUnit,
                    'totalCost' => round($qty * $costUnit, 2),
                    'slaMinutes' => 60,
                    'verificationStatus' => 'pendiente',
                    'paymentSent' => false,
                    'date' => $item->created_at ? $item->created_at->diffForHumans() : 'Reciente',
                ];
            })->values();

        $portalBrands = \App\Models\Brand::with('supplier:id,name,legal_name,vendor_code,origin,status')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'supplier_id' => $brand->supplier_id,
                    'supplier_name' => $brand->supplier ? ($brand->supplier->trade_name ?? $brand->supplier->name) : 'Marca Directa / Independiente',
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'description' => $brand->description,
                    'logo_url' => $brand->logo_url,
                    'website' => $brand->website,
                    'is_active' => (bool)$brand->is_active,
                    'is_archived' => (bool)$brand->is_archived,
                    'sort_order' => $brand->sort_order,
                    'sku_count' => \App\Models\Product::where('brand', $brand->name)->count(),
                ];
            });

        $portalScrapedPendingItems = \App\Models\ScrapedPendingItem::query()
            ->with(['supplier', 'supplierProduct'])
            ->where('curation_status', 'pending')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($item) {
                $images = is_array($item->images) && count($item->images) ? $item->images : array_values(array_filter([$item->external_image_url]));
                $mainImg = $item->external_image_url ?: ($images[0] ?? 'https://torzo.kexe.mx/imagenes_productos/default.jpg');
                $sizesObj = is_array($item->sizes) ? $item->sizes : ['ch' => 2, 'm' => 4, 'g' => 3, 'xl' => 1];
                $availableSizes = [];
                foreach ($sizesObj as $sz => $qty) {
                    if ($qty > 0) $availableSizes[] = strtoupper($sz);
                }
                if (empty($availableSizes)) $availableSizes = ['CH', 'M', 'G', 'XL'];

                $colorName = $item->detected_color_name ?: ($item->curated_color ?: 'Negro Profundo');
                $imageColorMap = [];
                foreach ($images as $im) {
                    $imageColorMap[$im] = $colorName;
                }

                return [
                    'id' => (string) $item->id,
                    'dbId' => $item->id,
                    'supplierSku' => $item->external_sku,
                    'suggestedSku' => $item->external_sku,
                    'name' => $item->external_title ?: $item->curated_title,
                    'brand' => $item->curated_brand ?: ($item->supplier?->name ?: 'Torzo'),
                    'supplier_id' => $item->supplier_id ?: 4,
                    'suggestedCategory' => $item->suggested_category ?: 'Ropa Interior',
                    'suggestedSubcategory' => $item->suggested_subcategory ?: 'Básicos',
                    'suggestedFabric' => $item->suggested_fabric ?: 'Microfibra Spandex / Licra',
                    'detectedColorName' => $colorName,
                    'detectedColorHex' => $item->detected_color_hex ?: '#0f172a',
                    'colorType' => $item->color_type ?: 'solid',
                    'suggestedDesign' => $item->curated_design_type ?: 'sin_diseno',
                    'suggestedSection' => $item->curated_website_section ?: 'basicos',
                    'scrapedDescription' => $item->scraped_description ?: '',
                    'originUrl' => $item->origin_url ?: 'https://torzo.kexe.mx',
                    'cost' => (float) ($item->scraped_cost ?: ($item->external_price * 0.5)),
                    'scrapedCost' => (float) ($item->scraped_cost ?: ($item->external_price * 0.5)),
                    'suggestedPvp' => (float) ($item->curated_price ?: $item->external_price),
                    'supplierStock' => (int) $item->supplier_stock,
                    'sizes' => $sizesObj,
                    'availableSizes' => $availableSizes,
                    'image' => $mainImg,
                    'images' => $images,
                    'imageColorMap' => $imageColorMap,
                ];
            })->values();

        return view('portal.inventory', [
            'summary' => [
                'stock_local' => number_format($totalLocalStock) . ' piezas',
                'bajo_pedido' => number_format($totalDropshipRefs) . ' referencias',
                'skus_activos' => number_format($totalSkus) . ' modelos',
                'last_scrape' => (string) $lastScrape,
            ],
            'portalProducts' => $portalProducts,
            'portalDraftProducts' => $portalDraftProducts,
            'portalArchivedProducts' => $portalArchivedProducts,
            'portalScrapedPendingItems' => $portalScrapedPendingItems,
            'portalProviders' => $portalProviders,
            'portalBrands' => $portalBrands,
            'portalPurchaseOrders' => $portalPurchaseOrders,
            'portalFuturePurchaseOrders' => $portalFuturePurchaseOrders,
            'portalPurchaseKardex' => $portalPurchaseKardex,
            'portalCreditAgreements' => $portalCreditAgreements,
            'portalProductBundles' => $portalProductBundles,
            'portalCycleCounts' => $portalCycleCounts,
            'portalOnDemandRequests' => $portalOnDemandRequests,
            'portalSizeCurves' => $portalSizeCurves,
            'portalFragranceVolumes' => $portalFragranceVolumes,
            'portalCategories' => $portalCategories,
            'portalFabrics' => \App\Models\ApparelFabric::orderBy('name')->get(),
            'portalApparelColors' => (function() {
                $colors = \App\Models\ApparelColor::orderBy('name')->get();
                if (!$colors->contains(fn($c) => strtolower($c->name) === 'multicolor')) {
                    \App\Models\ApparelColor::firstOrCreate(
                        ['name' => 'Multicolor'],
                        [
                            'color_type' => 'gradient',
                            'hex_code' => '#ef4444',
                            'gradient_css' => 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)',
                            'is_active' => true,
                        ]
                    );
                    $colors = \App\Models\ApparelColor::orderBy('name')->get();
                }
                return $colors;
            })(),
            'portalStoreSections' => \App\Models\CmsSection::orderBy('sort_order')->orderBy('name')->get(),
            'portalMoqSheets' => \App\Models\SupplierMoqSheet::with('supplier')->orderByDesc('is_default')->orderBy('supplier_name')->get(),
            'syncRuns' => $syncRuns,
            'syncSuppliers' => \App\Models\Supplier::query()->orderBy('name')->get(['id', 'name']),
            'variantMappings' => \App\Models\SupplierVariantMapping::query()
                ->with(['supplierProduct.supplier', 'productVariant.product'])
                ->latest()
                ->limit(50)
                ->get(),
        ]);
    }
}

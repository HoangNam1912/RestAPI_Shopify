<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Shopify\Rest\Admin2024_01\Collect;
use Shopify\Utils;


class ShopifyController extends Controller
{
    public function getAllProducts(Request $request)
{
    // Lấy thông tin người dùng đang đăng nhập (shop)
    $shop = Auth::user();
    $domain = $shop->getDomain()->toNative();
    
    // Thêm tham số 
    $pageInfo = $request->input('page_info', null); // Lấy giá trị page_info từ request 
    $limit = $request->input('limit', null); // Số lượng sản phẩm mỗi trang 
    $tag = $request->input('tag', null); // Lấy giá trị tag từ request nếu có
    $searchName = $request->input('search_name', null); 
    $vendor = $request->input('vendor', null);

    // Xây dựng các tham số cho yêu cầu API
    $queryParams = [
        'limit' => 8,
    ];
    
    $allProducts = [];
    $allTags = [];
    $allVendor = [];
    do {
        if ($pageInfo) {
            $queryParams['page_info'] = $pageInfo;
        }    
       
        // Gọi API của Shopify để lấy danh sách sản phẩm
        $productsResponse = $shop->api()->rest('GET', '/admin/products.json', $queryParams);
        // Kiểm tra phản hồi của API và lấy danh sách sản phẩm
        $products = $productsResponse['body']['products']->toArray();
       $pagination = $productsResponse['healers']['link'] ?? null;
        $pageInfo = $pagination['next'] ?? null;
        $nextPageInfo = $pagination['next'] ?? null;
     
        // Thêm sản phẩm vào danh sách tất cả sản phẩm
        $allProducts = array_merge($allProducts, $products);
      //dd($productsResponse, $queryParams, $nextPageInfo, $pagination);
        // Trích xuất danh sách các tag
        foreach ($products as $product) {
            $productTags = array_map('trim', explode(',', strtolower($product['tags'])));
            $allTags = array_merge($allTags, $productTags);
        }

        foreach ($products as $product) {
            $productVendor = array_map('trim', explode(',', strtolower($product['vendor'])));
            $allVendor = array_merge($allVendor, $productVendor);
        }
    } while ($pageInfo);
    
    // Loại bỏ các tag trùng lặp
    $allTags = array_unique($allTags);
    $allVendor = array_unique($allVendor);

    
    // Lọc sản phẩm theo tag nếu có
    if ($tag) {
        $allProducts = $this->filterProductsByTag($allProducts, $tag);
    }
     // Tìm kiếm sản phẩm theo tên nếu có
     if ($searchName) {
        $allProducts = $this->searchProductsByName($allProducts, $searchName);
    }

    if ($vendor) {
        $allProducts = $this->filterProductByVendor($allProducts, $vendor);
    }
    Log::info($request->all());
    return view('shopify.products', [
        'products' => $allProducts,
        'nextPageInfo' => $nextPageInfo,
        'allTags' => $allTags,
        'allVendor' => $allVendor,
    ]);
}

public function showProduct($id)
{
    // Lấy thông tin người dùng đang đăng nhập (shop)
    $shop = Auth::user();

    // Gọi API của Shopify để lấy thông tin chi tiết sản phẩm
    $productResponse = $shop->api()->rest('GET', "/admin/products/{$id}.json");

    // Kiểm tra phản hồi của API và lấy thông tin chi tiết sản phẩm
    $product = $productResponse['body']['product']->toArray();

    // Gọi API của Shopify để lấy danh sách các bộ sưu tập
    $collectionsResponse = $shop->api()->rest('GET', '/admin/custom_collections.json');

    // Kiểm tra phản hồi của API và lấy danh sách các bộ sưu tập
    $collections = $collectionsResponse['body']['custom_collections']->toArray();
    
    
    return view('shopify.product_detail', [
        'product' => $product,
        'collections' => $collections,
    ]);
}
public function updateProduct(Request $request, $id)
{
    dd($request->all());
    // Lấy thông tin người dùng đang đăng nhập (shop)
    $shop = Auth::user();

    // Tạo dữ liệu sản phẩm để cập nhật
    $data = [
        'product' => [
            'title' => $request->input('title'),
            'body_html' => $request->input('body_html'),
            'variants' => []
        ]
    ];

    // Cập nhật thông tin cho từng variant
    foreach ($request->input('variants') as $variantId => $variant) {
        $variantData = [
            'id' => $variantId,
            'price' => $variant['price'],
            'compare_at_price' => $variant['compare_at_price'],
            'inventory_quantity' => $variant['inventory_quantity']
        ];

        // Áp dụng giảm giá
        $discountType = $request->input('discount_type');
        $discountValue = $request->input('discount_value');
        
        if ($discountType && $discountValue) {
            if ($discountType == 'percentage') {
                $variantData['price'] -= $variantData['price'] * ($discountValue / 100);
            } else if ($discountType == 'fixed_amount') {
                $variantData['price'] -= $discountValue;
            }
        }

        $data['product']['variants'][] = $variantData;
    }

    // Gọi API của Shopify để cập nhật sản phẩm
    $shop->api()->rest('PUT', "/admin/products/{$id}.json", $data);
    
    return redirect()->route('product.show', ['id' => $id])->with('success', 'Product updated and discount applied successfully.');
}


private function filterProductsByTag(array $products, string $tag): array
{
    $tag = strtolower($tag); // Chuyển tag về chữ thường để so sánh
    return array_filter($products, function ($product) use ($tag) {
        $productTags = array_map('trim', explode(',', strtolower($product['tags'])));
        return in_array($tag, $productTags);
    });
}   
private function searchProductsByName(array $products, string $name): array
{
    $name = strtolower($name); // Chuyển tên về chữ thường để so sánh
    return array_filter($products, function ($product) use ($name) {
        return strpos(strtolower($product['title']), $name) !== false;
    });
}

private function filterProductByVendor(array $products, string $vendor): array {
    $vendor = strtolower($vendor); 
    return array_filter($products, function ($product) use ($vendor) {
        $productVendor = array_map('trim', explode(',', strtolower($product['vendor'])));
        return in_array($vendor, $productVendor);
    });
}
    
}

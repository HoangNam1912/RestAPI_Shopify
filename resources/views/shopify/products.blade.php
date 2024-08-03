<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopify Products</title>
    <!-- Include Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Shopify Products</title>
    
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <!-- PAGE CONTENT BEGINS -->
                <div class="row">
    <div class="col-xs-12">
         <!-- Form tìm kiếm sản phẩm theo tên -->   
        
        <div class="container mt-5">
        <div class="form-group">
            <label for="search_name">Search by Product Name:</label>
            <input type="text" id="search_name" class="form-control" value="{{ request('search_name') }}">
            <button id="search-button" class="btn btn-primary mt-2">Search</button>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="tag-filter">Search by Tag:</label>
                <select id="tag-filter" class="form-control" style="width: 140px; height: 35px;">
                    @foreach ($allTags as $tag)
                        <option value="{{ $tag }}" {{ request('tag') == $tag ? 'selected' : '' }}>{{ $tag }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                    <label for="vendor-filter">Search by Vendor:</label>    
                    <select id="vendor-filter" name="vendor" class="form-control" style="width: 140px; height: 35px;">
                        <option value=""></option>
                        @foreach ($allVendor as $vendor)
                            <option value="{{ $vendor }}" {{ request('vendor') == $vendor ? 'selected' : '' }}>{{ $vendor }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <table id="simple-table" class="table table-bordered table-hover">
    <thead>
        <tr>
            <th class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" />
                    <span class="lbl"></span>
                </label>
            </th>
            <th class="hidden-480">Image</th>
            <th class="hidden-480">Product</th>
            <th class="hidden-480">Status</th>
            <th class="hidden-480">Inventory</th>
            <th class="hidden-480">Sales channels</th>
            <th class="hidden-480">Markets</th>
            <th class="hidden-480">B2B catalogs</th>
            <th class="hidden-480">Category</th>
            <th class="hidden-480">Type</th>
            <th class="hidden-480">Vendor</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
            <tr>
                <td class="center">
                    <label class="pos-rel">
                        <input type="checkbox" class="ace" />
                        <span class="lbl"></span>
                    </label>
                </td>
                <td class="hidden-480">
                    @if(isset($product['image']['src']))
                        <img src="{{ $product['image']['src'] }}" alt="{{ $product['image']['alt'] ?? 'Product Image' }}" width="50">
                    @else
                        No Image
                    @endif  
                </td>
                <td>
                    <a>
                        {{ $product['title'] }}
                    </a>
                </td>
                <td class="hidden-480">{{ $product['status'] }}</td>
                <td>{{ $product['variants'][0]['inventory_quantity'] ?? 'N/A' }} in stock </td>
                <td class="hidden-480">{{ $product['published_scope'] }}</td>
                <td class="hidden-480"></td> 
                <td class="hidden-480"></td> 
                <td class="hidden-480">{{ $product['tags'] }}</td>
                <td class="hidden-480">{{ $product['product_type'] }}</td>
                <td class="hidden-480">{{ $product['vendor'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<!-- Thanh chuyển trang -->
@if ($nextPageInfo)
    <div class="d-flex justify-content-center">
        <a href="{{ route('shopify.products', ['page_info' => $nextPageInfo]) }}" class="btn btn-primary">Trang tiếp theo</a>
    </div>
@endif
</div>
    </div>
</div>

 <!-- JavaScript để xử lý sự kiện chọn tag và vendor -->
 <script>
     document.getElementById('search-button').addEventListener('click', function() {
            var searchName = document.getElementById('search_name').value;
            var url = new URL(window.location.href);
            if (searchName) {
                url.searchParams.set('search_name', searchName);
            } else {
                url.searchParams.delete('search_name');
            }
            window.location.href = url.toString();
        });

        document.getElementById('tag-filter').addEventListener('change', function() {
            var selectedTag = this.value;
            var url = new URL(window.location.href);
            url.searchParams.set('tag', selectedTag);
            window.location.href = url.toString();
        });

        document.getElementById('vendor-filter').addEventListener('change', function() {
        var selectedVendor = this.value;
        var url = new URL(window.location.href);
        if (selectedVendor) {
            url.searchParams.set('vendor', selectedVendor);
        } else {
            url.searchParams.delete('vendor');
        }
        window.location.href = url.toString();
    });

    </script>
    
                </div>
            </div>
        </div>
    </div>

    <!-- Include jQuery and Bootstrap JS for handling the table -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

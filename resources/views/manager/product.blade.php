@extends('manager.layout')

@section('title', 'Product Inventory')

@section('content')
<h1 class="inventory-title">Product Inventory</h1>

<div class="top-bar">
    <button id="addStockBtn" class="btn add-stock">+ Add Product</button>
    <button id="exportExcel" class="btn export-btn">Export to Excel</button>
</div>

<!-- Add & Edit Product Modal (Unified) -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modalTitle">Add New Product</h2>
        <form id="productForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <input type="hidden" name="product_id" id="productId">
            
            <div class="form-group">
                <label>Product Name:</label>
                <input type="text" name="product_name" id="productName" required>
            </div>

            <div class="form-group">
                <label>Category:</label>
                <input type="text" name="category" id="category" required>
            </div>

            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity" id="quantity" required>
            </div>

            <div class="form-group">
                <label>Price:</label>
                <input type="text" name="price" id="price" required>
            </div>

            <button type="submit" class="btn save-btn">Save</button>
        </form>
    </div>
</div>

<!-- Inventory Table -->
<table class="inventory-table">
    <thead>
        <tr>
            <th style="width: 50px;">ID</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Price</th>
            <th style="width: 150px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>{{ $product->product_name }}</td>
            <td>{{ $product->category }}</td>
            <td>{{ $product->quantity }}</td>
            <td>${{ $product->price }}</td>
            <td>
                <button class="btn edit-btn" data-id="{{ $product->id }}" data-name="{{ $product->product_name }}" data-category="{{ $product->category }}" data-price="{{ $product->price }}" data-quantity="{{ $product->quantity }}">Edit</button>
                <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn delete-btn">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection

<!-- for functionality -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const productModal = document.getElementById("productModal");
        const closeBtn = document.querySelector(".close-btn");
        const productForm = document.getElementById("productForm");
        const modalTitle = document.getElementById("modalTitle");
        const methodField = document.getElementById("methodField");

        document.getElementById("addStockBtn").addEventListener("click", function () {
            modalTitle.innerText = "Add New Product";
            methodField.value = "POST";
            productForm.action = "{{ route('products.store') }}";
            productModal.style.display = "block";
        });

        document.querySelectorAll(".edit-btn").forEach(button => {
            button.addEventListener("click", function () {
                modalTitle.innerText = "Edit Product";
                methodField.value = "PUT";
                productForm.action = `/products/${this.dataset.id}`;

                document.getElementById("productId").value = this.dataset.id;
                document.getElementById("productName").value = this.dataset.name;
                document.getElementById("category").value = this.dataset.category;
                document.getElementById("quantity").value = this.dataset.quantity;
                document.getElementById("price").value = this.dataset.price;

                productModal.style.display = "block";
            });
        });

        closeBtn.addEventListener("click", () => productModal.style.display = "none");
        window.addEventListener("click", event => event.target === productModal && (productModal.style.display = "none"));
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

@extends('manager.layout')

@section('title', 'Product Inventory')

@section('content')
<h1 class="inventory-title">Product Inventory</h1>

<div class="top-bar">
    <button id="addStockBtn" class="btn add-stock">+ Add Product</button>
    <button id="exportExcel" class="btn export-btn">Export to Excel</button>
</div>

<!-- Add & Edit Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2 id="modalTitle">Add New Product</h2>
        <form id="productForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <input type="hidden" name="product_id" id="productId">
            
            <div class="form-group">
                <label>Product Name:</label>
                <input type="text" name="product_name" id="productName" required value="{{ old('product_name', $product->product_name ?? '') }}">
            </div>

            <div class="form-group">
                <label>Category:</label>
                <input type="text" name="category" id="category" required>
            </div>

            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity" id="quantity" min="0" max="999999" required>
            </div>

            <div class="form-group">
                <label>Price:</label>
                <input type="float" step="0.5" name="price" id="price" min="0" max="999999" required >
            </div>

            <button type="submit" class="btn save-btn" id="SaveBtn">ADD</button>
        </form>
    </div>
</div>

<!-- Inventory Table -->
<table class="inventory-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>{{ $product->product_name }}</td>
            <td>{{ $product->category }}</td>
            <td>{{ $product->quantity }}</td>
            <td>₱{{ $product->price }}</td>
            <td>
                <button class="btn edit-btn" data-id="{{ $product->id }}" data-name="{{ $product->product_name }}" data-category="{{ $product->category }}" data-price="{{ $product->price }}" data-quantity="{{ $product->quantity }}" >
                    <i class="fa-solid fa-pencil"></i>
                </button>
                <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline; " onsubmit="return confirm('Are you sure you want to delete this product?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn delete-btn"><i class="fa-solid fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- already existing Product Warning Modal -->
<div id="duplicateModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Warning!</h2>
        <p id="duplicateMessage">This product already exists.</p>
        <button class="btn close-duplicate">OK</button>
    </div>
</div>
@endsection

<!-- adi tanan na functionality ngan logic -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const productModal = document.getElementById("productModal");
    const duplicateModal = document.getElementById("duplicateModal");
    const productForm = document.getElementById("productForm");
    const modalTitle = document.getElementById("modalTitle");
    const methodField = document.getElementById("methodField");
    const SaveBtn = document.getElementById("SaveBtn");
    const closeBtns = document.querySelectorAll(".close-btn");
    const closeDuplicateBtn = document.querySelector(".close-duplicate");

    const quantityInput = document.getElementById("quantity");
    const priceInput = document.getElementById("price");

    // Prevent negative values and invalid input
    function enforceValidInput(input, allowDecimals = false) {
        input.addEventListener("input", function () {
            this.value = allowDecimals ? this.value.replace(/[^0-9.]/g, "") : Math.max(0, this.value);
        });
    }

    enforceValidInput(quantityInput);
    enforceValidInput(priceInput, true);

    // para mag pakita an modal han new product form
    document.getElementById("addStockBtn").addEventListener("click", function () {
        modalTitle.innerText = "Add New Product";
        methodField.value = "POST";
        productForm.action = "{{ route('products.store') }}";
        productModal.style.display = "block";
        SaveBtn.innerText = "ADD";
        productForm.reset();
    });

    // para mag pakita liwat an edit product form na modal
    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", function () {
            modalTitle.innerText = "Edit Product";
            methodField.value = "PUT";
            productForm.action = `/products/${this.dataset.id}`;
            
            document.getElementById("productId").value = this.dataset.id;
            document.getElementById("productName").value = this.dataset.name;
            document.getElementById("category").value = this.dataset.category;
            document.getElementById("quantity").value = Math.max(0, this.dataset.quantity);
            document.getElementById("price").value = Math.max(0, this.dataset.price);
            
            SaveBtn.innerText = "UPDATE";
            productModal.style.display = "block";
        });
    });

    // adi an para prompt han duplicate product names error
    productForm.addEventListener("submit", function (event) {
        if (methodField.value === "POST") {
            const existingProducts = Array.from(document.querySelectorAll("tbody tr")).map(row =>
                row.querySelector("td:nth-child(2)").innerText.trim().toLowerCase()
            );

            if (existingProducts.includes(document.getElementById("productName").value.trim().toLowerCase())) {
                event.preventDefault();
                duplicateModal.style.display = "block";
            }
        }
    });

    // Close modals
    closeBtns.forEach(btn => btn.addEventListener("click", () => {
        productModal.style.display = "none";
        duplicateModal.style.display = "none";
    }));
    closeDuplicateBtn.addEventListener("click", () => duplicateModal.style.display = "none");

});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

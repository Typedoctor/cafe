@extends('manager.layout')

@section('title', 'Product Inventory')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/inventory.css') }}">
    <style>
        .quantity-note {
            font-size: 0.8em;
            color: red;
            font-style: italic;
        }

        .quantity-error {
            border: 2px solid red;
        }

        .inv-form-group small {
            display: block;
            margin-top: 5px;
            font-size: 0.8em;
            color: #666;
        }
    </style>
@endpush

@section('content')
    <h1 class="inv-title">Product Inventory</h1>

    <div class="inv-top-bar">
        <button id="addStockBtn" class="inv-btn inv-add-stock">+ Add Product</button>
    </div>

    <div id="productModal" class="inv-modal">
        <div class="inv-modal-content">
            <span class="inv-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
            <h2 id="modalTitle">Add New Product</h2>
            <div id="errorMessages" class="inv-error-messages" style="display: none; color: red; margin-bottom: 10px;"></div>
            <form id="productForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">
                <input type="hidden" name="product_id" id="productId">
                <div class="inv-form-group">
                    <label>Product Name:</label>
                    <input type="text" name="product_name" id="productName" required maxlength="50" value="{{ old('product_name', $product->product_name ?? '') }}">
                    <small id="productNameCount">0 / 50</small>
                </div>
                <div class="inv-form-group">
                    <label>Category:</label>
                    <select name="category" id="category" required>
                        <option value="">Select Category</option>
                        <option value="snack">Snack</option>
                        <option value="drink">Drink</option>
                        <option value="meal">Meal</option>
                        <option value="dessert">Dessert</option>
                    </select>
                </div>
                <div class="inv-form-group">
                    <label>Quantity: <span class="quantity-note">Must be between 1 and 1200</span></label>
                    <input type="number" name="quantity" id="quantity" min="1" max="1200" required>
                </div>
                <div class="inv-form-group">
                    <label>Supplier:</label>
                    <input type="text" name="supplier" id="supplier" required maxlength="50" value="{{ old('supplier', $product->supplier ?? '') }}">
                    <small id="supplierCount">0 / 50</small>
                </div>
                <button type="submit" class="inv-btn inv-save-btn" id="SaveBtn">ADD</button>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="tableSuccessModal" class="inv-modal-success">
        <div class="inv-modal-content">
            <p>Product saved successfully!</p>
            <button class="inv-close-success-btn" onclick="closeModal()">Close</button>
        </div>
    </div>

    <div class="inv-table-container" id="transaction-table"> 
        <div class="inv-section-title">Products List</div>
        <table class="inv-table" id="productsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>In stock</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td title="{{ $product->product_name }}">{{ $product->product_name }}</td>
                    <td title="{{ $product->category }}">{{ $product->category }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td title="{{ $product->supplier }}">{{ $product->supplier }}</td>
                    <td>
                        <button class="inv-btn inv-edit-btn" data-id="{{ $product->id }}" data-name="{{ $product->product_name }}" data-category="{{ $product->category }}" data-supplier="{{ $product->supplier }}" data-quantity="{{ $product->quantity }}">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="inv-btn inv-delete-btn"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        let table = $('#productsTable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: 5 }
            ]
        });

        $('#productsTable').on('click', '.inv-edit-btn', function() {
            const modalTitle = document.getElementById("modalTitle");
            const methodField = document.getElementById("methodField");
            const productForm = document.getElementById("productForm");
            const productModal = document.getElementById("productModal");
            const SaveBtn = document.getElementById("SaveBtn");

            modalTitle.innerText = "Edit Product";
            methodField.value = "PUT";
            productForm.action = `/products/${this.dataset.id}`;
            document.getElementById("productId").value = this.dataset.id;
            document.getElementById("productName").value = this.dataset.name;
            document.getElementById("category").value = this.dataset.category;
            document.getElementById("quantity").value = Math.max(1, parseInt(this.dataset.quantity));
            document.getElementById("supplier").value = this.dataset.supplier;
            SaveBtn.innerText = "UPDATE";
            productModal.style.display = "block";
            document.getElementById("errorMessages").style.display = "none";

            // Update character counters
            updateCharacterCount('productName', 'productNameCount');
            updateCharacterCount('supplier', 'supplierCount');
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const productModal = document.getElementById("productModal");
        const productForm = document.getElementById("productForm");
        const modalTitle = document.getElementById("modalTitle");
        const methodField = document.getElementById("methodField");
        const SaveBtn = document.getElementById("SaveBtn");
        const closeBtn = document.querySelector(".inv-close-btn");
        const productNameInput = document.getElementById("productName");
        const productNameCount = document.getElementById("productNameCount");
        const quantityInput = document.getElementById("quantity");
        const supplierInput = document.getElementById("supplier");
        const supplierCount = document.getElementById("supplierCount");
        const errorMessages = document.getElementById("errorMessages");
        const tableSuccessModal = document.getElementById("tableSuccessModal");

        function showTableSuccessModal() {
            tableSuccessModal.style.display = 'block';
            setTimeout(() => {
                tableSuccessModal.style.display = 'none';
            }, 2000);
        }

        function closeModal() {
            tableSuccessModal.style.display = 'none';
        }

        function showError(message) {
            errorMessages.style.display = 'block';
            errorMessages.innerHTML = message;
        }

        function clearErrors() {
            errorMessages.style.display = 'none';
            errorMessages.innerHTML = '';
        }

        function enforceValidProductName(input) {
            input.addEventListener("input", function () {
                this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
            });
        }

        function enforceValidQuantity(input) {
            input.addEventListener("input", function () {
                let val = parseInt(this.value) || 1;
                if (val < 1) {
                    this.value = 1;
                } else if (val > 1200) {
                    this.value = 1200;
                } else {
                    this.value = val;
                }
            });
        }

        function enforceValidSupplier(input) {
            input.addEventListener("input", function () {
                // Allow letters, numbers, spaces, and common punctuation
                this.value = this.value.replace(/[^a-zA-Z0-9\s.,-]/g, '');
            });
        }

        function updateCharacterCount(inputId, countId) {
            const input = document.getElementById(inputId);
            const count = document.getElementById(countId);
            if (input && count) {
                const currentCount = input.value.length;
                count.textContent = `${currentCount} / 50`;
            } else {
                console.error(`Element not found: inputId=${inputId}, countId=${countId}`);
            }
        }

        if (productNameInput) {
            enforceValidProductName(productNameInput);
            productNameInput.addEventListener("input", () => updateCharacterCount('productName', 'productNameCount'));
            updateCharacterCount('productName', 'productNameCount'); // Initial value
        }

        if (quantityInput) enforceValidQuantity(quantityInput);
        if (supplierInput) {
            enforceValidSupplier(supplierInput);
            supplierInput.addEventListener("input", () => updateCharacterCount('supplier', 'supplierCount'));
            updateCharacterCount('supplier', 'supplierCount'); // Initial value
        }

        document.getElementById("addStockBtn").addEventListener("click", function () {
            modalTitle.innerText = "Add New Product";
            methodField.value = "POST";
            productForm.action = "{{ route('products.store') }}";
            productModal.style.display = "block";
            SaveBtn.innerText = "ADD";
            productForm.reset();
            quantityInput.value = 1;
            clearErrors();
            updateCharacterCount('productName', 'productNameCount'); // Reset counter to 0
            updateCharacterCount('supplier', 'supplierCount'); // Reset counter to 0
        });

        productForm.addEventListener("submit", function (event) {
            event.preventDefault();
            clearErrors();

            const quantity = parseInt(quantityInput.value);
            if (quantity > 1200) {
                showError("Quantity cannot exceed 1200.");
                quantityInput.classList.add('quantity-error');
                return;
            } else if (quantity < 1) {
                showError("Quantity must be at least 1.");
                quantityInput.classList.add('quantity-error');
                return;
            } else {
                quantityInput.classList.remove('quantity-error');
            }

            $.ajax({
                url: productForm.action,
                type: methodField.value === "POST" ? "POST" : "PUT",
                data: $(productForm).serialize(),
                success: function(response) {
                    showTableSuccessModal();
                    let table = $('#productsTable').DataTable();
                    const newRow = [
                        response.product.id,
                        response.product.product_name,
                        response.product.category,
                        response.product.quantity,
                        response.product.supplier,
                        `<button class="inv-btn inv-edit-btn" data-id="${response.product.id}" data-name="${response.product.product_name}" data-category="${response.product.category}" data-supplier="${response.product.supplier}" data-quantity="${response.product.quantity}">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <form action="/products/${response.product.id}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                            <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="inv-btn inv-delete-btn"><i class="fa-solid fa-trash"></i></button>
                        </form>`
                    ];

                    if (methodField.value === "POST") {
                        table.row.add(newRow).draw();
                    } else {
                        let row = table.row($(`button[data-id="${response.product.id}"]`).closest('tr'));
                        row.data(newRow).draw();
                    }

                    productModal.style.display = "none";
                    productForm.reset();
                    quantityInput.value = 1;
                    updateCharacterCount('productName', 'productNameCount');
                    updateCharacterCount('supplier', 'supplierCount');
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON?.message || 'An error occurred while saving the product.';
                    if (xhr.status === 422) {
                        errorMsg = Object.values(xhr.responseJSON.errors || {}).flat().join('<br>');
                    }
                    showError(errorMsg);
                }
            });
        });

        closeBtn.addEventListener("click", () => {
            productModal.style.display = "none";
            clearErrors();
        });

        window.addEventListener("click", event => {
            if (event.target === productModal) {
                productModal.style.display = "none";
                clearErrors();
            }
            if (event.target === tableSuccessModal) {
                tableSuccessModal.style.display = "none";
            }
        });
    });
</script>
@endpush

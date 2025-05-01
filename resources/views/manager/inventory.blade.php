@extends('manager.layout')

@section('title', 'Product Inventory')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
       
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
                    <input type="text" name="product_name" id="productName" required value="{{ old('product_name', $product->product_name ?? '') }}">
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
                    <label>Quantity:</label>
                    <input type="number" name="quantity" id="quantity" min="1" required>
                </div>
                <div class="inv-form-group">
                    <label>Supplier:</label>
                    <input type="text" name="supplier" id="supplier" required>
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
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->category }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>{{ $product->supplier }}</td>
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            let table = $('#productsTable').DataTable({
                pageLength: 10,
                responsive: true,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 5 } // Disable sorting on Actions column
                ]
            });

            // Ensure edit buttons work with DataTables
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
                document.getElementById("quantity").value = Math.max(1, this.dataset.quantity);
                document.getElementById("supplier").value = this.dataset.supplier;
                SaveBtn.innerText = "UPDATE";
                productModal.style.display = "block";
                document.getElementById("errorMessages").style.display = "none";
            });
        });

        // JavaScript for modals, add product, and input validation
        document.addEventListener("DOMContentLoaded", function () {
            const productModal = document.getElementById("productModal");
            const productForm = document.getElementById("productForm");
            const modalTitle = document.getElementById("modalTitle");
            const methodField = document.getElementById("methodField");
            const SaveBtn = document.getElementById("SaveBtn");
            const closeBtn = document.querySelector(".inv-close-btn");
            const productNameInput = document.getElementById("productName");
            const quantityInput = document.getElementById("quantity");
            const supplierInput = document.getElementById("supplier");
            const errorMessages = document.getElementById("errorMessages");
            const tableSuccessModal = document.getElementById("tableSuccessModal");

            // Function to show success modal
            function showTableSuccessModal() {
                tableSuccessModal.style.display = 'block';
                setTimeout(() => {
                    tableSuccessModal.style.display = 'none';
                }, 2000);
            }

            // Function to close success modal
            function closeModal() {
                tableSuccessModal.style.display = 'none';
            }

            // Function to display error messages
            function showError(message) {
                errorMessages.style.display = 'block';
                errorMessages.innerHTML = message;
            }

            // Function to clear error messages
            function clearErrors() {
                errorMessages.style.display = 'none';
                errorMessages.innerHTML = '';
            }

            // Validate product name (letters and spaces only)
            function enforceValidProductName(input) {
                input.addEventListener("input", function () {
                    this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
                });
            }
            if (productNameInput) enforceValidProductName(productNameInput);

            // Validate quantity (positive integers only)
            function enforceValidQuantity(input) {
                input.addEventListener("input", function () {
                    this.value = Math.max(1, parseInt(this.value) || 1);
                });
            }
            if (quantityInput) enforceValidQuantity(quantityInput);

            // Validate supplier (letters and spaces only)
            function enforceValidSupplier(input) {
                input.addEventListener("input", function () {
                    this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
                });
            }
            if (supplierInput) enforceValidSupplier(supplierInput);

            document.getElementById("addStockBtn").addEventListener("click", function () {
                modalTitle.innerText = "Add New Product";
                methodField.value = "POST";
                productForm.action = "{{ route('products.store') }}";
                productModal.style.display = "block";
                SaveBtn.innerText = "ADD";
                productForm.reset();
                quantityInput.value = 1; // Default to 1
                clearErrors();
            });

            productForm.addEventListener("submit", function (event) {
                event.preventDefault();
                clearErrors();

                $.ajax({
                    url: productForm.action,
                    type: methodField.value === "POST" ? "POST" : "PUT",
                    data: $(productForm).serialize(),
                    success: function(response) {
                        showTableSuccessModal();
                        let table = $('#productsTable').DataTable();
                        if (methodField.value === "POST") {
                            // Add new row for create
                            table.row.add([
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
                            ]).draw();
                        } else {
                            // Update existing row for edit
                            let row = table.row($(`button[data-id="${response.product.id}"]`).closest('tr'));
                            row.data([
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
                            ]).draw();
                        }
                        productModal.style.display = "none";
                        productForm.reset();
                        quantityInput.value = 1;
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

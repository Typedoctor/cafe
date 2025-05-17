@extends('manager.layout')

@section('title', 'Product Inventory')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="{{ asset('css/manager-inventory.css') }}">
    <style>
        .invalid-indicator {
            display: none;
            color: red;
            font-size: 0.8em;
            margin-top: 5px;
        }
        .product-name-error, .supplier-error, .quantity-error {
            border: 1px solid red;
        }
    </style>
@endpush

@section('content')

<!-- Overlays for Modals -->
<div class="inv-modal-overlay" data-modal-id="productModal"></div>
<div class="inv-modal-overlay" data-modal-id="deleteModal"></div>
<div class="inv-modal-overlay" data-modal-id="tableSuccessModal"></div>

<!-- Add/Edit Product Modal -->
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
                <span id="productNameInvalid" class="invalid-indicator">Product name can only contain letters, spaces, apostrophes, hyphens, commas, and ampersands</span>
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
                <label>Quantity: <span class="quantity-note">Must be between 1 and 9999</span></label>
                <input type="number" name="quantity" id="quantity" min="1" max="9999" required>
            </div>
            <div class="inv-form-group">
                <label>Unit of Measurement:</label>
                <select name="unit_of_measurement" id="unitOfMeasurement" required>
                    <option value="">Select Unit</option>
                    <option value="pieces">Pieces</option>
                    <option value="liters">Liters</option>
                    <option value="kilograms">Kilograms</option>
                    <option value="grams">Grams</option>
                </select>
            </div>
            <div class="inv-form-group">
                <label>Purchase Cost:</label>
                <input type="number" name="purchase_cost" id="purchaseCost" min="0" step="0.01" required value="{{ old('purchase_cost', $product->purchase_cost ?? '') }}">
            </div>
            <div class="inv-form-group">
                <label>Supplier:</label>
                <input type="text" name="supplier" id="supplier" required maxlength="50" value="{{ old('supplier', $product->supplier ?? '') }}">
                <small id="supplierCount">0 / 50</small>
                <span id="supplierInvalid" class="invalid-indicator">Supplier can only contain letters, spaces, commas, periods, ampersands, apostrophes, and hyphens</span>
            </div>
            <button type="submit" class="inv-btn inv-save-btn" id="SaveBtn">ADD</button>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="inv-modal delete">
    <div class="inv-modal-content">
        <span class="inv-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this product?</p>
        <div class="delete-btn-container">
            <button class="delete-cancel-btn" onclick="closeDeleteModal()">Cancel</button>
            <button class="delete-confirm-btn" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="tableSuccessModal" class="inv-modal-success">
    <div class="inv-modal-success-content">
        <p id="successMessage">Product saved successfully!</p>
    </div>
</div>

<div class="rep-metric-container">
    <div class="rep-metric-box">
        <div class="rep-metric-title">Total Items in stock</div>
        <div class="rep-metric-value">{{ $products->count() }}</div>
    </div>
    <div class="rep-metric-box rep-metric-box--low">
        <div class="rep-metric-title">Low Stock Items</div>
        <div class="rep-metric-value">{{ $products->whereBetween('quantity', [3, 5])->count() }}</div>
    </div>
    <div class="rep-metric-box rep-metric-box--critical">
        <div class="rep-metric-title">Critical Stock Items</div>
        <div class="rep-metric-value">{{ $products->where('quantity', '<=', 2)->count() }}</div>
    </div>
</div>

<div class="inv-table-container" id="transaction-table"> 
    <div class="inv-section-title">Products List</div>
    <div class="category-filter-wrapper">
        <div class="category-filter">
            <select id="categoryFilter">
                <option value="">All Categories</option>
                <option value="snack">Snack</option>
                <option value="drink">Drink</option>
                <option value="meal">Meal</option>
                <option value="dessert">Dessert</option>
            </select>
        </div>
        <div class="inv-top-bar">
            <button id="addStockBtn" class="inv-btn inv-add-btn">+ Add Product</button>
        </div>
    </div>
  
    <table class="inv-table" id="productsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>In stock</th>
                <th>Unit</th>
                <th>Purchase Cost</th>
                <th>Supplier</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr data-id="{{ $product->id }}">
                <td>{{ $product->id }}</td>
                <td title="{{ $product->product_name }}">{{ $product->product_name }}</td>
                <td title="{{ $product->category }}">{{ $product->category }}</td>
                <td class="{{ $product->quantity <= 2 ? 'product-critical' : ($product->quantity >= 3 && $product->quantity <= 5 ? 'product-low' : '') }}">{{ $product->quantity }}</td>
                <td>{{ $product->unit_of_measurement }}</td>
                <td>{{ number_format($product->purchase_cost, 2) }}</td>
                <td title="{{ $product->supplier }}">{{ $product->supplier }}</td>
                <td>
                    <button class="inv-btn inv-edit-btn" 
                            data-id="{{ $product->id }}" 
                            data-name="{{ $product->product_name }}" 
                            data-category="{{ $product->category }}" 
                            data-supplier="{{ $product->supplier }}" 
                            data-quantity="{{ $product->quantity }}"
                            data-unit-of-measurement="{{ $product->unit_of_measurement }}"
                            data-purchase-cost="{{ $product->purchase_cost }}">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <button class="inv-btn inv-delete-btn" 
                            data-id="{{ $product->id }}"
                            onclick="showDeleteModal({{ $product->id }})">
                        <i class="fa-solid fa-trash"></i>
                    </button>
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
// Set up CSRF token for all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
    }
});

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        const overlay = document.querySelector(`.inv-modal-overlay[data-modal-id="${modalId}"]`);
        if (overlay) {
            overlay.style.display = 'block';
        }
        document.body.classList.add('modal-open'); // Disable scrolling
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        const overlay = document.querySelector(`.inv-modal-overlay[data-modal-id="${modalId}"]`);
        if (overlay) {
            overlay.style.display = 'none';
        }
        document.body.classList.remove('modal-open'); // Re-enable scrolling
    }
}

function closeDeleteModal() {
    closeModal("deleteModal");
}

function showDeleteModal(productId) {
    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
    confirmDeleteBtn.onclick = function() {
        deleteProduct(productId);
        closeDeleteModal();
    };
    openModal("deleteModal");
}

function updateCharacterCount(inputId, countId) {
    const input = document.getElementById(inputId);
    const count = document.getElementById(countId);
    if (input && count) {
        count.textContent = `${input.value.length} / 50`;
    } else {
        console.error(`Element not found: inputId=${inputId}, countId=${countId}`);
    }
}

function updateMetrics() {
    $.ajax({
        url: "{{ route('products.metrics') }}",
        type: "GET",
        success: function(data) {
            $('.rep-metric-value').eq(0).text(data.totalItems);
            $('.rep-metric-value').eq(1).text(data.lowStockItems);
            $('.rep-metric-value').eq(2).text(data.criticalStockItems);
        },
        error: function(xhr) {
            console.error('Failed to update metric boxes:', xhr.responseText);
        }
    });
}

function deleteProduct(productId) {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (!csrfToken) {
        $('#errorMessages').css('display', 'block').html('CSRF token not found. Please refresh the page.');
        console.error('CSRF token not found in meta tag');
        return;
    }

    $.ajax({
        url: "{{ url('products') }}/" + productId,
        type: 'DELETE',
        success: function(response) {
            $('#tableSuccessModal').removeClass('add update delete').addClass('delete show');
            $('#successMessage').text('Product deleted successfully!');
            let table = $('#productsTable').DataTable();
            let row = table.row($(`tr[data-id="${productId}"]`));
            if (row.length) {
                row.remove().draw(false);
            }
            updateMetrics();
            closeModal("tableSuccessModal");
        },
        error: function(xhr) {
            let errorMsg = xhr.responseJSON?.message || 'An error occurred while deleting the product.';
            if (xhr.status === 419) {
                errorMsg = 'CSRF token mismatch. Please refresh the page and try again.';
            } else if (xhr.status === 422) {
                errorMsg = Object.values(xhr.responseJSON.errors || {}).flat().join('<br>');
            } else if (xhr.status === 404) {
                errorMsg = 'Product not found.';
            }
            $('#errorMessages').css('display', 'block').html(errorMsg);
            console.error('Delete error:', xhr.responseText);
        }
    });
}

$(document).ready(function () {
    let table = $('#productsTable').DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100, 250, 500],
        responsive: true,
        order: [[0, 'asc']],
        autoWidth: false,
        columnDefs: [
            { orderable: false, targets: 7 },
            { width: '50px', targets: 0 },
            { width: '180px', targets: 1 },
            { width: '60px', targets: 2 },
            { width: '50px', targets: 3 },
            { width: '80px', targets: 4 },
            { width: '80px', targets: 5 },
            { width: '180px', targets: 6 },
            { width: '80px', targets: 7 }
        ]
    });

    $('#categoryFilter').on('change', function() {
        let value = this.value;
        table.column(2).search(value).draw();
    });

    $('#productsTable').on('click', '.inv-edit-btn', function() {
        const modalTitle = document.getElementById("modalTitle");
        const methodField = document.getElementById("methodField");
        const productForm = document.getElementById("productForm");
        const SaveBtn = document.getElementById("SaveBtn");

        modalTitle.innerText = "Edit Product";
        methodField.value = "PUT";
        productForm.action = `/products/${this.dataset.id}`;
        document.getElementById("productId").value = this.dataset.id;
        document.getElementById("productName").value = this.dataset.name.replace(/[^a-zA-Z\s',&À-ÿ-]/g, '');
        document.getElementById("category").value = this.dataset.category;
        document.getElementById("quantity").value = Math.max(1, parseInt(this.dataset.quantity));
        document.getElementById("unitOfMeasurement").value = this.dataset.unitOfMeasurement;
        document.getElementById("supplier").value = this.dataset.supplier.replace(/[^a-zA-Z\s,.&'\-.À-ÿ]/g, '');
        document.getElementById("purchaseCost").value = parseFloat(this.dataset.purchaseCost).toFixed(2);
        SaveBtn.innerText = "UPDATE";
        openModal("productModal");
        document.getElementById("errorMessages").style.display = "none";

        updateCharacterCount('productName', 'productNameCount');
        updateCharacterCount('supplier', 'supplierCount');
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const productModal = document.getElementById("productModal");
    const deleteModal = document.getElementById("deleteModal");
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
    const productNameInvalid = document.getElementById("productNameInvalid");
    const supplierInvalid = document.getElementById("supplierInvalid");

    function showTableSuccessModal(message, action) {
        $('#successMessage').text(message);
        $('#tableSuccessModal')
            .removeClass('add update delete show')
            .addClass(`${action}`)
            .css({ display: 'block', opacity: 1 });

        setTimeout(() => {
            closeModal("tableSuccessModal");
        }, 2500);
    }

    function showError(message) {
        errorMessages.style.display = 'block';
        errorMessages.innerHTML = message;
    }

    function clearErrors() {
        errorMessages.style.display = 'none';
        errorMessages.innerHTML = '';
        productNameInvalid.style.display = 'none';
        supplierInvalid.style.display = 'none';
        productNameInput.classList.remove('product-name-error');
        supplierInput.classList.remove('supplier-error');
        quantityInput.classList.remove('quantity-error');
    }

    function enforceValidProductName() {
        productNameInput.addEventListener("input", function () {
            const value = this.value.replace(/[^a-zA-Z\s',&À-ÿ-]/g, '');
            this.value = value;
            productNameInvalid.style.display = value === this.value ? 'none' : 'block';
            productNameInput.classList.toggle('product-name-error', value !== this.value);
            updateCharacterCount('productName', 'productNameCount');
        });
    }

    function enforceValidQuantity() {
        quantityInput.addEventListener("input", function () {
            let val = parseInt(this.value) || 1;
            this.value = Math.min(Math.max(val, 1), 9999);
        });
    }

    function enforceValidSupplier() {
        supplierInput.addEventListener("input", function () {
            const value = this.value.replace(/[^a-zA-Z\s,.&'\-.À-ÿ]/g, '');
            this.value = value;
            supplierInvalid.style.display = value === this.value ? 'none' : 'block';
            supplierInput.classList.toggle('supplier-error', value !== this.value);
            updateCharacterCount('supplier', 'supplierCount');
        });
    }

    if (productNameInput) {
        enforceValidProductName();
        updateCharacterCount('productName', 'productNameCount');
    }

    if (quantityInput) enforceValidQuantity();

    if (supplierInput) {
        enforceValidSupplier();
        updateCharacterCount('supplier', 'supplierCount');
    }

    document.getElementById("addStockBtn").addEventListener("click", function () {
        modalTitle.innerText = "Add New Product";
        methodField.value = "POST";
        productForm.action = "{{ route('products.store') }}";
        SaveBtn.innerText = "ADD";
        productForm.reset();
        quantityInput.value = 1;
        document.getElementById("unitOfMeasurement").value = "pieces";
        clearErrors();
        updateCharacterCount('productName', 'productNameCount');
        updateCharacterCount('supplier', 'supplierCount');
        openModal("productModal");
    });

    productForm.addEventListener("submit", function (event) {
        event.preventDefault();
        clearErrors();

        const productName = productNameInput.value.trim();
        const supplier = supplierInput.value.trim();
        const quantity = parseInt(quantityInput.value);
        const category = document.getElementById("category").value;
        const unitOfMeasurement = document.getElementById("unitOfMeasurement").value;
        let errors = [];

        if (!productName || !supplier || !category || !unitOfMeasurement) {
            errors.push("Please fill in all required fields!");
        }

        if (!/^[a-zA-Z\s',&À-ÿ-]+$/.test(productName)) {
            errors.push("Product name can only contain letters, spaces, apostrophes, hyphens, commas, and ampersands.");
            productNameInput.classList.add('product-name-error');
            productNameInvalid.style.display = 'block';
        }

        if (!/^[a-zA-Z\s,.&'\-.À-ÿ]+$/.test(supplier)) {
            errors.push("Supplier can only contain letters, spaces, commas, periods, ampersands, apostrophes, and hyphens.");
            supplierInput.classList.add('supplier-error');
            supplierInvalid.style.display = 'block';
        }

        if (quantity > 9999 || quantity < 1) {
            errors.push("Quantity must be between 1 and 9999.");
            quantityInput.classList.add('quantity-error');
        }

        if (errors.length > 0) {
            showError(errors.join('<br>'));
            return;
        }

        $.ajax({
            url: productForm.action,
            type: methodField.value === "POST" ? "POST" : "PUT",
            data: $(productForm).serialize(),
            success: function(response) {
                const action = methodField.value === "POST" ? "add" : "update";
                if (!response.product || !response.product.id) {
                    showError('Product was not saved. Please check for errors.');
                    return;
                }
                showTableSuccessModal(methodField.value === "POST" ? "Product added successfully!" : "Product updated successfully!", action);
                let table = $('#productsTable').DataTable();
                const quantity = parseInt(response.product.quantity);
                const stockClass = quantity <= 2 ? 'product-critical' : (quantity >= 3 && quantity <= 5 ? 'product-low' : '');
                const newRow = [
                    response.product.id,
                    response.product.product_name,
                    response.product.category,
                    `<span class="${stockClass}">${response.product.quantity}</span>`,
                    response.product.unit_of_measurement,
                    parseFloat(response.product.purchase_cost).toFixed(2),
                    response.product.supplier,
                    `<button class="inv-btn inv-edit-btn" 
                             data-id="${response.product.id}" 
                             data-name="${response.product.product_name}" 
                             data-category="${response.product.category}" 
                             data-supplier="${response.product.supplier}" 
                             data-quantity="${response.product.quantity}"
                             data-unit-of-measurement="${response.product.unit_of_measurement}"
                             data-purchase-cost="${response.product.purchase_cost}">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <button class="inv-btn inv-delete-btn" 
                            data-id="${response.product.id}"
                            onclick="showDeleteModal(${response.product.id})">
                        <i class="fa-solid fa-trash"></i>
                    </button>`
                ];

                if (methodField.value === "POST") {
                    table.row.add(newRow).draw(false);
                    const lastRow = $('#productsTable tbody tr').last();
                    lastRow.attr('data-id', response.product.id);
                } else {
                    let row = table.row($(`tr[data-id="${response.product.id}"]`));
                    row.data(newRow).draw(false);
                }

                closeModal("productModal");
                productForm.reset();
                quantityInput.value = 1;
                document.getElementById("purchaseCost").value = "0.00";
                document.getElementById("unitOfMeasurement").value = "pieces";
                clearErrors();
                updateCharacterCount('productName', 'productNameCount');
                updateCharacterCount('supplier', 'supplierCount');
                updateMetrics();
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.message || 'An error occurred while saving the product.';
                if (xhr.status === 419) {
                    errorMsg = 'CSRF token mismatch. Please refresh the page and try again.';
                } else if (xhr.status === 422) {
                    errorMsg = Object.values(xhr.responseJSON.errors || {}).flat().join('<br>');
                }
                showError(errorMsg);
            }
        });
    });

    closeBtn.addEventListener("click", () => {
        closeModal("productModal");
        clearErrors();
        productForm.reset();
        quantityInput.value = 1;
        document.getElementById("unitOfMeasurement").value = "pieces";
        updateCharacterCount('productName', 'productNameCount');
        updateCharacterCount('supplier', 'supplierCount');
    });

    window.addEventListener("click", event => {
        if (event.target.classList.contains('inv-modal') || event.target.classList.contains('inv-close-btn')) {
            closeModal(event.target.id || event.target.closest('.inv-modal').id);
            clearErrors();
            productForm.reset();
            quantityInput.value = 1;
            document.getElementById("unitOfMeasurement").value = "pieces";
            updateCharacterCount('productName', 'productNameCount');
            updateCharacterCount('supplier', 'supplierCount');
        }
    });
});
</script>
@endpush
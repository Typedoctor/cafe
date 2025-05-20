@extends('cashier.layout')

@section('title', 'Manage Trash')

@push('styles')
    
@endpush

@section('content')
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
   <link rel="stylesheet" href="{{ asset('css/cashier-spoil.css') }}">
</head>
<!-- Display Success/Error Messages -->
@if (session('success'))
    <div class="modal-overlay" data-modal-id="successModal"></div>
    <div id="successModal" class="success-modal">
        <div class="success-modal-content">
            <p>{{ session('success') }}</p>
        </div>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-error" style="margin: 15px; padding: 10px; background-color: #f8d7da; color: #721c24; border-radius: 5px;">
        {{ session('error') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-error" style="margin: 15px; padding: 10px; background-color: #f8d7da; color: #721c24; border-radius: 5px;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    // Get all products for inventory tab
    $products = \App\Models\Product::all();
@endphp

<!-- Add Trash Modal -->
<div class="modal-overlay" data-modal-id="trashModal"></div>
<div id="trashModal" class="modal">
    <span class="close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
    <div class="modal-content">
        <!-- Sub Tabs -->
        <div class="sub-tabs" style="display:flex;justify-content:center;gap:10px;margin-bottom:10px;">
            <button type="button" class="sub-tab-btn active" data-source="inventory">Inventory</button>
            <button type="button" class="sub-tab-btn" data-source="shelf">Shelfed Items</button>
            <button type="button" class="sub-tab-btn" data-source="all">All</button>
        </div>
        <br>
        <!-- Category Tabs -->
        <div class="category-tabs">
            <button class="tab-btn active" data-category="snack">Snack</button>
            <button class="tab-btn" data-category="drink">Drink</button>
            <button class="tab-btn" data-category="meal">Meal</button>
            <button class="tab-btn" data-category="dessert">Dessert</button>
        </div>

        <form id="trashForm" method="POST" action="{{ route('spoilage.store') }}">
            @csrf
            <input type="hidden" name="category" id="category" value="snack">
            <input type="hidden" name="source" id="source" value="inventory">
            <label>What products are you discarding?</label>
            <div class="form-group">
                <!-- Move DataTables controls here -->
                <div class="product-table-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div id="productTable_length"></div>
                    <div id="productTable_filter"></div>
                </div>
                <div class="product-table-container">
                    <table id="productTable" class="product-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price (₱)</th>
                                <th>Source</th> <!-- Added Source column -->
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        
                        <tbody id="productTableBody">
                            {{-- Inventory products --}}
                            @foreach($products as $product)
                                <tr data-source="inventory"
                                    data-product-name="{{ e(trim($product->product_name)) }}"
                                    data-price="{{ $product->purchase_cost }}"
                                    data-category="{{ $product->category }}"
                                    data-stock="{{ $product->quantity }}">
                                    <td>{{ e(trim($product->product_name)) }}</td>
                                    <td>{{ number_format($product->purchase_cost, 2) }}</td>
                                    <td>Inventory</td>
                                    <td class="{{ $product->quantity <= 2 ? 'product-critical' : ($product->quantity >= 3 && $product->quantity <= 5 ? 'product-low' : '') }}">{{ $product->quantity }}</td>
                                    <td>
                                        <button type="button" class="select-product-btn"
                                            @if($product->quantity == 0) disabled @endif>
                                            @if($product->quantity == 0) No Stock @else Select @endif
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            {{-- Shelfed items --}}
                            @foreach($shelfItems as $shelfItem)
                                @php
                                    $profit = $shelfItem->price - $shelfItem->product->purchase_cost;
                                    $qty = $shelfItem->quantity_added;
                                @endphp
                                <tr data-source="shelf"
                                    data-product-name="{{ e(trim($shelfItem->product->product_name)) }}"
                                    data-price="{{ $profit }}"
                                    data-category="{{ $shelfItem->product->category }}"
                                    data-stock="{{ $qty }}">
                                    <td>{{ e(trim($shelfItem->product->product_name)) }}</td>
                                    <td>{{ number_format($profit, 2) }}</td>
                                    <td>Shelfed Item</td>
                                    <td class="{{ $qty <= 2 ? 'product-critical' : ($qty >= 3 && $qty <= 5 ? 'product-low' : '') }}">{{ $qty }}</td>
                                    <td>
                                        <button type="button" class="select-product-btn"
                                            @if($qty == 0) disabled @endif>
                                            @if($qty == 0) No Stock @else Select @endif
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <span id="productError" style="color: red; display: none;">Please select at least one product!</span>
            </div>

            <div class="form-group">
                <label>Selected Products</label>
                <div class="selected-product-table-container">
                    <table id="selectedProductTable" class="selected-product-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price (₱)</th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4">No products selected</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-group">
                <label>Why are you discarding these items?</label>
                <textarea name="reason" id="reason" placeholder="e.g., Expired, Damaged" maxlength="255" required></textarea>
                <div class="trsh-char-counter" id="charCounter">255 characters remaining</div>
                <div id="reasonError" style="color: red; display: none;">
                    Only letters, spaces, apostrophes, commas, ampersands, and hyphens are allowed.
                </div>
            </div>
            <div class="total-loss-display">
                <label>Total Loss (₱)</label>
                <div id="totalLossDisplay">Select products to see total loss.</div>
            </div>
            <button type="submit" class="btn save-btn" id="saveBtn">Add</button>
        </form>
        <div id="loadingSpinner" style="display: none; text-align: center; margin-top: 10px;">
            <i class="fa-solid fa-spinner fa-spin"></i> Saving...
        </div>
    </div>
</div>

<!-- Stock Limit Warning Modal -->
<div class="modal-overlay" data-modal-id="stockLimitModal"></div>
<div id="stockLimitModal" class="modal warning-modal">
    <div class="modal-content warning-modal-content">
        <span class="close-btn warning-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2>Stock Limit Reached</h2>
        <p id="stockLimitMessage">Cannot add more of <span id="stockLimitProduct"></span>; stock limit reached!</p>
        <button class="btn warning-ok-btn">OK</button>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" data-modal-id="deleteConfirmModal"></div>
<div id="deleteConfirmModal" class="modal delete-modal">
    <div class="modal-content delete-modal-content">
        <h2>Confirm Deletion</h2>
        <p id="deleteConfirmMessage">Are you sure you want to delete this spoilage entry?</p>
        <div class="delete-btn-container">
            <button class="btn delete-cancel-btn">Cancel</button>
            <button class="btn delete-confirm-btn">Delete</button>
        </div>
    </div>
</div>

<!-- Spoil Entry Details Modal -->
<div class="modal-overlay" id="spoilDetailsModalOverlay" data-modal-id="spoilDetailsModal"></div>
<div id="spoilDetailsModal" class="spoil-details-modal">
    <div class="spoil-details-header">Spoil Entry Details</div>
    <div class="spoil-details-content" id="spoilDetailsContent"></div>
    <div class="spoil-modal-buttons">
        <button class="spoil-modal-print" id="spoilPrintModal">Print</button>
        <button class="spoil-modal-close" id="spoilCloseModal">Close</button>
    </div>
</div>

<!-- Trash Table -->
<div class="trsh-table-container" id="transaction-table">
    <h1 class="page-title">Lists of Spoils</h1>
    <div class="top-bar-container">
        <div class="filter-container">
            <form id="filterForm" method="GET" action="{{ route('spoilage.index') }}" style="display: flex; gap: 10px; align-items: center;">
                <select class="rep-month-filter" name="month" id="filterMonth" onchange="submitForm()">
                    <option value="all" {{ request('month') == 'all' ? 'selected' : '' }}>All Months</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (string)request('month', now()->month) === (string)$m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
                <select class="rep-year-filter" name="year" id="filterYear" onchange="submitForm()">
                    <option value="all" {{ request('year') == 'all' ? 'selected' : '' }}>All Years</option>
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ (string)request('year', now()->year) === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="button" class="btn reset-filter-btn" id="resetFilterBtn">Reset</button>
            </form>
        </div>
        <div class="add-trash-container">
            <button id="addTrashBtn" class="btn add-trash">+ Add Spoil Entry</button>
        </div>
    </div>
    <table class="inventory-table" id="trashTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Reason</th>
                <th>Total Loss</th>
                <th>Date thrown</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trashes as $trash)
            <tr class="spoil-row" data-trash-id="{{ $trash->id }}"
                data-trash-details="{{ json_encode([
                    'id' => $trash->id,
                    'product_name' => $trash->product_name,
                    'category' => $trash->category,
                    'quantity' => $trash->quantity,
                    'reason' => $trash->reason,
                    'total_loss' => number_format($trash->total_loss, 2),
                    'created_at' => $trash->created_at->format('M j Y / g:i A')
                ]) }}">
                <td>{{ $trash->id }}</td>
                <td>{{ $trash->product_name }}</td>
                <td>{{ $trash->category }}</td>
                <td>{{ $trash->quantity }}</td>
                <td>{{ $trash->reason }}</td>
                <td>₱{{ number_format($trash->total_loss, 2) }}</td>
                <td>{{ $trash->created_at->format('M j Y / g:i A') }}</td>
                <td>
                    <button type="button" class="btn delete-btn" data-trash-id="{{ $trash->id }}"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
function submitForm() {
    document.getElementById('filterForm').submit();
}

document.addEventListener('DOMContentLoaded', function () {
    // Initialize DataTables for Trash Table
    const trashTable = $('#trashTable').DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100, 250],
        responsive: true,
        searching: true,
        lengthChange: true,
        paging: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: 7 }
        ],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            paginate: {
                previous: "Previous",
                next: "Next"
            },
            emptyTable: "No trash entries available."
        }
    });

    // Initialize DataTables for Product Table
    const productTable = $('#productTable').DataTable({
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50, 100],
        responsive: true,
        searching: true,
        lengthChange: true,
        paging: true,
        // Set default order to Product Name (column 0) ascending
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: 4 } // Make "Action" column not orderable
        ],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            paginate: {
                previous: "Previous",
                next: "Next"
            },
            emptyTable: "No products available."
        }
    });

    // Success Modal Auto-Dismiss
    const successModal = document.getElementById('successModal');
    if (successModal) {
        openModal("successModal");
        setTimeout(() => {
            closeModal("successModal");
        }, 3000);
    }

    // Character counter for reason textarea
    const reasonInput = document.getElementById('reason');
    const charCounter = document.getElementById('charCounter');
    const reasonError = document.getElementById('reasonError');
    const maxLength = 255;

    reasonInput.addEventListener('input', () => {
        const remaining = maxLength - reasonInput.value.length;
        charCounter.textContent = `${remaining} characters remaining`;
    });

    // Modal and form handling
    const trashModal = document.getElementById('trashModal');
    const stockLimitModal = document.getElementById('stockLimitModal');
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    const spoilDetailsModal = document.getElementById('spoilDetailsModal');
    const trashForm = document.getElementById('trashForm');
    const closeBtns = document.querySelectorAll('.close-btn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const productError = document.getElementById('productError');
    const totalLossDisplay = document.getElementById('totalLossDisplay');
    const categoryInput = document.getElementById('category');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const subTabButtons = document.querySelectorAll('.sub-tab-btn');
    const sourceInput = document.getElementById('source');
    const saveBtn = document.getElementById('saveBtn');
    const selectedProductTable = document.getElementById('selectedProductTable');
    const stockLimitProduct = document.getElementById('stockLimitProduct');
    const warningOkBtn = document.querySelector('.warning-ok-btn');
    const deleteConfirmBtn = document.querySelector('.delete-confirm-btn');
    const deleteCancelBtn = document.querySelector('.delete-cancel-btn');

    // Restrict reason input to allowed characters
    const reasonRegex = /^[a-zA-Z\s',&À-ÿ-]+$/;
    reasonInput.addEventListener('input', function () {
        const value = this.value;
        if (value && !reasonRegex.test(value)) {
            reasonError.style.display = 'block';
            reasonInput.setCustomValidity('Only letters, spaces, apostrophes, commas, ampersands, and hyphens are allowed.');
        } else {
            reasonError.style.display = 'none';
            reasonInput.setCustomValidity('');
        }
    });

    // Store all product rows
    const allProductRows = Array.from(document.querySelectorAll('#productTable tbody tr'));

    // Array to track selected products
    let selectedProducts = [];
    let currentTrashId = null;

    // Modal open and close functions
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            const overlay = document.querySelector(`.modal-overlay[data-modal-id="${modalId}"]`);
            if (overlay) {
                overlay.style.display = 'block';
                setTimeout(() => overlay.classList.add('active'), 10); // Ensure transition works
            }
            document.body.classList.add('modal-open'); // Disable scrolling
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const overlay = document.querySelector(`.modal-overlay[data-modal-id="${modalId}"]`);
            if (overlay) {
                overlay.classList.remove('active');
                setTimeout(() => {
                    overlay.style.display = 'none';
                    modal.style.display = 'none';
                    document.body.classList.remove('modal-open'); // Re-enable scrolling
                }, 300); // Match the transition duration
            } else {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        }
    }

    // Close modal when clicking outside
    $(document).on('click', '.modal-overlay', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            const modalId = $(this).data('modal-id');
            closeModal(modalId);
        }
    });

    // Handle spoil row click to show details modal
    $(document).on('click', '.spoil-row', function(e) {
        // Prevent triggering modal if clicking the delete button
        if ($(e.target).closest('.delete-btn').length) {
            return;
        }
        const trashDetails = $(this).data('trash-details');
        const detailsHtml = `
            <div class="spoil-row">
                <span class="spoil-label">ID:</span>
                <span class="spoil-value">${trashDetails.id}</span>
            </div>
            <div class="spoil-row">
                <span class="spoil-label">Product Name:</span>
                <span class="spoil-value">${trashDetails.product_name || 'N/A'}</span>
            </div>
            <div class="spoil-row">
                <span class="spoil-label">Category:</span>
                <span class="spoil-value">${trashDetails.category || 'N/A'}</span>
            </div>
            <div class="spoil-row">
                <span class="spoil-label">Quantity:</span>
                <span class="spoil-value">${trashDetails.quantity || '0'}</span>
            </div>
            <div class="spoil-row reason-row">
                <span class="spoil-label">Reason:</span>
                <span class="spoil-value">${trashDetails.reason || 'N/A'}</span>
            </div>
            <div class="spoil-row">
                <span class="spoil-label">Total Loss:</span>
                <span class="spoil-value">₱${trashDetails.total_loss || '0.00'}</span>
            </div>
            <div class="spoil-row">
                <span class="spoil-label">Date Thrown:</span>
                <span class="spoil-value">${trashDetails.created_at || 'N/A'}</span>
            </div>
        `;
        $('#spoilDetailsContent').html(detailsHtml);
        openModal('spoilDetailsModal');
    });

    // Print spoil details modal
    $('#spoilPrintModal').on('click', function() {
        window.print();
    });

    // Close spoil details modal
    $('#spoilCloseModal').on('click', function() {
        closeModal('spoilDetailsModal');
    });

    // Function to update hidden inputs for form submission
    function updateHiddenInputs() {
        const form = document.getElementById('trashForm');
        form.querySelectorAll('input[name="product_names[]"], input[name="quantities[]"]').forEach(input => input.remove());
        
        selectedProducts.forEach(product => {
            const nameInput = document.createElement('input');
            nameInput.type = 'hidden';
            nameInput.name = 'product_names[]';
            nameInput.value = product.name;
            form.appendChild(nameInput);

            const qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = 'quantities[]';
            qtyInput.value = product.quantity;
            form.appendChild(qtyInput);
        });
    }

    // Function to update selected product table
    function updateSelectedProductTable() {
        const tbody = selectedProductTable.querySelector('tbody');
        tbody.innerHTML = '';

        if (selectedProducts.length > 0) {
            selectedProducts.forEach((product, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.name}</td>
                    <td>${parseFloat(product.price).toFixed(2)}</td>
                    <td>
                        <input type="number" class="selected-qty-input" data-index="${index}" min="1" max="${product.stock}" value="${product.quantity}" style="width:100px;">
                        <span class="stock-info" style="font-size:11px;color:#888;">/ ${product.stock}</span>
                    </td>
                    <td><button type="button" class="remove-product-btn" data-index="${index}">Remove</button></td>
                `;
                tbody.appendChild(row);
            });

            // Quantity input event
            tbody.querySelectorAll('.selected-qty-input').forEach(input => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === '.' || e.key === 'Decimal') {
                        e.preventDefault();
                    }
                });
                input.addEventListener('input', function () {
                    const idx = parseInt(this.getAttribute('data-index'));
                    let val = this.value;
                    // Allow empty input for editing
                    if (val === "") {
                        selectedProducts[idx].quantity = "";
                        validateQuantities();
                        updateTotalLoss();
                        return;
                    }
                    val = parseInt(val);
                    const max = parseInt(this.max);
                    if (isNaN(val)) {
                        selectedProducts[idx].quantity = "";
                    } else if (val < 1) {
                        selectedProducts[idx].quantity = 1;
                        this.value = 1;
                    } else if (val > max) {
                        selectedProducts[idx].quantity = max;
                        this.value = max;
                    } else {
                        selectedProducts[idx].quantity = val;
                    }
                    updateTotalLoss();
                    validateQuantities();
                });
            });

            tbody.querySelectorAll('.remove-product-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const index = parseInt(this.getAttribute('data-index'));
                    selectedProducts.splice(index, 1);
                    updateSelectedProductTable();
                    updateHiddenInputs();
                    updateTotalLoss();
                    validateQuantities();
                    filterProductsByCategory(categoryInput.value, sourceInput.value);
                });
            });
        } else {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="4">No products selected</td>';
            tbody.appendChild(row);
        }
    }

    // Function to filter products by category and source
    function filterProductsByCategory(category, source = sourceInput.value) {
        // Remove manual row show/hide, use DataTables custom filter instead
        productTable.search('').draw();

        // Remove previous custom filter to avoid stacking
        $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
            // Remove any previous filter for this table
            return !fn._isProductCategorySourceFilter;
        });

        // Add new custom filter
        var customFilter = function(settings, data, dataIndex, rowData, counter) {
            // Only apply to productTable
            if (settings.nTable !== productTable.table().node()) return true;
            // Get row node
            var row = productTable.row(dataIndex).node();
            var rowCategory = row.getAttribute('data-category');
            var rowSource = row.getAttribute('data-source');
            if (source === 'all') {
                return (rowCategory === category || !rowCategory);
            } else {
                return (rowCategory === category || !rowCategory) && rowSource === source;
            }
        };
        customFilter._isProductCategorySourceFilter = true;
        $.fn.dataTable.ext.search.push(customFilter);

        productTable.draw();

        // Re-attach select button events
        const selectButtons = document.querySelectorAll('#productTable .select-product-btn');
        selectButtons.forEach(button => {
            if (!button.disabled) {
                button.removeEventListener('click', handleProductSelection);
                button.addEventListener('click', handleProductSelection);
            }
        });

        productError.style.display = 'none';
    }

    // Function to handle product selection
    function handleProductSelection(event) {
        const row = event.target.closest('tr');
        const productName = row.getAttribute('data-product-name');
        const price = parseFloat(row.getAttribute('data-price'));
        const stock = parseInt(row.getAttribute('data-stock'));

        const existingProduct = selectedProducts.find(p => p.name === productName);
        if (existingProduct) {
            if (existingProduct.quantity < stock) {
                existingProduct.quantity += 1;
            } else {
                stockLimitProduct.textContent = productName;
                openModal("stockLimitModal");
                return;
            }
        } else {
            if (stock > 0) {
                selectedProducts.push({
                    name: productName,
                    price: price,
                    quantity: 1,
                    stock: stock
                });
            } else {
                stockLimitProduct.textContent = productName;
                openModal("stockLimitModal");
                return;
            }
        }

        updateSelectedProductTable();
        updateHiddenInputs();
        updateTotalLoss();
        validateQuantities();
        filterProductsByCategory(categoryInput.value, sourceInput.value);
    }

    // Function to calculate and update total loss display
    function updateTotalLoss() {
        const totalLoss = selectedProducts.reduce((sum, product) => {
            return sum + (product.price * product.quantity);
        }, 0);
        totalLossDisplay.textContent = totalLoss > 0 ? `₱${totalLoss.toFixed(2)}` : 'Select products to see total loss.';
    }

    // Validate quantities against stock
    function validateQuantities() {
        let isValid = true;
        selectedProducts.forEach(product => {
            // Quantity must be a number, not empty, >=1, <=stock
            if (
                product.quantity === "" ||
                isNaN(product.quantity) ||
                product.quantity < 1 ||
                product.quantity > product.stock
            ) {
                isValid = false;
            }
        });
        saveBtn.disabled = !isValid || selectedProducts.length === 0;
        return isValid;
    }

    // Tab click event
    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const category = this.getAttribute('data-category');
            categoryInput.value = category;
            filterProductsByCategory(category, sourceInput.value);
        });
    });

    // Sub-tab switching logic
    subTabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            subTabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const source = this.getAttribute('data-source');
            sourceInput.value = source;
            filterProductsByCategory(categoryInput.value, source);
        });
    });

    // Show modal for new trash entry
    document.getElementById('addTrashBtn').addEventListener('click', function () {
        openModal("trashModal");
        trashForm.reset();
        selectedProducts = [];
        categoryInput.value = 'snack';
        totalLossDisplay.textContent = 'Select products to see total loss.';
        loadingSpinner.style.display = 'none';
        saveBtn.disabled = true;
        productError.style.display = 'none';
        reasonError.style.display = 'none';
        reasonInput.setCustomValidity('');

        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabButtons[0].classList.add('active');
        subTabButtons.forEach(btn => btn.classList.remove('active'));
        subTabButtons[0].classList.add('active');
        sourceInput.value = 'inventory';
        filterProductsByCategory('snack', 'inventory');
        updateSelectedProductTable();
        updateHiddenInputs();
        updateTotalLoss();
    });

    // Validate and submit form
    trashForm.addEventListener('submit', function (event) {
        event.preventDefault();

        try {
            const category = categoryInput.value.trim();
            const reason = reasonInput.value.trim();

            if (selectedProducts.length === 0) {
                productError.style.display = 'block';
                alert('Please select at least one product!');
                return;
            }
            if (!category || !reason) {
                alert('Please fill in all fields!');
                return;
            }

            if (!reasonRegex.test(reason)) {
                reasonError.style.display = 'block';
                alert('Reason can only contain letters, spaces, apostrophes, commas, ampersands, and hyphens!');
                return;
            }

            if (!validateQuantities()) {
                alert('One or more products have invalid or empty quantities!');
                return;
            }

            loadingSpinner.style.display = 'block';
            saveBtn.disabled = true;
            trashForm.submit();
        } catch (error) {
            alert('An error occurred while submitting the form. Please try again.');
            loadingSpinner.style.display = 'none';
            saveBtn.disabled = false;
        }
    });

    // Close modals
    closeBtns.forEach(btn => btn.addEventListener('click', () => {
        closeModal(btn.closest('.modal').id);
        loadingSpinner.style.display = 'none';
        saveBtn.disabled = true;
        productError.style.display = 'none';
        reasonError.style.display = 'none';
        reasonInput.setCustomValidity('');
        selectedProducts = [];
        updateSelectedProductTable();
        updateHiddenInputs();
        updateTotalLoss();
    }));

    // Stock Limit Modal OK Button
    warningOkBtn.addEventListener('click', () => {
        closeModal("stockLimitModal");
    });

    // Delete Confirmation Handling
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            currentTrashId = this.getAttribute('data-trash-id');
            openModal("deleteConfirmModal");
        });
    });

    deleteConfirmBtn.addEventListener('click', () => {
        if (currentTrashId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('spoilage.destroy', ':id') }}`.replace(':id', currentTrashId);
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
        closeModal("deleteConfirmModal");
    });

    deleteCancelBtn.addEventListener('click', () => {
        closeModal("deleteConfirmModal");
        currentTrashId = null;
    });

    // Reset filter button logic
    document.getElementById('resetFilterBtn').addEventListener('click', function() {
        const now = new Date();
        document.getElementById('filterMonth').value = now.getMonth() + 1;
        document.getElementById('filterYear').value = now.getFullYear();
        document.getElementById('filterForm').submit();
    });
});
</script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

@endsection
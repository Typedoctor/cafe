@extends('manager.layout')

@section('title', 'Add to Shelf')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/manager-add-to-shelf.css') }}">
@endpush

@section('content')
    <div class="shelf-modal-overlay" data-modal-id="shelfModal"></div>
    <div class="shelf-modal-overlay" data-modal-id="editShelfItemModal"></div>
    <div class="shelf-modal-overlay" data-modal-id="successModal"></div>
    <div class="shelf-modal-overlay" data-modal-id="deleteSuccessModal"></div>
    <div class="shelf-modal-overlay" data-modal-id="confirmDeletionModal"></div>
    <div class="shelf-modal-overlay" data-modal-id="errorModal"></div>
    <div class="shelf-modal-overlay" data-modal-id="warningModal"></div>
    <div class="shelf-modal-overlay" data-modal-id="editErrorModal"></div>
    <div class="shelf-modal-overlay" data-modal-id="priceErrorModal"></div>

    <div id="shelfModal" class="shelf-modal">
        <div class="shelf-add-modal-content">
            <span class="shelf-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
            <h2>Add to Shelf</h2>
            <form id="shelfForm" method="POST" action="{{ route('add-to-shelf.store') }}">
                @csrf
                <div class="shelf-tabs-categ">
                    <button type="button" class="shelf-tab-link-categ active" data-tab="meal">Meal</button>
                    <button type="button" class="shelf-tab-link-categ" data-tab="drink">Drink</button>
                    <button type="button" class="shelf-tab-link-categ" data-tab="dessert">Dessert</button>
                    <button type="button" class="shelf-tab-link-categ" data-tab="snack">Snack</button>
                </div>

                <div id="product-table-container">
                    @foreach (['meal', 'drink', 'dessert', 'snack'] as $category)
                        <div id="{{ $category }}-tab" class="shelf-tab-content" style="display: {{ $category == 'meal' ? 'block' : 'none' }};">
                            <table class="shelf-product-table" id="{{ $category }}-product-table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Stock</th>
                                        <th>Purchase Cost</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products->where('category', $category) as $product)
                                        <tr>
                                            <td>{{ $product->product_name }}</td>
                                            <td style="text-transform: capitalize;">{{ $product->category }}</td>
                                            <td class="{{ $product->quantity == 0 ? 'shelf-out-of-stock' : ($product->quantity <= 2 ? 'product-critical' : ($product->quantity <= 5 ? 'product-low' : '')) }}">{{ $product->quantity }}</td>
                                            <td>₱{{ number_format($product->purchase_cost, 2) }}</td>
                                            <td>
                                                <button type="button" class="shelf-btn shelf-product-add-btn" 
                                                        data-product-id="{{ $product->id }}" 
                                                        data-product-name="{{ $product->product_name }}"
                                                        data-product-stock="{{ $product->quantity }}"
                                                        data-purchase-cost="{{ $product->purchase_cost }}"
                                                        @if($product->quantity == 0) disabled @endif>
                                                    @if($product->quantity == 0) Out of Stock @else Add to Shelf @endif
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($products->where('category', $category)->isEmpty())
                                        <tr>
                                            <td>No {{ $category }} products available.</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>

                <div id="selected-products">
                    <h3>Selected Items</h3>
                    <table class="shelf-selected-products-table" id="selected-products-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Purchase Cost (₱)</th>
                                <th>Price (₱)<br><span class="price-note">Required, greater than purchase cost, max 1200</span></th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="selected-products-body">
                            <!-- Initially empty to avoid column count issues -->
                        </tbody>
                    </table>
                </div>

                <div class="shelf-form-actions">
                    <button type="submit" class="shelf-btn shelf-save-btn" disabled>Add to Shelf</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editShelfItemModal" class="shelf-modal">
        <div class="shelf-edit-modal-content">
            <span class="shelf-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
            <h2>Edit Shelf Item</h2>
            <form id="editShelfForm" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="shelf_item_id" id="edit-shelf-item-id">
                <div class="shelf-form-group">
                    <label for="edit-product-name">Product Name</label>
                    <input type="text" id="edit-product-name" class="shelf-form-input" readonly>
                </div>
                <div class="shelf-form-group">
                    <label for="edit-available-stock">Available Stock</label>
                    <input type="number" id="edit-available-stock" class="shelf-form-input" readonly>
                </div>
                <div class="shelf-form-group">
                    <label for="edit-purchase-cost">Purchase Cost (₱)</label>
                    <input type="number" id="edit-purchase-cost" class="shelf-form-input" readonly>
                </div>
                <div class="shelf-form-group">
                    <label for="edit-quantity-added">Quantity Added</label>
                    <input type="number" name="quantity_added" id="edit-quantity-added" class="shelf-form-input" min="1">
                </div>
                <div class="shelf-form-group">
                    <label for="edit-price">Price (₱)</label>
                    <input type="number" name="price" id="edit-price" class="shelf-form-input" step="0.01" min="0" max="1200" required>
                </div>
                <div class="shelf-form-actions">
                    <button type="submit" class="shelf-btn shelf-save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="successModal" class="shelf-modal-success">
        <p id="successModalMessage"></p>
    </div>

    <div id="deleteSuccessModal" class="shelf-delete-modal-success">
        <p id="deleteSuccessModalMessage"></p>
    </div>

    <div id="confirmDeletionModal" class="confirm-deletion-modal">
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this shelf item?</p>
        <button type="button" class="modal-btn cancel-btn">Cancel</button>
        <button type="button" class="modal-btn delete-btn">Delete</button>
    </div>

    <div id="errorModal" class="shelf-error-modal">
        <p id="errorModalMessage"></p>
    </div>

    <div id="warningModal" class="shelf-warning-modal">
        <p id="warningModalMessage"></p>
    </div>

    <div id="editErrorModal" class="shelf-error-modal">
        <p id="editErrorModalMessage"></p>
    </div>

    <div id="priceErrorModal" class="shelf-error-modal">
        <p id="priceErrorModalMessage"></p>
    </div>

    <div class="rep-metric-container">
        <div class="rep-metric-box">
            <div class="rep-metric-title">Total Shelfed Items</div>
            <div class="rep-metric-value">{{ $shelfItems->count() }}</div>
        </div>
        <div class="rep-metric-box rep-metric-box--low">
            <div class="rep-metric-title">Low Stock Items</div>
            <div class="rep-metric-value">{{ $shelfItems->whereBetween('quantity_added', [3, 5])->count() }}</div>
        </div>
        <div class="rep-metric-box rep-metric-box--critical">
            <div class="rep-metric-title">Critical Stock Items</div>
            <div class="rep-metric-value">{{ $shelfItems->where('quantity_added', '<=', 2)->count() }}</div>
        </div>
    </div>
    <div class="shelf-table-container">
        <div class="shelf-section-title">Shelfed Items</div>
        <div class="category-filter-wrapper">
            <div class="category-filter">
                <select id="shelfCategoryFilter">
                    <option value="">All Categories</option>
                    <option value="meal">Meal</option>
                    <option value="drink">Drink</option>
                    <option value="dessert">Dessert</option>
                    <option value="snack">Snack</option>
                </select>
            </div>
            <div class="shelf-top-bar">
                <button id="openModalBtn" class="shelf-btn shelf-add-btn">+ Add to Shelf</button>
            </div>
        </div>
        <table class="shelf-items-table" id="shelfItemsTable">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Purchase Cost</th>
                    <th>Profit</th>
                    <th>Quantity Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shelfItems as $item)
                <tr data-category="{{ $item->product->category }}">
                    <td>{{ $item->product->product_name }}</td>
                    <td style="text-transform: capitalize;">{{ $item->product->category }}</td>
                    <td>₱{{ number_format($item->price, 2) }}</td>
                    <td>₱{{ number_format($item->product->purchase_cost, 2) }}</td>
                    <td>₱{{ number_format($item->price - $item->product->purchase_cost, 2) }}</td>
                    <td
                        class="@if($item->quantity_added == 0 || $item->quantity_added == 1 || $item->quantity_added == 2)
                                    product-critical
                                @elseif($item->quantity_added >= 3 && $item->quantity_added <= 5)
                                    product-low
                                @endif"
                    >
                        {{ $item->quantity_added }}
                    </td>
                    <td>
                        <button type="button" class="shelf-btn shelf-edit-btn" 
                                data-shelf-item-id="{{ $item->id }}"
                                data-product-name="{{ $item->product->product_name }}"
                                data-quantity-added="{{ $item->quantity_added }}"
                                data-price="{{ $item->price ?? '' }}"
                                data-product-id="{{ $item->product_id }}"
                                data-available-stock="{{ $item->product->quantity }}"
                                data-purchase-cost="{{ $item->product->purchase_cost }}">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <button type="button" class="shelf-btn shelf-delete-btn" data-shelf-item-id="{{ $item->id }}">
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

        const SUCCESS_MODAL_DURATION = 2000;
        const WARNING_MODAL_DURATION = 3000;
        let idx = 0;
        let selectedProductsTable = null;
        let productTables = {};

        $(document).ready(() => {
            // Initialize shelf items table ONCE
            const shelfItemsTable = $('#shelfItemsTable').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100, 250, 500],
                responsive: true,
                order: [[0, 'asc']],
                searching: true,
                columnDefs: [{ orderable: false, targets: 6 }],
                language: {
                    search: "Search shelfed items:",
                    emptyTable: "No shelfed items available."
                }
            });

            // Restore shelf category filter from localStorage
            const savedCategory = localStorage.getItem('shelfCategoryFilter');
            if (savedCategory) {
                $('#shelfCategoryFilter').val(savedCategory);
                shelfItemsTable.column(1).search(savedCategory).draw();
            }

            // Category filter for shelfed items
            $('#shelfCategoryFilter').on('change', function() {
                let value = this.value;
                // Save selected category to localStorage
                localStorage.setItem('shelfCategoryFilter', value);
                shelfItemsTable.column(1).search(value).draw();
            });

            // Initialize product tables for each category
            const categories = ['meal', 'drink', 'dessert', 'snack'];
            categories.forEach(category => {
                productTables[category] = $(`#${category}-product-table`).DataTable({
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50, 100,],
                    responsive: true,
                    searching: true,
                    ordering: true,
                    columnDefs: [{ orderable: false, targets: 4 }],
                    language: {
                        search: "Search products:",
                        emptyTable: `No ${category} products available.`
                    }
                });
            });

            // Initialize selected products table
            initializeSelectedProductsTable();

            // Update submit button state
            updateSubmitButton();

            // Update metrics
            updateMetrics();
        });

        const initializeSelectedProductsTable = () => {
            try {
                if (selectedProductsTable) {
                    selectedProductsTable.destroy();
                }
                selectedProductsTable = $('#selected-products-table').DataTable({
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50, 100,],
                    responsive: true,
                    searching: true,
                    ordering: true,
                    columnDefs: [{ orderable: false, targets: 4 }],
                    language: {
                        search: "Search selected products:",
                        emptyTable: "No products selected."
                    }
                });
            } catch (e) {
                console.error('Error initializing selected products table:', e);
            }
        };

        const updateMetrics = () => {
            try {
                $.ajax({
                    url: "{{ route('shelf.metrics') }}",
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
            } catch (e) {
                console.error('Error updating metrics:', e);
            }
        };

        const openModal = (modalId, clearErrors = false) => {
            try {
                const modal = document.getElementById(modalId);
                if (!modal) {
                    console.error(`Modal with ID ${modalId} not found`);
                    return;
                }
                modal.style.display = 'block';
                const overlay = document.querySelector(`.shelf-modal-overlay[data-modal-id="${modalId}"]`);
                if (overlay) {
                    overlay.style.display = 'block';
                }
                if (modalId === 'shelfModal') {
                    document.body.classList.add('modal-open'); // Disable scrolling
                    const activeTab = document.querySelector('.shelf-tab-link-categ.active')?.dataset.tab;
                    if (activeTab && productTables[activeTab]) {
                        productTables[activeTab].draw();
                    }
                    updateSubmitButton();
                }
            } catch (e) {
                console.error(`Error opening modal ${modalId}:`, e);
            }
        };

        const closeModal = (modalId) => {
            try {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                modal.style.display = 'none';
                const overlay = document.querySelector(`.shelf-modal-overlay[data-modal-id="${modalId}"]`);
                if (overlay) {
                    overlay.style.display = 'none';
                }
                if (modalId === 'shelfModal') {
                    document.body.classList.remove('modal-open'); // Re-enable scrolling
                    idx = 0;
                    $('#selected-products-body').empty();
                    initializeSelectedProductsTable();
                    updateSubmitButton();
                }
            } catch (e) {
                console.error(`Error closing modal ${modalId}:`, e);
            }
        };

        const handleModalClose = (target) => {
            try {
                const closeBtn = target.closest('.shelf-close-btn');
                const modal = target.closest('.shelf-modal, .shelf-modal-success, .shelf-delete-modal-success, .confirm-deletion-modal, .shelf-error-modal, .shelf-warning-modal');
                if (closeBtn || (modal && target === modal) || target.classList.contains('cancel-btn')) {
                    closeModal(modal?.id);
                }
            } catch (e) {
                console.error('Error handling modal close:', e);
            }
        };

        const showSuccessModal = (msg, modalId = 'successModal') => {
            try {
                const messageElement = document.getElementById(modalId === 'successModal' ? 'successModalMessage' : 'deleteSuccessModalMessage');
                messageElement.textContent = msg;
                openModal(modalId);
                setTimeout(() => {
                    closeModal(modalId);
                    // Reload the page, preserving the filter
                    location.reload();
                }, SUCCESS_MODAL_DURATION);
            } catch (e) {
                console.error(`Error showing success modal ${modalId}:`, e);
            }
        };

        const showErrorModal = (msg, modalId = 'errorModal') => {
            try {
                const messageElement = document.getElementById(modalId === 'errorModal' ? 'errorModalMessage' : (modalId === 'editErrorModal' ? 'editErrorModalMessage' : 'priceErrorModalMessage'));
                messageElement.innerHTML = msg; // Use innerHTML to support lists
                openModal(modalId);
                setTimeout(() => {
                    closeModal(modalId);
                }, WARNING_MODAL_DURATION);
            } catch (e) {
                console.error(`Error showing error modal ${modalId}:`, e);
            }
        };

        const showWarningModal = (msg) => {
            try {
                const messageElement = document.getElementById('warningModalMessage');
                messageElement.textContent = msg;
                openModal('warningModal');
                setTimeout(() => {
                    closeModal('warningModal');
                }, WARNING_MODAL_DURATION);
            } catch (e) {
                console.error('Error showing warning modal:', e);
            }
        };

        const updateSubmitButton = () => {
            try {
                const submitBtn = document.querySelector('.shelf-save-btn');
                let hasError = false;

                selectedProductsTable.rows().every(function() {
                    const row = this.node();
                    const priceInput = row.querySelector('input[name$="[price]"]');
                    const qtyInput = row.querySelector('input[name$="[quantity_added]"]');
                    const priceError = row.querySelector('.shelf-price-error');
                    const qtyError = row.querySelector('.shelf-quantity-error');
                    const purchaseCost = parseFloat(row.dataset.purchaseCost || row.querySelector('input[name$="[purchase_cost]"]')?.value || 0);

                    if (!priceInput || !qtyInput) {
                        hasError = true;
                        return true;
                    }

                    const price = parseFloat(priceInput.value);
                    const qty = parseInt(qtyInput.value) || 0;
                    const maxStock = parseInt(row.dataset.productStock) || 0;

                    if (!price || isNaN(price) || price <= purchaseCost || price > 1200) {
                        priceInput.classList.add('price-error');
                        priceError.style.display = 'block';
                        hasError = true;
                    } else {
                        priceInput.classList.remove('price-error');
                        priceError.style.display = 'none';
                    }

                    if (qty > maxStock || qty <= 0) {
                        qtyInput.classList.add('quantity-error');
                        qtyError.style.display = 'block';
                        hasError = true;
                    } else {
                        qtyInput.classList.remove('quantity-error');
                        qtyError.style.display = 'none';
                    }

                    return true;
                });

                const rowCount = selectedProductsTable.rows().count();
                submitBtn.disabled = hasError || rowCount === 0;
            } catch (e) {
                console.error('Error updating submit button:', e);
            }
        };

        document.querySelectorAll('.shelf-tab-link-categ').forEach(btn => {
            btn.addEventListener('click', () => {
                try {
                    document.querySelectorAll('.shelf-tab-link-categ').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    document.querySelectorAll('.shelf-tab-content').forEach(c => {
                        c.style.display = c.id === `${btn.dataset.tab}-tab` ? 'block' : 'none';
                    });
                    if (productTables[btn.dataset.tab]) {
                        productTables[btn.dataset.tab].draw();
                    }
                } catch (e) {
                    console.error('Error handling tab click:', e);
                }
            });
        });

        document.querySelectorAll('.shelf-product-add-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                try {
                    const { productId, productName, productStock, purchaseCost } = btn.dataset;
                    addProduct(productId, productName, productStock, purchaseCost);
                } catch (e) {
                    console.error('Error handling add button click:', e);
                }
            });
        });

        const addProduct = (id, name, stock, purchaseCost) => {
            try {
                if (parseInt(stock) === 0) {
                    showWarningModal(`Cannot add "${name}". Out of stock.`);
                    return;
                }

                let existingRow = null;
                let existingRowNode = null;
                selectedProductsTable.rows().every(function() {
                    const rowNode = this.node();
                    const productIdInput = rowNode.querySelector('input[name$="[product_id]"]');
                    if (productIdInput && productIdInput.value === id) {
                        existingRow = this;
                        existingRowNode = rowNode;
                        return false;
                    }
                    return true;
                });

                if (existingRow) {
                    const qtyInput = existingRowNode.querySelector('input[name$="[quantity_added]"]');
                    const currentQty = parseInt(qtyInput.value) || 1;
                    const maxStock = parseInt(stock);
                    if (currentQty < maxStock) {
                        qtyInput.value = currentQty + 1;
                        updateSubmitButton();
                    } else {
                        showWarningModal(`Cannot add more "${name}". Maximum stock (${maxStock}) reached.`);
                    }
                    return;
                }

                $.ajax({
                    url: '{{ route("add-to-shelf.check") }}',
                    method: 'POST',
                    data: { product_id: id, _token: '{{ csrf_token() }}' },
                    success: (res) => {
                        try {
                            const existingPrice = res.exists ? (res.price || '') : '';
                            const cost = purchaseCost ?? (res.purchase_cost ?? 0);
                            const newRow = `
                                <tr data-product-stock="${stock}" data-purchase-cost="${cost}">
                                    <td style="text-align: center;">${name}</td>
                                    <td style="text-align: center;">₱${parseFloat(cost).toFixed(2)}
                                        <input type="hidden" name="items[${idx}][purchase_cost]" value="${cost}">
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="number" name="items[${idx}][price]" step="0.01" min="${parseFloat(cost) + 0.01}" max="1200" class="shelf-form-input" value="${existingPrice}" required>
                                        <span class="shelf-price-error" style="display: none;">Price is required, must be greater than purchase cost, max 1200</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="number" name="items[${idx}][quantity_added]" min="1" max="${stock}" value="1" required class="shelf-form-input">
                                        <input type="hidden" name="items[${idx}][product_id]" value="${id}">
                                        <div class="shelf-quantity-error" style="display: none;">Cannot add more than available stock for ${name}. Stock: ${stock}</div>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="shelf-btn shelf-product-remove-btn">Remove</button>
                                    </td>
                                </tr>
                            `;
                            const $row = $(newRow);
                            if ($row.find('td').length === 5) {
                                selectedProductsTable.row.add($row[0]).draw();
                                idx++;
                                updateSubmitButton();
                            } else {
                                console.error('Invalid row structure: incorrect number of columns');
                                showWarningModal('Failed to add product: invalid row structure.');
                            }
                        } catch (e) {
                            console.error('Error adding row to DataTable:', e);
                            showWarningModal('Failed to add product to table.');
                        }
                    },
                    error: (xhr) => {
                        console.error('Error checking product status:', xhr.responseText);
                        showWarningModal(xhr.responseJSON?.message || 'Failed to check product status.');
                    }
                });
            } catch (e) {
                console.error('Error in addProduct:', e);
                showWarningModal('Failed to add product.');
            }
        };

        document.addEventListener('click', (e) => {
            try {
                const tgt = e.target;

                if (tgt.classList.contains('shelf-product-remove-btn')) {
                    const row = tgt.closest('tr');
                    selectedProductsTable.row(row).remove().draw();
                    updateSubmitButton();
                } else if (tgt.closest('.shelf-delete-btn')) {
                    const id = $(tgt.closest('.shelf-delete-btn')).data('shelfItemId');
                    document.getElementById('confirmDeletionModal').dataset.shelfItemId = id;
                    openModal('confirmDeletionModal');
                } else if (tgt.closest('.shelf-edit-btn')) {
                    const btn = $(tgt.closest('.shelf-edit-btn'));
                    const { shelfItemId, productName, quantityAdded, price, productId, availableStock, purchaseCost } = btn.data();
                    document.getElementById('edit-shelf-item-id').value = shelfItemId;
                    document.getElementById('edit-product-name').value = productName;
                    document.getElementById('edit-available-stock').value = availableStock;
                    document.getElementById('edit-quantity-added').value = quantityAdded;
                    document.getElementById('edit-price').value = price || '';
                    document.getElementById('edit-purchase-cost').value = purchaseCost !== undefined ? parseFloat(purchaseCost).toFixed(2) : '';
                    document.getElementById('editShelfForm').action = `/add-to-shelf/${shelfItemId}`;

                    // Highlight available stock input based on value
                    const stockInput = document.getElementById('edit-available-stock');
                    stockInput.classList.remove('stock-critical', 'stock-low');
                    const stockVal = parseInt(availableStock, 10);
                    if (stockVal >= 0 && stockVal <= 2) {
                        stockInput.classList.add('stock-critical');
                    } else if (stockVal >= 3 && stockVal <= 5) {
                        stockInput.classList.add('stock-low');
                    }

                    openModal('editShelfItemModal', true);
                } else if (tgt.id === 'openModalBtn') {
                    openModal('shelfModal');
                } else if (tgt.classList.contains('delete-btn')) {
                    const id = document.getElementById('confirmDeletionModal').dataset.shelfItemId;
                    $.ajax({
                        url: `/add-to-shelf/${id}`,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: (res) => {
                            closeModal('confirmDeletionModal');
                            showSuccessModal(res.message, 'deleteSuccessModal');
                            updateMetrics();
                        },
                        error: (xhr) => {
                            console.error('Error deleting shelf item:', xhr.responseText);
                            showWarningModal(xhr.responseJSON?.message || 'Failed to delete item.');
                        }
                    });
                } else {
                    handleModalClose(tgt);
                }
            } catch (e) {
                console.error('Error handling click event:', e);
            }
        });

        document.getElementById('editShelfForm').addEventListener('submit', (e) => {
            e.preventDefault();
            try {
                const form = e.target;
                const formData = new FormData(form);
                $.ajax({
                    url: form.action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (res) => {
                        closeModal('editShelfItemModal');
                        showSuccessModal(res.message);
                        updateMetrics();
                    },
                    error: (xhr) => {
                        const errors = xhr.responseJSON?.errors || { error: [xhr.responseJSON?.message || 'Failed to update item.'] };
                        const errMsg = Object.values(errors).flat().map(error => `<li>${error}</li>`).join('');
                        showErrorModal(`<ul>${errMsg}</ul>`, 'editErrorModal');
                    }
                });
            } catch (e) {
                console.error('Error submitting edit form:', e);
            }
        });

        document.getElementById('shelfForm').addEventListener('submit', (e) => {
            e.preventDefault();
            try {
                const form = e.target;
                const items = selectedProductsTable.rows().nodes();

                if (!items.length) {
                    showWarningModal('Please add at least one product to the shelf.');
                    return;
                }

                let hasError = false;
                items.each(function(row) {
                    const priceInput = row.querySelector('input[name$="[price]"]');
                    const qtyInput = row.querySelector('input[name$="[quantity_added]"]');
                    const priceError = row.querySelector('.shelf-price-error');
                    const qtyError = row.querySelector('.shelf-quantity-error');
                    const purchaseCost = parseFloat(row.dataset.purchaseCost || row.querySelector('input[name$="[purchase_cost]"]')?.value || 0);

                    if (!priceInput || !qtyInput) {
                        hasError = true;
                        return;
                    }

                    const price = parseFloat(priceInput.value);
                    const qty = parseInt(qtyInput.value) || 0;
                    const maxStock = parseInt(row.dataset.productStock);

                    if (!price || isNaN(price) || price <= purchaseCost || price > 1200) {
                        priceInput.classList.add('price-error');
                        priceError.style.display = 'block';
                        hasError = true;
                    } else {
                        priceInput.classList.remove('price-error');
                        priceError.style.display = 'none';
                    }

                    if (qty > maxStock || qty <= 0) {
                        qtyInput.value = qty > maxStock ? maxStock : 1;
                        qtyInput.classList.add('quantity-error');
                        qtyError.style.display = 'block';
                        hasError = true;
                    } else {
                        qtyInput.classList.remove('quantity-error');
                        qtyError.style.display = 'none';
                    }
                });

                if (hasError) {
                    showErrorModal('Fix price (required, greater than purchase cost, max 1200) or quantity (within stock) errors.', 'priceErrorModal');
                    return;
                }

                $.ajax({
                    url: form.action,
                    method: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    success: (res) => {
                        closeModal('shelfModal');
                        showSuccessModal(res.message);
                        updateMetrics();
                    },
                    error: (xhr) => {
                        const errors = xhr.responseJSON?.errors || { error: [xhr.responseJSON?.message || 'Failed to add items to shelf.'] };
                        const errMsg = Object.values(errors).flat().map(error => `<li>${error}</li>`).join('');
                        showErrorModal(`<ul>${errMsg}</ul>`);
                        openModal('shelfModal');
                    }
                });
            } catch (e) {
                console.error('Error submitting shelf form:', e);
            }
        });

        document.getElementById('selected-products').addEventListener('input', (e) => {
            try {
                if (e.target.type !== 'number') return;
                const input = e.target;
                const row = input.closest('tr');

                if (input.name.includes('quantity_added') && row) {
                    // ...existing code...
                } else if (input.name.includes('price')) {
                    const val = parseFloat(input.value);
                    const priceError = row.querySelector('.shelf-price-error');
                    const purchaseCost = parseFloat(row.dataset.purchaseCost || row.querySelector('input[name$="[purchase_cost]"]')?.value || 0);
                    if (isNaN(val) || val <= purchaseCost) {
                        input.classList.add('price-error');
                        priceError.style.display = 'block';
                    } else if (val > 1200) {
                        input.value = 1200;
                        input.classList.add('price-error');
                        priceError.style.display = 'block';
                        showWarningModal(`Price must be greater than purchase cost and max 1200 for "${row.querySelector('td').textContent}".`);
                    } else {
                        input.classList.remove('price-error');
                        priceError.style.display = 'none';
                    }
                }
                updateSubmitButton();
            } catch (e) {
                console.error('Error handling input event:', e);
            }
        });

        document.getElementById('selected-products').addEventListener('blur', (e) => {
            try {
                if (e.target.type !== 'number') return;
                const input = e.target;
                const row = input.closest('tr');
                if (!row) return;

                if (input.name.includes('quantity_added')) {
                    // ...existing code...
                } else if (input.name.includes('price')) {
                    const val = parseFloat(input.value);
                    const purchaseCost = parseFloat(row.dataset.purchaseCost || row.querySelector('input[name$="[purchase_cost]"]')?.value || 0);
                    if (isNaN(val) || val <= purchaseCost) {
                        input.value = (parseFloat(purchaseCost) + 0.01).toFixed(2);
                        showWarningModal(`Price must be greater than purchase cost (₱${purchaseCost.toFixed(2)}) for "${row.querySelector('td').textContent}".`);
                    } else if (val > 1200) {
                        input.value = 1200;
                        showWarningModal(`Price must be greater than purchase cost and max 1200 for "${row.querySelector('td').textContent}".`);
                    }
                }
                updateSubmitButton();
            } catch (e) {
                console.error('Error handling blur event:', e);
            }
        }, true);
    </script>
@endpush
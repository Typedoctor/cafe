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

    <div id="shelfModal" class="shelf-modal">
        <div class="shelf-add-modal-content">
            <span class="shelf-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
            <h2>Add to Shelf</h2>
            <form id="shelfForm" method="POST" action="{{ route('add-to-shelf.store') }}">
                @csrf
                <div id="shelf-error-message" class="shelf-error-message" style="display: none;">
                    <ul class="shelf-error-list"></ul>
                </div>

                <div id="shelf-warning-message" class="shelf-warning-message">
                    <span class="close-warning">×</span>
                    <span id="warning-text"></span>
                </div>

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
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products->where('category', $category) as $product)
                                        <tr>
                                            <td>{{ $product->product_name }}</td>
                                            <td class="{{ $product->quantity == 0 ? 'shelf-out-of-stock' : ($product->quantity <= 2 ? 'product-critical' : ($product->quantity <= 5 ? 'product-low' : '')) }}">{{ $product->quantity }}</td>
                                            <td>
                                                <button type="button" class="shelf-btn shelf-product-add-btn" 
                                                        data-product-id="{{ $product->id }}" 
                                                        data-product-name="{{ $product->product_name }}"
                                                        data-product-stock="{{ $product->quantity }}"
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
                                <th>Price (₱)<br><span class="price-note">Required, max 1200</span></th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="selected-products-body"></tbody>
                    </table>
                    <div id="price-error-message" class="shelf-error-message-inline" style="display: none;"></div>
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
                <div id="edit-error-message" class="shelf-error-message" style="display: none;">
                    <ul class="shelf-error-list"></ul>
                </div>
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
                    <label for="edit-quantity-added">Quantity Added</label>
                    <input type="number" name="quantity_added" id="edit-quantity-added" class="shelf-form-input" min="1" required>
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

    <div class="shelf-table-container">
        <div class="shelf-section-title">Shelfed Items</div>
        <div class="rep-metric-container">
            <div class="rep-metric-box">
                <div class="rep-metric-title">Total Items</div>
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
        <div class="shelf-top-bar">
            <button id="openModalBtn" class="shelf-btn shelf-add-btn">+ Add to Shelf</button>
        </div>
        <table class="shelf-items-table" id="shelfItemsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Price (₱)</th>
                    <th>Quantity Added</th>
                    <th>Category</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shelfItems as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->product->product_name }}</td>
                        <td>{{ $item->price ? number_format($item->price, 2) : 'N/A' }}</td>
                        <td class="{{ $item->quantity_added == 0 ? 'shelf-out-of-stock' : ($item->quantity_added <= 2 ? 'product-critical' : ($item->quantity_added >= 3 && $item->quantity_added <= 5 ? 'product-low' : '')) }}">{{ $item->quantity_added }}</td>
                        <td>{{ $item->product->category }}</td>
                        <td>
                            <button type="button" class="shelf-btn shelf-edit-btn" 
                                    data-shelf-item-id="{{ $item->id }}"
                                    data-product-name="{{ $item->product->product_name }}"
                                    data-quantity-added="{{ $item->quantity_added }}"
                                    data-price="{{ $item->price ?? '' }}"
                                    data-product-id="{{ $item->product_id }}"
                                    data-available-stock="{{ $item->product->quantity }}">
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
        const SUCCESS_MODAL_DURATION = 2000;
        let idx = 0;
        let selectedProductsTable = null;
        let productTables = {};

        $(document).ready(() => {
            $('#shelfItemsTable').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                responsive: true,
                order: [[0, 'asc']],
                searching: true,
                columnDefs: [{ orderable: false, targets: 5 }],
                language: {
                    search: "Search shelfed items:",
                    emptyTable: "No shelfed items available."
                }
            });

            const categories = ['meal', 'drink', 'dessert', 'snack'];
            categories.forEach(category => {
                productTables[category] = $(`#${category}-product-table`).DataTable({
                    pageLength: 5,
                    lengthMenu: [5, 10, 25, 50, 100],
                    responsive: true,
                    searching: true,
                    ordering: true,
                    columnDefs: [{ orderable: false, targets: 2 }],
                    language: {
                        search: "Search products:",
                        emptyTable: "No products available."
                    }
                });
            });

            initializeSelectedProductsTable();

            document.querySelector('.close-warning')?.addEventListener('click', () => {
                document.getElementById('shelf-warning-message').style.display = 'none';
            });

            updateSubmitButton();

            updateMetrics();
        });

        const initializeSelectedProductsTable = () => {
            if (selectedProductsTable) {
                selectedProductsTable.destroy();
            }
            selectedProductsTable = $('#selected-products-table').DataTable({
                pageLength: 5,
                responsive: true,
                searching: true,
                ordering: true,
                columnDefs: [{ orderable: false, targets: 3 }],
                language: {
                    search: "Search selected products:",
                    emptyTable: "No products selected."
                }
            });
        };

        const updateMetrics = () => {
            $.ajax({
                url: "{{ route('shelf.metrics') }}",
                type: "GET",
                success: function(data) {
                    $('.rep-metric-value').eq(0).text(data.totalItems);
                    $('.rep-metric-value').eq(1).text(data.lowStockItems);
                    $('.rep-metric-value').eq(2).text(data.criticalStockItems);
                },
                error: function() {
                    console.error('Failed to update metric boxes');
                }
            });
        };

        const openModal = (modalId, clearErrors = false) => {
            document.getElementById(modalId).style.display = 'block';
            if (['shelfModal', 'editShelfItemModal', 'successModal', 'deleteSuccessModal', 'confirmDeletionModal'].includes(modalId)) {
                document.querySelector(`.shelf-modal-overlay[data-modal-id="${modalId}"]`).style.display = 'block';
            }
            if (clearErrors && modalId === 'editShelfItemModal') {
                const errDiv = document.getElementById('edit-error-message');
                errDiv.style.display = 'none';
                errDiv.querySelector('ul').innerHTML = '';
            }
            if (modalId === 'shelfModal') {
                const activeTab = document.querySelector('.shelf-tab-link-categ.active').dataset.tab;
                productTables[activeTab]?.draw();
                updateSubmitButton();
            }
        };

        const closeModal = (modalId) => {
            document.getElementById(modalId).style.display = 'none';
            if (['shelfModal', 'editShelfItemModal', 'successModal', 'deleteSuccessModal', 'confirmDeletionModal'].includes(modalId)) {
                document.querySelector(`.shelf-modal-overlay[data-modal-id="${modalId}"]`).style.display = 'none';
            }
            if (modalId === 'shelfModal') {
                idx = 0;
                $('#selected-products-body').empty();
                initializeSelectedProductsTable();
                updateSubmitButton();
            }
        };

        const handleModalClose = (target) => {
            const closeBtn = target.closest('.shelf-close-btn');
            const modal = target.closest('.shelf-modal, .shelf-modal-success, .shelf-delete-modal-success, .confirm-deletion-modal');
            if (closeBtn || (modal && target === modal) || target.classList.contains('cancel-btn')) {
                closeModal(modal.id);
            }
        };

        const showSuccessModal = (msg, modalId = 'successModal') => {
            document.getElementById(modalId === 'successModal' ? 'successModalMessage' : 'deleteSuccessModalMessage').textContent = msg;
            openModal(modalId);
            setTimeout(() => {
                closeModal(modalId);
                location.reload();
            }, SUCCESS_MODAL_DURATION);
        };

        const showWarning = (msg) => {
            const warning = document.getElementById('shelf-warning-message');
            document.getElementById('warning-text').textContent = msg;
            warning.style.display = 'block';
            setTimeout(() => warning.style.display = 'none', 3000);
        };

        const hideErrorMessage = () => {
            const errorDiv = document.getElementById('price-error-message');
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';
        };

        const updateSubmitButton = () => {
            const submitBtn = document.querySelector('.shelf-save-btn');
            let hasError = false;

            selectedProductsTable.rows().every(function() {
                const row = this.node();
                const priceInput = row.querySelector('input[name$="[price]"]');
                const qtyInput = row.querySelector('input[name$="[quantity_added]"]');
                const priceError = row.querySelector('.shelf-price-error');
                const qtyError = row.querySelector('.shelf-quantity-error');

                if (!priceInput || !qtyInput) {
                    hasError = true;
                    return true;
                }

                const price = parseFloat(priceInput.value);
                const qty = parseInt(qtyInput.value) || 0;
                const maxStock = parseInt(row.dataset.productStock) || 0;

                if (!price || isNaN(price) || price <= 0 || price > 1200) {
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
        };

        document.querySelectorAll('.shelf-tab-link-categ').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.shelf-tab-link-categ').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                document.querySelectorAll('.shelf-tab-content').forEach(c => {
                    c.style.display = c.id === `${btn.dataset.tab}-tab` ? 'block' : 'none';
                });
                productTables[btn.dataset.tab]?.draw();
            });
        });

        document.querySelectorAll('.shelf-product-add-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const { productId, productName, productStock } = btn.dataset;
                addProduct(productId, productName, productStock);
            });
        });

        const addProduct = (id, name, stock) => {
            if (parseInt(stock) === 0) {
                showWarning(`Cannot add "${name}". Out of stock.`);
                return;
            }

            // Check if product is already in the table
            let existingRow = null;
            let existingRowNode = null;
            selectedProductsTable.rows().every(function() {
                const rowNode = this.node();
                const productIdInput = rowNode.querySelector('input[name$="[product_id]"]');
                if (productIdInput && productIdInput.value === id) {
                    existingRow = this;
                    existingRowNode = rowNode;
                    return false; // Break the loop
                }
                return true;
            });

            if (existingRow) {
                // Increment quantity if within stock
                const qtyInput = existingRowNode.querySelector('input[name$="[quantity_added]"]');
                const currentQty = parseInt(qtyInput.value) || 1;
                const maxStock = parseInt(stock);
                if (currentQty < maxStock) {
                    qtyInput.value = currentQty + 1;
                    updateSubmitButton();
                } else {
                    showWarning(`Cannot add more "${name}". Maximum stock (${maxStock}) reached.`);
                }
                return;
            }

            // Add new row if product not in table
            $.ajax({
                url: '{{ route("add-to-shelf.check") }}',
                method: 'POST',
                data: { product_id: id, _token: '{{ csrf_token() }}' },
                success: (res) => {
                    const existingPrice = res.exists ? (res.price || '') : '';
                    const newRow = `
                        <tr data-product-stock="${stock}">
                            <td style="text-align: center;">${name}</td>
                            <td style="text-align: center;">
                                <input type="number" name="items[${idx}][price]" step="0.01" min="0" max="1200" class="shelf-form-input" value="${existingPrice}" required>
                                <span class="shelf-price-error" style="display: none;">Price is required, 0-1200</span>
                            </td>
                            <td style="text-align: center;">
                                <input type="number" name="items[${idx}][quantity_added]" min="1" max="${stock}" value="1" required class="shelf-form-input">
                                <input type="hidden" name="items[${idx}][product_id]" value="${id}">
                                <div class="shelf-quantity-error">Cannot add more than available stock for ${name}. Stock: ${stock}</div>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="shelf-btn shelf-product-remove-btn">Remove</button>
                            </td>
                        </tr>
                    `;
                    selectedProductsTable.row.add($(newRow)[0]).draw();
                    idx++;
                    updateSubmitButton();
                },
                error: (xhr) => {
                    showWarning(xhr.responseJSON?.message || 'Failed to check product status.');
                }
            });
        };

        document.addEventListener('click', (e) => {
            const tgt = e.target;

            if (tgt.classList.contains('shelf-product-remove-btn')) {
                const row = tgt.closest('tr');
                selectedProductsTable.row(row).remove().draw();
                hideErrorMessage();
                updateSubmitButton();
            } else if (tgt.closest('.shelf-delete-btn')) {
                const id = $(tgt.closest('.shelf-delete-btn')).data('shelfItemId');
                document.getElementById('confirmDeletionModal').dataset.shelfItemId = id;
                openModal('confirmDeletionModal');
            } else if (tgt.closest('.shelf-edit-btn')) {
                const btn = $(tgt.closest('.shelf-edit-btn'));
                const { shelfItemId, productName, quantityAdded, price, productId, availableStock } = btn.data();
                document.getElementById('edit-shelf-item-id').value = shelfItemId;
                document.getElementById('edit-product-name').value = productName;
                document.getElementById('edit-available-stock').value = availableStock;
                document.getElementById('edit-quantity-added').value = quantityAdded;
                document.getElementById('edit-price').value = price || '';
                document.getElementById('editShelfForm').action = '{{ route("add-to-shelf.update", ":id") }}'.replace(':id', shelfItemId);
                openModal('editShelfItemModal', true);
            } else if (tgt.id === 'openModalBtn') {
                openModal('shelfModal');
            } else if (tgt.classList.contains('delete-btn')) {
                const id = document.getElementById('confirmDeletionModal').dataset.shelfItemId;
                $.ajax({
                    url: '{{ route("add-to-shelf.destroy", ":id") }}'.replace(':id', id),
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: (res) => {
                        closeModal('confirmDeletionModal');
                        showSuccessModal(res.message, 'deleteSuccessModal');
                        updateMetrics();
                    },
                    error: (xhr) => showWarning(xhr.responseJSON?.message || 'Failed to delete item.')
                });
            } else {
                handleModalClose(tgt);
            }
        });

        document.getElementById('editShelfForm').addEventListener('submit', (e) => {
            e.preventDefault();
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
                    const errList = document.getElementById('edit-error-message').querySelector('ul');
                    errList.innerHTML = Object.values(errors).flat().map(error => `<li>${error}</li>`).join('');
                    document.getElementById('edit-error-message').style.display = 'block';
                }
            });
        });

        document.getElementById('shelfForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const form = e.target;
            const items = selectedProductsTable.rows().nodes();
            const errorDiv = document.getElementById('shelf-error-message');
            hideErrorMessage();

            if (!items.length) {
                showWarning('Please add at least one product to the shelf.');
                return;
            }

            let hasError = false;
            items.each(function(row) {
                const priceInput = row.querySelector('input[name$="[price]"]');
                const qtyInput = row.querySelector('input[name$="[quantity_added]"]');
                const priceError = row.querySelector('.shelf-price-error');
                const qtyError = row.querySelector('.shelf-quantity-error');

                if (!priceInput || !qtyInput) {
                    hasError = true;
                    return;
                }

                const price = parseFloat(priceInput.value);
                const qty = parseInt(qtyInput.value) || 0;
                const maxStock = parseInt(row.dataset.productStock);

                if (!price || isNaN(price) || price <= 0 || price > 1200) {
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
                document.getElementById('price-error-message').textContent = 'Fix price (required, 0-1200) or quantity (within stock) errors.';
                document.getElementById('price-error-message').style.display = 'block';
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
                    const errList = errorDiv.querySelector('ul');
                    errList.innerHTML = Object.values(errors).flat().map(error => `<li>${error}</li>`).join('');
                    errorDiv.style.display = 'block';
                    openModal('shelfModal');
                }
            });
        });

        document.getElementById('selected-products').addEventListener('input', (e) => {
            if (e.target.type !== 'number') return;
            const input = e.target;
            const row = input.closest('tr');
            hideErrorMessage();

            if (input.name.includes('quantity_added') && row) {
                const val = input.value === '' ? '' : parseInt(input.value);
                const maxStock = parseInt(row.dataset.productStock);
                const qtyError = row.querySelector('.shelf-quantity-error');

                if (val === '') {
                    // Allow empty input during typing
                    input.classList.remove('quantity-error');
                    qtyError.style.display = 'none';
                } else if (val <= 0) {
                    input.classList.add('quantity-error');
                    qtyError.textContent = 'Quantity must not be less than 1';
                    qtyError.style.display = 'block';
                    showWarning(`Quantity must not be less than 1 for "${row.querySelector('td').textContent}".`);
                } else if (val > maxStock) {
                    input.classList.add('quantity-error');
                    qtyError.textContent = `Cannot add more than available stock for ${row.querySelector('td').textContent}. Stock: ${maxStock}`;
                    qtyError.style.display = 'block';
                    showWarning(`Cannot add more than available stock (${maxStock}) for "${row.querySelector('td').textContent}".`);
                } else {
                    input.classList.remove('quantity-error');
                    qtyError.style.display = 'none';
                }
            } else if (input.name.includes('price')) {
                const val = parseFloat(input.value) || 0;
                const priceError = row.querySelector('.shelf-price-error');
                if (val < 0) {
                    input.value = '';
                } else if (val > 1200) {
                    input.value = 1200;
                    showWarning(`Price must be between 0 and 1200 for "${row.querySelector('td').textContent}".`);
                }
                input.classList.toggle('price-error', !val || isNaN(val) || val <= 0 || val > 1200);
                priceError.style.display = (!val || isNaN(val) || val <= 0 || val > 1200) ? 'block' : 'none';
            }
            updateSubmitButton();
        });

        document.getElementById('selected-products').addEventListener('blur', (e) => {
            if (e.target.type !== 'number' || !e.target.name.includes('quantity_added')) return;
            const input = e.target;
            const row = input.closest('tr');
            const val = input.value === '' ? '' : parseInt(input.value);
            const maxStock = parseInt(row.dataset.productStock);
            const qtyError = row.querySelector('.shelf-quantity-error');

            if (val === '' || val <= 0) {
                input.value = 1;
                input.classList.add('quantity-error');
                qtyError.textContent = 'Quantity must not be less than 1';
                qtyError.style.display = 'block';
                showWarning(`Quantity must not be less than 1 for "${row.querySelector('td').textContent}".`);
            } else if (val > maxStock) {
                input.value = maxStock;
                input.classList.add('quantity-error');
                qtyError.textContent = `Cannot add more than available stock for ${row.querySelector('td').textContent}. Stock: ${maxStock}`;
                qtyError.style.display = 'block';
                showWarning(`Cannot add more than available stock (${maxStock}) for "${row.querySelector('td').textContent}".`);
            } else {
                input.classList.remove('quantity-error');
                qtyError.style.display = 'none';
            }
            updateSubmitButton();
        }, true);
    </script>
@endpush

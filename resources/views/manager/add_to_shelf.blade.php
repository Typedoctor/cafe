@extends('manager.layout')

@section('title', 'Add to Shelve')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
    <h1 class="shelf-title">Add to Shelve</h1>

    <div class="shelf-top-bar">
        <button id="openModalBtn" class="shelf-btn shelf-add-btn">+ Add to Shelve</button>
    </div>

    <div id="shelfModal" class="shelf-modal">
        <div class="shelf-add-modal-content">
            <span class="shelf-close-btn" id="closeModalBtn"><i class="fa-solid fa-circle-xmark"></i></span>
            <h2>Add to Shelf</h2>
            <form id="shelfForm" method="POST" action="{{ route('add-to-shelf.store') }}">
                @csrf
                @if ($errors->any())
                    <div class="shelf-error-message">
                        <ul class="shelf-error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="shelf-tabs-categ">
                    <button type="button" class="shelf-tab-link-categ active" data-tab="meal">Meal</button>
                    <button type="button" class="shelf-tab-link-categ" data-tab="drink">Drink</button>
                    <button type="button" class="shelf-tab-link-categ" data-tab="dessert">Dessert</button>
                    <button type="button" class="shelf-tab-link-categ" data-tab="snack">Snack</button>
                </div>

                <div id="product-table-container">
                    @foreach (['meal', 'drink', 'dessert', 'snack'] as $category)
                        <div id="{{ $category }}-tab" class="shelf-tab-content" style="display: {{ $category == 'meal' ? 'block' : 'none' }};">
                            <table class="shelf-product-table">
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
                                            <td>{{ $product->quantity }}</td>
                                            <td>
                                                <button type="button" class="shelf-btn shelf-product-add-btn" 
                                                        data-product-id="{{ $product->id }}" 
                                                        data-product-name="{{ $product->product_name }}">
                                                    Add to Shelf
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($products->where('category', $category)->isEmpty())
                                        <tr>
                                            <td colspan="3">No {{ $category }} products available.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>

                <div id="selected-products">
                    <h3>Selected Items</h3>
                    <table class="shelf-selected-products-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price (₱)<br><span class="price-note">Must not be zero</span></th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="selected-products-body"></tbody>
                    </table>
                    <div id="price-error-message" class="shelf-error-message-inline" style="display: none;"></div>
                </div>

                <div class="shelf-form-actions">
                    <button type="submit" class="shelf-btn shelf-save-btn">Add to Shelve</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editShelfItemModal" class="shelf-modal">
        <div class="shelf-add-modal-content">
            <span class="shelf-close-btn" id="closeEditModalBtn"><i class="fa-solid fa-circle-xmark"></i></span>
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
                    <input type="number" name="price" id="edit-price" class="shelf-form-input" step="0.01" min="0">
                </div>
                <div class="shelf-form-actions">
                    <button type="submit" class="shelf-btn shelf-save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="errorShelfModal" class="shelf-modal">
        <div class="shelf-add-modal-content">
            <span class="shelf-close-btn" id="closeErrorModalBtn"><i class="fa-solid fa-circle-xmark"></i></span>
            <h2> Shelve Items</h2>
            <p>The following products are already on the shelf. Remove them to add new quantities.</p>
            <table class="shelf-error-products-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity Added</th>
                        <th>Price (₱)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="error-products-body"></tbody>
            </table>
            <div class="shelf-form-actions">
                <button type="button" class="shelf-btn shelf-close-error-btn" id="closeErrorModalBtnSecondary">Close</button>
            </div>
        </div>
    </div>

    <div id="successModal" class="shelf-modal-success">
        <div class="shelf-modal-content">
            <h2>Success</h2>
            <p id="successModalMessage"></p>
            <div class="shelf-form-actions">
                <button type="button" class="shelf-btn shelf-close-success-btn" id="closeSuccessModalBtnSecondary">Close</button>
            </div>
        </div>
    </div>

    <div id="deleteSuccessModal" class="shelf-delete-modal-success">
        <div class="shelf-delete-modal-content">
            <h2>Deleted</h2>
            <p id="deleteSuccessModalMessage"></p>
            <div class="shelf-form-actions">
                <button type="button" class="shelf-btn shelf-close-delete-success-btn" id="closeDeleteSuccessModalBtnSecondary">Close</button>
            </div>
        </div>
    </div>

    <div class="shelf-table-container">
        <div class="shelf-section-title">Shelved Items</div>
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
                        <td>{{ $item->quantity_added }}</td>
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
                            <button type="button" class="shelf-btn shelf-delete-btn" data-shelf-item-id="{{ $item->id }}"><i class="fa-solid fa-trash"></i></button>
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

        $(document).ready(() => {
            $('#shelfItemsTable').DataTable({
                pageLength: 10,
                responsive: true,
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: 5 }]
            });

            @if ($errors->any())
                openModal('shelfModal');
            @endif
        });

        const openModal = (modalId, clearErrors = false) => {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
            if (clearErrors) {
                const errDiv = modal.querySelector('.shelf-error-message');
                if (errDiv) {
                    errDiv.style.display = 'none';
                    errDiv.querySelector('ul').innerHTML = '';
                }
            }
        };

        const closeModal = (modalId) => {
            document.getElementById(modalId).style.display = 'none';
        };

        const showSuccessModal = (msg, modalId = 'successModal') => {
            document.getElementById(modalId === 'successModal' ? 'successModalMessage' : 'deleteSuccessModalMessage').textContent = msg;
            openModal(modalId);
            setTimeout(() => {
                closeModal(modalId);
                location.reload();
            }, SUCCESS_MODAL_DURATION);
        };

        document.querySelectorAll('.shelf-tab-link-categ').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.shelf-tab-link-categ').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                document.querySelectorAll('.shelf-tab-content').forEach(c => {
                    c.style.display = c.id === `${btn.dataset.tab}-tab` ? 'block' : 'none';
                });
            });
        });

        document.querySelectorAll('.shelf-product-add-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const { productId, productName } = btn.dataset;
                $.ajax({
                    url: '{{ route("add-to-shelf.check") }}',
                    method: 'POST',
                    data: { product_id: productId, _token: '{{ csrf_token() }}' },
                    success: (res) => {
                        if (res.exists) {
                            document.getElementById('error-products-body').innerHTML = res.items.map(item => `
                                <tr>
                                    <td>${item.product_name}</td>
                                    <td>${item.quantity_added}</td>
                                    <td>${item.price ? parseFloat(item.price).toFixed(2) : 'N/A'}</td>
                                    <td>
                                        <button type="button" class="shelf-btn shelf-error-remove-btn" 
                                                data-shelf-item-id="${item.id}">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            `).join('');
                            openModal('errorShelfModal');
                        } else {
                            addProductToTable(productId, productName);
                        }
                    },
                    error: (xhr) => alert('Error checking shelf items: ' + (xhr.responseJSON?.message || 'Unknown error'))
                });
            });
        });

        const addProductToTable = (id, name) => {
            const tbody = document.getElementById('selected-products-body');
            const row = Array.from(tbody.rows).find(r => r.querySelector(`input[name$="[product_id]"]`).value === id);
            if (row) {
                const qtyInput = row.querySelector('input[name$="[quantity_added]"]');
                qtyInput.value = parseInt(qtyInput.value) + 1;
            } else {
                tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td style="text-align: center;">${name}</td>
                        <td style="text-align: center;">
                            <input type="number" name="items[${idx}][price]" step="0.01" min="0" class="shelf-form-input" placeholder="0.00">
                        </td>
                        <td style="text-align: center;">
                            <input type="number" name="items[${idx}][quantity_added]" min="1" value="1" required class="shelf-form-input">
                            <input type="hidden" name="items[${idx}][product_id]" value="${id}">
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="shelf-btn shelf-product-remove-btn">Remove</button>
                        </td>
                    </tr>
                `);
                idx++;
            }
        };

        document.addEventListener('click', (e) => {
            const tgt = e.target;
            const closeBtn = tgt.closest('.shelf-close-btn');
            const modal = tgt.closest('.shelf-modal');

            if (tgt.classList.contains('shelf-product-remove-btn')) {
                tgt.closest('tr').remove();
                document.getElementById('price-error-message').style.display = 'none';
            } else if (tgt.classList.contains('shelf-error-remove-btn')) {
                const id = tgt.dataset.shelfItemId;
                if (confirm('Are you sure you want to remove this item from the shelf?')) {
                    $.ajax({
                        url: '{{ route("add-to-shelf.destroy", ":id") }}'.replace(':id', id),
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: (res) => {
                            tgt.closest('tr').remove();
                            if (!document.querySelector('#error-products-body tr')) closeModal('errorShelfModal');
                            showSuccessModal(res.message, 'deleteSuccessModal');
                        },
                        error: (xhr) => alert('Error removing shelf item: ' + (xhr.responseJSON?.message || 'Unknown error'))
                    });
                }
            } else if (tgt.closest('.shelf-delete-btn')) {
                const btn = tgt.closest('.shelf-delete-btn');
                const id = btn.dataset.shelfItemId;
                if (confirm('Are you sure you want to remove this item from the shelf?')) {
                    $.ajax({
                        url: '{{ route("add-to-shelf.destroy", ":id") }}'.replace(':id', id),
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: (res) => {
                            showSuccessModal(res.message, 'deleteSuccessModal');
                        },
                        error: (xhr) => alert('Error removing shelf item: ' + (xhr.responseJSON?.message || 'Unknown error'))
                    });
                }
            } else if (tgt.closest('.shelf-edit-btn')) {
                const btn = tgt.closest('.shelf-edit-btn');
                const { shelfItemId, productName, quantityAdded, price, productId, availableStock } = btn.dataset;
                document.getElementById('edit-shelf-item-id').value = shelfItemId;
                document.getElementById('edit-product-name').value = productName;
                document.getElementById('edit-available-stock').value = availableStock;
                document.getElementById('edit-quantity-added').value = quantityAdded;
                document.getElementById('edit-price').value = price;
                document.getElementById('editShelfForm').action = '{{ route("add-to-shelf.update", ":id") }}'.replace(':id', shelfItemId);
                openModal('editShelfItemModal', true);
            } else if (tgt.id === 'openModalBtn') {
                openModal('shelfModal');
            } else if (closeBtn) {
                const modalId = closeBtn.closest('.shelf-modal').id;
                closeModal(modalId, modalId === 'editShelfItemModal');
            } else if (tgt.classList.contains('shelf-close-error-btn') || tgt.classList.contains('shelf-close-success-btn') || tgt.classList.contains('shelf-close-delete-success-btn')) {
                const modalId = tgt.closest('.shelf-modal').id;
                closeModal(modalId);
            } else if (modal && tgt === modal) {
                closeModal(modal.id, modal.id === 'editShelfItemModal');
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
                },
                error: (xhr) => {
                    const errors = xhr.responseJSON?.errors || { error: [xhr.responseJSON?.message || 'Unknown error'] };
                    const errList = document.getElementById('edit-error-message').querySelector('ul');
                    errList.innerHTML = Object.values(errors).flat().map(error => `<li>${error}</li>`).join('');
                    document.getElementById('edit-error-message').style.display = 'block';
                }
            });
        });

        document.getElementById('shelfForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const items = document.querySelectorAll('#selected-products-body tr');
            const errorDiv = document.getElementById('price-error-message');
            errorDiv.style.display = 'none';

            if (!items.length) {
                alert('Please add at least one product to the shelf.');
                return;
            }

            let hasPriceError = false;
            items.forEach(row => {
                const priceInput = row.querySelector('input[name$="[price]"]');
                const price = parseFloat(priceInput.value);
                if (isNaN(price) || price <= 0) {
                    priceInput.classList.add('price-error');
                    hasPriceError = true;
                } else {
                    priceInput.classList.remove('price-error');
                }
            });

            if (hasPriceError) {
                errorDiv.textContent = 'All prices must be greater than zero.';
                errorDiv.style.display = 'block';
                return;
            }

            $.ajax({
                url: form.action,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (res) => {
                    closeModal('shelfModal');
                    showSuccessModal(res.message);
                },
                error: () => openModal('shelfModal')
            });
        });

        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', () => {
                if (input.name.includes('quantity_added')) {
                    input.value = Math.max(1, parseInt(input.value) || 1);
                } else if (input.name.includes('price')) {
                    let val = parseFloat(input.value);
                    input.value = isNaN(val) || val < 0 ? '' : val.toFixed(2);
                    const errorDiv = document.getElementById('price-error-message');
                    errorDiv.style.display = 'none';
                    const row = input.closest('tr');
                    if (row) {
                        if (isNaN(val) || val <= 0) {
                            input.classList.add('price-error');
                        } else {
                            input.classList.remove('price-error');
                        }
                    }
                }
            });
        });
    </script>
@endpush


@extends('manager.layout')

@section('title', 'Add to Shelf')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/shelf-styles.css') }}">
@endpush

@section('content')
    <h1 class="shelf-title">Add to Shelf</h1>

    <div class="shelf-top-bar">
        <button id="openModalBtn" class="shelf-btn shelf-add-btn">+ Add to Shelf</button>
    </div>

    <!-- Modal for Adding to Shelf -->
    <div id="shelfModal" class="shelf-modal">
        <div class="shelf-modal-content">
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

                <!-- Tabs for Categories -->
                <div class="shelf-tabs-categ">
                    <button type="button" class="shelf-tab-link-categ active" data-tab="meal">Meal</button>
                    <button type="button" class="shelf-tab-link-categ" data-tab="drink">Drink</button>
                    <button type="button" class="shelf-tab-link-categ" data-tab="dessert">Dessert</button>
                    <button type="button" class="shelf-tab-link-categ" data-tab="snack">Snack</button>
                </div>

                <!-- Product Table -->
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

                <!-- Selected Products (Shelf Items) -->
                <div id="selected-products">
                    <h3>Shelf Items</h3>
                    <table class="shelf-selected-products-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price (₱)</th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="selected-products-body">
                            <!-- Dynamically added rows will appear here -->
                        </tbody>
                    </table>
                </div>

                <div class="shelf-form-actions">
                    <button type="submit" class="shelf-btn shelf-save-btn">Add to Shelf</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Editing Shelf Item -->
    <div id="editShelfItemModal" class="shelf-modal">
        <div class="shelf-modal-content">
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

    <!-- Error Modal for Existing Shelf Items -->
    <div id="errorShelfModal" class="shelf-modal">
        <div class="shelf-modal-content">
            <span class="shelf-close-btn" id="closeErrorModalBtn"><i class="fa-solid fa-circle-xmark"></i></span>
            <h2>Existing Shelf Items</h2>
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
                <tbody id="error-products-body">
                    <!-- Dynamically populated via JavaScript -->
                </tbody>
            </table>
            <div class="shelf-form-actions">
                <button type="button" class="shelf-btn shelf-close-error-btn" id="closeErrorModalBtnSecondary">Close</button>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="shelf-success-message" id="successMessage">
            {{ session('success') }}
        </div>
    @endif

    <!-- Shelf Items Table -->
    <div class="shelf-table-container">
        <div class="shelf-section-title">Shelf Items</div>
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
                            <form action="{{ route('add-to-shelf.destroy', $item) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this item from the shelf?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="shelf-btn shelf-delete-btn"><i class="fa-solid fa-trash"></i></button>
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
        let productIndex = 0;

        $(document).ready(function () {
            // Initialize DataTables for Shelf Items Table
            $('#shelfItemsTable').DataTable({
                pageLength: 10,
                responsive: true,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 5 } // Disable sorting on Actions column
                ]
            });

            // Keep add modal open if there are any errors
            @if ($errors->any())
                document.getElementById('shelfModal').style.display = 'flex';
            @endif

            // Handle success message fade-out and modal close only on success
            @if (session('success'))
                document.getElementById('shelfModal').style.display = 'none';
                document.getElementById('editShelfItemModal').style.display = 'none';
                setTimeout(() => {
                    const successMessage = document.getElementById('successMessage');
                    if (successMessage) {
                        successMessage.style.opacity = '0';
                        setTimeout(() => {
                            successMessage.classList.add('hidden');
                        }, 500);
                    }
                }, 2000);
            @endif
        });

        // Tab Switching for Categories
        document.querySelectorAll('.shelf-tab-link-categ').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.shelf-tab-link-categ').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                document.querySelectorAll('.shelf-tab-content').forEach(content => {
                    content.style.display = content.id === `${button.dataset.tab}-tab` ? 'block' : 'none';
                });
            });
        });

        // Check if product is already on shelf before adding
        document.querySelectorAll('.shelf-product-add-btn').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.dataset.productId;
                const productName = button.dataset.productName;

                $.ajax({
                    url: '{{ route("add-to-shelf.check") }}',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.exists) {
                            const errorProductsBody = document.getElementById('error-products-body');
                            errorProductsBody.innerHTML = response.items.map(item => `
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
                            document.getElementById('errorShelfModal').style.display = 'flex';
                        } else {
                            addProductToTable(productId, productName);
                        }
                    },
                    error: function(xhr) {
                        alert('Error checking shelf items: ' + xhr.responseJSON?.message || 'Unknown error');
                    }
                });
            });
        });

        // Function to add product to selected products table
        function addProductToTable(productId, productName) {
            const selectedProductsBody = document.getElementById('selected-products-body');
            const existingRow = Array.from(selectedProductsBody.rows).find(row => 
                row.querySelector(`input[name$="[product_id]"]`).value === productId
            );

            if (existingRow) {
                const quantityInput = existingRow.querySelector('input[name$="[quantity_added]"]');
                quantityInput.value = parseInt(quantityInput.value) + 1;
            } else {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="text-align: center;">${productName}</td>
                    <td style="text-align: center;">
                        <input type="number" name="items[${productIndex}][price]" step="0.01" min="0" class="shelf-form-input" placeholder="0.00">
                    </td>
                    <td style="text-align: center;">
                        <input type="number" name="items[${productIndex}][quantity_added]" min="1" value="1" required class="shelf-form-input">
                        <input type="hidden" name="items[${productIndex}][product_id]" value="${productId}">
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="shelf-btn shelf-product-remove-btn">Remove</button>
                    </td>
                `;
                selectedProductsBody.appendChild(row);
                productIndex++;
            }
        }

        // Remove Product from Selected Products Table
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('shelf-product-remove-btn')) {
                e.target.closest('tr').remove();
            }
        });

        // Remove Shelf Item from Error Modal
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('shelf-error-remove-btn')) {
                const shelfItemId = e.target.dataset.shelfItemId;
                if (confirm('Are you sure you want to remove this item from the shelf?')) {
                    $.ajax({
                        url: '{{ route("add-to-shelf.destroy", ":id") }}'.replace(':id', shelfItemId),
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            e.target.closest('tr').remove();
                            if (document.querySelectorAll('#error-products-body tr').length === 0) {
                                document.getElementById('errorShelfModal').style.display = 'none';
                            }
                            alert(response.message);
                            location.reload();
                        },
                        error: function(xhr) {
                            alert('Error removing shelf item: ' + xhr.responseJSON?.message || 'Unknown error');
                        }
                    });
                }
            }
        });

        // Modal Handling for Add to Shelf Modal
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const shelfModal = document.getElementById('shelfModal');

        openModalBtn.addEventListener('click', () => {
            shelfModal.style.display = 'flex';
        });

        closeModalBtn.addEventListener('click', () => {
            shelfModal.style.display = 'none';
        });

        shelfModal.addEventListener('click', (e) => {
            if (e.target === shelfModal) {
                shelfModal.style.display = 'none';
            }
        });

        // Modal Handling for Edit Shelf Modal
        const editShelfItemModal = document.getElementById('editShelfItemModal');
        const closeEditModalBtn = document.getElementById('closeEditModalBtn');

        closeEditModalBtn.addEventListener('click', () => {
            editShelfItemModal.style.display = 'none';
            document.getElementById('edit-error-message').style.display = 'none';
            document.getElementById('edit-error-message').querySelector('ul').innerHTML = '';
        });

        editShelfItemModal.addEventListener('click', (e) => {
            if (e.target === editShelfItemModal) {
                editShelfItemModal.style.display = 'none';
                document.getElementById('edit-error-message').style.display = 'none';
                document.getElementById('edit-error-message').querySelector('ul').innerHTML = '';
            }
        });

        // Handle Edit Button Click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('shelf-edit-btn') || e.target.closest('.shelf-edit-btn')) {
                const button = e.target.classList.contains('shelf-edit-btn') ? e.target : e.target.closest('.shelf-edit-btn');
                const shelfItemId = button.dataset.shelfItemId;
                const productName = button.dataset.productName;
                const quantityAdded = button.dataset.quantityAdded;
                const price = button.dataset.price;
                const productId = button.dataset.productId;
                const availableStock = button.dataset.availableStock;

                // Populate Edit Modal
                document.getElementById('edit-shelf-item-id').value = shelfItemId;
                document.getElementById('edit-product-name').value = productName;
                document.getElementById('edit-available-stock').value = availableStock;
                document.getElementById('edit-quantity-added').value = quantityAdded;
                document.getElementById('edit-price').value = price;
                document.getElementById('editShelfForm').action = '{{ route("add-to-shelf.update", ":id") }}'.replace(':id', shelfItemId);

                // Clear previous errors
                document.getElementById('edit-error-message').style.display = 'none';
                document.getElementById('edit-error-message').querySelector('ul').innerHTML = '';

                editShelfItemModal.style.display = 'flex';
            }
        });

        // Handle Edit Form Submission
        document.getElementById('editShelfForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const shelfItemId = document.getElementById('edit-shelf-item-id').value;

            $.ajax({
                url: form.action,
                method: 'POST', // Laravel uses POST with _method=PUT
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    alert(response.message);
                    editShelfItemModal.style.display = 'none';
                    location.reload();
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || { error: [xhr.responseJSON?.message || 'Unknown error'] };
                    const errorList = document.getElementById('edit-error-message').querySelector('ul');
                    errorList.innerHTML = Object.values(errors).flat().map(error => `<li>${error}</li>`).join('');
                    document.getElementById('edit-error-message').style.display = 'block';
                }
            });
        });

        // Modal Handling for Error Modal
        const closeErrorModalBtn = document.getElementById('closeErrorModalBtn');
        const closeErrorModalBtnSecondary = document.getElementById('closeErrorModalBtnSecondary');
        const errorShelfModal = document.getElementById('errorShelfModal');

        closeErrorModalBtn.addEventListener('click', () => {
            errorShelfModal.style.display = 'none';
        });

        closeErrorModalBtnSecondary.addEventListener('click', () => {
            errorShelfModal.style.display = 'none';
        });

        errorShelfModal.addEventListener('click', (e) => {
            if (e.target === errorShelfModal) {
                errorShelfModal.style.display = 'none';
            }
        });

        // Ensure at least one item is selected before submission
        document.getElementById('shelfForm').addEventListener('submit', (e) => {
            const selectedItems = document.querySelectorAll('#selected-products-body tr');
            if (selectedItems.length === 0) {
                e.preventDefault();
                alert('Please add at least one product to the shelf.');
            }
        });

        // Validate quantity and price inputs
        document.querySelectorAll('input[type="number"]').forEach(input => {
            if (input.name.includes('quantity_added')) {
                input.addEventListener('input', function () {
                    this.value = Math.max(1, parseInt(this.value) || 1);
                });
            } else if (input.name.includes('price')) {
                input.addEventListener('input', function () {
                    let value = parseFloat(this.value);
                    if (isNaN(value) || value < 0) {
                        this.value = '';
                    } else {
                        this.value = value.toFixed(2);
                    }
                });
            }
        });
    </script>
@endpush

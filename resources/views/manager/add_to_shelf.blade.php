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

    <!-- Modal -->
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
                                            <td colspan="4">No {{ $category }} products available.</td>
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

    @if (session('success'))
        <div class="shelf-success-message" id="successMessage">
            {{ session('success') }}
        </div>
        <script>
            // Auto-close modal and fade out success message
            document.getElementById('shelfModal').style.display = 'none';
            setTimeout(() => {
                const successMessage = document.getElementById('successMessage');
                if (successMessage) {
                    successMessage.style.opacity = '0';
                    setTimeout(() => {
                        successMessage.classList.add('hidden');
                    }, 500);
                }
            }, 2000);
        </script>
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
                    <th style="width: 100px;">Actions</th>
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
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

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

            // Check for errors from form submission and reopen modal only for store action
            @if ($errors->any() && request()->is('*/add-to-shelf'))
                document.getElementById('shelfModal').style.display = 'flex';
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

        // Add Product to Shelf Items Table (in modal)
        document.querySelectorAll('.shelf-product-add-btn').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.dataset.productId;
                const productName = button.dataset.productName;

                const selectedProductsBody = document.getElementById('selected-products-body');
                const existingRow = Array.from(selectedProductsBody.rows).find(row => 
                    row.querySelector(`input[name$="[product_id]"]`).value === productId
                );

                if (existingRow) {
                    // Update quantity
                    const quantityInput = existingRow.querySelector('input[name$="[quantity_added]"]');
                    quantityInput.value = parseInt(quantityInput.value) + 1;
                } else {
                    // Add new row with centered alignment
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
            });
        });

        // Remove Product from Shelf Items Table
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('shelf-product-remove-btn')) {
                e.target.closest('tr').remove();
            }
        });

        // Modal Handling
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
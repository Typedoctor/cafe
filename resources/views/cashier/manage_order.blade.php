@extends('cashier.layout')

@section('title', 'Cashier Dashboard')

@section('styles')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('css/cashier-order.css') }}">
</head>
<h1 class="csh-dashboard-title">Cashier Dashboard</h1>
<div class="csh-main-container">
    <!-- Add Order Button -->
    <div class="csh-button-container">
        <button id="openModalBtn" class="csh-add-order-btn">New Order</button>
    </div>

    <!-- Modal -->
    <div id="orderModal" class="csh-modal modal-base" role="dialog" aria-labelledby="modalTitle">
        <div class="csh-modal-content">
            <h2 id="modalTitle" class="csh-modal-title">Create New Order</h2>
            <form action="{{ route('order.store') }}" method="POST" id="orderForm">
                @csrf
                @if ($errors->any())
                    <div class="csh-error-message">
                        <ul class="csh-error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="csh-form-group">
                    <label for="customer_name" class="csh-form-label">Customer Name:</label>
                    <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}"
                           required class="csh-form-input" placeholder="Enter customer name here"
                           pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" maxlength="50">
                    <div class="csh-customer-name-error" style="display: none;">Only letters and spaces are allowed.</div>
                </div>

                <!-- Tabs for Categories -->
                <div class="csh-tabs-categ" role="tablist">
                    @foreach (['meal', 'drink', 'dessert', 'snack'] as $category)
                        <button type="button" class="csh-tab-link-categ {{ $category == 'meal' ? 'active' : '' }}"
                                data-tab="{{ $category }}" role="tab" aria-selected="{{ $category == 'meal' ? 'true' : 'false' }}"
                                tabindex="{{ $category == 'meal' ? '0' : '-1' }}">{{ ucfirst($category) }}</button>
                    @endforeach
                </div>

                <!-- Product Table -->
                <div id="product-table-container">
                    @foreach (['meal', 'drink', 'dessert', 'snack'] as $category)
                        <div id="{{ $category }}-tab" class="csh-tab-content" style="display: {{ $category == 'meal' ? 'block' : 'none' }};">
                            <table class="csh-product-table" id="{{ $category }}-product-table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shelfItems->where('product.category', $category) as $shelfItem)
                                        <tr>
                                            <td>{{ $shelfItem->product->product_name }}</td>
                                            <td>₱{{ number_format($shelfItem->price, 2) }}</td>
                                            <td class="csh-stock" data-product-id="{{ $shelfItem->product_id }}">{{ $shelfItem->quantity_added }}</td>
                                            <td>
                                                <button type="button" class="csh-add-product-btn"
                                                        data-product-id="{{ $shelfItem->product_id }}"
                                                        data-product-name="{{ $shelfItem->product->product_name }}"
                                                        data-product-price="{{ $shelfItem->price }}"
                                                        data-product-stock="{{ $shelfItem->quantity_added }}"
                                                        {{ $shelfItem->quantity_added <= 0 ? 'disabled' : '' }}>
                                                    {{ $shelfItem->quantity_added <= 0 ? 'Out of Stock' : 'Add to Order' }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($shelfItems->where('product.category', $category)->isEmpty())
                                        <tr>
                                            <td>No {{ $category }} products available.</td>
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

                <!-- Selected Products -->
                <div id="selected-products">
                    <h3>Selected Products</h3>
                    <table class="csh-selected-products-table" id="selected-products-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Stock Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="selected-products-body">
                            @if (old('products'))
                                @foreach (old('products') as $index => $product)
                                    @php
                                        $shelfItem = $shelfItems->firstWhere('product_id', $product['product_id']);
                                        $productName =rosemary($shelfItem ? $shelfItem->product->product_name : 'Unknown');
                                        $productPrice = $shelfItem ? $shelfItem->price : 0;
                                        $productStock = $shelfItem ? $shelfItem->quantity_added : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $productName }}</td>
                                        <td>{{ number_format($productPrice, 2) }}</td>
                                        <td class="quantity-container">
                                            <input type="number" name="products[{{ $index }}][quantity]" min="1" max="{{ $productStock }}"
                                                   value="{{ $product['quantity'] }}" required class="csh-form-input">
                                            <input type="hidden" name="products[{{ $index }}][product_id]" value="{{ $product['product_id'] }}">
                                            <div class="quantity-error">Cannot add more items</div>
                                        </td>
                                        <td>{{ $product['quantity'] <= $productStock ? 'In Stock' : 'Cannot add more items' }}</td>
                                        <td>
                                            <button type="button" class="csh-remove-product-btn">Remove</button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="csh-form-group">
                    <label class="csh-form-label">Order Type:</label>
                    <div class="csh-radio-group">
                        <input type="radio" name="order_type" id="order_type_dine_in" value="Dine-in"
                               {{ old('order_type', 'Dine-in') == 'Dine-in' ? 'checked' : '' }} required>
                        <label for="order_type_dine_in" class="csh-radio-label">Dine-in</label>
                        <input type="radio" name="order_type" id="order_type_takeout" value="Takeout"
                               {{ old('order_type') == 'Takeout' ? 'checked' : '' }}>
                        <label for="order_type_takeout" class="csh-radio-label">Takeout</label>
                    </div>
                </div>

                <div class="csh-form-group">
                    <label for="special_instructions" class="csh-form-label">Special Instructions:</label>
                    <textarea placeholder="Any special instructions? Add here" name="special_instructions"
                              id="special_instructions" class="csh-form-textarea" maxlength="255">{{ old('special_instructions') }}</textarea>
                    <div class="csh-char-counter" id="charCounter">255 characters remaining</div>
                    <div class="csh-special-instructions-error" style="display: none;">HTML tags are not allowed.</div>
                </div>

                <div class="csh-form-actions">
                    <button type="submit" class="csh-submit-btn" id="submitOrderBtn">Place Order</button>
                    przeds            <button type="button" id="closeModalBtn" class="csh-close-btn">Close</button>
                </div>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="csh-success-message" id="successMessage">
            {{ session('success') }}
        </div>
        <style>
            .csh-success-message {
                transition: opacity 0.5s ease;
            }
            .csh-success-message.hidden {
                opacity: 0;
                display: none;
            }
        </style>
        <script>
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

    <div class="csh-orders-container">
        <div class="csh-orders-list">
            @if ($orders->isEmpty())
                <p class="csh-no-orders">No orders have been placed yet.</p>
            @else
                @foreach ($orders as $order)
                    @php
                        $productsString = '';
                        foreach ($order->orderItems as $index => $item) {
                            $productsString .= $item->quantity . ' x ' . ($item->product->product_name ?? 'N/A');
                            if ($index < $order->orderItems->count() - 1) {
                                $productsString .= ', ';
                            }
                        }
                    @endphp
                    <div class="csh-order-card" data-order-id="{{ $order->id }}" role="button" tabindex="0"
                         data-customer-name="{{ $order->customer_name }}"
                         data-order-type="{{ $order->order_type }}"
                         data-special-instructions="{{ $order->special_instructions ?? 'None' }}"
                         data-total-price="{{ number_format($order->total_price, 2) }}"
                         data-time="{{ $order->created_at->format('h:i A') }}"
                         data-products="{{ $productsString }}">
                        <div class="csh-order-header">
                            <span class="csh-order-id">{{ $order->id }}</span>
                            <span class="csh-order-products">
                                @foreach ($order->orderItems as $item)
                                    {{ $item->product->product_name ?? 'N/A' }}
                                    @if (!$loop->last), @endif
                                @endforeach
                            </span>
                        </div>
                        <div class="csh-order-details">
                            <span class="csh-order-time">{{ $order->created_at->format('h:i A') }}</span>
                            <span class="csh-order-customer">{{ $order->customer_name }}</span>
                            <span class="csh-order-type">{{ $order->order_type }}</span>
                        </div>
                    </div>
                @endforeach
                <div class="csh-pagination">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        <!-- Order Details Form -->
        <div id="orderDetailsForm" class="csh-order-details-form">
            <form id="orderDetailsFormInner" method="POST">
                @csrf
                <input type="hidden" name="order_id" id="orderDetailsId">
                <div class="csh-order-details-header">
                    <span id="orderDetailsIdDisplay">Select an order</span>
                    <span id="orderDetailsCustomer"></span>
                </div>
                <div class="csh-order-details-time-type">
                    <span id="orderDetailsTime"></span>
                    <span id="orderDetailsType"></span>
                </div>
                <div class="csh-order-details-products" id="orderDetailsProducts"></div>
                <div class="csh-order-details-payment">
                    <span class="csh-order-details-label">Payment mode</span>
                    <span class="csh-order-details-value">Cash</span>
                </div>
                <div class="csh-order-details-total">
                    <U+00A0>                    <span class="csh-order-details-label">TOTAL</span>
                    <span class="csh-order-details-value" id="orderDetailsTotal">₱ 0.00</span>
                </div>
                <div class="csh-order-details-instructions">
                    <span class="csh-order-details-label">Special Instructions</span>
                    <div class="csh-order-details-value" id="orderDetailsInstructions"></div>
                </div>
                <div class="csh-order-details-actions">
                    <button type="submit" formaction="{{ route('order.cancel') }}" class="csh-cancel-btn">Cancel Order</button>
                    <button type="submit" formaction="{{ route('order.complete') }}" class="csh-complete-btn">Mark as Completed</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        let productIndex = {{ old('products') ? count(old('products')) : 0 }};
        let productTables = {};

        // Modal Handling
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const orderModal = document.getElementById('orderModal');

        openModalBtn.addEventListener('click', () => {
            orderModal.style.display = 'flex';
            const activeTab = document.querySelector('.csh-tab-link-categ.active')?.dataset.tab;
            if (activeTab && productTables[activeTab]) {
                productTables[activeTab].draw();
            }
        });

        closeModalBtn.addEventListener('click', () => {
            orderModal.style.display = 'none';
            document.getElementById('selected-products-body').innerHTML = '';
            productIndex = 0;
            updateSubmitButtonState();
        });

        orderModal.addEventListener('click', (e) => {
            if (e.target === orderModal) {
                orderModal.style.display = 'none';
                document.getElementById('selected-products-body').innerHTML = '';
                productIndex = 0;
                updateSubmitButtonState();
            }
        });

        // Validate customer name input
        const customerNameInput = document.getElementById('customer_name');
        const customerNameError = document.querySelector('.csh-customer-name-error');
        const nameRegex = /^[A-Za-z\s]+$/;

        customerNameInput.addEventListener('input', () => {
            const value = customerNameInput.value;
            if (value && !nameRegex.test(value)) {
                customerNameError.style.display = 'block';
                customerNameInput.setCustomValidity('Only letters and spaces are allowed.');
            } else {
                customerNameError.style.display = 'none';
                customerNameInput.setCustomValidity('');
            }
        });

        // Validate special instructions
        const specialInstructions = document.getElementById('special_instructions');
        const specialInstructionsError = document.querySelector('.csh-special-instructions-error');
        const charCounter = document.getElementById('charCounter');
        const maxLength = 255;

        specialInstructions.addEventListener('input', () => {
            const value = specialInstructions.value;
            const remaining = maxLength - value.length;
            charCounter.textContent = `${remaining} characters remaining`;
            if (value.includes('<') || value.includes('>')) {
                specialInstructionsError.style.display = 'block';
                specialInstructions.setCustomValidity('HTML tags are not allowed.');
            } else {
                specialInstructionsError.style.display = 'none';
                specialInstructions.setCustomValidity('');
            }
        });

        // Function to check stock and update submit button state
        function updateSubmitButtonState() {
            const selectedProductsBody = document.getElementById('selected-products-body');
            const submitBtn = document.getElementById('submitOrderBtn');
            let hasInsufficientStock = false;

            Array.from(selectedProductsBody.rows).forEach(row => {
                const productIdInput = row.querySelector(`input[name$="[product_id]"]`);
                if (!productIdInput) return;
                const productId = productIdInput.value;
                const quantityInput = row.querySelector('input[type="number"]');
                const quantity = parseInt(quantityInput.value);
                const stockCell = document.querySelector(`.csh-stock[data-product-id="${productId}"]`);
                const stock = parseInt(stockCell.textContent);
                const stockStatusCell = row.cells[3];
                const quantityError = row.querySelector('.quantity-error');

                if (quantity > stock) {
                    hasInsufficientStock = true;
                    stockStatusCell.textContent = 'Cannot add more items. Stock available: (' + stock + ')';
                    stockStatusCell.style.color = 'red';
                    quantityError.style.display = 'block';
                } else {
                    stockStatusCell.textContent = 'In Stock';
                    stockStatusCell.style.color = 'green';
                    quantityError.style.display = 'none';
                }
            });

            submitBtn.disabled = hasInsufficientStock || selectedProductsBody.rows.length === 0;
        }

        // Tab Switching for Categories
        document.querySelectorAll('.csh-tab-link-categ').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.csh-tab-link-categ').forEach(btn => {
                    btn.classList.remove('active');
                    btn.setAttribute('aria-selected', 'false');
                    btn.tabIndex = -1;
                });
                button.classList.add('active');
                button.setAttribute('aria-selected', 'true');
                button.tabIndex = 0;

                document.querySelectorAll('.csh-tab-content').forEach(content => {
                    content.style.display = content.id === `${button.dataset.tab}-tab` ? 'block' : 'none';
                });

                if (productTables[button.dataset.tab]) {
                    productTables[button.dataset.tab].draw();
                }
            });

            button.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    button.click();
                }
            });
        });

        // Add Product to Selected Products Table
        document.querySelectorAll('.csh-add-product-btn').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.dataset.productId;
                const productName = button.dataset.productName;
                const productPrice = button.dataset.productPrice;
                const productStock = parseInt(button.dataset.productStock);

                const selectedProductsBody = document.getElementById('selected-products-body');
                const existingRow = Array.from(selectedProductsBody.rows).find(row => {
                    const productIdInput = row.querySelector(`input[name$="[product_id]"]`);
                    return productIdInput && productIdInput.value === productId;
                });

                if (existingRow) {
                    const quantityInput = existingRow.querySelector('input[type="number"]');
                    const newQuantity = parseInt(quantityInput.value) + 1;
                    if (newQuantity <= productStock) {
                        quantityInput.value = newQuantity;
                        existingRow.querySelector('.quantity-error').style.display = 'none';
                    } else {
                        existingRow.querySelector('.quantity-error').style.display = 'block';
                    }
                } else {
                    if (productStock > 0) {
                        const rowHtml = `
                            <tr>
                                <td>${productName}</td>
                                <td>${parseFloat(productPrice).toFixed(2)}</td>
                                <td class="quantity-container">
                                    <input type="number" name="products[${productIndex}][quantity]" min="1" max="${productStock}" value="1" required class="csh-form-input">
                                    <input type="hidden" name="products[${productIndex}][product_id]" value="${productId}">
                                    <div class="quantity-error">Insufficient stock!</div>
                                </td>
                                <td>In Stock</td>
                                <td>
                                    <button type="button" class="csh-remove-product-btn">Remove</button>
                                </td>
                            </tr>
                        `;
                        selectedProductsBody.innerHTML += rowHtml;
                        productIndex++;
                    } else {
                        alert(`Cannot add ${productName}. Out of stock.`);
                    }
                }
                updateSubmitButtonState();
            });
        });

        // Update stock status when quantity changes
        document.getElementById('selected-products-body').addEventListener('change', (e) => {
            if (e.target.type === 'number') {
                updateSubmitButtonState();
            }
        });

        // Remove Product from Selected Products
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('csh-remove-product-btn')) {
                const row = e.target.closest('tr');
                row.remove();
                updateSubmitButtonState();
            }
        });

        // Form Submission Validation
        document.getElementById('orderForm').addEventListener('submit', (e) => {
            const selectedProducts = document.querySelectorAll('#selected-products-body tr');
            if (selectedProducts.length === 0) {
                e.preventDefault();
                alert('Please add at least one product to the order.');
                return;
            }

            let hasInsufficientStock = false;
            Array.from(selectedProducts).forEach(row => {
                const productIdInput = row.querySelector(`input[name$="[product_id]"]`);
                if (!productIdInput) return;
                const productId = productIdInput.value;
                const quantity = parseInt(row.querySelector('input[type="number"]').value);
                const stock = parseInt(document.querySelector(`.csh-stock[data-product-id="${productId}"]`).textContent);
                if (quantity > stock) {
                    hasInsufficientStock = true;
                }
            });

            if (hasInsufficientStock) {
                e.preventDefault();
                alert('Please fix insufficient stock issues before submitting.');
            }

            if (!nameRegex.test(customerNameInput.value)) {
                e.preventDefault();
                customerNameError.style.display = 'block';
                customerNameInput.setCustomValidity('Only letters and spaces are allowed.');
            }

            if (specialInstructions.value.includes('<') || specialInstructions.value.includes('>')) {
                e.preventDefault();
                specialInstructionsError.style.display = 'block';
                specialInstructions.setCustomValidity('HTML tags are not allowed.');
            }
        });

        @if ($errors->any())
            document.getElementById('orderModal').style.display = 'flex';
            customerNameInput.dispatchEvent(new Event('input'));
            specialInstructions.dispatchEvent(new Event('input'));
        @endif

        // Order Details Form Handling
        const orderDetailsForm = document.getElementById('orderDetailsForm');
        const orderDetailsId = document.getElementById('orderDetailsId');
        const orderDetailsIdDisplay = document.getElementById('orderDetailsIdDisplay');
        const orderDetailsCustomer = document.getElementById('orderDetailsCustomer');
        const orderDetailsTime = document.getElementById('orderDetailsTime');
        const orderDetailsType = document.getElementById('orderDetailsType');
        const orderDetailsProducts = document.getElementById('orderDetailsProducts');
        const orderDetailsTotal = document.getElementById('orderDetailsTotal');
        const orderDetailsInstructions = document.getElementById('orderDetailsInstructions');

        function updateOrderDetails(card) {
            document.querySelectorAll('.csh-order-card').forEach(c => c.classList.remove('csh-order-card-selected'));
            card.classList.add('csh-order-card-selected');

            orderDetailsId.value = card.dataset.orderId;
            orderDetailsIdDisplay.textContent = `Order ${card.dataset.orderId}`;
            orderDetailsCustomer.textContent = card.dataset.customerName;
            orderDetailsTime.textContent = card.dataset.time;
            orderDetailsType.textContent = card.dataset.orderType;
            orderDetailsProducts.textContent = card.dataset.products;
            orderDetailsTotal.textContent = `₱ ${card.dataset.totalPrice}`;
            orderDetailsInstructions.textContent = card.dataset.specialInstructions;

            orderDetailsForm.style.display = 'block';
        }

        document.querySelectorAll('.csh-order-card').forEach(card => {
            card.addEventListener('click', () => updateOrderDetails(card));
            card.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    updateOrderDetails(card);
                }
            });
        });

        // Initialize Product Tables and Order Selection
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof jQuery === 'undefined') {
                console.error('jQuery is not loaded for DataTables initialization');
                return;
            }

            try {
                const categories = ['meal', 'drink', 'dessert', 'snack'];
                categories.forEach(category => {
                    productTables[category] = jQuery(`#${category}-product-table`).DataTable({
                        pageLength: 5,
                        lengthMenu: [5, 10, 25, 50],
                        responsive: true,
                        searching: true,
                        ordering: true,
                        columnDefs: [{ orderable: false, targets: 3 }],
                        language: {
                            search: "Search products:",
                            emptyTable: "No products available"
                        },
                        initComplete: function () {
                            // Wrap .dataTables_length and .dataTables_filter in a div.dataTables_top_controls
                            const wrapper = this.api().table().container();
                            const length = wrapper.querySelector('.dataTables_length');
                            const filter = wrapper.querySelector('.dataTables_filter');
                            if (length && filter) {
                                const topControls = document.createElement('div');
                                topControls.className = 'dataTables_top_controls';
                                length.parentNode.insertBefore(topControls, length);
                                topControls.appendChild(length);
                                topControls.appendChild(filter);
                            }
                        }
                    });
                    console.log(`Product table for ${category} initialized`);
                });
            } catch (e) {
                console.error('Error during DataTables initialization:', e);
            }

            const orderCards = document.querySelectorAll('.csh-order-card');
            if (orderCards.length > 0) {
                let latestCard = null;
                let highestId = -1;

                orderCards.forEach(card => {
                    const orderId = parseInt(card.dataset.orderId, 10);
                    if (orderId > highestId) {
                        highestId = orderId;
                        latestCard = card;
                    }
                });

                if (latestCard) {
                    updateOrderDetails(latestCard);
                }
            }
            updateSubmitButtonState();
        });
    </script>
</div>
@endsection
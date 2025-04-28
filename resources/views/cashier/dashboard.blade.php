@extends('cashier.layout')

@section('title', 'Cashier Dashboard')

@section('content')
    <h1 class="csh-dashboard-title">Cashier Dashboard</h1>
    <div class="csh-main-container">
        <!-- Add Order Button -->
        <div class="csh-button-container">
            <button id="openModalBtn" class="csh-add-order-btn">
                New Order
            </button>
        </div>

        <!-- Modal -->
        <div id="orderModal" class="csh-modal">
            <div class="csh-modal-content">
                <h2 class="csh-modal-title">Create New Order</h2>
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
                        <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" required class="csh-form-input" placeholder="Enter customer name here">
                    </div>

                    <!-- Tabs for Categories -->
                    <div class="csh-tabs-categ">
                        <button type="button" class="csh-tab-link-categ active" data-tab="meal">Meal</button>
                        <button type="button" class="csh-tab-link-categ" data-tab="drink">Drink</button>
                        <button type="button" class="csh-tab-link-categ" data-tab="dessert">Dessert</button>
                        <button type="button" class="csh-tab-link-categ" data-tab="snack">Snack</button>
                    </div>

                    <!-- Product Table -->
                    <div id="product-table-container">
                        @foreach (['meal', 'drink', 'dessert', 'snack'] as $category)
                            <div id="{{ $category }}-tab" class="csh-tab-content" style="display: {{ $category == 'meal' ? 'block' : 'none' }};">
                                <table class="csh-product-table">
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
                                                <td colspan="4">No {{ $category }} products available.</td>
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
                        <table class="csh-selected-products-table">
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
                                <!-- Dynamically added rows will appear here -->
                            </tbody>
                        </table>
                    </div>

                    <div class="csh-form-group">
                        <label class="csh-form-label">Order Type:</label>
                        <div class="csh-radio-group">
                            <input type="radio" name="order_type" id="order_type_dine_in" value="Dine-in" {{ old('order_type', 'Dine-in') == 'Dine-in' ? 'checked' : '' }} required>
                            <label for="order_type_dine_in" class="csh-radio-label">Dine-in</label>
                            <input type="radio" name="order_type" id="order_type_takeout" value="Takeout" {{ old('order_type') == 'Takeout' ? 'checked' : '' }}>
                            <label for="order_type_takeout" class="csh-radio-label">Takeout</label>
                        </div>
                    </div>

                    <div class="csh-form-group">
                        <label for="special_instructions" class="csh-form-label">Special Instructions:</label>
                        <textarea placeholder="Any special instructions? Add here" name="special_instructions" id="special_instructions" class="csh-form-textarea">{{ old('special_instructions') }}</textarea>
                    </div>

                    <div class="csh-form-actions">
                        <button type="submit" class="csh-submit-btn" id="submitOrderBtn">Place Order</button>
                        <button type="button" id="closeModalBtn" class="csh-close-btn">Close</button>
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
                // Fade out the success message after 2 seconds
                setTimeout(() => {
                    const successMessage = document.getElementById('successMessage');
                    if (successMessage) {
                        successMessage.style.opacity = '0';
                        setTimeout(() => {
                            successMessage.classList.add('hidden');
                        }, 500); // Match the transition duration (0.5s)
                    }
                }, 2000); // 2000 milliseconds = 2 seconds
            </script>
        @endif

        <div class="csh-orders-container">
            <div class="csh-orders-list">
                @if ($orders->isEmpty())
                    <p class="csh-no-orders">No orders have been placed yet.</p>
                @else
                    @foreach ($orders as $order)
                        @php
                            // Build the products string for data-products attribute
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
                        <span class="csh-order-details-label">TOTAL</span>
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

        <style>
            .csh-selected-products-table .quantity-container {
                position: relative;
            }
            .csh-selected-products-table .quantity-container input[type="number"] {
                width: 100%;
                padding: 8px;
                border: 2px solid #28a745; /* Green border like the image */
                border-radius: 4px;
            }
            .csh-selected-products-table .quantity-error {
                color: red;
                font-size: 12px;
                margin-top: 5px;
                display: none;
            }
            .csh-submit-btn:disabled {
                background-color: #cccccc;
                cursor: not-allowed;
            }
        </style>

        <script>
            let productIndex = 0;

            // Function to check stock and update submit button state
            function updateSubmitButtonState() {
                const selectedProductsBody = document.getElementById('selected-products-body');
                const submitBtn = document.getElementById('submitOrderBtn');
                let hasInsufficientStock = false;

                Array.from(selectedProductsBody.rows).forEach(row => {
                    const productId = row.querySelector(`input[name$="[product_id]"]`).value;
                    const quantityInput = row.querySelector('input[type="number"]');
                    const quantity = parseInt(quantityInput.value);
                    const stockCell = document.querySelector(`.csh-stock[data-product-id="${productId}"]`);
                    const stock = parseInt(stockCell.textContent);
                    const stockStatusCell = row.cells[3]; // Stock Status column
                    const quantityError = row.querySelector('.quantity-error');

                    if (quantity > stock) {
                        hasInsufficientStock = true;
                        stockStatusCell.textContent = 'Insufficient Stock';
                        stockStatusCell.style.color = 'red';
                        quantityError.style.display = 'block'; // Show inline error message
                    } else {
                        stockStatusCell.textContent = 'In Stock';
                        stockStatusCell.style.color = 'green';
                        quantityError.style.display = 'none'; // Hide inline error message
                    }
                });

                submitBtn.disabled = hasInsufficientStock || selectedProductsBody.rows.length === 0;
            }

            // Tab Switching for Categories
            document.querySelectorAll('.csh-tab-link-categ').forEach(button => {
                button.addEventListener('click', () => {
                    document.querySelectorAll('.csh-tab-link-categ').forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');

                    document.querySelectorAll('.csh-tab-content').forEach(content => {
                        content.style.display = content.id === `${button.dataset.tab}-tab` ? 'block' : 'none';
                    });
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
                    const existingRow = Array.from(selectedProductsBody.rows).find(row => 
                        row.querySelector(`input[name$="[product_id]"]`).value === productId
                    );

                    if (existingRow) {
                        // Update quantity
                        const quantityInput = existingRow.querySelector('input[type="number"]');
                        const newQuantity = parseInt(quantityInput.value) + 1;
                        if (newQuantity <= productStock) {
                            quantityInput.value = newQuantity;
                        } else {
                            const quantityError = existingRow.querySelector('.quantity-error');
                            quantityError.style.display = 'block'; // Show inline error message
                        }
                    } else {
                        // Add new row
                        if (productStock > 0) {
                            const row = document.createElement('tr');
                            row.innerHTML = `
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
                            `;
                            selectedProductsBody.appendChild(row);
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
                    e.target.closest('tr').remove();
                    updateSubmitButtonState();
                }
            });

            // Modal Handling
            const openModalBtn = document.getElementById('openModalBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const orderModal = document.getElementById('orderModal');

            openModalBtn.addEventListener('click', () => {
                orderModal.style.display = 'flex';
            });

            closeModalBtn.addEventListener('click', () => {
                orderModal.style.display = 'none';
            });

            orderModal.addEventListener('click', (e) => {
                if (e.target === orderModal) {
                    orderModal.style.display = 'none';
                }
            });

            // Ensure at least one product is selected before submission
            document.getElementById('orderForm').addEventListener('submit', (e) => {
                const selectedProducts = document.querySelectorAll('#selected-products-body tr');
                if (selectedProducts.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one product to the order.');
                    return;
                }

                let hasInsufficientStock = false;
                Array.from(selectedProducts).forEach(row => {
                    const productId = row.querySelector(`input[name$="[product_id]"]`).value;
                    const quantity = parseInt(row.querySelector('input[type="number"]').value);
                    const stock = parseInt(document.querySelector(`.csh-stock[data-product-id="${productId}"]`).textContent);
                    if (quantity > stock) {
                        hasInsufficientStock = true;
                    }
                });

                if (hasInsufficientStock) {
                    e.preventDefault();
                    // No alert here; the inline error message handles it
                }
            });

            // Reopen modal if errors exist
            @if ($errors->any())
                document.getElementById('orderModal').style.display = 'flex';
            @endif

            // Add click and keyboard interaction to show order details
            const orderDetailsForm = document.getElementById('orderDetailsForm');
            const orderDetailsId = document.getElementById('orderDetailsId');
            const orderDetailsIdDisplay = document.getElementById('orderDetailsIdDisplay');
            const orderDetailsCustomer = document.getElementById('orderDetailsCustomer');
            const orderDetailsTime = document.getElementById('orderDetailsTime');
            const orderDetailsType = document.getElementById('orderDetailsType');
            const orderDetailsProducts = document.getElementById('orderDetailsProducts');
            const orderDetailsTotal = document.getElementById('orderDetailsTotal');
            const orderDetailsInstructions = document.getElementById('orderDetailsInstructions');

            // Function to update order details
            function updateOrderDetails(card) {
                // Highlight the selected card
                document.querySelectorAll('.csh-order-card').forEach(c => c.classList.remove('csh-order-card-selected'));
                card.classList.add('csh-order-card-selected');

                // Populate the order details form
                orderDetailsId.value = card.dataset.orderId;
                orderDetailsIdDisplay.textContent = `Order ${card.dataset.orderId}`;
                orderDetailsCustomer.textContent = card.dataset.customerName;
                orderDetailsTime.textContent = card.dataset.time;
                orderDetailsType.textContent = card.dataset.orderType;
                orderDetailsProducts.textContent = card.dataset.products;
                orderDetailsTotal.textContent = `₱ ${card.dataset.totalPrice}`;
                orderDetailsInstructions.textContent = card.dataset.specialInstructions;

                // Show the form
                orderDetailsForm.style.display = 'block';
            }

            // Attach event listeners to order cards
            document.querySelectorAll('.csh-order-card').forEach(card => {
                card.addEventListener('click', () => updateOrderDetails(card));

                card.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        updateOrderDetails(card);
                    }
                });
            });

            // Automatically select the most recent order on page load
            document.addEventListener('DOMContentLoaded', () => {
                const orderCards = document.querySelectorAll('.csh-order-card');
                if (orderCards.length > 0) {
                    // Find the card with the highest order ID (most recent)
                    let latestCard = null;
                    let highestId = -1;

                    orderCards.forEach(card => {
                        const orderId = parseInt(card.dataset.orderId, 10);
                        if (orderId > highestId) {
                            highestId = orderId;
                            latestCard = card;
                        }
                    });

                    // Trigger updateOrderDetails for the latest card
                    if (latestCard) {
                        updateOrderDetails(latestCard);
                    }
                }
                // Initial check for submit button state
                updateSubmitButtonState();
            });
        </script>
    </div>
@endsection
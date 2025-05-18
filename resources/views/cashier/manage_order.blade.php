@extends('cashier.layout')

@section('title', 'Cashier Dashboard')

@section('styles')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('css/cashier-order.css') }}">
</head>
<div class="csh-main-container">
    <div class="csh-button-container">
        <button id="openModalBtn" class="csh-add-order-btn">New Order</button>
    </div>
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
                           pattern="[a-zA-Z\s',&À-ÿ-]+" title="Only letters, spaces, apostrophes, commas, ampersands, and hyphens are allowed" maxlength="24">
                    <div class="csh-customer-name-error" style="display: none;">
                        Only letters, spaces, apostrophes, commas, ampersands, and hyphens are allowed.
                    </div>
                </div>
                <div class="csh-tabs-categ" role="tablist">
                    @foreach (['meal', 'drink', 'dessert', 'snack'] as $category)
                        <button type="button" class="csh-tab-link-categ {{ $category == 'meal' ? 'active' : '' }}"
                                data-tab="{{ $category }}" role="tab" aria-selected="{{ $category == 'meal' ? 'true' : 'false' }}"
                                tabindex="{{ $category == 'meal' ? '0' : '-1' }}">{{ ucfirst($category) }}</button>
                    @endforeach
                </div>
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
                                        @php
                                            $stock = $shelfItem->quantity_added;
                                            $stockClass = $stock <= 2 ? 'product-critical' : ($stock >= 3 && $stock <= 5 ? 'product-low' : '');
                                        @endphp
                                        <tr>
                                            <td>{{ $shelfItem->product->product_name }}</td>
                                            <td>₱{{ number_format($shelfItem->price, 2) }}</td>
                                            <td class="csh-stock {{ $stockClass }}" data-product-id="{{ $shelfItem->product_id }}">{{ $shelfItem->quantity_added }}</td>
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
                <div id="selected-products">
                    <h3>Selected Products</h3>
                    <table class="csh-selected-products-table" id="selected-products-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="selected-products-body">
                            @if (old('products'))
                                @foreach (old('products') as $index => $product)
                                    @php
                                        $shelfItem = $shelfItems->firstWhere('product_id', $product['product_id']);
                                        $productName = $shelfItem ? $shelfItem->product->product_name : 'Unknown';
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
                    <div class="csh-special-instructions-error" style="display: none;">
                        Only letters, numbers, spaces, apostrophes, commas, ampersands, hyphens, periods, and percentage symbols are allowed.
                    </div>
                </div>
                <div class="csh-form-group">
                    <label for="money_received" class="csh-form-label">Money Received:</label>
                    <div class="csh-money-received-container">
                        <input type="number" name="money_received" id="moneyReceived" min="0" step="0.01"
                               maxlength="6" value="{{ old('money_received') }}" class="csh-form-input"
                               placeholder="Enter amount here.." required>
                        <div class="csh-money-received-error" style="display: none;">Enter an appropriate amount received.</div>
                        <span class="csh-total-display" id="orderTotal">Total: ₱ 0.00</span>
                        <span class="csh-change-display" id="orderChange">Change: ₱ 0.00</span>
                    </div>
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
            setTimeout(() => {
                const successMessage = document.getElementById('successMessage');
                if (successMessage) {
                    successMessage.style.opacity = '0';
                    setTimeout(() => {
                        successMessage.classList.add('hidden');
                    }, 500);
                }
            }, 3000);
        </script>
    @endif
    <div class="csh-orders-container">
        <div class="csh-orders-list">
            <div class="csh-order-card-list">
                @if ($orders->isEmpty())
                    <p class="csh-no-orders">No orders have been placed yet.</p>
                @else
                    @foreach ($orders as $order)
                        @php
                            $totalProfit = 0;
                            foreach ($order->orderItems as $item) {
                                $shelfItem = $shelfItems->firstWhere('product_id', $item->product_id);
                                if ($shelfItem) {
                                    $profitPerItem = $shelfItem->price - $shelfItem->product->purchase_cost;
                                    $totalProfit += $profitPerItem * $item->quantity;
                                }
                            }
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
                             data-total-profit="{{ number_format($totalProfit, 2) }}"
                             data-time="{{ $order->created_at->format('h:i A') }}"
                             data-products="{{ $productsString }}"
                             data-money-received="{{ number_format($order->money_received, 2) }}">
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
            <div class="csh-pagination">
                {{ $orders->links() }}
            </div>
        </div>
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
                <div class="csh-order-details-products" id="orderDetailsProducts">
                    {{-- Render products as a table --}}
                    <table class="csh-order-details-products-table" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left;">Product</th>
                                <th style="text-align:right;">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($orders) && $orders->count())
                                {{-- This section is for server-rendered details (if you want to show the latest order by default) --}}
                                @php
                                    $selectedOrder = $orders->first();
                                @endphp
                                @if($selectedOrder)
                                    @foreach($selectedOrder->orderItems as $item)
                                        <tr>
                                            <td>{{ $item->product->product_name ?? 'N/A' }}</td>
                                            <td style="text-align:right;">{{ $item->quantity }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="csh-order-details-payment">
                    <span class="csh-order-details-label">Payment mode</span>
                    <span class="csh-order-details-value">Cash</span>
                </div>
                <div class="csh-order-details-money-received">
                    <span class="csh-order-details-label">Money Received</span>
                    <span class="csh-order-details-recieved" id="orderDetailsMoneyReceived">₱ 0.00</span>
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
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        let productIndex = {{ old('products') ? count(old('products')) : 0 }};
        let productTables = {};
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const orderModal = document.getElementById('orderModal');
        openModalBtn.addEventListener('click', () => {
            orderModal.style.display = 'flex';
            const activeTab = document.querySelector('.csh-tab-link-categ.active')?.dataset.tab;
            if (activeTab && productTables[activeTab]) {
                productTables[activeTab].draw();
            }
            updateOrderTotal();
        });
        closeModalBtn.addEventListener('click', () => {
            orderModal.style.display = 'none';
            document.getElementById('selected-products-body').innerHTML = '';
            productIndex = 0;
            updateSubmitButtonState();
            document.getElementById('moneyReceived').value = '';
            document.getElementById('orderTotal').textContent = 'Total: ₱ 0.00';
            document.getElementById('orderChange').textContent = 'Change: ₱ 0.00';
            document.querySelector('.csh-money-received-error').style.display = 'none';
            document.querySelector('.csh-customer-name-error').style.display = 'none';
            document.querySelector('.csh-special-instructions-error').style.display = 'none';
            document.getElementById('customer_name').setCustomValidity('');
            document.getElementById('special_instructions').setCustomValidity('');
        });
        orderModal.addEventListener('click', (e) => {
            if (e.target === orderModal) {
                orderModal.style.display = 'none';
                document.getElementById('selected-products-body').innerHTML = '';
                productIndex = 0;
                updateSubmitButtonState();
                document.getElementById('moneyReceived').value = '';
                document.getElementById('orderTotal').textContent = 'Total: ₱ 0.00';
                document.getElementById('orderChange').textContent = 'Change: ₱ 0.00';
                document.querySelector('.csh-money-received-error').style.display = 'none';
                document.querySelector('.csh-customer-name-error').style.display = 'none';
                document.querySelector('.csh-special-instructions-error').style.display = 'none';
                document.getElementById('customer_name').setCustomValidity('');
                document.getElementById('special_instructions').setCustomValidity('');
            }
        });
        const customerNameInput = document.getElementById('customer_name');
        const customerNameError = document.querySelector('.csh-customer-name-error');
        const specialInstructions = document.getElementById('special_instructions');
        const specialInstructionsError = document.querySelector('.csh-special-instructions-error');
        const charCounter = document.getElementById('charCounter');
        const nameRegex = /^[a-zA-Z\s',&À-ÿ-]+$/;
        const instructionsRegex = /^[a-zA-Z0-9\s',&À-ÿ%.-]+$/;
        const maxLength = 255;

        customerNameInput.addEventListener('input', () => {
            const value = customerNameInput.value;
            if (value && !nameRegex.test(value)) {
                customerNameError.style.display = 'block';
                customerNameInput.setCustomValidity('Only letters, spaces, apostrophes, commas, ampersands, and hyphens are allowed.');
            } else {
                customerNameError.style.display = 'none';
                customerNameInput.setCustomValidity('');
            }
        });

        specialInstructions.addEventListener('input', () => {
            const value = specialInstructions.value;
            const remaining = maxLength - value.length;
            charCounter.textContent = `${remaining} characters remaining`;
            if (value && !instructionsRegex.test(value)) {
                specialInstructionsError.style.display = 'block';
                specialInstructions.setCustomValidity('Only letters, numbers, spaces, apostrophes, commas, ampersands, hyphens, periods, and percentage symbols are allowed.');
            } else {
                specialInstructionsError.style.display = 'none';
                specialInstructions.setCustomValidity('');
            }
        });

        const moneyReceivedInput = document.getElementById('moneyReceived');
        moneyReceivedInput.addEventListener('input', () => {
            let value = moneyReceivedInput.value;
            // Remove non-numeric characters except decimal point
            value = value.replace(/[^0-9.]/g, '');
            // Ensure only one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts[1];
            }
            // Limit to 6 characters (e.g., 999.99)
            if (value.length > 6) {
                value = value.substring(0, 6);
            }
            // Ensure valid decimal format (max 2 decimal places)
            if (parts.length === 2) {
                value = parts[0] + (parts[1] ? '.' + parts[1].substring(0, 2) : '');
            }
            moneyReceivedInput.value = value;
            updateSubmitButtonState();
        });

        function updateOrderTotal() {
            const selectedProductsBody = document.getElementById('selected-products-body');
            let total = 0;
            Array.from(selectedProductsBody.rows).forEach(row => {
                const price = parseFloat(row.cells[1].textContent) || 0;
                const quantity = parseInt(row.querySelector('input[type="number"]').value) || 0;
                total += price * quantity;
            });
            document.getElementById('orderTotal').textContent = `Total: ₱ ${total.toFixed(2)}`;
            const moneyReceived = parseFloat(document.getElementById('moneyReceived').value) || 0;
            const change = moneyReceived - total;
            const changeDisplay = document.getElementById('orderChange');
            const submitBtn = document.getElementById('submitOrderBtn');
            const moneyReceivedError = document.querySelector('.csh-money-received-error');
            changeDisplay.textContent = `Change: ₱ ${change.toFixed(2)}`;
            if (change < 0) {
                changeDisplay.classList.add('csh-change-negative');
                moneyReceivedError.style.display = 'block';
                submitBtn.disabled = true;
            } else {
                changeDisplay.classList.remove('csh-change-negative');
                moneyReceivedError.style.display = 'none';
                submitBtn.disabled = selectedProductsBody.rows.length === 0;
            }
        }

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
                const quantityError = row.querySelector('.quantity-error');
                if (quantity > stock) {
                    hasInsufficientStock = true;
                    quantityError.textContent = `Cannot add more items. Stock available: ${stock}`;
                    quantityError.style.display = 'block';
                } else {
                    quantityError.style.display = 'none';
                }
            });
            const moneyReceived = parseFloat(document.getElementById('moneyReceived').value) || 0;
            const total = parseFloat(document.getElementById('orderTotal').textContent.replace('Total: ₱ ', '')) || 0;
            const change = moneyReceived - total;
            submitBtn.disabled = hasInsufficientStock || selectedProductsBody.rows.length === 0 || change < 0;
            updateOrderTotal();
        }

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
                        existingRow.querySelector('.quantity-error').textContent = `Cannot add more items. Stock available: ${productStock}`;
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
                                    <div class="quantity-error">Cannot add more items</div>
                                </td>
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

        document.getElementById('selected-products-body').addEventListener('change', (e) => {
            if (e.target.type === 'number') {
                updateSubmitButtonState();
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('csh-remove-product-btn')) {
                const row = e.target.closest('tr');
                row.remove();
                updateSubmitButtonState();
            }
        });

        document.getElementById('moneyReceived').addEventListener('input', () => {
            updateSubmitButtonState();
        });

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
            const moneyReceived = parseFloat(document.getElementById('moneyReceived').value) || 0;
            const total = parseFloat(document.getElementById('orderTotal').textContent.replace('Total: ₱ ', '')) || 0;
            const change = moneyReceived - total;
            if (hasInsufficientStock) {
                e.preventDefault();
                alert('Please fix insufficient stock issues before submitting.');
            } else if (change < 0) {
                e.preventDefault();
                alert('Please enter an appropriate amount received.');
            }
            if (!nameRegex.test(customerNameInput.value)) {
                e.preventDefault();
                customerNameError.style.display = 'block';
                customerNameInput.setCustomValidity('Only letters, spaces, apostrophes, commas, ampersands, and hyphens are allowed.');
            }
            const specialInstructionsValue = specialInstructions.value.trim();
            if (specialInstructionsValue && !instructionsRegex.test(specialInstructionsValue)) {
                e.preventDefault();
                specialInstructionsError.style.display = 'block';
                specialInstructions.setCustomValidity('Only letters, numbers, spaces, apostrophes, commas, ampersands, hyphens, periods, and percentage symbols are allowed.');
            }
        });

        @if ($errors->any())
            document.getElementById('orderModal').style.display = 'flex';
            customerNameInput.dispatchEvent(new Event('input'));
            specialInstructions.dispatchEvent(new Event('input'));
            updateOrderTotal();
        @endif

        const orderDetailsForm = document.getElementById('orderDetailsForm');
        const orderDetailsId = document.getElementById('orderDetailsId');
        const orderDetailsIdDisplay = document.getElementById('orderDetailsIdDisplay');
        const orderDetailsCustomer = document.getElementById('orderDetailsCustomer');
        const orderDetailsTime = document.getElementById('orderDetailsTime');
        const orderDetailsType = document.getElementById('orderDetailsType');
        const orderDetailsProducts = document.getElementById('orderDetailsProducts');
        const orderDetailsTotal = document.getElementById('orderDetailsTotal');
        const orderDetailsMoneyReceived = document.getElementById('orderDetailsMoneyReceived');
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
            orderDetailsMoneyReceived.textContent = `₱ ${card.dataset.moneyReceived}`;
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
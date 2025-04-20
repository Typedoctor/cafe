@extends('cashier.layout')

@section('title', 'Cashier Dashboard')

@section('content')
    <h1 class="csh-dashboard-title">Cashier Dashboard</h1>

    <!-- Add Order Button -->
    <div class="csh-button-container">
        <button id="openModalBtn" class="csh-add-order-btn">
            Add Order
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
                <div class="csh-tabs">
                    <button type="button" class="csh-tab-link active" data-tab="meal">Meal</button>
                    <button type="button" class="csh-tab-link" data-tab="drink">Drink</button>
                    <button type="button" class="csh-tab-link" data-tab="dessert">Dessert</button>
                    <button type="button" class="csh-tab-link" data-tab="snack">Snack</button>
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
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products->where('category', $category) as $product)
                                        <tr>
                                            <td>{{ $product->product_name }}</td>
                                            
                                            <td>₱{{ number_format($product->price, 2) }}</td>
                                            
                                            <td>
                                                <button type="button" class="csh-add-product-btn" 
                                                        data-product-id="{{ $product->id }}" 
                                                        data-product-name="{{ $product->product_name }}" 
                                                        data-product-price="{{ $product->price }}">  
                                                        Add to Order
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

                <!-- Selected Products -->
                <div id="selected-products">
                    <h3>Selected Products</h3>
                    <table class="csh-selected-products-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
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
                    <button type="submit" class="csh-submit-btn">Place Order</button>
                    <button type="button" id="closeModalBtn" class="csh-close-btn">Close</button>
                </div>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="csh-success-message">
            {{ session('success') }}
        </div>
    @endif

    <h2 class="csh-orders-title">Placed Orders</h2>
    @if ($orders->isEmpty())
        <p class="csh-no-orders">No orders have been placed yet.</p>
    @else
        <table class="csh-orders-table">
            <thead>
                <tr class="csh-table-header">
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Products</th>
                    <th>Order Type</th>
                    <th>Special Instructions</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr class="csh-table-row">
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>
                            @foreach ($order->orderItems as $item)
                                {{ $item->product->product_name ?? 'N/A' }} (Qty: {{ $item->quantity }})<br>
                            @endforeach
                        </td>
                        <td>{{ $order->order_type }}</td>
                        <td>{{ $order->special_instructions ?? 'None' }}</td>
                        <td>₱{{ number_format($order->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    

    <script>
        let productIndex = 0;

        // Tab Switching
        document.querySelectorAll('.csh-tab-link').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.csh-tab-link').forEach(btn => btn.classList.remove('active'));
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

                const selectedProductsBody = document.getElementById('selected-products-body');
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${productName}</td>
                    <td>${parseFloat(productPrice).toFixed(2)}</td>
                    <td>
                        <input type="number" name="products[${productIndex}][quantity]" min="1" value="1" required class="csh-form-input">
                        <input type="hidden" name="products[${productIndex}][product_id]" value="${productId}">
                    </td>
                    <td>
                        <button type="button" class="csh-remove-product-btn">Remove</button>
                    </td>
                `;
                selectedProductsBody.appendChild(row);
                productIndex++;
            });
        });

        // Remove Product from Selected Products
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('csh-remove-product-btn')) {
                e.target.closest('tr').remove();
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

        // Debugging form submission
        document.getElementById('orderForm').addEventListener('submit', (e) => {
            console.log('Form submission attempted');
            console.log('Form data:', new FormData(e.target));
        });

        // Reopen modal if errors exist
        @if ($errors->any())
            document.getElementById('orderModal').style.display = 'flex';
        @endif
    </script>
@endsection
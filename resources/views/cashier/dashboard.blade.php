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
                    <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" required class="csh-form-input">
                </div>

                <div id="product-list">
                    <div class="csh-form-group csh-product-row">
                        <label class="csh-form-label">Product:</label>
                        <select name="products[0][product_id]" required class="csh-form-input">
                            <option value="">Choose a product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ old('products.0.product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->product_name }} ({{ number_format($product->price, 2) }})
                                </option>
                            @endforeach
                        </select>
                        <label for="products[0][quantity]" class="csh-form-label">Quantity:</label>
                        <input type="number" name="products[0][quantity]" min="1" value="{{ old('products.0.quantity', 1) }}" required class="csh-form-input">
                        <button type="button" class="csh-remove-product-btn" style="display:none;">Remove</button>
                    </div>
                </div>
                <div class="csh-form-group">
                    <button type="button" id="addProductBtn" class="csh-add-product-btn">Add Another Product</button>
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
                    <textarea name="special_instructions" id="special_instructions" class="csh-form-textarea">{{ old('special_instructions') }}</textarea>
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
                        <td>{{ number_format($order->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <script>
        let productIndex = 1;

        document.getElementById('addProductBtn').addEventListener('click', () => {
            const productList = document.getElementById('product-list');
            const newRow = document.createElement('div');
            newRow.className = 'csh-form-group csh-product-row';
            newRow.innerHTML = `
                <label class="csh-form-label">Product:</label>
                <select name="products[${productIndex}][product_id]" required class="csh-form-input">
                    <option value="">Choose a product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->product_name }} ({{ number_format($product->price, 2) }})</option>
                    @endforeach
                </select>
                <label for="products[${productIndex}][quantity]" class="csh-form-label">Quantity:</label>
                <input type="number" name="products[${productIndex}][quantity]" min="1" value="1" required class="csh-form-input">
                <button type="button" class="csh-remove-product-btn">Remove</button>
            `;
            productList.appendChild(newRow);
            productIndex++;
            updateRemoveButtons();
        });

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('csh-remove-product-btn')) {
                e.target.parentElement.remove();
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.csh-product-row');
            rows.forEach((row, index) => {
                const removeBtn = row.querySelector('.csh-remove-product-btn');
                removeBtn.style.display = index === 0 && rows.length === 1 ? 'none' : 'inline-block';
            });
        }

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

@extends('cashier.layout')

@section('title', 'Manage Trash')

@section('content')
<h1 class="inventory-title">Manage Trash</h1>

<div class="trsh-top-bar">
    <button id="addTrashBtn" class="btn add-trash">+ Add Trash Entry</button>
    <button id="exportExcel" class="btn export-btn">Export to Excel</button>
</div>

<!-- Search and Filter Section -->
<div class="search-filter-container">
    <form id="searchFilterForm" class="search-filter-form" method="GET">
        <div class="search-box">
            <input type="text" name="search" placeholder="Search products..." value="{{ request('search') }}">
        </div>
        <div class="filter-box">
            <select name="category">
                <option value="all">All Categories</option>
                <option value="snack" {{ request('category') == 'snack' ? 'selected' : '' }}>Snack</option>
                <option value="drink" {{ request('category') == 'drink' ? 'selected' : '' }}>Drink</option>
                <option value="meal" {{ request('category') == 'meal' ? 'selected' : '' }}>Meal</option>
                <option value="dessert" {{ request('category') == 'dessert' ? 'selected' : '' }}>Dessert</option>
            </select>
            <select name="month">
                <option value="all">All Months</option>
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endfor
            </select>
            <select name="year">
                <option value="all">All Years</option>
                @for ($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="btn filter-btn">Apply Filter</button>
            <a href="{{ route('trash.index') }}" class="btn reset-btn">Reset</a>
        </div>
    </form>
</div>

<!-- Add & Edit Trash Modal -->
<div id="trashModal" class="modal">
    <div class="modal-content">
        <span class="close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2 id="modalTitle" style="text-align: center;">Add New Trash Entry</h2>
        
        <!-- Category Tabs -->
        <div class="category-tabs">
            <button class="tab-btn active" data-category="snack">Snack</button>
            <button class="tab-btn" data-category="drink">Drink</button>
            <button class="tab-btn" data-category="meal">Meal</button>
            <button class="tab-btn" data-category="dessert">Dessert</button>
        </div>

        <form id="trashForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <input type="hidden" name="trash_id" id="trashId">
            <input type="hidden" name="category" id="category" value="snack">

            <div class="form-group">
                <label>What product are you discarding?</label>
                <select name="product_name" id="productName" required>
                    <option value="">-- Choose a Product --</option>
                    @if(!empty($products) && $products->count() > 0)
                        @foreach($products as $product)
                            <option value="{{ e(trim($product->product_name)) }}" 
                                    data-price="{{ $product->price }}" 
                                    data-category="{{ $product->category }}">
                                {{ e(trim($product->product_name)) }} (Stock: {{ $product->quantity }})
                            </option>
                        @endforeach
                    @else
                        <option value="" disabled>No products available</option>
                    @endif
                </select>
            </div>

            <div class="form-group">
                <label>How many items?</label>
                <input type="number" name="quantity" id="quantity" min="1" placeholder="e.g., 5" required>
            </div>

            <div class="form-group">
                <label>Why are you discarding it?</label>
                <input type="text" name="reason" id="reason" placeholder="e.g., Expired, Damaged" required>
            </div>
            <div class="total-loss-display">
                <label>Total Loss (₱)</label>
                <div id="totalLossDisplay">Enter product and quantity to see total loss.</div>
            </div>
            <button type="submit" class="btn save-btn" id="saveBtn">Add</button>
        </form>
        <div id="loadingSpinner" style="display: none; text-align: center; margin-top: 10px;">
            <i class="fa-solid fa-spinner fa-spin"></i> Saving...
        </div>
    </div>
</div>

<!-- Duplicate Entry Warning Modal -->
<div id="duplicateModal" class="warning-modal">
    <div class="warning-modal-content">
        <span class="close-warning" name="close-button"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2 style="text-align: center;">Already Added!</h2>
        <p style="text-align: center;" id="duplicateMessage">This product is already in the trash list. Please edit the existing entry instead.</p>
        <button class="warning-close-duplicate">OK</button>
    </div>
</div>

<!-- Trash Table -->
<div class="trsh-table-container" id="transaction-table">
    <table class="inventory-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Reason</th>
                <th>Total Loss</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trashes as $trash)
            <tr>
                <td>{{ $trash->id }}</td>
                <td>{{ $trash->product_name }}</td>
                <td>{{ $trash->category }}</td>
                <td>{{ $trash->quantity }}</td>
                <td>{{ $trash->reason }}</td>
                <td>₱{{ number_format($trash->total_loss, 2) }}</td>
                <td>
                    <button class="btn edit-btn" data-id="{{ $trash->id }}" 
                            data-name="{{ e(trim($trash->product_name)) }}" 
                            data-category="{{ $trash->category }}" 
                            data-quantity="{{ $trash->quantity }}" 
                            data-reason="{{ $trash->reason }}"
                            data-total-loss="{{ $trash->total_loss }}">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <form action="{{ route('trash.destroy', $trash) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this trash entry?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn delete-btn"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


<style>
.total-loss-display {
    margin-top: 15px;
}
.total-loss-display label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}
.total-loss-display #totalLossDisplay {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: #f9f9f9;
    width: 100%;
    box-sizing: border-box;
    color: #333;
}
.category-tabs {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}
.tab-btn {
    padding: 10px 20px;
    margin: 0 5px;
    border: none;
    background: #f1f1f1;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
}
.tab-btn.active {
    background: #007bff;
    color: white;
}
.tab-btn:hover {
    background: #ddd;
}
.tab-btn.active:hover {
    background: #0056b3;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const trashModal = document.getElementById('trashModal');
    const duplicateModal = document.getElementById('duplicateModal');
    const trashForm = document.getElementById('trashForm');
    const modalTitle = document.getElementById('modalTitle');
    const methodField = document.getElementById('methodField');
    const saveBtn = document.getElementById('saveBtn');
    const closeBtns = document.querySelectorAll('.close-btn');
    const closeWarn = document.querySelector('.close-warning');
    const closeDuplicateBtn = document.querySelector('.warning-close-duplicate');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const productSelect = document.getElementById('productName');
    const quantityInput = document.getElementById('quantity');
    const totalLossDisplay = document.getElementById('totalLossDisplay');
    const categoryInput = document.getElementById('category');
    const tabButtons = document.querySelectorAll('.tab-btn');

    // Store all product options
    const allProductOptions = Array.from(productSelect.options).slice(1); // Exclude the "-- Choose a Product --" option

    // Function to filter products by category
    function filterProductsByCategory(category) {
        // Clear current options except the placeholder
        while (productSelect.options.length > 1) {
            productSelect.remove(1);
        }

        // Add filtered options
        const filteredOptions = allProductOptions.filter(option => 
            option.getAttribute('data-category') === category || !option.getAttribute('data-category')
        );
        filteredOptions.forEach(option => {
            const newOption = document.createElement('option');
            newOption.value = option.value;
            newOption.text = option.text;
            newOption.setAttribute('data-price', option.getAttribute('data-price'));
            newOption.setAttribute('data-category', option.getAttribute('data-category'));
            productSelect.appendChild(newOption);
        });

        // Reset product selection
        productSelect.value = '';
        updateTotalLoss();
    }

    // Function to calculate and update total loss display
    function updateTotalLoss() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const price = selectedOption && selectedOption.getAttribute('data-price') ? parseFloat(selectedOption.getAttribute('data-price')) : 0;
        const quantity = parseInt(quantityInput.value) || 0;
        const totalLoss = price * quantity;
        totalLossDisplay.textContent = totalLoss > 0 ? `₱${totalLoss.toFixed(2)}` : 'Enter product and quantity to see total loss.';
    }

    // Event listeners for real-time total loss calculation
    productSelect.addEventListener('change', updateTotalLoss);
    quantityInput.addEventListener('input', updateTotalLoss);

    // Tab click event
    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Update active tab
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Update category input and filter products
            const category = this.getAttribute('data-category');
            categoryInput.value = category;
            filterProductsByCategory(category);
        });
    });

    // Show modal for new trash entry
    document.getElementById('addTrashBtn').addEventListener('click', function () {
        // Remove dynamically added options
        Array.from(productSelect.options).forEach(option => {
            if (option.text.includes('(Not in current inventory)')) {
                option.remove();
            }
        });

        modalTitle.innerText = 'Add New Trash Entry';
        methodField.value = 'POST';
        trashForm.action = '{{ route('trash.store') }}';
        trashModal.style.display = 'block';
        saveBtn.innerText = 'Add';
        trashForm.reset();
        productSelect.value = '';
        categoryInput.value = 'snack';
        totalLossDisplay.textContent = 'Enter product and quantity to see total loss.';
        loadingSpinner.style.display = 'none';
        saveBtn.disabled = false;

        // Reset tabs
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabButtons[0].classList.add('active');
        filterProductsByCategory('snack');
        updateTotalLoss();
    });

    // Show modal for editing trash entry
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const productName = this.dataset.name ? this.dataset.name.trim() : '';
            const productCategory = this.dataset.category || 'snack';

            // Debug
            console.log('Editing product name:', productName);
            console.log('Available options:', Array.from(productSelect.options).map(opt => opt.value));

            // Check if the product name exists in the select options
            const optionExists = Array.from(productSelect.options).some(option => option.value === productName);

            if (!optionExists && productName) {
                // Add the missing product as an option with zero price
                const newOption = document.createElement('option');
                newOption.value = productName;
                newOption.text = productName + ' (Not in current inventory)';
                newOption.setAttribute('data-price', '0');
                newOption.setAttribute('data-category', productCategory);
                newOption.selected = true;
                productSelect.appendChild(newOption);
            } else {
                // Set the value if it exists
                productSelect.value = productName || '';
            }

            // Warn if the product name couldn't be set
            if (!productSelect.value && productName) {
                console.warn(`Product "${productName}" not found in select options.`);
                alert(`The product "${productName}" is not available in the current inventory. Please select another product or contact support.`);
            }

            modalTitle.innerText = 'Edit Trash Entry';
            methodField.value = 'PUT';
            trashForm.action = '{{ url('trash') }}/' + this.dataset.id;
            document.getElementById('trashId').value = this.dataset.id;
            categoryInput.value = productCategory;
            document.getElementById('quantity').value = this.dataset.quantity || '';
            document.getElementById('reason').value = this.dataset.reason || '';
            saveBtn.innerText = 'Update';
            trashModal.style.display = 'block';
            loadingSpinner.style.display = 'none';
            saveBtn.disabled = false;

            // Set active tab
            tabButtons.forEach(btn => btn.classList.remove('active'));
            const activeTab = Array.from(tabButtons).find(btn => btn.getAttribute('data-category') === productCategory);
            if (activeTab) activeTab.classList.add('active');
            filterProductsByCategory(productCategory);
            productSelect.value = productName || '';
            updateTotalLoss();
        });
    });

    // Check for duplicate product names
    trashForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const productName = document.getElementById('productName').value.trim();
        const category = document.getElementById('category').value.trim();
        const quantity = document.getElementById('quantity').value.trim();
        const reason = document.getElementById('reason').value.trim();

        if (!productName || !category || !quantity || !reason) {
            alert('Please fill in all fields!');
            return;
        }

        if (methodField.value === 'POST') {
            const existingProducts = Array.from(document.querySelectorAll('tbody tr')).map(row =>
                row.querySelector('td:nth-child(2)').innerText.trim().toLowerCase()
            );
            if (existingProducts.includes(productName.toLowerCase())) {
                duplicateModal.style.display = 'block';
                return;
            }
        }

        loadingSpinner.style.display = 'block';
        saveBtn.disabled = true;
        trashForm.submit();
    });

    // Close modals
    closeBtns.forEach(btn => btn.addEventListener('click', () => {
        trashModal.style.display = 'none';
        duplicateModal.style.display = 'none';
        loadingSpinner.style.display = 'none';
        saveBtn.disabled = false;
    }));
    closeDuplicateBtn.addEventListener('click', () => {
        duplicateModal.style.display = 'none';
    });
    closeWarn.addEventListener('click', () => {
        duplicateModal.style.display = 'none';
    });

    // Export to Excel
    document.getElementById('exportExcel').addEventListener('click', function () {
        const table = document.querySelector('.inventory-table');
        const wb = XLSX.utils.table_to_book(table);
        XLSX.writeFile(wb, 'trash_entries.xlsx');
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
@endsection

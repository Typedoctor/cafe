@extends('cashier.layout')

@section('title', 'Manage Trash')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<head>
    <link rel="stylesheet" href="{{ asset('css/cashier-trash.css') }}">
</head>

<h1 class="inventory-title">Manage Trash</h1>

<div class="trsh-top-bar">
    <button id="addTrashBtn" class="btn add-trash">+ Add Trash Entry</button>
    <button id="exportExcel" class="btn export-btn">Export to Excel</button>
</div>

<!-- Add Trash Modal -->
<div id="trashModal" class="modal">
    <div class="modal-content">
        <span class="close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2 id="modalTitle" style="text-align: center;">Add New Spoilage Entry</h2>
        
        <!-- Category Tabs -->
        <div class="category-tabs">
            <button class="tab-btn active" data-category="snack">Snack</button>
            <button class="tab-btn" data-category="drink">Drink</button>
            <button class="tab-btn" data-category="meal">Meal</button>
            <button class="tab-btn" data-category="dessert">Dessert</button>
        </div>

        <form id="trashForm" method="POST" action="{{ route('trash.store') }}">
            @csrf
            <input type="hidden" name="category" id="category" value="snack">

            <div class="form-group">
                <label>What product are you discarding?</label>
                <select name="product_name" id="productName" required>
                    <option value="">-- Choose a Product --</option>
                    @if(!empty($shelfItems) && $shelfItems->count() > 0)
                        @foreach($shelfItems as $shelfItem)
                            <option value="{{ e(trim($shelfItem->product->product_name)) }}" 
                                    data-price="{{ $shelfItem->price }}" 
                                    data-category="{{ $shelfItem->product->category }}"
                                    data-stock="{{ $shelfItem->quantity_added }}">
                                {{ e(trim($shelfItem->product->product_name)) }} (Stock: {{ $shelfItem->quantity_added }})
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
                <span id="quantityError" style="color: red; display: none;">Insufficient stock!</span>
            </div>

            <div class="form-group">
                <label>Why are you discarding it?</label>
                <textarea type="text" name="reason" id="reason" placeholder="e.g., Expired, Damaged" pattern="[A-Za-z\s]+" maxlength="255"></textarea>
                <div class="trsh-char-counter" id="charCounter">255 characters remaining</div>
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
        <p style="text-align: center;" id="duplicateMessage">This product is already in the list. Please delete and add a new entry if needed.</p>
        <button class="warning-close-duplicate">OK</button>
    </div>
</div>

<!-- Trash Table -->
<div class="trsh-table-container" id="transaction-table">
    <table class="inventory-table" id="trashTable">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize DataTables
    $('#trashTable').DataTable({
        pageLength: 10,
        responsive: true,
        searching: true,
        lengthChange: true,
        paging: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: 6 }
        ],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            paginate: {
                previous: "Previous",
                next: "Next"
            },
            emptyTable: "No trash entries available."
        }
    });

    // Character counter for reason textarea
    const reasonInput = document.getElementById('reason');
    const charCounter = document.getElementById('charCounter');
    const maxLength = 255;

    reasonInput.addEventListener('input', () => {
        const remaining = maxLength - reasonInput.value.length;
        charCounter.textContent = `${remaining} characters remaining`;
    });

    // Modal and form handling
    const trashModal = document.getElementById('trashModal');
    const duplicateModal = document.getElementById('duplicateModal');
    const trashForm = document.getElementById('trashForm');
    const closeBtns = document.querySelectorAll('.close-btn');
    const closeWarn = document.querySelector('.close-warning');
    const closeDuplicateBtn = document.querySelector('.warning-close-duplicate');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const productSelect = document.getElementById('productName');
    const quantityInput = document.getElementById('quantity');
    const quantityError = document.getElementById('quantityError');
    const totalLossDisplay = document.getElementById('totalLossDisplay');
    const categoryInput = document.getElementById('category');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const saveBtn = document.getElementById('saveBtn');

    // Prevent 'e' in quantity input
    quantityInput.addEventListener('keydown', function (e) {
        if (e.key === 'e' || e.key === 'E') {
            e.preventDefault();
        }
    });

    // Restrict reason input to letters and spaces only
    reasonInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^A-Za-z\s]/g, '');
    });

    // Store all product options
    const allProductOptions = Array.from(productSelect.options).slice(1);

    // Function to filter products by category
    function filterProductsByCategory(category) {
        while (productSelect.options.length > 1) {
            productSelect.remove(1);
        }

        const filteredOptions = allProductOptions.filter(option => 
            option.getAttribute('data-category') === category || !option.getAttribute('data-category')
        );
        filteredOptions.forEach(option => {
            const newOption = document.createElement('option');
            newOption.value = option.value;
            newOption.text = option.text;
            newOption.setAttribute('data-price', option.getAttribute('data-price'));
            newOption.setAttribute('data-category', option.getAttribute('data-category'));
            newOption.setAttribute('data-stock', option.getAttribute('data-stock'));
            productSelect.appendChild(newOption);
        });

        productSelect.value = '';
        updateTotalLoss();
        quantityError.style.display = 'none';
    }

    // Function to calculate and update total loss display
    function updateTotalLoss() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const price = selectedOption && selectedOption.getAttribute('data-price') ? parseFloat(selectedOption.getAttribute('data-price')) : 0;
        const quantity = parseInt(quantityInput.value) || 0;
        const totalLoss = price * quantity;
        totalLossDisplay.textContent = totalLoss > 0 ? `₱${totalLoss.toFixed(2)}` : 'Enter product and quantity to see total loss.';
    }

    // Validate quantity against stock
    function validateQuantity() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const stock = selectedOption && selectedOption.getAttribute('data-stock') ? parseInt(selectedOption.getAttribute('data-stock')) : 0;
        const quantity = parseInt(quantityInput.value) || 0;
        
        if (quantity > stock && stock !== null) {
            quantityError.style.display = 'block';
            saveBtn.disabled = true;
        } else {
            quantityError.style.display = 'none';
            saveBtn.disabled = false;
        }
    }

    // Event listeners for real-time total loss calculation and quantity validation
    productSelect.addEventListener('change', function () {
        updateTotalLoss();
        validateQuantity();
    });
    quantityInput.addEventListener('input', function () {
        updateTotalLoss();
        validateQuantity();
    });

    // Tab click event
    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const category = this.getAttribute('data-category');
            categoryInput.value = category;
            filterProductsByCategory(category);
        });
    });

    // Show modal for new trash entry
    document.getElementById('addTrashBtn').addEventListener('click', function () {
        Array.from(productSelect.options).forEach(option => {
            if (option.text.includes('(Not in current inventory)')) {
                option.remove();
            }
        });

        trashModal.style.display = 'block';
        trashForm.reset();
        productSelect.value = '';
        categoryInput.value = 'snack';
        totalLossDisplay.textContent = 'Enter product and quantity to see total loss.';
        loadingSpinner.style.display = 'none';
        saveBtn.disabled = false;
        quantityError.style.display = 'none';

        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabButtons[0].classList.add('active');
        filterProductsByCategory('snack');
        updateTotalLoss();
    });

    // Check for duplicate product names and validate form
    trashForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const productName = document.getElementById('productName').value.trim();
        const category = document.getElementById('category').value.trim();
        const quantity = document.getElementById('quantity').value.trim();
        const reason = document.getElementById('reason').value.trim();
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const stock = selectedOption && selectedOption.getAttribute('data-stock') ? parseInt(selectedOption.getAttribute('data-stock')) : 0;

        // Basic form validation
        if (!productName || !category || !quantity || !reason) {
            alert('Please fill in all fields!');
            return;
        }

        if (!/^[A-Za-z\s]+$/.test(reason)) {
            alert('Reason can only contain letters and spaces!');
            return;
        }

        if (parseInt(quantity) > stock && stock !== null) {
            quantityError.style.display = 'block';
            alert('Insufficient stock! The quantity entered exceeds available stock.');
            return;
        }

        // Duplicate check
        const rows = document.querySelectorAll('#trashTable tbody tr');
        let existingProducts = [];

        // Iterate through rows and safely extract product names
        rows.forEach(row => {
            const productCell = row.querySelector('td:nth-child(2)');
            // Only include rows with a valid product cell
            if (productCell) {
                existingProducts.push(productCell.innerText.trim().toLowerCase());
            }
        });

        // Check for duplicates
        if (existingProducts.includes(productName.toLowerCase())) {
            duplicateModal.style.display = 'block';
            return;
        }

        // Proceed with form submission
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
        quantityError.style.display = 'none';
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
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
@endsection
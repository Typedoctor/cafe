@extends('cashier.layout')

@section('title', 'Manage Trash')

@section('content')
<h1 class="inventory-title">Manage Trash</h1>

<div class="top-bar">
    <button id="addTrashBtn" class="btn add-trash">+ Add Trash Entry</button>
    <button id="exportExcel" class="btn export-btn">Export to Excel</button>
</div>

<!-- Search and Filter Section (Unchanged) -->
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

<!-- Simplified Add & Edit Trash Modal with Original Styles, Fully Non-Clickable Total Loss -->
<div id="trashModal" class="modal">
    <div class="modal-content">
        <span class="close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2 id="modalTitle" style="text-align: center;">Add New Trash Entry</h2>
        <form id="trashForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <input type="hidden" name="trash_id" id="trashId">

            <div class="form-group">
                <label>What product are you discarding?</label>
                <select name="product_name" id="productName" required>
                    <option value="">-- Choose a Product --</option>
                    @if(!empty($products) && $products->count() > 0)
                        @foreach($products as $product)
                            <option value="{{ $product->product_name }}" data-price="{{ $product->price ?? 0 }}">
                                {{ $product->product_name }} (Stock: {{ $product->quantity }})
                            </option>
                        @endforeach
                    @else
                        <option value="" disabled>No products available</option>
                    @endif
                </select>
            </div>

            <div class="form-group">
                <label>Product Type</label>
                <select name="category" id="category" required>
                    <option value="">-- Choose a Type --</option>
                    <option value="snack">Snack</option>
                    <option value="drink">Drink</option>
                    <option value="meal">Meal</option>
                    <option value="dessert">Dessert</option>
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

            <div class="form-group">
                <label>Total Cost (₱)</label>
                <input type="number"  id="totalLoss"  placeholder="0.00" disabled tabindex="-1" onfocus="this.blur()" required>
               
            </div>

            <button type="submit" class="btn save-btn" id="saveBtn">Add</button>
        </form>
        <div id="loadingSpinner" style="display: none; text-align: center; margin-top: 10px;">
            <i class="fa-solid fa-spinner fa-spin"></i> Saving...
        </div>
    </div>
</div>

<!-- Simplified Duplicate Entry Warning Modal with Original Styles -->
<div id="duplicateModal" class="warning-modal">
    <div class="warning-modal-content">
        <span class="close-warning" name="close-button"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2 style="text-align: center;">Already Added!</h2>
        <p style="text-align: center;" id="duplicateMessage">This product is already in the trash list. Please edit the existing entry instead.</p>
        <button class="warning-close-duplicate">OK</button>
    </div>
</div>

<!-- Trash Table (Unchanged) -->
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
                        data-name="{{ $product->product_name }}" 
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    const trashModal = document.getElementById("trashModal");
    const duplicateModal = document.getElementById("duplicateModal");
    const trashForm = document.getElementById("trashForm");
    const modalTitle = document.getElementById("modalTitle");
    const methodField = document.getElementById("methodField");
    const saveBtn = document.getElementById("saveBtn");
    const closeBtns = document.querySelectorAll(".close-btn");
    const closeWarn = document.querySelector(".close-warning");
    const closeDuplicateBtn = document.querySelector(".warning-close-duplicate");
    const loadingSpinner = document.getElementById("loadingSpinner");
    const productName = document.getElementById("productName");
    const quantity = document.getElementById("quantity");
    const totalLoss = document.getElementById("totalLoss");
    const hiddenTotalLoss = document.getElementById("hiddenTotalLoss");

    // Calculate total loss
    function calculateTotalLoss() {
        const selectedProduct = productName.options[productName.selectedIndex];
        const price = selectedProduct ? parseFloat(selectedProduct.dataset.price || 0) : 0;
        const qty = parseInt(quantity.value || 0);
        const calculatedLoss = (price * qty).toFixed(2);
        totalLoss.value = calculatedLoss;
        hiddenTotalLoss.value = calculatedLoss;
    }

    // Update total loss on product or quantity change
    productName.addEventListener("change", calculateTotalLoss);
    quantity.addEventListener("input", calculateTotalLoss);

    // Show modal for new trash entry
    document.getElementById("addTrashBtn").addEventListener("click", function () {
        modalTitle.innerText = "Add New Trash Entry";
        methodField.value = "POST";
        trashForm.action = "{{ route('trash.store') }}";
        trashModal.style.display = "block";
        saveBtn.innerText = "Add";
        trashForm.reset();
        productName.value = "";
        document.getElementById("category").value = "";
        totalLoss.value = "";
        hiddenTotalLoss.value = "";
        loadingSpinner.style.display = "none";
        saveBtn.disabled = false;
    });

    // Show modal for editing trash entry
    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", function () {
            modalTitle.innerText = "Edit Trash Entry";
            methodField.value = "PUT";
            trashForm.action = "{{ url('trash') }}/" + this.dataset.id;
            document.getElementById("trashId").value = this.dataset.id;
            productName.value = this.dataset.name || "";
            document.getElementById("category").value = this.dataset.category || "";
            quantity.value = this.dataset.quantity || "";
            document.getElementById("reason").value = this.dataset.reason || "";
            totalLoss.value = this.dataset.totalLoss || "";
            hiddenTotalLoss.value = this.dataset.totalLoss || "";
            saveBtn.innerText = "Update";
            trashModal.style.display = "block";
            loadingSpinner.style.display = "none";
            saveBtn.disabled = false;
            calculateTotalLoss(); // Recalculate total loss in case product/quantity changes
        });
    });

    // Check for duplicate product names
    trashForm.addEventListener("submit", function (event) {
        event.preventDefault();
        const productNameValue = productName.value.trim();
        const category = document.getElementById("category").value.trim();
        const quantityValue = quantity.value.trim();
        const reason = document.getElementById("reason").value.trim();
        const totalLossValue = hiddenTotalLoss.value.trim();

        if (!productNameValue || !category || !quantityValue || !reason || !totalLossValue) {
            alert("Please fill in all fields!");
            return;
        }

        if (methodField.value === "POST") {
            const existingProducts = Array.from(document.querySelectorAll("tbody tr")).map(row =>
                row.querySelector("td:nth-child(2)").innerText.trim().toLowerCase()
            );
            if (existingProducts.includes(productNameValue.toLowerCase())) {
                duplicateModal.style.display = "block";
                return;
            }
        }

        loadingSpinner.style.display = "block";
        saveBtn.disabled = true;
        trashForm.submit();
    });

    // Close modals
    closeBtns.forEach(btn => btn.addEventListener("click", () => {
        trashModal.style.display = "none";
        duplicateModal.style.display = "none";
        loadingSpinner.style.display = "none";
        saveBtn.disabled = false;
    }));
    closeDuplicateBtn.addEventListener("click", () => {
        duplicateModal.style.display = "none";
    });
    closeWarn.addEventListener("click", () => {
        duplicateModal.style.display = "none";
    });

    // Export to Excel (Unchanged)
    document.getElementById("exportExcel").addEventListener("click", function() {
        const table = document.querySelector(".inventory-table");
        const wb = XLSX.utils.table_to_book(table);
        XLSX.writeFile(wb, "trash_entries.xlsx");
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
@endsection
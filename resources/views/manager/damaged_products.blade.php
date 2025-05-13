@extends('manager.layout')

@section('title', 'Damaged Products')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/manager-damaged.css') }}">
@endpush

@section('content')
<h1 class="dmg-title">Damaged Products</h1>

<div id="damagedProductModal" class="dmg-modal">
    <div class="dmg-modal-content">
        <span class="dmg-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2 id="modalTitle">Report Damaged Product</h2>
        <div id="errorMessages" class="dmg-error-messages" style="display: none;"></div>
        <form id="damagedProductForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <input type="hidden" name="id" id="damagedProductId">
            <div class="dmg-form-group">
                <label>Product Name:</label>
                <input type="text" name="product_name" id="productName" required maxlength="50">
                <small id="productNameCount">0 / 50</small>
                <span id="productNameInvalid" class="invalid-indicator">Only letters and spaces allowed</span>
            </div>
            <div class="dmg-form-group">
                <label>Quantity:<br><span class="quantity-note">Must be between 1 and 1200</span></label>
                <input type="number" name="quantity" id="quantity" min="1" max="1200" required>
            </div>
            <div class="dmg-form-group">
                <label>Price per Item (₱):<br><span class="quantity-note">Enter the cost per unit</span></label>
                <input type="number" name="price_per_item" id="pricePerItem" step="0.01" min="0.01" required>
                <small id="totalCostDisplay">Total Cost: ₱0.00</small>
            </div>
            <div class="dmg-form-group">
                <label>Reason:</label>
                <textarea name="reason" id="reason" required maxlength="100"></textarea>
                <small id="reasonCount">0 / 100</small>
            </div>
            <div class="dmg-form-group">
                <label>Supplier:</label>
                <input type="text" name="supplier" id="supplier" required maxlength="50">
                <small id="supplierCount">0 / 50</small>
            </div>
            <div class="dmg-form-group">
                <label>Status:</label>
                <select name="status" id="status" required>
                    <option value="Successfully Returned">Successfully Returned</option>
                    <option value="Marked as Loss">Marked as Loss</option>
                </select>
            </div>
            <div class="dmg-form-group">
                <label>Return Notes (Optional):</label>
                <textarea name="return_notes" id="returnNotes" maxlength="255"></textarea>
                <small id="returnNotesCount">0 / 255</small>
            </div>
            <div class="dmg-form-group">
                <label>Reported At:</label>
                <input type="datetime-local" name="reported_at" id="reportedAt">
            </div>
            <button type="submit" class="dmg-btn dmg-save-btn" id="SaveBtn">ADD</button>
        </form>
        <div id="loadingSpinner" style="display: none; text-align: center; margin-top: 10px;">
            <i class="fa-solid fa-spinner fa-spin"></i> Saving...
        </div>
    </div>
</div>

<div id="returnModal" class="dmg-modal">
    <div class="dmg-modal-content">
        <span class="dmg-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2>Mark as Returned</h2>
        <div id="returnErrorMessages" class="dmg-error-messages" style="display: none;"></div>
        <form id="returnForm" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="id" id="returnProductId">
            <div class="dmg-form-group">
                <label>Return Notes (Optional):</label>
                <textarea name="return_notes" id="returnNotesField" maxlength="255"></textarea>
                <small id="returnNotesFieldCount">0 / 255</small>
            </div>
            <button type="submit" class="dmg-btn dmg-save-btn" id="returnSaveBtn">Mark as Returned</button>
        </form>
        <div id="returnLoadingSpinner" style="display: none; text-align: center; margin-top: 10px;">
            <i class="fa-solid fa-spinner fa-spin"></i> Saving...
        </div>
    </div>
</div>

@if (session('success'))
    <div class="dmg-success-message">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="dmg-error-messages">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="dmg-error-messages">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
    </div>
@endif

<div class="dmg-header-row">
    <div class="dmg-tabs">
        <div class="dmg-tab active" data-status="all">All</div>
        <div class="dmg-tab" data-status="Successfully Returned">Successfully Returned</div>
        <div class="dmg-tab" data-status="Marked as Loss">Marked as Loss</div>
    </div>
    <button id="addDamagedProductBtn" class="dmg-btn dmg-add-btn">+ Report Damaged Product</button>
</div>

<div class="dmg-table-container">
    <div class="dmg-section-title" id="sectionTitle">All Damaged Products List</div>
    <table class="dmg-table" id="damagedProductsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price per Item</th>
                <th>Total Cost</th>
                <th>Reason</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Reported At</th>
                <th>Return Date</th>
                <th>Return Notes</th>
                <th style="width: 80px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($damagedProducts as $damagedProduct)
            <tr>
                <td>{{ $damagedProduct->id }}</td>
                <td>{{ $damagedProduct->product_name }}</td>
                <td>{{ $damagedProduct->quantity }}</td>
                <td>₱{{ number_format($damagedProduct->price_per_item, 2) }}</td>
                <td>₱{{ number_format($damagedProduct->total_cost, 2) }}</td>
                <td>{{ $damagedProduct->reason }}</td>
                <td>{{ $damagedProduct->supplier }}</td>
                <td>{{ $damagedProduct->status }}</td>
                <td>{{ $damagedProduct->reported_at->format('F j Y/ g:i A') }}</td>
                <td>{{ $damagedProduct->return_date ? $damagedProduct->return_date->format('F j Y/ g:i A') : '-' }}</td>
                <td>{{ $damagedProduct->return_notes ?? '-' }}</td>
                <td>
                    <button class="dmg-btn dmg-edit-btn" 
                        data-id="{{ $damagedProduct->id }}"
                        data-product_name="{{ $damagedProduct->product_name }}"
                        data-quantity="{{ $damagedProduct->quantity }}"
                        data-price_per_item="{{ $damagedProduct->price_per_item }}"
                        data-reason="{{ $damagedProduct->reason }}"
                        data-supplier="{{ $damagedProduct->supplier }}"
                        data-status="{{ $damagedProduct->status }}"
                        data-return_notes="{{ $damagedProduct->return_notes }}"
                        data-reported_at="{{ $damagedProduct->reported_at->format('Y-m-d\TH:i') }}">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <form action="{{ route('damaged-products.destroy', $damagedProduct) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this damaged product report?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dmg-btn dmg-delete-btn"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const table = $('#damagedProductsTable').DataTable({
                pageLength: 10,
                responsive: true,
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: 11 }],
                pagingType: 'simple_numbers',
                language: {
                    paginate: {
                        previous: 'Previous',
                        next: 'Next'
                    }
                }
            });

            const successMessage = document.querySelector(".dmg-success-message");
            if (successMessage) {
                setTimeout(() => successMessage.style.display = "none", 3000);
            }

            document.querySelectorAll('.dmg-tab').forEach(tab => {
                tab.addEventListener('click', function () {
                    document.querySelectorAll('.dmg-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    const status = this.dataset.status;
                    table.column(7).search(status === 'all' ? '' : status).draw();
                    const sectionTitle = document.getElementById('sectionTitle');
                    if (status === 'all') {
                        sectionTitle.textContent = 'All Damaged Products List';
                    } else if (status === 'Successfully Returned') {
                        sectionTitle.textContent = 'Successfully Returned Items List';
                    } else if (status === 'Marked as Loss') {
                        sectionTitle.textContent = 'Marked as Loss Items List';
                    }
                });
            });

            const damagedProductModal = document.getElementById("damagedProductModal");
            const damagedProductForm = document.getElementById("damagedProductForm");
            const returnModal = document.getElementById("returnModal");
            const returnForm = document.getElementById("returnForm");
            const modalTitle = document.getElementById("modalTitle");
            const methodField = document.getElementById("methodField");
            const SaveBtn = document.getElementById("SaveBtn");
            const returnSaveBtn = document.getElementById("returnSaveBtn");
            const closeBtn = document.querySelectorAll(".dmg-close-btn");
            const productNameInput = document.getElementById("productName");
            const quantityInput = document.getElementById("quantity");
            const pricePerItemInput = document.getElementById("pricePerItem");
            const reasonInput = document.getElementById("reason");
            const supplierInput = document.getElementById("supplier");
            const statusInput = document.getElementById("status");
            const returnNotesInput = document.getElementById("returnNotes");
            const returnNotesField = document.getElementById("returnNotesField");
            const productNameCount = document.getElementById("productNameCount");
            const reasonCount = document.getElementById("reasonCount");
            const supplierCount = document.getElementById("supplierCount");
            const returnNotesCount = document.getElementById("returnNotesCount");
            const returnNotesFieldCount = document.getElementById("returnNotesFieldCount");
            const totalCostDisplay = document.getElementById("totalCostDisplay");
            const errorMessages = document.getElementById("errorMessages");
            const returnErrorMessages = document.getElementById("returnErrorMessages");
            const productNameInvalid = document.getElementById("productNameInvalid");
            const loadingSpinner = document.getElementById("loadingSpinner");
            const returnLoadingSpinner = document.getElementById("returnLoadingSpinner");

            function showError(element, message) {
                element.style.display = 'block';
                element.innerHTML = message;
            }

            function clearErrors() {
                errorMessages.style.display = 'none';
                returnErrorMessages.style.display = 'none';
                errorMessages.innerHTML = '';
                returnErrorMessages.innerHTML = '';
                productNameInvalid.style.display = 'none';
                productNameInput.classList.remove('product-name-error');
                quantityInput.classList.remove('quantity-error');
                pricePerItemInput.classList.remove('price-error');
            }

            function updateCharacterCount(inputId, countId, maxLength) {
                const input = document.getElementById(inputId);
                const count = document.getElementById(countId);
                if (input && count) {
                    count.textContent = `${input.value.length} / ${maxLength}`;
                }
            }

            function updateTotalCost() {
                const quantity = parseInt(quantityInput.value) || 0;
                const price = parseFloat(pricePerItemInput.value) || 0;
                totalCostDisplay.textContent = `Total Cost: ₱${(quantity * price).toFixed(2)}`;
            }

            function enforceValidProductName() {
                productNameInput.addEventListener("input", function () {
                    const value = this.value.replace(/[^a-zA-Z\s]/g, '');
                    this.value = value;
                    productNameInvalid.style.display = value === this.value ? 'none' : 'block';
                    productNameInput.classList.toggle('product-name-error', value !== this.value);
                    updateCharacterCount('productName', 'productNameCount', 50);
                });
            }

            function enforceValidQuantity() {
                quantityInput.addEventListener("input", function () {
                    let val = parseInt(this.value) || 1;
                    this.value = Math.min(Math.max(val, 1), 1200);
                    updateTotalCost();
                });
            }

            function enforceValidPrice() {
                pricePerItemInput.addEventListener("input", function () {
                    let val = parseFloat(this.value) || 1;
                    this.value = Math.max(val, 0.01).toFixed(2);
                    updateTotalCost();
                });
            }

            function enforceValidReason() {
                reasonInput.addEventListener("input", function () {
                    this.value = this.value.replace(/[^a-zA-Z0-9\s.,-]/g, '');
                    updateCharacterCount('reason', 'reasonCount', 100);
                });
            }

            function enforceValidSupplier() {
                supplierInput.addEventListener("input", function () {
                    this.value = this.value.replace(/[^a-zA-Z0-9\s.,-]/g, '');
                    updateCharacterCount('supplier', 'supplierCount', 50);
                });
            }

            function enforceValidNotes(input, countId) {
                input.addEventListener("input", function () {
                    this.value = this.value.replace(/[^a-zA-Z0-9\s.,-]/g, '');
                    updateCharacterCount(input.id, countId, 255);
                });
            }

            enforceValidProductName();
            enforceValidQuantity();
            enforceValidPrice();
            enforceValidReason();
            enforceValidSupplier();
            enforceValidNotes(returnNotesInput, 'returnNotesCount');
            enforceValidNotes(returnNotesField, 'returnNotesFieldCount');

            updateCharacterCount('productName', 'productNameCount', 50);
            updateCharacterCount('reason', 'reasonCount', 100);
            updateCharacterCount('supplier', 'supplierCount', 50);
            updateCharacterCount('returnNotes', 'returnNotesCount', 255);
            updateCharacterCount('returnNotesField', 'returnNotesFieldCount', 255);

            document.getElementById("addDamagedProductBtn").addEventListener("click", function () {
                modalTitle.innerText = "Report Damaged Product";
                methodField.value = "POST";
                damagedProductForm.action = "{{ route('damaged-products.store') }}";
                damagedProductModal.style.display = "block";
                SaveBtn.innerText = "ADD";
                damagedProductForm.reset();
                quantityInput.value = 1;
                pricePerItemInput.value = '0.01';
                statusInput.value = "Marked as Loss";
                document.getElementById("reportedAt").value = new Date().toISOString().slice(0, 16);
                clearErrors();
                loadingSpinner.style.display = 'none';
                SaveBtn.disabled = false;
                updateCharacterCount('productName', 'productNameCount', 50);
                updateCharacterCount('reason', 'reasonCount', 100);
                updateCharacterCount('supplier', 'supplierCount', 50);
                updateCharacterCount('returnNotes', 'returnNotesCount', 255);
                updateTotalCost();
            });

            document.querySelectorAll('.dmg-edit-btn').forEach(button => {
                button.addEventListener('click', function () {
                    modalTitle.innerText = "Edit Damaged Product";
                    methodField.value = "PUT";
                    damagedProductForm.action = "{{ url('damaged-products') }}/" + this.dataset.id;
                    document.getElementById("damagedProductId").value = this.dataset.id;
                    productNameInput.value = this.dataset.product_name;
                    quantityInput.value = Math.max(1, this.dataset.quantity);
                    pricePerItemInput.value = parseFloat(this.dataset.price_per_item).toFixed(2);
                    reasonInput.value = this.dataset.reason;
                    supplierInput.value = this.dataset.supplier;
                    statusInput.value = this.dataset.status;
                    returnNotesInput.value = this.dataset.return_notes || '';
                    document.getElementById("reportedAt").value = this.dataset.reported_at;
                    SaveBtn.innerText = "UPDATE";
                    damagedProductModal.style.display = "block";
                    clearErrors();
                    loadingSpinner.style.display = 'none';
                    SaveBtn.disabled = false;
                    updateCharacterCount('productName', 'productNameCount', 50);
                    updateCharacterCount('reason', 'reasonCount', 100);
                    updateCharacterCount('supplier', 'supplierCount', 50);
                    updateCharacterCount('returnNotes', 'returnNotesCount', 255);
                    updateTotalCost();
                });
            });

            damagedProductForm.addEventListener("submit", function (event) {
                event.preventDefault();
                clearErrors();

                const productName = productNameInput.value.trim();
                const quantity = parseInt(quantityInput.value);
                const price = parseFloat(pricePerItemInput.value);
                const reason = reasonInput.value.trim();
                const supplier = supplierInput.value.trim();
                const status = statusInput.value;

                if (!productName || !quantity || !price || !reason || !supplier || !status) {
                    showError(errorMessages, "Please fill in all required fields!");
                    return;
                }

                if (!/^[a-zA-Z\s]+$/.test(productName)) {
                    showError(errorMessages, "Product name must contain only letters and spaces.");
                    productNameInput.classList.add('product-name-error');
                    productNameInvalid.style.display = 'block';
                    return;
                }

                if (quantity > 1200 || quantity < 1) {
                    showError(errorMessages, `Quantity must be between 1 and 1200.`);
                    quantityInput.classList.add('quantity-error');
                    return;
                }

                if (price < 0.01) {
                    showError(errorMessages, "Price per item must be at least ₱0.01.");
                    pricePerItemInput.classList.add('price-error');
                    return;
                }

                loadingSpinner.style.display = 'block';
                SaveBtn.disabled = true;
                damagedProductForm.submit();
            });

            returnForm.addEventListener("submit", function (event) {
                event.preventDefault();
                clearErrors();
                returnLoadingSpinner.style.display = 'block';
                returnSaveBtn.disabled = true;
                returnForm.submit();
            });

            closeBtn.forEach(btn => btn.addEventListener("click", () => {
                damagedProductModal.style.display = "none";
                returnModal.style.display = "none";
                clearErrors();
                loadingSpinner.style.display = 'none';
                returnLoadingSpinner.style.display = 'none';
                SaveBtn.disabled = false;
                returnSaveBtn.disabled = false;
            }));

            window.addEventListener("click", event => {
                if (event.target === damagedProductModal || event.target === returnModal) {
                    damagedProductModal.style.display = "none";
                    returnModal.style.display = "none";
                    clearErrors();
                    loadingSpinner.style.display = 'none';
                    returnLoadingSpinner.style.display = 'none';
                    SaveBtn.disabled = false;
                    returnSaveBtn.disabled = false;
                }
            });
        });
    </script>
@endpush
@endsection
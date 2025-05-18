@extends('manager.layout')

@section('title', 'Damaged Products')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/manager-damaged.css') }}">
@endpush

@section('content')

<!-- Overlays for Modals -->
<div class="dmg-modal-overlay" data-modal-id="damagedProductModal">
    <!-- Damaged Product Modal -->
    <div id="damagedProductModal" class="dmg-modal">
          <span class="dmg-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
        <div class="dmg-modal-content">
          
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
                    <span id="productNameInvalid" class="invalid-indicator">Product name can only contain letters, spaces, apostrophes, hyphens, commas, and ampersands</span>
                </div>
                <div class="dmg-form-group">
                    <label>Quantity:<br><span class="quantity-note">Must be between 1 and 9999</span></label>
                    <input type="number" name="quantity" id="quantity" min="1" max="9999" required>
                </div>
                <div class="dmg-form-group">
                    <label>Price per Item (₱):<br><span class="quantity-note">Enter the cost per unit (1 to 1200)</span></label>
                    <input type="number" name="price_per_item" id="pricePerItem" max="1200"  required>
                    <small id="totalCostDisplay">Total Cost: ₱0.00</small>
                </div>
                <div class="dmg-form-group">
                    <label>Supplier:</label>
                    <input type="text" name="supplier" id="supplier" required maxlength="50">
                    <small id="supplierCount">0 / 50</small>
                    <span id="supplierInvalid" class="invalid-indicator">Supplier can only contain letters, spaces, commas, periods, ampersands, apostrophes, and hyphens</span>
                </div>
                <div class="dmg-form-group">
                    <label>Status:</label>
                    <select name="status" id="status" required>
                        <option value="Successfully Returned and Replaced">Successfully Returned and Replaced</option>
                        <option value="Marked as Loss">Marked as Loss</option>
                    </select>
                </div>
                <div class="dmg-form-group">
                    <label>Reported At:</label>
                    <input type="datetime-local" name="reported_at" id="reportedAt">
                </div>
                <div class="dmg-form-group">
                    <label>Reason:</label>
                    <textarea name="reason" id="reason" required maxlength="100"></textarea>
                    <small id="reasonCount">0 / 100</small>
                    <span id="reasonInvalid" class="invalid-indicator">Reason can only contain letters, numbers, spaces, commas, periods, parentheses, and hyphens</span>
                </div>
                <button type="submit" class="dmg-btn dmg-save-btn" id="SaveBtn">ADD</button>
            </form>
            <div id="loadingSpinner" style="display: none; text-align: center; margin-top: 10px;">
                <i class="fa-solid fa-spinner fa-spin"></i> Saving...
            </div>
        </div>
    </div>
</div>

<div class="dmg-modal-overlay" data-modal-id="productDetailsModal">
    <!-- Product Details Modal -->
    <div id="productDetailsModal" class="dmg-product-modal">
        <div class="dmg-product-header">Product Details</div>
        <div class="dmg-product-details" id="productDetails"></div>
        <div class="dmg-modal-buttons">
            <button class="dmg-modal-close" id="closeProductModal">Close</button>
        </div>
    </div>
</div>

<!-- Success Modal (similar to inventory) -->
<div id="dmgSuccessModal" class="dmg-modal-success">
    <div class="dmg-modal-success-content">
        <p id="dmgSuccessMessage"></p>
    </div>
</div>

<!-- Delete Confirmation Modal (similar to inventory) -->
<div class="dmg-modal-overlay" data-modal-id="dmgDeleteModal"></div>
<div id="dmgDeleteModal" class="dmg-modal delete">
    <div class="dmg-modal-content">
        <span class="dmg-close-btn" id="dmgDeleteCloseBtn"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this damaged product report?</p>
        <div class="dmg-delete-btn-container">
            <button class="dmg-delete-cancel-btn" id="dmgDeleteCancelBtn">Cancel</button>
            <form id="dmgDeleteForm" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="dmg-delete-confirm-btn">Delete</button>
            </form>
        </div>
    </div>
</div>

@if (session('success'))
    <script>
        window.dmgSuccessMessage = @json(session('success'));
    </script>
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

<div class="dmg-table-container">
    <div class="dmg-section-title" id="sectionTitle">All Damaged Products List</div>
    <div class="dmg-header-row">
        <div class="dmg-tabs">
            <div class="dmg-tab active" data-status="all">All</div>
            <div class="dmg-tab" data-status="Successfully Returned and Replaced">Successfully Returned and Replaced</div>
            <div class="dmg-tab" data-status="Marked as Loss">Marked as Loss</div>
        </div>
        <button id="addDamagedProductBtn" class="dmg-btn dmg-add-btn">+ Report Damaged Product</button>
    </div>
    <table class="dmg-table" id="damagedProductsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price per Item</th>
                <th>Total Cost</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Reported At</th>
                <th>Return Date</th>
                <th>Reason</th>
                <th style="width: 80px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($damagedProducts as $damagedProduct)
            <tr class="dmg-product-row" data-product="{{ json_encode([
                'id' => $damagedProduct->id,
                'product_name' => $damagedProduct->product_name,
                'quantity' => $damagedProduct->quantity,
                'price_per_item' => number_format($damagedProduct->price_per_item, 2),
                'total_cost' => number_format($damagedProduct->total_cost, 2),
                'supplier' => $damagedProduct->supplier,
                'status' => $damagedProduct->status,
                'reported_at' => $damagedProduct->reported_at->format('F j Y / g:i A'),
                'return_date' => $damagedProduct->return_date ? $damagedProduct->return_date->format('F j Y / g:i A') : '-',
                'reason' => $damagedProduct->reason
            ]) }}">
                <td>{{ $damagedProduct->id }}</td>
                <td>{{ $damagedProduct->product_name }}</td>
                <td>{{ $damagedProduct->quantity }}</td>
                <td>₱{{ number_format($damagedProduct->price_per_item, 2) }}</td>
                <td>₱{{ number_format($damagedProduct->total_cost, 2) }}</td>
                <td>{{ $damagedProduct->supplier }}</td>
                <td>{{ $damagedProduct->status }}</td>
                <td>{{ $damagedProduct->reported_at->format('F j Y/ g:i A') }}</td>
                <td>{{ $damagedProduct->return_date ? $damagedProduct->return_date->format('F j Y/ g:i A') : '-' }}</td>
                <td>{{ $damagedProduct->reason }}</td>
                <td>
                    <button class="dmg-btn dmg-edit-btn" 
                        data-id="{{ $damagedProduct->id }}"
                        data-product_name="{{ $damagedProduct->product_name }}"
                        data-quantity="{{ $damagedProduct->quantity }}"
                        data-price_per_item="{{ $damagedProduct->price_per_item }}"
                        data-reason="{{ $damagedProduct->reason }}"
                        data-supplier="{{ $damagedProduct->supplier }}"
                        data-status="{{ $damagedProduct->status }}"
                        data-reported_at="{{ $damagedProduct->reported_at->format('Y-m-d\TH:i') }}">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <button type="button" class="dmg-btn dmg-delete-btn"><i class="fa-solid fa-trash"></i></button>
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
                columnDefs: [{ orderable: false, targets: 10 }],
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

            // Success Modal Logic
            if (typeof window.dmgSuccessMessage === 'string' && window.dmgSuccessMessage.trim() !== '') {
                const modal = document.getElementById('dmgSuccessModal');
                const msg = document.getElementById('dmgSuccessMessage');
                msg.textContent = window.dmgSuccessMessage;
                modal.classList.add('show');
                modal.style.display = 'block';
                setTimeout(() => {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                }, 2500);
            }

            document.querySelectorAll('.dmg-tab').forEach(tab => {
                tab.addEventListener('click', function () {
                    document.querySelectorAll('.dmg-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    const status = this.dataset.status;
                    table.column(6).search(status === 'all' ? '' : status).draw();
                    const sectionTitle = document.getElementById('sectionTitle');
                    if (status === 'all') {
                        sectionTitle.textContent = 'All Damaged Products List';
                    } else if (status === 'Successfully Returned and Replaced') {
                        sectionTitle.textContent = 'Successfully Returned and Replaced Items List';
                    } else if (status === 'Marked as Loss') {
                        sectionTitle.textContent = 'Marked as Loss Items List';
                    }
                });
            });

            const damagedProductModal = document.getElementById("damagedProductModal");
            const damagedProductForm = document.getElementById("damagedProductForm");
            const productDetailsModal = document.getElementById("productDetailsModal");
            const modalTitle = document.getElementById("modalTitle");
            const methodField = document.getElementById("methodField");
            const SaveBtn = document.getElementById("SaveBtn");
            const closeBtn = document.querySelectorAll(".dmg-close-btn");
            const closeProductModal = document.getElementById("closeProductModal");
            const productNameInput = document.getElementById("productName");
            const quantityInput = document.getElementById("quantity");
            const pricePerItemInput = document.getElementById("pricePerItem");
            const reasonInput = document.getElementById("reason");
            const supplierInput = document.getElementById("supplier");
            const statusInput = document.getElementById("status");
            const productNameCount = document.getElementById("productNameCount");
            const reasonCount = document.getElementById("reasonCount");
            const supplierCount = document.getElementById("supplierCount");
            const totalCostDisplay = document.getElementById("totalCostDisplay");
            const errorMessages = document.getElementById("errorMessages");
            const productNameInvalid = document.getElementById("productNameInvalid");
            const supplierInvalid = document.getElementById("supplierInvalid");
            const reasonInvalid = document.getElementById("reasonInvalid");
            const loadingSpinner = document.getElementById("loadingSpinner");

            function openModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'block';
                    const overlay = document.querySelector(`.dmg-modal-overlay[data-modal-id="${modalId}"]`);
                    if (overlay) {
                        overlay.style.display = 'block';
                        setTimeout(() => overlay.classList.add('active'), 10); // Ensure transition works
                    }
                    document.body.classList.add('modal-open'); // Disable scrolling
                }
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    const overlay = document.querySelector(`.dmg-modal-overlay[data-modal-id="${modalId}"]`);
                    if (overlay) {
                        overlay.classList.remove('active');
                        setTimeout(() => {
                            overlay.style.display = 'none';
                            modal.style.display = 'none';
                            document.body.classList.remove('modal-open'); // Re-enable scrolling
                        }, 300); // Match the transition duration
                    } else {
                        modal.style.display = 'none';
                        document.body.classList.remove('modal-open');
                    }
                }
            }

            function showError(element, message) {
                element.style.display = 'block';
                element.innerHTML = message;
            }

            function clearErrors() {
                errorMessages.style.display = 'none';
                errorMessages.innerHTML = '';
                productNameInvalid.style.display = 'none';
                supplierInvalid.style.display = 'none';
                reasonInvalid.style.display = 'none';
                productNameInput.classList.remove('product-name-error');
                quantityInput.classList.remove('quantity-error');
                pricePerItemInput.classList.remove('price-error');
                supplierInput.classList.remove('supplier-error');
                reasonInput.classList.remove('reason-error');
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

            function getInvalidCharacters(input, regex) {
                const invalidChars = [];
                for (let char of input) {
                    if (!regex.test(char)) {
                        invalidChars.push(char);
                    }
                }
                return [...new Set(invalidChars)]; // Remove duplicates
            }

            function enforceValidProductName() {
                productNameInput.addEventListener("input", function () {
                    const value = this.value.replace(/[^a-zA-Z\s',&À-ÿ-]/g, '');
                    this.value = value;
                    productNameInvalid.style.display = value === this.value ? 'none' : 'block';
                    productNameInput.classList.toggle('product-name-error', value !== this.value);
                    updateCharacterCount('productName', 'productNameCount', 50);
                });
            }

            function enforceValidQuantity() {
                quantityInput.addEventListener("input", function () {
                    // Allow any value (including empty) while typing
                    updateTotalCost();
                });
                quantityInput.addEventListener("blur", function () {
                    // On blur, enforce min/max and set to 1 if empty or invalid
                    let val = parseInt(this.value);
                    if (isNaN(val) || val < 1) val = 1;
                    if (val > 9999) val = 9999;
                    this.value = val;
                    updateTotalCost();
                });
            }

            function enforceValidPrice() {
                pricePerItemInput.addEventListener("input", function () {
                    let val = parseFloat(this.value) ;
                    this.value = Math.min(Math.max(val, 1), 1200);
                    updateTotalCost();
                });
            }

            function enforceValidReason() {
                reasonInput.addEventListener("input", function () {
                    const value = this.value.replace(/[^a-zA-Z0-9\s,.()\-.À-ÿ]/g, '');
                    this.value = value;
                    reasonInvalid.style.display = value === this.value ? 'none' : 'block';
                    reasonInput.classList.toggle('reason-error', value !== this.value);
                    updateCharacterCount('reason', 'reasonCount', 100);
                });
            }

            function enforceValidSupplier() {
                supplierInput.addEventListener("input", function () {
                    const value = this.value.replace(/[^a-zA-Z\s,.&'\-.À-ÿ]/g, '');
                    this.value = value;
                    supplierInvalid.style.display = value === this.value ? 'none' : 'block';
                    supplierInput.classList.toggle('supplier-error', value !== this.value);
                    updateCharacterCount('supplier', 'supplierCount', 50);
                });
            }

            enforceValidProductName();
            enforceValidQuantity();
            enforceValidPrice();
            enforceValidReason();
            enforceValidSupplier();

            updateCharacterCount('productName', 'productNameCount', 50);
            updateCharacterCount('reason', 'reasonCount', 100);
            updateCharacterCount('supplier', 'supplierCount', 50);

            document.getElementById("addDamagedProductBtn").addEventListener("click", function () {
                modalTitle.innerText = "Report Damaged Product";
                methodField.value = "POST";
                damagedProductForm.action = "{{ route('damaged-products.store') }}";
                SaveBtn.innerText = "ADD";
                damagedProductForm.reset();
                quantityInput.value = 1;
                statusInput.value = "Marked as Loss";
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                document.getElementById("reportedAt").value = `${year}-${month}-${day}T${hours}:${minutes}`;
                clearErrors();
                loadingSpinner.style.display = 'none';
                SaveBtn.disabled = false;
                updateCharacterCount('productName', 'productNameCount', 50);
                updateCharacterCount('reason', 'reasonCount', 100);
                updateCharacterCount('supplier', 'supplierCount', 50);
                updateTotalCost();
                openModal("damagedProductModal");
            });

            document.querySelectorAll('.dmg-edit-btn').forEach(button => {
                button.addEventListener('click', function () {
                    modalTitle.innerText = "Edit Damaged Product";
                    methodField.value = "PUT";
                    damagedProductForm.action = "{{ url('damaged-products') }}/" + this.dataset.id;
                    document.getElementById("damagedProductId").value = this.dataset.id;
                    productNameInput.value = this.dataset.product_name;
                    quantityInput.value = Math.min(Math.max(1, parseInt(this.dataset.quantity)), 9999);
                    pricePerItemInput.value = Math.min(Math.max(parseFloat(this.dataset.price_per_item), 1), 1200);
                    reasonInput.value = this.dataset.reason;
                    supplierInput.value = this.dataset.supplier;
                    statusInput.value = this.dataset.status;
                    document.getElementById("reportedAt").value = this.dataset.reported_at;
                    SaveBtn.innerText = "UPDATE";
                    clearErrors();
                    loadingSpinner.style.display = 'none';
                    SaveBtn.disabled = false;
                    updateCharacterCount('productName', 'productNameCount', 50);
                    updateCharacterCount('reason', 'reasonCount', 100);
                    updateCharacterCount('supplier', 'supplierCount', 50);
                    updateTotalCost();
                    openModal("damagedProductModal");
                });
            });

            // Handle row click to show product details modal
            $(document).on('click', '.dmg-product-row', function(e) {
                if ($(e.target).closest('.dmg-edit-btn, .dmg-delete-btn').length) {
                    return;
                }
                const product = $(this).data('product');
                const detailsHtml = `
                    <p><strong>ID:</strong> <span>${product.id}</span></p>
                    <p><strong>Product Name:</strong> <span>${product.product_name}</span></p>
                    <p><strong>Quantity:</strong> <span>${product.quantity}</span></p>
                    <p><strong>Price per Item:</strong> <span>₱${product.price_per_item}</span></p>
                    <p><strong>Total Cost:</strong> <span>₱${product.total_cost}</span></p>
                    <p><strong>Supplier:</strong> <span class="dmg-supplier-text">${product.supplier}</span></p>
                    <p><strong>Status:</strong> <span>${product.status}</span></p>
                    <p><strong>Reported At:</strong> <span>${product.reported_at}</span></p>
                    <p><strong>Return Date:</strong> <span>${product.return_date}</span></p>
                    <p><strong>Reason:</strong></p>
                    <div class="dmg-reason-text">${product.reason}</div>
                `;
                $('#productDetails').html(detailsHtml);
                openModal("productDetailsModal");
            });

            // Close product details modal
            $('#closeProductModal').on('click', function() {
                closeModal("productDetailsModal");
            });

            // Close product details modal when clicking outside
            $(document).on('click', '.dmg-modal-overlay', function(e) {
                if (e.target.classList.contains('dmg-modal-overlay')) {
                    const modalId = $(this).data('modal-id');
                    closeModal(modalId);
                }
            });

            // Delete Modal Logic
            let deleteFormAction = '';
            $(document).on('click', '.dmg-delete-btn', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                deleteFormAction = form.attr('action');
                $('#dmgDeleteForm').attr('action', deleteFormAction);
                openModal('dmgDeleteModal');
            });

            document.getElementById('dmgDeleteCancelBtn').onclick = function() {
                closeModal('dmgDeleteModal');
            };
            document.getElementById('dmgDeleteCloseBtn').onclick = function() {
                closeModal('dmgDeleteModal');
            };

            damagedProductForm.addEventListener("submit", function (event) {
                event.preventDefault();
                clearErrors();

                const productName = productNameInput.value.trim();
                const quantity = parseInt(quantityInput.value);
                const price = parseFloat(pricePerItemInput.value);
                const reason = reasonInput.value.trim();
                const supplier = supplierInput.value.trim();
                const status = statusInput.value;
                const reportedAt = document.getElementById("reportedAt").value;
                let errors = [];

                if (!productName || !quantity || !price || !reason || !supplier || !status || !reportedAt) {
                    errors.push("Please fill in all required fields!");
                }

                const productNameRegex = /^[a-zA-Z\s',&À-ÿ-]+$/;
                const invalidProductNameChars = getInvalidCharacters(productName, productNameRegex);
                if (!productNameRegex.test(productName)) {
                    errors.push(`Product name can only contain letters, spaces, apostrophes, hyphens, commas, and ampersands. Invalid characters detected: '${invalidProductNameChars.join("', '")}'. Allowed: letters (a-z, A-Z), spaces, apostrophes ('), hyphens (-), commas (,), ampersands (&), accented characters (e.g., é, ñ).`);
                    productNameInput.classList.add('product-name-error');
                    productNameInvalid.style.display = 'block';
                }

                const supplierRegex = /^[a-zA-Z\s,.&'\-.À-ÿ]+$/;
                const invalidSupplierChars = getInvalidCharacters(supplier, supplierRegex);
                if (!supplierRegex.test(supplier)) {
                    errors.push(`Supplier can only contain letters, spaces, commas, periods, ampersands, apostrophes, and hyphens. Invalid characters detected: '${invalidSupplierChars.join("', '")}'. Allowed: letters (a-z, A-Z), spaces, commas (,), periods (.), ampersands (&), apostrophes ('), hyphens (-), accented characters (e.g., é, ñ).`);
                    supplierInput.classList.add('supplier-error');
                    supplierInvalid.style.display = 'block';
                }

                const reasonRegex = /^[a-zA-Z0-9\s,.()\-.À-ÿ]+$/;
                const invalidReasonChars = getInvalidCharacters(reason, reasonRegex);
                if (!reasonRegex.test(reason)) {
                    errors.push(`Reason can only contain letters, numbers, spaces, commas, periods, parentheses, and hyphens. Invalid characters detected: '${invalidReasonChars.join("', '")}'. Allowed: letters (a-z, A-Z), numbers (0-9), spaces, commas (,), periods (.), parentheses (()), hyphens (-), accented characters (e.g., é, ñ).`);
                    reasonInput.classList.add('reason-error');
                    reasonInvalid.style.display = 'block';
                }

                if (quantity < 1 || quantity > 9999) {
                    errors.push("Quantity must be between 1 and 9999.");
                    quantityInput.classList.add('quantity-error');
                }

                if (price < 1 || price > 1200) {
                    errors.push("Price per item must be between ₱1 and ₱1200.");
                    pricePerItemInput.classList.add('price-error');
                }

                if (errors.length > 0) {
                    showError(errorMessages, errors.join('<br>'));
                    loadingSpinner.style.display = 'none';
                    SaveBtn.disabled = false;
                    return;
                }

                loadingSpinner.style.display = 'block';
                SaveBtn.disabled = true;
                damagedProductForm.submit();
            });

            closeBtn.forEach(btn => btn.addEventListener("click", () => {
                closeModal("damagedProductModal");
                clearErrors();
                loadingSpinner.style.display = 'none';
                SaveBtn.disabled = false;
            }));

            window.addEventListener("click", event => {
                if (event.target === damagedProductModal) {
                    closeModal("damagedProductModal");
                    clearErrors();
                    loadingSpinner.style.display = 'none';
                    SaveBtn.disabled = false;
                }
            });
        });
    </script>
@endpush
@endsection
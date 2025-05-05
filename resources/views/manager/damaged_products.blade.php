@extends('manager.layout')

@section('title', 'Damaged Products')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<h1 class="dmg-title">Damaged Products</h1>

<div class="dmg-top-bar">
    <button id="addDamagedProductBtn" class="dmg-btn dmg-add-btn">+ Report Damaged Product</button>
</div>

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
                <input type="text" name="product_name" id="productName" required maxlength="24">
                <small id="productNameCount">0 / 24</small>
                <span id="productNameInvalid" class="invalid-indicator">Only letters and spaces allowed</span>
            </div>
            <div class="dmg-form-group">
                <label>Quantity:<br><span class="quantity-note">Must be between 1 and 1200</span></label>
                <input type="number" name="quantity" id="quantity" min="1" max="1200" required>
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

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const successMessage = document.querySelector(".dmg-success-message");
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.display = "none";
            }, 3000);
        }
    });
</script>

<div class="dmg-table-container">
    <div class="dmg-section-title">Damaged Products List</div>
    <table class="dmg-table" id="damagedProductsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Reason</th>
                <th>Supplier</th>
                <th>Reported At</th>
                <th style="width: 50px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($damagedProducts as $damagedProduct)
            <tr>
                <td>{{ $damagedProduct->id }}</td>
                <td>{{ $damagedProduct->product_name }}</td>
                <td>{{ $damagedProduct->quantity }}</td>
                <td>{{ $damagedProduct->reason }}</td>
                <td>{{ $damagedProduct->supplier }}</td>
                <td>{{ $damagedProduct->reported_at->format('F j Y/ g:i A') }}</td>
                <td>
                    <button class="dmg-btn dmg-edit-btn" 
                        data-id="{{ $damagedProduct->id }}"
                        data-product_name="{{ $damagedProduct->product_name }}"
                        data-quantity="{{ $damagedProduct->quantity }}"
                        data-reason="{{ $damagedProduct->reason }}"
                        data-supplier="{{ $damagedProduct->supplier }}"
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
            // Initialize DataTable
            $('#damagedProductsTable').DataTable({
                pageLength: 10,
                responsive: true,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 6 }
                ],
                pagingType: 'simple_numbers',
                language: {
                    paginate: {
                        previous: 'Previous',
                        next: 'Next'
                    }
                }
            });

            const damagedProductModal = document.getElementById("damagedProductModal");
            const damagedProductForm = document.getElementById("damagedProductForm");
            const modalTitle = document.getElementById("modalTitle");
            const methodField = document.getElementById("methodField");
            const SaveBtn = document.getElementById("SaveBtn");
            const closeBtn = document.querySelector(".dmg-close-btn");
            const productNameInput = document.getElementById("productName");
            const quantityInput = document.getElementById("quantity");
            const reasonInput = document.getElementById("reason");
            const supplierInput = document.getElementById("supplier");
            const productNameCount = document.getElementById("productNameCount");
            const reasonCount = document.getElementById("reasonCount");
            const supplierCount = document.getElementById("supplierCount");
            const errorMessages = document.getElementById("errorMessages");
            const productNameInvalid = document.getElementById("productNameInvalid");
            const loadingSpinner = document.getElementById("loadingSpinner");

            function showError(message) {
                errorMessages.style.display = 'block';
                errorMessages.innerHTML = message;
            }

            function clearErrors() {
                errorMessages.style.display = 'none';
                errorMessages.innerHTML = '';
                productNameInvalid.style.display = 'none';
                productNameInput.classList.remove('product-name-error');
                quantityInput.classList.remove('quantity-error');
            }

            function updateCharacterCount(inputId, countId, maxLength) {
                const input = document.getElementById(inputId);
                const count = document.getElementById(countId);
                if (input && count) {
                    const currentCount = input.value.length;
                    count.textContent = `${currentCount} / ${maxLength}`;
                }
            }

            function enforceValidProductName(input) {
                input.addEventListener("input", function () {
                    const value = this.value;
                    const validValue = value.replace(/[^a-zA-Z\s]/g, '');
                    this.value = validValue;
                    if (value !== validValue) {
                        productNameInvalid.style.display = 'block';
                        productNameInput.classList.add('product-name-error');
                    } else {
                        productNameInvalid.style.display = 'none';
                        productNameInput.classList.remove('product-name-error');
                    }
                    updateCharacterCount('productName', 'productNameCount', 24);
                });
            }

            function enforceValidQuantity(input) {
                input.addEventListener("input", function () {
                    let val = parseInt(this.value) || 1;
                    if (val < 1) {
                        this.value = 1;
                    } else if (val > 1200) {
                        this.value = 1200;
                    } else {
                        this.value = val;
                    }
                });
            }

            function enforceValidReason(input) {
                input.addEventListener("input", function () {
                    this.value = this.value.replace(/[^a-zA-Z0-9\s.,-]/g, '');
                });
            }

            function enforceValidSupplier(input) {
                input.addEventListener("input", function () {
                    this.value = this.value.replace(/[^a-zA-Z0-9\s.,-]/g, '');
                });
            }

            if (productNameInput) {
                enforceValidProductName(productNameInput);
                productNameInput.addEventListener("input", () => updateCharacterCount('productName', 'productNameCount', 24));
                updateCharacterCount('productName', 'productNameCount', 24);
            }
            if (quantityInput) enforceValidQuantity(quantityInput);
            if (reasonInput) {
                enforceValidReason(reasonInput);
                reasonInput.addEventListener("input", () => updateCharacterCount('reason', 'reasonCount', 100));
                updateCharacterCount('reason', 'reasonCount', 100);
            }
            if (supplierInput) {
                enforceValidSupplier(supplierInput);
                supplierInput.addEventListener("input", () => updateCharacterCount('supplier', 'supplierCount', 50));
                updateCharacterCount('supplier', 'supplierCount', 50);
            }

            document.getElementById("addDamagedProductBtn").addEventListener("click", function () {
                modalTitle.innerText = "Report Damaged Product";
                methodField.value = "POST";
                damagedProductForm.action = "{{ route('damaged-products.store') }}";
                damagedProductModal.style.display = "block";
                SaveBtn.innerText = "ADD";
                damagedProductForm.reset();
                quantityInput.value = 1;
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
                updateCharacterCount('productName', 'productNameCount', 24);
                updateCharacterCount('reason', 'reasonCount', 100);
                updateCharacterCount('supplier', 'supplierCount', 50);
            });

            document.querySelectorAll('.dmg-edit-btn').forEach(button => {
                button.addEventListener('click', function () {
                    modalTitle.innerText = "Edit Damaged Product";
                    methodField.value = "PUT";
                    damagedProductForm.action = "{{ url('damaged-products') }}/" + this.dataset.id;
                    document.getElementById("damagedProductId").value = this.dataset.id;
                    document.getElementById("productName").value = this.dataset.product_name;
                    document.getElementById("quantity").value = Math.max(1, this.dataset.quantity);
                    document.getElementById("reason").value = this.dataset.reason;
                    document.getElementById("supplier").value = this.dataset.supplier;
                    document.getElementById("reportedAt").value = this.dataset.reported_at;
                    SaveBtn.innerText = "UPDATE";
                    damagedProductModal.style.display = "block";
                    clearErrors();
                    loadingSpinner.style.display = 'none';
                    SaveBtn.disabled = false;
                    updateCharacterCount('productName', 'productNameCount', 24);
                    updateCharacterCount('reason', 'reasonCount', 100);
                    updateCharacterCount('supplier', 'supplierCount', 50);
                });
            });

            damagedProductForm.addEventListener("submit", function (event) {
                event.preventDefault();
                clearErrors();

                const productName = productNameInput.value.trim();
                const quantity = parseInt(quantityInput.value);
                const reason = reasonInput.value.trim();
                const supplier = supplierInput.value.trim();

                if (!productName || !quantity || !reason || !supplier) {
                    showError("Please fill in all fields!");
                    return;
                }

                if (!/^[a-zA-Z\s]+$/.test(productName)) {
                    showError("Product name must contain only letters and spaces.");
                    productNameInput.classList.add('product-name-error');
                    productNameInvalid.style.display = 'block';
                    return;
                }

                if (quantity > 1200) {
                    showError("Quantity cannot exceed 1200.");
                    quantityInput.classList.add('quantity-error');
                    return;
                } else if (quantity < 1) {
                    showError("Quantity must be at least 1.");
                    quantityInput.classList.add('quantity-error');
                    return;
                }

                loadingSpinner.style.display = 'block';
                SaveBtn.disabled = true;
                damagedProductForm.submit();
            });

            closeBtn.addEventListener("click", () => {
                damagedProductModal.style.display = "none";
                clearErrors();
                loadingSpinner.style.display = 'none';
                SaveBtn.disabled = false;
            });

            window.addEventListener("click", event => {
                if (event.target === damagedProductModal) {
                    damagedProductModal.style.display = "none";
                    clearErrors();
                    loadingSpinner.style.display = 'none';
                    SaveBtn.disabled = false;
                }
            });
        });
    </script>
@endpush
@endsection
@extends('manager.layout')

@section('title', 'Damaged Products')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .quantity-note {
            font-size: 0.8em;
            color: red;
            font-style: italic;
        }

        .quantity-error {
            border: 2px solid red;
        }

        .dmg-form-group small {
            display: block;
            margin-top: 5px;
            font-size: 0.8em;
            color: #666;
        }
    </style>
@endpush

@section('content')
<h1 class="dmg-title">Damaged Products</h1>

<div class="dmg-top-bar">
    <button id="addDamagedProductBtn" class="dmg-btn dmg-add-stock">+ Report Damaged Product</button>
</div>

<div id="damagedProductModal" class="dmg-modal">
    <div class="dmg-modal-content">
        <span class="dmg-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
        <h2 id="modalTitle">Report Damaged Product</h2>
        <div id="errorMessages" class="dmg-error-messages" style="display: none;"></div>
        <form id="damagedProductForm" method="POST" action="{{ route('damaged-products.store') }}">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <input type="hidden" name="id" id="damagedProductId">
            <div class="dmg-form-group">
                <label>Product Name:</label>
                <input type="text" name="product_name" id="productName" required>
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
    </div>
</div>

<div id="tableSuccessMessage" class="dmg-success-message" style="display: none;">Damaged product reported successfully!</div>

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
                <th style="width: 100px;">Actions</th>
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
                <td>{{ $damagedProduct->reported_at->setTimezone('Asia/Manila')->format('Y-m-d H:i') }}</td>
                <td>
                    <button class="dmg-btn dmg-edit-btn" 
                        data-id="{{ $damagedProduct->id }}"
                        data-product_name="{{ $damagedProduct->product_name }}"
                        data-quantity="{{ $damagedProduct->quantity }}"
                        data-reason="{{ $damagedProduct->reason }}"
                        data-supplier="{{ $damagedProduct->supplier }}"
                        data-reported_at="{{ $damagedProduct->reported_at->setTimezone('Asia/Manila')->format('Y-m-d\TH:i') }}">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <form action="{{ route('damaged-products.destroy', $damagedProduct) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this damaged product report?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="dmg-btn dmg-delete-btn"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            let table = $('#damagedProductsTable').DataTable({
                pageLength: 10,
                responsive: true,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 6 } // Disable sorting on Actions column
                ],
                pagingType: 'simple_numbers',
                language: {
                    paginate: {
                        previous: 'Previous',
                        next: 'Next'
                    }
                }
            });

            // Log the number of rows to debug pagination
            console.log('Total rows in table:', table.rows().count());
            console.log('Number of pages:', table.page.info().pages);

            // Handle edit button clicks
            $('#damagedProductsTable').on('click', '.dmg-edit-btn', function() {
                const modalTitle = document.getElementById("modalTitle");
                const methodField = document.getElementById("methodField");
                const damagedProductForm = document.getElementById("damagedProductForm");
                const damagedProductModal = document.getElementById("damagedProductModal");
                const SaveBtn = document.getElementById("SaveBtn");

                modalTitle.innerText = "Edit Damaged Product";
                methodField.value = "PUT";
                damagedProductForm.action = `/damaged-products/${this.dataset.id}`;
                document.getElementById("damagedProductId").value = this.dataset.id;
                document.getElementById("productName").value = this.dataset.product_name;
                document.getElementById("quantity").value = Math.max(1, this.dataset.quantity);
                document.getElementById("reason").value = this.dataset.reason;
                document.getElementById("supplier").value = this.dataset.supplier;
                document.getElementById("reportedAt").value = this.dataset.reported_at;
                SaveBtn.innerText = "UPDATE";
                damagedProductModal.style.display = "block";
                document.getElementById("errorMessages").style.display = "none";

                // Update character counters
                updateCharacterCount('reason', 'reasonCount', 100);
                updateCharacterCount('supplier', 'supplierCount', 50);
            });
        });

        // JavaScript for modals, add/edit damaged product, and input validation
        document.addEventListener("DOMContentLoaded", function () {
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
            const reasonCount = document.getElementById("reasonCount");
            const supplierCount = document.getElementById("supplierCount");
            const errorMessages = document.getElementById("errorMessages");
            const tableSuccessMessage = document.getElementById("tableSuccessMessage");

            // Function to display error messages
            function showError(message) {
                errorMessages.style.display = 'block';
                errorMessages.innerHTML = message;
            }

            // Function to clear error messages
            function clearErrors() {
                errorMessages.style.display = 'none';
                errorMessages.innerHTML = '';
            }

            // Function to show success message above table
            function showTableSuccessMessage() {
                tableSuccessMessage.style.display = 'block';
                setTimeout(() => {
                    tableSuccessMessage.style.display = 'none';
                }, 2000);
            }

            // Validate product name (letters, numbers, and spaces only)
            function enforceValidProductName(input) {
                input.addEventListener("input", function () {
                    this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '');
                });
            }
            if (productNameInput) enforceValidProductName(productNameInput);

            // Validate quantity (positive integers up to 1200)
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
            if (quantityInput) enforceValidQuantity(quantityInput);

            // Validate reason (allow letters, numbers, and common punctuation)
            function enforceValidReason(input) {
                input.addEventListener("input", function () {
                    this.value = this.value.replace(/[^a-zA-Z0-9\s.,-]/g, '');
                });
            }
            if (reasonInput) enforceValidReason(reasonInput);

            // Validate supplier (allow letters, numbers, and common punctuation)
            function enforceValidSupplier(input) {
                input.addEventListener("input", function () {
                    this.value = this.value.replace(/[^a-zA-Z0-9\s.,-]/g, '');
                });
            }
            if (supplierInput) enforceValidSupplier(supplierInput);

            // Update character count
            function updateCharacterCount(inputId, countId, maxLength) {
                const input = document.getElementById(inputId);
                const count = document.getElementById(countId);
                if (input && count) {
                    const currentCount = input.value.length;
                    count.textContent = `${currentCount} / ${maxLength}`;
                }
            }

            if (reasonInput) {
                reasonInput.addEventListener("input", () => updateCharacterCount('reason', 'reasonCount', 100));
                updateCharacterCount('reason', 'reasonCount', 100); // Initial value
            }
            if (supplierInput) {
                supplierInput.addEventListener("input", () => updateCharacterCount('supplier', 'supplierCount', 50));
                updateCharacterCount('supplier', 'supplierCount', 50); // Initial value
            }

            // Open modal for adding new damaged product
            document.getElementById("addDamagedProductBtn").addEventListener("click", function () {
                modalTitle.innerText = "Report Damaged Product";
                methodField.value = "POST";
                damagedProductForm.action = "{{ route('damaged-products.store') }}";
                damagedProductModal.style.display = "block";
                SaveBtn.innerText = "ADD";
                damagedProductForm.reset();
                quantityInput.value = 1; // Default to 1
                // Set reported_at to current local time (PHT)
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                document.getElementById("reportedAt").value = `${year}-${month}-${day}T${hours}:${minutes}`; // Format: YYYY-MM-DDThh:mm
                clearErrors();
                updateCharacterCount('reason', 'reasonCount', 100);
                updateCharacterCount('supplier', 'supplierCount', 50);
            });

            // Form submission with AJAX
            damagedProductForm.addEventListener("submit", function (event) {
                event.preventDefault();
                clearErrors();

                const quantity = parseInt(quantityInput.value);
                if (quantity > 1200) {
                    showError("Quantity cannot exceed 1200.");
                    quantityInput.classList.add('quantity-error');
                    return;
                } else if (quantity < 1) {
                    showError("Quantity must be at least 1.");
                    quantityInput.classList.add('quantity-error');
                    return;
                } else {
                    quantityInput.classList.remove('quantity-error');
                }

                $.ajax({
                    url: damagedProductForm.action,
                    type: methodField.value === "POST" ? "POST" : "PUT",
                    data: $(damagedProductForm).serialize(),
                    success: function(response) {
                        showTableSuccessMessage();
                        let table = $('#damagedProductsTable').DataTable();
                        if (methodField.value === "POST") {
                            // Add new row for create
                            table.row.add([
                                response.damagedProduct.id,
                                response.damagedProduct.product_name,
                                response.damagedProduct.quantity,
                                response.damagedProduct.reason,
                                response.damagedProduct.supplier,
                                new Date(response.damagedProduct.reported_at).toLocaleString('en-PH', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false }),
                                `<button class="dmg-btn dmg-edit-btn" 
                                    data-id="${response.damagedProduct.id}" 
                                    data-product_name="${response.damagedProduct.product_name}" 
                                    data-quantity="${response.damagedProduct.quantity}" 
                                    data-reason="${response.damagedProduct.reason}" 
                                    data-supplier="${response.damagedProduct.supplier}" 
                                    data-reported_at="${new Date(response.damagedProduct.reported_at).toLocaleString('en-PH', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false }).replace(/, /, 'T').replace(/(\d+)\/(\d+)\/(\d+)/, '$3-$1-$2')}">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <form action="/damaged-products/${response.damagedProduct.id}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this damaged product report?');">
                                    <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="dmg-btn dmg-delete-btn"><i class="fa-solid fa-trash"></i></button>
                                </form>`
                            ]).draw();
                        } else {
                            // Update existing row for edit
                            let row = table.row($(`button[data-id="${response.damagedProduct.id}"]`).closest('tr'));
                            row.data([
                                response.damagedProduct.id,
                                response.damagedProduct.product_name,
                                response.damagedProduct.quantity,
                                response.damagedProduct.reason,
                                response.damagedProduct.supplier,
                                new Date(response.damagedProduct.reported_at).toLocaleString('en-PH', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false }),
                                `<button class="dmg-btn dmg-edit-btn" 
                                    data-id="${response.damagedProduct.id}" 
                                    data-product_name="${response.damagedProduct.product_name}" 
                                    data-quantity="${response.damagedProduct.quantity}" 
                                    data-reason="${response.damagedProduct.reason}" 
                                    data-supplier="${response.damagedProduct.supplier}" 
                                    data-reported_at="${new Date(response.damagedProduct.reported_at).toLocaleString('en-PH', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false }).replace(/, /, 'T').replace(/(\d+)\/(\d+)\/(\d+)/, '$3-$1-$2')}">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <form action="/damaged-products/${response.damagedProduct.id}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this damaged product report?');">
                                    <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="dmg-btn dmg-delete-btn"><i class="fa-solid fa-trash"></i></button>
                                </form>`
                            ]).draw();
                        }
                        damagedProductModal.style.display = "none";
                        damagedProductForm.reset();
                        quantityInput.value = 1;
                        updateCharacterCount('reason', 'reasonCount', 100);
                        updateCharacterCount('supplier', 'supplierCount', 50);
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON?.message || 'An error occurred while saving the damaged product.';
                        if (xhr.status === 422) {
                            errorMsg = Object.values(xhr.responseJSON.errors || {}).flat().join('<br>');
                        }
                        showError(errorMsg);
                    }
                });
            });

            // Close modal
            closeBtn.addEventListener("click", () => {
                damagedProductModal.style.display = "none";
                clearErrors();
            });

            // Close modal when clicking outside
            window.addEventListener("click", event => {
                if (event.target === damagedProductModal) {
                    damagedProductModal.style.display = "none";
                    clearErrors();
                }
            });
        });
    </script>
@endpush
@endsection

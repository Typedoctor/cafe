
@extends('manager.layout')

@section('title', 'Manage Users')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/manager-user.css') }}">
@endpush

@section('content')

<!-- Overlay for Modal -->
<div class="usr-modal-overlay" data-modal-id="userModal"></div>

<div id="userModal" class="usr-modal">
    <span class="usr-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
    <div class="usr-modal-content">
        <h2 id="modalTitle">Add New User</h2>
        <div id="errorMessages" class="usr-error-messages" style="display: none; color: red; margin-bottom: 10px;"></div>
        <form id="userForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <input type="hidden" name="user_id" id="userId">
            <div class="usr-form-group">
                <label>User name:</label>
                <input type="text" name="name" id="name" required maxlength="24">
                <small id="nameCount">0 / 24</small>
                <span id="nameInvalid" class="invalid-indicator">User name can only contain letters, spaces, apostrophes, hyphens, and commas</span>
            </div>
            <div class="usr-form-group" name="pass">
                <label>Password:</label>
                <input type="password" name="password" id="password">
            </div>
            <div class="usr-form-group" id="confirmPasswordGroup" style="display: none;">
                <label>Confirm Password:</label>
                <input type="password" name="password_confirmation" id="passwordConfirmation">
            </div>
            <div class="usr-form-group">
                <label>Privilege:</label>
                <select name="privilege" id="privilege" required>
                    <option value="cashier">Cashier</option>
                    <option value="manager">Manager</option>
                </select>
            </div>
            <div class="usr-form-group">
                <label>Status:</label>
                <select name="is_active" id="isActive" required>
                    <option value="1">Activate</option>
                    <option value="0">Disable</option>
                </select>
            </div>
            <div class="usr-button-group">
                <button type="submit" class="usr-btn usr-save-btn" id="saveBtn">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="usr-table-container">
    <div class="usr-section-title">Users List</div>
    <div class="usr-top-bar">
        <button id="addUserBtn" class="usr-btn usr-add-btn">+ Add User</button>
    </div>
    <table class="usr-table" id="usersTable">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>User name</th>
                <th>Privilege</th>
                <th>Status</th>
                <th style="width: 150px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->privilege }}</td>
                    <td>
                        <select class="status-dropdown" data-user-id="{{ $user->id }}">
                            <option value="1" {{ $user->is_active ? 'selected' : '' }}>Activate</option>
                            <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Disable</option>
                        </select>
                    </td>
                    <td>
                        <button class="usr-btn usr-edit-btn" 
                            data-id="{{ $user->id }}" 
                            data-name="{{ $user->name }}" 
                            data-privilege="{{ $user->privilege }}"
                            data-active="{{ $user->is_active }}"><i class="fa-solid fa-pencil"></i>
                        </button>
                        <form action="{{ route('manage_users.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="usr-btn usr-delete-btn"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<form id="status-form" method="POST" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="is_active" id="status-value">
</form>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

<script>
(function () {
    // Shared functions accessible to both jQuery and DOM event listeners
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            const overlay = document.querySelector(`.usr-modal-overlay[data-modal-id="${modalId}"]`);
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
            const overlay = document.querySelector(`.usr-modal-overlay[data-modal-id="${modalId}"]`);
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

    function showError(message) {
        const errorMessages = document.getElementById("errorMessages");
        errorMessages.style.display = 'block';
        errorMessages.innerHTML = message;
    }

    function clearErrors() {
        const errorMessages = document.getElementById("errorMessages");
        const nameInvalid = document.getElementById("nameInvalid");
        const nameInput = document.getElementById("name");
        errorMessages.style.display = 'none';
        errorMessages.innerHTML = '';
        nameInvalid.style.display = 'none';
        nameInput.classList.remove('name-error');
    }

    function validateName(name) {
        const nameRegex = /^[a-zA-Z\s',À-ÿ]+$/;
        return nameRegex.test(name);
    }

    function enforceValidName() {
        const nameInput = document.getElementById("name");
        nameInput.addEventListener("input", function () {
            const value = this.value.replace(/[^a-zA-Z\s'.,À-ÿ]/g, '');
            this.value = value;
            const nameInvalid = document.getElementById("nameInvalid");
            nameInvalid.style.display = value === this.value ? 'none' : 'block';
            nameInput.classList.toggle('name-error', value !== this.value);
            updateCharacterCount('name', 'nameCount', 24);
        });
    }

    function validatePassword(password) {
        return password.length >= 6;
    }

    function isUserNameExists(name, currentUserId = null) {
        let exists = false;
        $('#usersTable tbody tr').each(function() {
            const rowName = $(this).find('td').eq(1).text().trim();
            const rowId = $(this).find('td').eq(0).text().trim();
            if (rowName.toLowerCase() === name.toLowerCase() && (!currentUserId || rowId !== currentUserId)) {
                exists = true;
                return false; // Break the loop
            }
        });
        return exists;
    }

    function updateCharacterCount(inputId, countId, maxLength) {
        const input = document.getElementById(inputId);
        const count = document.getElementById(countId);
        if (input && count) {
            count.textContent = `${input.value.length} / ${maxLength}`;
        }
    }

    // jQuery document ready
    $(document).ready(function () {
        let table = $('#usersTable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: 4 } // Disable sorting on Actions column
            ]
        });

        $('#usersTable').on('click', '.usr-edit-btn', function() {
            const modalTitle = document.getElementById("modalTitle");
            const methodField = document.getElementById("methodField");
            const userForm = document.getElementById("userForm");
            const saveBtn = document.getElementById("saveBtn");
            const confirmPasswordGroup = document.getElementById("confirmPasswordGroup");
            const passwordInput = document.getElementById("password");
            const confirmPasswordInput = document.getElementById("passwordConfirmation");

            modalTitle.innerText = "Edit User";
            methodField.value = "PUT";
            userForm.action = `/manage_users/${this.dataset.id}`;
            document.getElementById("userId").value = this.dataset.id;
            document.getElementById("name").value = this.dataset.name.replace(/\./g, '');
            document.getElementById("privilege").value = this.dataset.privilege;
            document.getElementById("isActive").value = this.dataset.active;
            document.getElementById("password").value = "";
            document.getElementById("password").placeholder = "Change pass? (If no, leave blank)";
            confirmPasswordGroup.style.display = "none";
            passwordInput.required = false;
            confirmPasswordInput.required = false;
            saveBtn.innerText = "UPDATE";
            clearErrors();
            updateCharacterCount('name', 'nameCount', 24);
            openModal("userModal");
        });

        $(document).on('click', '.usr-modal-overlay', function(e) {
            if (e.target.classList.contains('usr-modal-overlay')) {
                const modalId = $(this).data('modal-id');
                closeModal(modalId);
            }
        });
    });

    // DOM content loaded
    document.addEventListener("DOMContentLoaded", function () {
        const userModal = document.getElementById("userModal");
        const closeBtn = document.querySelector(".usr-close-btn");
        const userForm = document.getElementById("userForm");
        const modalTitle = document.getElementById("modalTitle");
        const methodField = document.getElementById("methodField");
        const confirmPasswordGroup = document.getElementById("confirmPasswordGroup");
        const passwordInput = document.getElementById("password");
        const confirmPasswordInput = document.getElementById("passwordConfirmation");
        const nameInput = document.getElementById("name");
        const saveBtn = document.getElementById("saveBtn");
        const statusForm = document.getElementById("status-form");
        const statusValue = document.getElementById("status-value");

        enforceValidName();
        updateCharacterCount('name', 'nameCount', 24); // Initial value

        document.querySelectorAll('.status-dropdown').forEach(dropdown => {
            dropdown.addEventListener('change', function() {
                const userId = this.getAttribute('data-user-id');
                const newStatus = this.value;
                
                statusForm.action = `/manage_users/${userId}/update_status`;
                statusValue.value = newStatus;
                
                statusForm.submit();
            });
        });

        document.getElementById("addUserBtn").addEventListener("click", function () {
            modalTitle.innerText = "Add New User";
            methodField.value = "POST";
            userForm.action = "{{ route('manage_users.store') }}";
            passwordInput.placeholder = "";
            passwordInput.required = true;
            confirmPasswordGroup.style.display = "block";
            confirmPasswordInput.required = true;
            document.getElementById("isActive").value = "1"; // Default to active for new users
            saveBtn.innerText = "ADD";
            userForm.reset();
            clearErrors();
            updateCharacterCount('name', 'nameCount', 24); // Reset counter
            openModal("userModal");
        });

        closeBtn.addEventListener("click", () => {
            closeModal("userModal");
            clearErrors();
        });

        window.addEventListener("click", event => {
            if (event.target === userModal) {
                closeModal("userModal");
                clearErrors();
            }
        });

        userForm.addEventListener("submit", function (event) {
            event.preventDefault();
            clearErrors();

            const name = nameInput.value.trim();
            let errors = [];

            if (!validateName(name)) {
                errors.push("User name can only contain letters, spaces, apostrophes, hyphens, and commas.");
                nameInput.classList.add('name-error');
                document.getElementById("nameInvalid").style.display = 'block';
            }

            const currentUserId = methodField.value === "PUT" ? document.getElementById("userId").value : null;
            if (isUserNameExists(name, currentUserId)) {
                errors.push("User name already exists. Please select a different username.");
            }

            if (methodField.value === "POST") {
                if (!validatePassword(passwordInput.value)) {
                    errors.push("Password must be at least 6 characters long.");
                }

                if (passwordInput.value !== confirmPasswordInput.value) {
                    errors.push("Passwords do not match!");
                }
            } else if (methodField.value === "PUT" && passwordInput.value) {
                if (!validatePassword(passwordInput.value)) {
                    errors.push("New password must be at least 6 characters long.");
                }
            }

            if (errors.length > 0) {
                showError(errors.join("<br>"));
            } else {
                userForm.submit();
            }
        });
    });
})();
</script>
@endpush
@endsection

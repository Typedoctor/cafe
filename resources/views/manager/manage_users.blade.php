@extends('manager.layout')

@section('title', 'Manage Users')

@section('content')
<h1 class="usr-manage-title">Manage Users</h1>

<div class="usr-top-bar">
    <button id="addUserBtn" class="usr-btn usr-add-btn">+ Add User</button>
</div>

<div class="usr-search-filter-container">
    <form action="{{ route('manage_users.index') }}" method="GET" class="usr-search-filter-form">
        <div class="usr-search-box">
            <input type="text" name="search" placeholder="Search by name or privilege..." value="{{ request('search') }}">
        </div>
        <div class="usr-filter-box">
            <button type="submit" class="usr-btn usr-filter-btn">Search</button>
            <a href="{{ route('manage_users.index') }}" class="usr-btn usr-reset-btn">Reset</a>
        </div>
    </form>
</div>

<div id="userModal" class="usr-modal">
    <span class="usr-close-btn"><i class="fa-solid fa-circle-xmark"></i></span>
    <div class="usr-modal-content">
        <h2 id="modalTitle">Add New User</h2>
        <form id="userForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <input type="hidden" name="user_id" id="userId">
            <div class="usr-form-group">
                <label>Name:</label>
                <input type="text" name="name" id="name" required>
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
            <div class="usr-button-group">
                <button type="submit" class="usr-btn usr-save-btn" id="saveBtn">Save</button>
            </div>
        </form>
    </div>
</div>


<div class="usr-table-container">
<div class="usr-section-title">Users List</div>
    <table class="usr-table">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Name</th>
                <th>Privilege</th>
                <th style="width: 100px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->privilege }}</td>
                    <td>
                        <button class="usr-btn usr-edit-btn" 
                            data-id="{{ $user->id }}" 
                            data-name="{{ $user->name }}" 
                            data-privilege="{{ $user->privilege }}"><i class="fa-solid fa-pencil"></i>
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
@endsection

<script>
document.addEventListener("DOMContentLoaded", function () {
    const userModal = document.getElementById("userModal");
    const closeBtn = document.querySelector(".usr-close-btn");
    const userForm = document.getElementById("userForm");
    const modalTitle = document.getElementById("modalTitle");
    const methodField = document.getElementById("methodField");
    const confirmPasswordGroup = document.getElementById("confirmPasswordGroup");
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("passwordConfirmation");
    const saveBtn = document.getElementById("saveBtn");

    document.getElementById("addUserBtn").addEventListener("click", function () {
        modalTitle.innerText = "Add New User";
        methodField.value = "POST";
        userForm.action = "{{ route('manage_users.store') }}";
        passwordInput.placeholder = ""; 
        passwordInput.required = true;
        confirmPasswordGroup.style.display = "block";
        confirmPasswordInput.required = true;
        saveBtn.innerText = "ADD";
        userModal.style.display = "block";
        userForm.reset();
    });

    document.querySelectorAll(".usr-edit-btn").forEach(button => {
        button.addEventListener("click", function () {
            modalTitle.innerText = "Edit User";
            methodField.value = "PUT"; 
            userForm.action = `/manage_users/${this.dataset.id}`;
            document.getElementById("userId").value = this.dataset.id;
            document.getElementById("name").value = this.dataset.name;
            document.getElementById("privilege").value = this.dataset.privilege;
            document.getElementById("password").value = "";
            document.getElementById("password").placeholder = "Change pass? if no leave it blank";
            confirmPasswordGroup.style.display = "none";
            passwordInput.required = false;
            confirmPasswordInput.required = false;
            saveBtn.innerText = "UPDATE";
            userModal.style.display = "block";
        });
    });

    closeBtn.addEventListener("click", () => userModal.style.display = "none");
    window.addEventListener("click", event => event.target === userModal && (userModal.style.display = "none"));

    userForm.addEventListener("submit", function (event) {
        if (methodField.value === "POST") {
            if (passwordInput.value !== confirmPasswordInput.value) {
                event.preventDefault();
                alert("Passwords do not match!");
            }
        }
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
@extends('front.layouts.app')

@section('main')
    <section class="section-5 bg-2">
        <div class="container py-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Account Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    @include('front.account.sidebar')
                </div>
                <div class="col-lg-9">
                    @include('front.message')
                    <form action="" method="post" id="userForm" name="userForm">
                        <div class="card border-0 shadow mb-4">
                            <div class="card-body  p-4">
                                <h3 class="fs-4 mb-1">My Profile</h3>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Name*</label>
                                    <input type="text" value="{{ $user->name }}" name="name" id="name"
                                        placeholder="Enter Name" class="form-control" value="">
                                    <p></p>
                                </div>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Email*</label>
                                    <input type="text" value="{{ $user->email }}" name="email" id="email"
                                        placeholder="Enter Email" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Designation*</label>
                                    <input type="text" value="{{ $user->designation }}" name="designation"
                                        id="designation" placeholder="Designation" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Mobile*</label>
                                    <input type="text" value="{{ $user->mobile }}" name="mobile" id="mobile"
                                        placeholder="Mobile" class="form-control">
                                    <p></p>
                                </div>
                            </div>
                            <div class="card-footer  p-4">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </form>

                    <div class="card border-0 shadow mb-4">
                        <form action="" method="post" id="changePasswordForm" name="changePasswordForm">
                            @csrf
                            <div class="card-body p-4">
                                <h3 class="fs-4 mb-1">Change Password</h3>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Old Password*</label>
                                    <input type="password" name="old_password" id="old_password" placeholder="Old Password"
                                        class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-4">
                                    <label for="" class="mb-2">New Password*</label>
                                    <input type="password" id="new_password" name="new_password" placeholder="New Password"
                                        class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-4">
                                    <label for="" class="mb-2">Confirm Password*</label>
                                    <input type="password" id="confirm_password" name="confirm_password"
                                        placeholder="Confirm Password" class="form-control">
                                    <p></p>
                                </div>
                            </div>
                            <div class="card-footer  p-4">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
@section('customJs')
    <script>
        $("#userForm").submit(function(event) {
            event.preventDefault();
            $.ajax({
                url: '{{ route('account.updateProfile') }}',
                type: 'put',
                data: $("#userForm").serializeArray(),
                dataType: 'json',
                success: function(response) {
                    if (response.status == true) {
                        window.location.href = "{{ url()->current() }}";
                    } else {
                        var errors = response.errors;
                        if (errors.name) {
                            $("#name").addClass('is-invalid').siblings('p')
                                .addClass('invalid-feedback').html(errors.name);
                        } else {
                            $("#name").removeClass('is-invalid').siblings('p')
                                .removeClass('invalid-feedback').html('');
                        }
                        if (errors.email) {
                            $("#email").addClass('is-invalid').siblings('p')
                                .addClass('invalid-feedback').html(errors.email);
                        } else {
                            $("#email").removeClass('is-invalid').siblings('p')
                                .removeClass('invalid-feedback').html('');
                        }
                        if (errors.designation) {
                            $("#designation").addClass('is-invalid').siblings('p')
                                .addClass('invalid-feedback').html(errors.designation);
                        } else {
                            $("#designation").removeClass('is-invalid').siblings('p')
                                .removeClass('invalid-feedback').html('');
                        }
                        if (errors.mobile) {
                            $("#mobile").addClass('is-invalid').siblings('p')
                                .addClass('invalid-feedback').html(errors.mobile);
                        } else {
                            $("#mobile").removeClass('is-invalid').siblings('p')
                                .removeClass('invalid-feedback').html('');
                        }

                    }
                }
            });

        });


        $("#changePasswordForm").submit(function(event) {
            event.preventDefault();
            $.ajax({
                url: '{{ route('account.updatePassword') }}',
                type: 'post',
                data: $("#changePasswordForm").serializeArray(),
                dataType: 'json',
                success: function(response) {
                    if (response.status == true) {
                        window.location.href = "{{ url()->current() }}";
                    } else {
                        var errors = response.errors;
                        if (errors.old_password) {
                            $("#old_password").addClass('is-invalid').siblings('p')
                                .addClass('invalid-feedback').html(errors.old_password);
                        } else {
                            $("#old_password").removeClass('is-invalid').siblings('p')
                                .removeClass('invalid-feedback').html('');
                        }
                        if (errors.new_password) {
                            $("#new_password").addClass('is-invalid').siblings('p')
                                .addClass('invalid-feedback').html(errors.new_password);
                        } else {
                            $("#new_password").removeClass('is-invalid').siblings('p')
                                .removeClass('invalid-feedback').html('');
                        }
                        if (errors.confirm_password) {
                            $("#confirm_password").addClass('is-invalid').siblings('p')
                                .addClass('invalid-feedback').html(errors.confirm_password);
                        } else {
                            $("#confirm_password").removeClass('is-invalid').siblings('p')
                                .removeClass('invalid-feedback').html('');
                        }


                    }
                }
            });

        });
    </script>
@endsection

@extends('inc.master')
@section('body')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<div class="container mt-5">
    <h1>Create Item</h1>
    <div id="success-message"></div>

    <form id="item-form" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="type">Type:</label>
            <input type="text" id="type" name="type" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="images">Images:</label>
            <input type="file" id="images" name="images[]" class="form-control" accept="image/*" multiple>
        </div>
        <button type="submit" class="btn btn-primary">Create Item</button>
    </form>

    <h2 class="mt-5">Items List</h2>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Type</th>
                <th scope="col">Images</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody id="items-list">
            @foreach($items as $index => $item)
            <tr id="item-{{ $item->id }}">
                <th scope="row">{{ $index + 1 }}</th>
                <td>{{ $item->name }}</td>
                <td>{{ $item->type }}</td>
                <td>
                    @if(is_array($item->images))
                    @foreach($item->images as $image)
                    <img src="{{ asset('storage/' . $image) }}" alt="Image" width="50">
                    @endforeach
                    @endif
                </td>
                <td>
                    <button class="btn btn-sm btn-warning edit-btn" data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-type="{{ $item->type }}" data-images="{{ json_encode($item->images) }}">Edit</button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $item->id }}">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal" tabindex="-1" role="dialog" id="editModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="edit-form" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="edit-item-id" name="id">
                    <div class="form-group">
                        <label for="edit-name">Name:</label>
                        <input type="text" id="edit-name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-type">Type:</label>
                        <input type="text" id="edit-type" name="type" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-images">Images:</label>
                        <input type="file" id="edit-images" name="images[]" class="form-control" accept="image/*" multiple>
                    </div>
                    <div id="current-images" class="mb-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Create item
        $('#item-form').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: "{{ route('items.store') }}",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#success-message').html('<div class="alert alert-success">' + response.success + '</div>');

                    // Append new item to the items list
                    let images = JSON.parse(response.item.images);
                    let newRow = `
                            <tr id="item-${response.item.id}">
                                <th scope="row">${$('#items-list tr').length + 1}</th>
                                <td>${response.item.name}</td>
                                <td>${response.item.type}</td>
                                <td>
                        `;

                    if (images.length) {
                        images.forEach(function(image) {
                            newRow += `<img src="{{ asset('storage') }}/${image}" alt="Image" width="50">`;
                        });
                    }

                    newRow += `</td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" data-id="${response.item.id}" data-name="${response.item.name}" data-type="${response.item.type}" data-images='${response.item.images}'>Edit</button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="${response.item.id}">Delete</button>
                                </td>
                            </tr>`;

                    $('#items-list').append(newRow);
                    $('#item-form')[0].reset();
                },
                error: function(response) {
                    console.log('Error:', response);
                }
            });
        });

        // Edit item
        $(document).on('click', '.edit-btn', function() {
            let itemId = $(this).data('id');
            let itemName = $(this).data('name');
            let itemType = $(this).data('type');
            let itemImages = $(this).data('images');

            $('#edit-item-id').val(itemId);
            $('#edit-name').val(itemName);
            $('#edit-type').val(itemType);

            // عرض الصور الحالية
            let imagesHtml = '';
            if (itemImages.length) {
                itemImages.forEach(function(image) {
                    imagesHtml += `<img src="{{ asset('storage') }}/${image}" alt="Image" width="50">`;
                });
            }
            $('#current-images').html(imagesHtml);

            $('#editModal').modal('show');
        });

        $('#edit-form').on('submit', function(e) {
            e.preventDefault();
            let itemId = $('#edit-item-id').val();
            let formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: "/items/" + itemId,
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#success-message').html('<div class="alert alert-success">' + response.success + '</div>');

                    let images = JSON.parse(response.item.images);
                    let updatedRow = `
                            <th scope="row">${$('#items-list tr').index($('#item-' + response.item.id)) + 1}</th>
                            <td>${response.item.name}</td>
                            <td>${response.item.type}</td>
                            <td>
                        `;

                    if (images.length) {
                        images.forEach(function(image) {
                            updatedRow += `<img src="{{ asset('storage') }}/${image}" alt="Image" width="50">`;
                        });
                    }

                    updatedRow += `</td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-btn" data-id="${response.item.id}" data-name="${response.item.name}" data-type="${response.item.type}" data-images='${response.item.images}'>Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${response.item.id}">Delete</button>
                            </td>`;

                    $('#item-' + response.item.id).html(updatedRow);
                    $('#editModal').modal('hide');
                },
                error: function(response) {
                    console.log('Error:', response);
                }
            });
        });

        // Delete item
        $(document).on('click', '.delete-btn', function() {
            let itemId = $(this).data('id');

            if (confirm('Are you sure you want to delete this item?')) {
                $.ajax({
                    type: 'DELETE',
                    url: "/items/" + itemId,
                    data: {
                        _token: "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        $('#success-message').html('<div class="alert alert-success">' + response.success + '</div>');
                        $('#item-' + itemId).remove();
                    },
                    error: function(response) {
                        console.log('Error:', response);
                    }
                });
            }
        });
    });
</script>

@endsection
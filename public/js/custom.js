function readURL(input, img) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#user_avtar').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
function deleteRecord(url) {
    $.ajax({
        url: url
        , type: 'post'
        , data: {
            '_method': 'DELETE'
            , '_token': csrf_token
        }
        , dataType: 'json'
        , success: function(response) {
            table.ajax.reload();
            toastr.success(response.msg);
        }
        , error: function(error) {
            console.log('error', error);
            toastr.error(error.responseJSON.errors);
        }
    });
}

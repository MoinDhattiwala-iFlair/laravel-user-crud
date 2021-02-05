<!-- Modal -->
<div class="modal fade" id="basicModal" tabindex="-1" role="dialog" aria-labelledby="basicModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="basicModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>
@section('js')
<script type="text/javascript">
    var modal = $('#basicModal');
    var modalBody = modal.find('.modal-body');

    function showBasicModal(title, url) {
        modalBody.html('');
        modal.find('.modal-title').html(title);
        $.ajax({
            'url': url
            , 'dataType': 'json'
            , success: function(response) {
                modalBody.html(response.html);
                $(modal).modal();                
            }
            , error: function(error) {
                console.log(error);
                toastr.error('Something went wrong.');
            }
        });
    }

    function saveBasicModalFrm(frm) {
        $(frm).find('.error').html('');
        $.ajax({
            'url': $(frm).attr('action')
            , 'type': $(frm).attr('method')
            , 'data': $(frm).serialize()
            , 'dataType': 'json'
            , success: function(response) {
                if (response.status == 1) {
                    $(modal).modal('hide');
                    table.ajax.reload();
                }
                toastr.success(response.msg);
            }
            , error: function(error) {
                $.each(error.responseJSON.errors, function(key, value) {
                    $('#' + key + '-error').html(value);
                });
                toastr.error(error.responseJSON.errors);
            }
        });
        return false;
    }

    $(document).on('submit', '#saveBasicModalFrm', function(e) {
        e.preventDefault();
        $(this).find('.error').html('');
        var formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action')
            , type: 'POST'
            , data: formData
            , dataType: 'json'
            , cache: false
            , contentType: false
            , processData: false
            , success: function(response) {
                console.log("success");
                if (response.status == 1) {
                    table.ajax.reload();
                    $(modal).modal('hide');
                }
                toastr.success(response.msg);
            }
            , error: function(error) {
                console.log("error");
                $.each(error.responseJSON.errors, function(key, value) {
                    $('#' + key + '-error').html(value);
                });
                toastr.error(error.responseJSON.errors);
            }
        });

    });
</script>
@endsection
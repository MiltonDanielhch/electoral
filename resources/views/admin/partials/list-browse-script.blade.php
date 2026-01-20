@push('javascript')
<script>
    let countPage = 10;
    const listUrl = '{{ $listUrl }}';

    $(document).ready(function () {
        list();

        let searchTimeout;
        $('#search').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => list(1), 400);
        });

        $('#search').on('keyup', function (e) {
            if (e.keyCode === 13) {
                clearTimeout(searchTimeout);
                list(1);
            }
        });

        $('#select-paginate').on('change', function() {
            countPage = $(this).val();
            list(1);
        });

        $('#list-container').on('click', '.pagination a', function(e) {
            e.preventDefault();
            let page = $(this).attr('href').split('page=')[1];
            list(page);
        });
    });

    function deleteItem(url, itemName) {
        $('#delete_form').attr('action', url);
        $('#delete_modal .modal-title').html(`<i class="voyager-trash"></i> ¿Estás seguro de que quieres eliminar "${itemName}"?`);
    }

    function list(page = 1) {
        const search = $('#search').val()?.trim() || '';

        let urlParams = new URLSearchParams({
            search: search,
            paginate: countPage,
            page: page
        });

        $('#list-container').html(`
            <div class="text-center" style="padding: 40px">
                <i class="voyager-refresh voyager-2x voyager-spin"></i><br>Cargando...
            </div>
        `);

        $.ajax({
            url: `${listUrl}?${urlParams.toString()}`,
            type: 'GET',
            success: response => $('#list-container').html(response),
            error: (xhr) => {
                console.error('Error al cargar la lista:', xhr);
                $('#list-container').html(`<div class="alert alert-danger text-center">Error al cargar los datos. Por favor, intenta de nuevo.</div>`);
            }
        });
    }
</script>
@endpush

@push('javascript')
<script>
    let countPage = 10;
    let currentRequest = null;
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

    // Manejo del botón eliminar mediante data-attributes
    $('#list-container').on('click', '[data-target="#delete_modal"]', function(e) {
        e.preventDefault();
        const url = $(this).data('delete-url');
        const itemName = $(this).data('item-name');
        
        $('#delete_form').attr('action', url);
        $('#delete_modal .modal-title').html(`<i class="voyager-trash"></i> ¿Eliminar "${itemName}"?`);
        $('#delete_modal .modal-body').html(`
            <div class="alert alert-warning">
                <strong>Advertencia:</strong> Esta acción no se puede deshacer.<br>
                "${itemName}" será eliminado permanentemente.
            </div>
        `);
    });

    function list(page = 1) {
        const search = $('#search').val()?.trim() || '';

        let urlParams = new URLSearchParams({
            search: search,
            paginate: countPage,
            page: page
        });

        if (currentRequest) {
            currentRequest.abort();
        }

        $('#list-container').html(`
            <div class="text-center" style="padding: 40px">
                <i class="voyager-refresh voyager-2x voyager-spin"></i><br>Cargando...
            </div>
        `);

        currentRequest = $.ajax({
            url: `${listUrl}?${urlParams.toString()}`,
            type: 'GET',
            success: response => {
                $('#list-container').html(response);
                const event = new CustomEvent('list-loaded');
                document.dispatchEvent(event);
                currentRequest = null;
            },
            error: (xhr) => {
                if (xhr.statusText !== 'abort') {
                    console.error('Error al cargar la lista:', xhr);
                    $('#list-container').html(`<div class="alert alert-danger text-center">Error al cargar los datos.</div>`);
                }
            }
        });
    }
</script>
@endpush

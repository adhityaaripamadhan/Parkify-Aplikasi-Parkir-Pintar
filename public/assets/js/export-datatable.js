$(function () {

    const table = $('#rekapTransaksi').DataTable({
        dom: 'frtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Rekap Transaksi Parkir'
            },
            {
                extend: 'pdfHtml5',
                title: 'Rekap Transaksi Parkir',
                orientation: 'landscape',
                pageSize: 'A4'
            }
        ],
        language:{
            url:"https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"
        }
    });

    $('.btn-export-toggle').on('click', function (e) {
        e.stopPropagation();
        $('.export-menu').toggle();
    });

    $(document).on('click', function () {
        $('.export-menu').hide();
    });
    
    $('#exportExcel').on('click', function () {
        table.button(0).trigger();
        $('.export-menu').hide();
    });

    $('#exportPdf').on('click', function () {
        table.button(1).trigger();
        $('.export-menu').hide();
    });

});
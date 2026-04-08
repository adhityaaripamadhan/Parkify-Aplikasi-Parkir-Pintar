$(document).ready(function(){
    $('#tblMasuk, #tblKeluar, #tblHistory').DataTable();

    setInterval(() => location.reload(), 5000);
});

function selesai(id){
    $.post('/transaksi/selesai', {id:id}, () => location.reload());
}

$(document).ready(function () {
    $('#rekapTransaksi').DataTable({
        dom: 'Bfrtip',
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
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"
        }
    });
});

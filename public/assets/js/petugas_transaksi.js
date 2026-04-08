$(document).ready(function () {

    const bahasaPaging = {
        paginate: {
            previous: "Sebelumnya",
            next: "Selanjutnya"
        }
    };

    initTable('#tblMasuk', bahasaPaging);
    initTable('#tblKeluar', bahasaPaging);
    initTable('#tblHistory', bahasaPaging);

});

function initTable(selector, bahasaPaging) {

    if ($(selector).length) {
        $(selector).DataTable({
            searching: false,
            lengthChange: false,
            info: false,
            paging: true,
            pageLength: 5,
            ordering: false,
            language: bahasaPaging
        });
    }
}

function selesai(id) {

    if (!confirm("Yakin buka palang dan selesaikan transaksi?")) return;

    fetch('index.php?url=TransaksiController/selesai', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({ id: id })
    })
        .then(res => res.text())
        .then(data => {
            console.log("Response:", data);
        })
        .then(data => {

            if (data.status === 'ok') {

                showToast("Palang terbuka & transaksi selesai ✅");

                setTimeout(() => {
                    location.reload();
                }, 800);

            } else {

                showToast("Gagal memproses transaksi ❌");

            }

        })
        .catch(error => {

            console.error("System Error:", error);
            showToast("Terjadi kesalahan sistem");

        });
}

function showToast(message) {

    let toast = document.createElement("div");
    toast.innerText = message;

    toast.style.position = "fixed";
    toast.style.bottom = "20px";
    toast.style.right = "20px";
    toast.style.background = "#1e293b";
    toast.style.color = "#fff";
    toast.style.padding = "12px 20px";
    toast.style.borderRadius = "8px";
    toast.style.boxShadow = "0 5px 15px rgba(0,0,0,0.2)";
    toast.style.zIndex = "9999";

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}
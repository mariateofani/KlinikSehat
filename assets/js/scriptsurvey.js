document.addEventListener("DOMContentLoaded", function(){
    const form = document.querySelector("form");

    if(form){
        form.addEventListener("submit", function(e){
            e.preventDefault();

            alert("✅ Terima kasih! Survei Anda berhasil dikirim.");

            setTimeout(() => {
                window.location.href = "dashboard.php";
            }, 300);
        });
    }
});
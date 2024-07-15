

// $(document).ready(function () {
//     const modal = $("#modal");
//     const showModal = $("#showModal");
//     const closeModal = $("#closeModal");

//     showModal.on("click", function () {
//         modal.show();
//     });
// })
$(document).ready(function () {
    const $modal = $(".modal");
    const $showModal = $(".showModal");
    const $closeModal = $(".closeModal");

    $showModal.on("click", function () {
        $modal[0].showModal();  // [0] pour accéder à l'élément natif
    });

    $closeModal.on("click", function () {
        $modal[0].close();  // [0] pour accéder à l'élément natif
    });

    // Ferme la fenêtre au click hors de la modal
    $(window).on("click", function (event) {
        if (event.target === $modal[0]) {
            $modal[0].close();
        }
    });
});
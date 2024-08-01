$(document).ready(function () {
    //_______________________________GESTION DES COULEURS ALEATOIRES________________________________
    // Variable de couleur pour les H2 des cards articles
    var colors = [
        "var(--pink-color)",
        "var(--red-color)",
        "var(--blue-color)",
        "var(--green-color)",
    ];
    // Couleur aléatoire pour chaque élément de la classe .badges_guide i
    $(".badges_guide i").each(function (index) {
        $(this).css("color", colors[index % colors.length]);
    });

    var stickerClasses = [
        "stickers_pink",
        "stickers_red",
        "stickers_blue",
        "stickers_green",
    ];
    // Couleur aléatoire pour chaque élément de la classe service_cards_header
    $(".service_cards_header").each(function (index) {
        var color = colors[index % colors.length];
        var stickerClass = stickerClasses[index % stickerClasses.length];

        $(this).css("color", color); // Change la couleur du H2

        $(this).find(".stickers_price").addClass(stickerClass); // Ajouter la classe de sticker à stickers_price
    });

    //_______________________________GESTION DES OFFRES DE PRIX (SERVICES)________________________________
    var stepMappings = [
        {
            next: "#next_to_site_services",
            back: "#back_to_identite_service",
            currentStep: "#identite_service",
            nextStep: "#site_services",
            currentStepId: "#step1",
            nextStepId: "#step2"
        },
        {
            next: "#next_to_presta_services",
            back: "#back_to_site_services",
            currentStep: "#site_services",
            nextStep: "#presta_a_la_carte",
            currentStepId: "#step2",
            nextStepId: "#step3"
        }
    ];

    $("#step1").css({ 
        "background-color": "white", 
        "color": "black",
        "width": "fit-content",
    });

    stepMappings.forEach(function(mapping) {
        $(mapping.next).on("click", function () {
            $(mapping.currentStep).hide();
            $(mapping.nextStep).show();
            $(mapping.currentStepId).removeAttr("style");
            $(mapping.nextStepId).css({
                "background-color": "white",
                "color": "black",
                "width": "fit-content",
            });
        });

        $(mapping.back).on("click", function () {
            $(mapping.nextStep).hide();
            $(mapping.currentStep).show();
            $(mapping.nextStepId).removeAttr("style");
            $(mapping.currentStepId).css({
                "background-color": "white",
                "color": "black",
                "width": "fit-content",
            });
        });
    });

    // Fonction pour gérer le changement d'état des checkboxes
    function handleCheckboxChange() {
        $(this).next('label').toggleClass(this.checked);
    }

    // Fonction pour gérer le clic sur les labels
    function handleLabelClick(e) {
        e.preventDefault();
        var $checkbox = $(this).prev('input[type="checkbox"]');
        $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
    }

    // Appliquer les gestionnaires d'événements à toutes les sections
    $('#identite_visuelle, #services_site_internet, #presta_a_la_carte').each(function() {
        $(this).find('input[type="checkbox"]').on('change', handleCheckboxChange);
        $(this).find('label').on('click', handleLabelClick);
    });


    //_______________________________FAQ Icones________________________________

    $("#faq summary").click(function(){
        var icon = $(this).find("i");
        if(icon.hasClass("fa-arrow-down")) {
            icon.removeClass("fa-arrow-down").addClass("fa-arrow-right");
        } else {
            icon.removeClass("fa-arrow-up").addClass("fa-arrow-down");
        }
    });
    
});

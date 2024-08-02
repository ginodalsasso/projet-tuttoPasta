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

    var buttonClasses = [
        "full_button_pink",
        "full_button_red",
        "full_button_blue",
        "full_button_green",
    ];

    // Couleur aléatoire pour chaque élément de la classe service_cards_header
    $(".service_cards").each(function (index) {
        var color = colors[index % colors.length];
        var stickerClass = stickerClasses[index % stickerClasses.length];
        var buttonClass = buttonClasses[index % buttonClasses.length];

        $(this).find(".service_cards_header").css("color", color); // Change la couleur du H2

        $(this).find(".stickers_price").addClass(stickerClass); // Ajoute la classe de sticker
        $(this).find(".service_button").addClass(buttonClass); // Ajoute la classe de button
    });



    //_______________________________GESTION DES OFFRES DE PRIX (SERVICES)________________________________
    // Variable pour les offres de prix
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

    // Initialisation des étapes des offres de prix
    $("#step1").css({ 
        "background-color": "white", 
        "color": "black",
        "width": "fit-content",
    });


    // Gestion des clics sur les boutons Suivant
    stepMappings.forEach(function(mapping) {
        $(mapping.next).on("click", function () {
            $(mapping.currentStep).hide(); // Masquer l'étape actuelle
            $(mapping.nextStep).show(); // Afficher l'étape suivante
            $(mapping.currentStepId).removeAttr("style");  // Supprimer les styles de l'étape actuelle
            $(mapping.nextStepId).css({ // Ajouter les styles à l'étape suivante
                "background-color": "white",
                "color": "black",
                "width": "fit-content",
            });
        });


        // Gestion du clic sur le bouton Précédent
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

    //_______________________________GESTION DES CHECKBOXES ET LABELS SUR LE FORMULAIRE D'OFFRES________________________________
    // Fonction pour gérer le changement d'état des checkboxes
    function handleCheckboxChange() {
        // Ajouter ou supprimer la classe checked à l'élément suivant
        $(this).next('label').toggleClass(this.checked);
    }


    // Fonction pour gérer le clic sur les labels
    function handleLabelClick(e) {
        e.preventDefault();
        var $checkbox = $(this).prev('input[type="checkbox"]');
        $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
    }


    // Gestion des checkboxes et des labels
    $('#identite_visuelle, #services_site_internet, #presta_a_la_carte').each(function() {
        $(this).find('input[type="checkbox"]').on('change', handleCheckboxChange); // Gestion des changements de checkbox
        $(this).find('label').on('click', handleLabelClick); // Gestion des clics sur les labels
    });


    //_______________________________FAQ Icones________________________________
    $("#faq summary").click(function(){
        // Change l'icone de la balise <summary> lorsqu'on clique dessus
        var icon = $(this).find("i");
        if(icon.hasClass("fa-arrow-down")) {
            icon.removeClass("fa-arrow-down").addClass("fa-arrow-right");
        } else {
            icon.removeClass("fa-arrow-up").addClass("fa-arrow-down");
        }
    });
    
});


$(document).ready(function() {
    //_______________________________GESTION DES COULEURS ALEATOIRES________________________________
    // Variable de couleur pour les H2 des cards articles
    var colors = ['var(--pink-color)', 'var(--red-color)', 'var(--blue-color)', 'var(--green-color)'];
    // Couleur aléatoire pour chaque élément de la classe .badges_guide i
    $(".badges_guide i").each(function(index) {
        $(this).css('color', colors[index % colors.length]);
    });

    var stickerClasses = ['stickers_pink', 'stickers_red', 'stickers_blue', 'stickers_green'];
    // Couleur aléatoire pour chaque élément de la classe service_cards_header
    $(".service_cards_header").each(function(index) {
        var color = colors[index % colors.length];
        var stickerClass = stickerClasses[index % stickerClasses.length];
        
        $(this).css('color', color); // Change la couleur du H2

        $(this).find('.stickers_price').addClass(stickerClass); // Ajouter la classe de sticker à stickers_price
    });


    //_______________________________GESTION DES OFFRES DE PRIX (SERVICES)________________________________
    var nextStep = $('#next_to_site_services')
    var stepBack = $('#back_to_identite_service')

    nextStep.on('click', function(){
        $('#site_services').show()
        $('#identite_service').hide()
    })

    stepBack.on('click', function(){
        $('#identite_service').show()
        $('#site_services').hide()
    })

    var nextStep = $('#next_to_presta_services')
    var stepBack = $('#back_to_site_services')


    nextStep.on('click', function(){
        $('#presta_services').show()
        $('#site_services').hide()
        console.log($('#service_services_identite_visuelle').val())
    })

    stepBack.on('click', function(){
        $('#site_services').show()
        $('#presta_services').hide()
    })

    var nextStep = $('#next_to_recap')
    var stepBack = $('#back_to_presta_services')


    nextStep.on('click', function(){
        $('#recap_services').show()
        $('#presta_services').hide()
    })

    stepBack.on('click', function(){
        $('#presta_services').show()
        $('#recap_services').hide()
    })


});
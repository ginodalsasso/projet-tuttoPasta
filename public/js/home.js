
$(document).ready(function() {
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
});
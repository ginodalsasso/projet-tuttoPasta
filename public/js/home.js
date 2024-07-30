
$(document).ready(function() {
    // Variable de couleur pour les H2 des cards articles
    var colors = ['var(--pink-color)', 'var(--red-color)', 'var(--blue-color)', 'var(--green-color)'];
    $(".badges_guide i").each(function(index) {
        $(this).css('color', colors[index % colors.length]);
    });

    $(".stickers_price").each(function() {
        $(this).css('color', colors[index % colors.length]);
    });
});
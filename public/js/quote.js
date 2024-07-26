$(document).ready(function() {   
    // Suppression d'un devis
    $(document).on('click', '.delete_quote', function(e) {
        e.preventDefault();
        var quoteId = $(this).data('id');
        
        if (confirm("Êtes-vous sûr de vouloir supprimer ce devis ?")) {
            deleteQuote(quoteId, csrfToken);
        }
    });

    // Suppression d'un devis
    $(document).on('click', '.archive_quote', function(e) {
        e.preventDefault();
        var quoteId = $(this).data('id');
        
        if (confirm("Êtes-vous sûr de vouloir archiver ce devis ?")) {
            archiveQuote(quoteId, csrfToken);
        }
    });
});
    
    
//___________________________________Suppression devis_______________________________________
function deleteQuote(quoteId, csrfToken) {
    var url = `/admin/quote/${quoteId}/delete`;
    
    $.ajax({
        url: url,
        method: 'DELETE', 
        headers: {
            'X-CSRF-TOKEN': csrfToken  // Ajout du token CSRF dans les headers
        },
        success: function(data) {
            if (data.success) {
                // Supprimer le devis
                $('#quote-' + quoteId).remove();
            } else {
                alert(data.message || "Erreur lors de la suppression du devis. Veuillez réessayer.");
                console.log(data);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Erreur lors de la suppression du devis :", textStatus, errorThrown);
            alert("Une erreur est survenue lors de la suppression du devis. Veuillez vérifier votre connexion et réessayer.");
        }
    });
}

//___________________________________Archivage devis_______________________________________
function archiveQuote(quoteId, csrfToken) {
    var url = `/admin/quote/${quoteId}/archive`;
    
    $.ajax({
        url: url,
        method: 'POST', 
        headers: {
            'X-CSRF-TOKEN': csrfToken  // Ajout du token CSRF dans les headers
        },
        success: function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || "Erreur lors du changement d'état du devis. Veuillez réessayer.");
                console.log(data);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Erreur lors du changement d'état du devis :", textStatus, errorThrown);
            alert("Une erreur est survenue lors du changement d'état du devis. Veuillez vérifier votre connexion et réessayer.");
        }
    });
}
var csrfToken = $('meta[name="csrf-token"]').attr('content');  // Récupère le token csrf dans le head du template

//___________________________________DOCUMENT READY_______________________________________
$(document).ready(function() {
    // Événement soumission du formulaire commentaire
    $('#comment_form').on('submit', function(e) {
        e.preventDefault();
        $(".error_msg").text("");
        $("#comment_commentContent").removeClass("input_invalid");
    
        let isValid = true;
        const message = $("#comment_commentContent").val();
    
        if ($("#comment_commentContent").val() === "") {
            $("#comment_commentContent").addClass("input_invalid");
            isValid = false;
        }
    
        if (message === "" || message.length < 5) {
            $("#message_error").text("Le message est invalide et doit contenir au minimum 5 caractères");
            $("#comment_commentContent").addClass("input_invalid");
            isValid = false;
        }
    
        // Si les contraintes sont respectées alors envoi la requête d'ajout d'un commentaire
        if (isValid) {
            submitNewComment($(this), csrfToken);
        }
    });


    // Gestion de la soumission du formulaire d'édition
    $(document).on('submit', '.edit_comment_form', function(e) {
        e.preventDefault();

        $(".error_msg").text("");
        $(".edit_commentContent").removeClass("input_invalid");
        
        let isValid = true;
        const editMessage = $("#edit_commentContent").val();

        if (editMessage === "" || editMessage.length < 5) {
            $("#editMessage_error").text("Le message est invalide et doit contenir au minimum 5 caractères");
            $("#edit_commentContent").addClass("input_invalid");
            isValid = false;
        }
        // Si les contraintes sont respectées alors envoi la requête d'édition d'un commentaire
        if (isValid) {
            var $form = $(this);
            submitEditComment($form, csrfToken);
        }
    });
    

    // Gestion de l'annulation de l'édition
    $(document).on('click', '.cancel_edit', function() {
        location.reload();
    });


    // // Gestion de la suppression de commentaire
    $('.delete_comment').on('click', function(e) {
        e.preventDefault();
        var commentId = $(this).data('id');
        var slug = $(this).closest('.comment').data('slug'); // Récupère le slug de l'article
        deleteComment(slug, commentId, csrfToken);
    });

    // Formulaire d'édition d'un commentaire
    $('.edit_comment').on('click', function(e) {
        e.preventDefault();
        var commentId = $(this).data('id'); // Récupère l'ID du commentaire à éditer
        var commentContent = $(this).closest('.comment').find('.comment_content p').text().trim(); // Récupère le contenu du commentaire à éditer
        var slug = $(this).closest('.comment').data('slug'); // Récupère le slug de l'article
        var csrfToken = $('#comment_form').find('input[name="comment[_token]"]').val(); // Récupère le token CSRF du formulaire d'ajout
    
        var editForm = `
            <form class="edit_comment_form" action="/blog/${slug}/comment/${commentId}/edit" method="POST" data-id="${commentId}">
                <textarea name="comment[commentContent]" class="data" id="edit_commentContent">${commentContent}</textarea>
                <div class="error_msg" id="editMessage_error"></div>
                <input type="hidden" name="comment[_token]" value="${csrfToken}">
                <button type="button" class="cancel_edit stickers_white">Annuler</button>
                <button id="editCommentMessage" class="stickers_black" type="submit">Mettre à jour</button>
            </form>
        `;
        $(this).closest('.comment').find('.comment_content').html(editForm); // Remplace le contenu du commentaire par le formulaire d'édition
    });
    
    // Variable de couleur pour les H2 des cards articles
    var colors = ['var(--pink-color)', 'var(--red-color)', 'var(--blue-color)', 'var(--green-color)'];
    $('.article_card_title').each(function(index) {
        $(this).css('color', colors[index % colors.length]);
    });
    
    // Message d'erreurs UI
    $("#saved_comment").on("click", function(event) {

    });
    
});


//___________________________________AJAX_______________________________________
// Requête d'ajout d'un commentaire
function submitNewComment($form, csrfToken) {
    var url = $form.attr('action');
    var formData = $form.serialize();

    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-Token': csrfToken
        },
        success: function(data) {
            if (data.success) {
                console.log(data)
                addNewComment(data.comment);
                
                $form[0].reset(); // Réinitialise le formulaire après l'ajout du commentaire
                
                updateCommentCount(true); // Incrémente le count commentaire

            } else {
                alert("Erreur lors de l'ajout du commentaire. Veuillez réessayer.");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Erreur lors de la récupération des données :", textStatus, errorThrown);
            alert("Une erreur est survenue lors de la soumission du commentaire. Veuillez vérifier votre connexion et réessayer.");
        }
    });
}



// Requête d'édition d'un commentaire
function submitEditComment($form, csrfToken) {
    var url = $form.attr('action');
    var formData = $form.serialize();

    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-Token': csrfToken
        },
        success: function(data) {
            if (data.success) {
                var commentDiv = $('#comment-' + data.comment.id); // Cherche l'id du commentaire séléctionné
                commentDiv.find('.comment_content').html('<p>' + data.comment.commentContent + '</p>'); // Met à jour le contenu du commentaire
            } else {
                alert("Erreur lors de la modification du commentaire. Veuillez réessayer.");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Erreur lors de la récupération des données :", textStatus, errorThrown);
            alert("Une erreur est survenue lors de la soumission du commentaire. Veuillez vérifier votre connexion et réessayer.");
        }
    });
}



// Requête suppression d'un commentaire
function deleteComment(slug, commentId, csrfToken) {
    var url = `/blog/${slug}/comment/${commentId}/delete`;

    $.ajax({
        url: url,
        method: 'DELETE',
        headers: {
            'X-CSRF-Token': csrfToken
        },
        success: function(data) {
            if (data.success) {
                // Supprimer le commentaire de l'interface utilisateur
                $('#comment-' + commentId).remove();
                updateCommentCount(false) // Décrémente le count commentaire
            } else {
                alert("Erreur lors de la suppression du commentaire. Veuillez réessayer.");
                console.log(data)
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Erreur lors de la suppression du commentaire :", textStatus, errorThrown);
            alert("Une erreur est survenue lors de la suppression du commentaire. Veuillez vérifier votre connexion et réessayer.");
        }
    });
}

//___________________________________HTML D'AJOUT D'UN COMMENTAIRE_______________________________________
// Ajout html du nouveau commentaire
function addNewComment(comment) {
    var newCommentHtml = `
        <div class="comment" id="comment-${comment.id}" data-slug="${comment.slug}">
            <div class="comment_head">
                <p>${comment.username}</p>
                <p>${comment.date}</p>
            </div>
            <div class="comment_content">
                ${comment.commentContent}
            </div>
            <div class="comment_actions">
                <a href="#" class="edit_comment" data-id="${comment.id}"><img src="/img/editer.png" height="18" alt="icône d'édition"></a>
                <a href="#" class="delete_comment" data-id="${comment.id}"><img src="/img/annuler.png" height="18" alt="icône d'annulation"></a>
            </div>
        </div>
    `;
    $('#comments_section').prepend(newCommentHtml); // Ajoute le nouveau commentaire à la fin de la section des commentaires
}


// //___________________________________LOGIQUE COMMENTAIRE_______________________________________
// // Mise à jour du count des commentaires
function updateCommentCount(addComment) {
    var $commentTitle = $('#comment_title');
    var commentCount = $commentTitle.data('count') || 0;

    if (addComment) {
        commentCount += 1;
    } else {
        if (commentCount > 0) { // Empêche le compteur d'aller en dessous de 0
            commentCount -= 1;
        }
    }

    $commentTitle.data('count', commentCount);
    
    if (commentCount <= 1) {
        $commentTitle.text(commentCount + ' Commentaire:');
    } else {
        $commentTitle.text(commentCount + ' Commentaires:');
    }
}
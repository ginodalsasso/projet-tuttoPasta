var csrfToken = $('meta[name="csrf-token"]').attr('content');  // Récupère le token csrf dans le head du template

//___________________________________DOCUMENT READY_______________________________________
$(document).ready(function() {
    // Événement soumission du formulaire commentaire
    $('#comment_form').on('submit', function(e) {
        e.preventDefault();
        submitNewComment($(this), csrfToken);
    });

    // Gestion de la soumission du formulaire d'édition
    $(document).on('submit', '.edit_comment_form', function(e) {
        e.preventDefault();

        submitEditComment($(this), csrfToken);
    });

    // Gestion de l'annulation de l'édition
    $(document).on('click', '.cancel_edit', function() {
        location.reload();
    });

    // Gestion de la suppression de commentaire
    $(document).on('click', '.delete_comment', function(e) {
        e.preventDefault();
        var commentId = $(this).data('id');
        var slug = $(this).closest('.comment').data('slug'); // Récupère le slug de l'article
        deleteComment(slug, commentId, csrfToken);
    });

    // Gestion de l'édition de commentaire
    $('.edit_comment').on('click', function(e) {
        e.preventDefault();
        var commentId = $(this).data('id'); // Récupère l'ID du commentaire à éditer
        var commentContent = $(this).closest('.comment').find('.comment_content').text().trim(); // Récupère le contenu du commentaire à éditer
        var slug = $(this).closest('.comment').data('slug'); // Récupère le slug de l'article
        var csrfToken = $('#comment_form').find('input[name="comment[_token]"]').val(); // Récupère le token CSRF du formulaire d'ajout

        var editUrl = `/blog/${slug}/comment/${commentId}/edit`;

        var editForm = `
            <form class="edit_comment_form" action="${editUrl}" method="POST" data-id="${commentId}">
                <textarea name="comment[commentContent]">${commentContent}</textarea>
                <input type="hidden" name="comment[_token]" value="${csrfToken}">
                <button id="update_comment" type="submit">Mettre à jour</button>
                <button type="button" class="cancel_edit">Annuler</button>
            </form>
        `;
        $(this).closest('.comment').html(editForm); // Remplace le contenu du commentaire par le formulaire d'édition
    });
});


//___________________________________AJAX_______________________________________
// Requête de soumission du formulaire pour un nouveau commentaire
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
                addNewComment(data.comment);
                $form[0].reset(); // Réinitialise le formulaire après l'ajout du commentaire
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


// Requête de soumission du formulaire pour l'édition d'un commentaire
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
                var commentId = $form.data('id');  // Récupère l'ID du commentaire à mettre à jour
                updateExistingComment(commentId, data.comment);
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


//___________________________________HTML D'AJOUT ET EDITION COMMENTAIRE_______________________________________
// Ajout html du nouveau commentaire
function addNewComment(comment) {
    var newCommentHtml = `
        <div class="comment" id="comment-${comment.id}" data-slug="${comment.slug}">
            <p>${comment.username}</p>
            <div class="comment_content">
                ${comment.commentContent}
            </div>
            <p>${comment.date}</p>
            <a href="#" class="edit_comment" data-id="${comment.id}">Modifier</a>
        </div>
    `;
    $('#comments_section').prepend(newCommentHtml); // Ajoute le nouveau commentaire à la fin de la section des commentaires
}

// Mise à jour html de l'édition d'un commentaire
function updateExistingComment(commentId, comment) {
    var updatedCommentHtml = `
        <p>${comment.username}</p>
        <div class="comment_content">
            ${comment.commentContent}
        </div>
        <p>${comment.date}</p>
        <a href="#" class="edit_comment" data-id="${comment.id}">Modifier</a>
    `;
    $(`#comment-${commentId}`).html(updatedCommentHtml); // Met à jour le contenu du commentaire dans la vue
}

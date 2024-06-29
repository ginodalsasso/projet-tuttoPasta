$(document).ready(function() {
    // Gestion de l'ajout de commentaire
    $('#commentForm').on('submit', function(e) {
        e.preventDefault();
        submitComment($(this));
    });

    // Gestion de l'édition de commentaire
    // $(document).on('click', '.edit-comment', function(e) {
    //     e.preventDefault();
    //     var commentId = $(this).data('id');
    //     var commentContent = $(this).closest('.comment').find('.comment-content').text();
        
    //     // Remplacer le contenu du commentaire par un formulaire d'édition
    //     var editForm = `
    //         <form class="edit-comment-form" data-id="${commentId}">
    //             <textarea name="commentContent">${commentContent}</textarea>
    //             <button type="submit">Mettre à jour</button>
    //             <button type="button" class="cancel-edit">Annuler</button>
    //         </form>
    //     `;
    //     $(this).closest('.comment').html(editForm);
    // });

    // // Gestion de la soumission du formulaire d'édition
    // $(document).on('submit', '.edit-comment-form', function(e) {
    //     e.preventDefault();
    //     var commentId = $(this).data('id');
    //     submitComment($(this), commentId);
    // });

    // // Gestion de l'annulation de l'édition
    // $(document).on('click', '.cancel-edit', function() {
    //     location.reload();
    // });

    function submitComment($form, commentId = null) {
        var url = commentId 
            ? $form.attr('action').replace('/comment', `/comment/${commentId}/edit`)
            : $form.attr('action');
        
        $.ajax({
            url: url,
            method: 'POST',
            data: $form.serialize(),
            success: function(data) {
                if (data.success) {
                    if (commentId) {
                        // Mise à jour du commentaire existant
                        updateExistingComment(commentId, data.comment);
                    } else {
                        // Ajout du nouveau commentaire
                        addNewComment(data.comment);
                    }
                    $form[0].reset();
                } else {
                    alert('Erreur: ' + data.errors.join(', '));
                }
            },
            error: function(xhr, status, error) {
                  }
        });
    }

    function addNewComment(comment) {
        var newCommentHtml = `
            <div class="comment" id="comment-${comment.id}">
                <div class="comment-content">${comment.commentContent}</div>
                <p>${comment.date}</p>
                <a href="#" class="edit-comment" data-id="${comment.id}">Modifier</a>
            </div>
        `;
        $('#commentsSection').append(newCommentHtml);
    }

    // function updateExistingComment(commentId, comment) {
    //     var updatedCommentHtml = `
    //         <div class="comment-content">${comment.commentContent}</div>
    //         <p>${comment.date}</p>
    //         <a href="#" class="edit-comment" data-id="${comment.id}">Modifier</a>
    //     `;
    //     $(`#comment-${commentId}`).html(updatedCommentHtml);
    // }
});
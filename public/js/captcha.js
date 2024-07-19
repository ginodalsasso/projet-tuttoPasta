// Fonction qui limite une valeur entre un minimum et un maximum
function clamp(n, min, max) {
    return Math.min(Math.max(n, min), max);
}

// Fonction qui génère un nombre aléatoire entre un minimum et un maximum
function randomNumberBetween(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
}

// Définition de la classe PuzzleCaptcha en tant qu'élément personnalisé HTML
class PuzzleCaptcha extends HTMLElement {
    connectedCallback() {
        // Lecture des attributs width, height, piece-width et piece-height de l'élément HTML
        const width = parseInt(this.getAttribute("width"), 10);
        const height = parseInt(this.getAttribute("height"), 10);
        const pieceWidth = parseInt(this.getAttribute("piece-width"), 10);
        const pieceHeight = parseInt(this.getAttribute("piece-height"), 10);
        const maxX = width - pieceWidth;
        const maxY = height - pieceHeight;

        // Ajout de classes CSS à l'élément
        this.classList.add("captcha");
        this.classList.add("captcha-waiting-interaction");

        // Définition de propriétés CSS personnalisées
        this.style.setProperty("--width", `${width}px`);
        this.style.setProperty("--image", `url(${this.getAttribute("src")})`);
        this.style.setProperty("--height", `${height}px`);
        this.style.setProperty("--pieceWidth", `${pieceWidth}px`);
        this.style.setProperty("--pieceHeight", `${pieceHeight}px`);

        // Sélection de l'élément d'entrée pour stocker la réponse
        const input = this.querySelector(".captcha-anwser");

        // Création de l'élément pièce du puzzle
        const piece = document.createElement("div");
        piece.classList.add("captcha-piece");
        this.appendChild(piece);

        // Variables pour suivre l'état du glissement et la position de la pièce
        let isDragging = false;
        let position = {
            x: randomNumberBetween(0, maxX),
            y: randomNumberBetween(0, maxY),
        };

        // Position initiale de la pièce
        piece.style.setProperty(
            "transform",
            `translate(${position.x}px, ${position.y}px)`
        );

        // Gestionnaire d'événement pour le début du glissement
        piece.addEventListener("pointerdown", (e) => {
            isDragging = true;
            document.body.style.setProperty("user-select", "none");
            this.classList.remove("captcha-waiting-interaction");
            piece.classList.add("is-moving");

            // Gestionnaire d'événement pour la fin du glissement
            window.addEventListener(
                "pointerup",
                () => {
                    document.body.style.removeProperty("user-select");
                    piece.classList.remove("is-moving");
                    isDragging = false;
                },
                { once: true }
            );
        });

        // Gestionnaire d'événement pour le mouvement de la souris
        this.addEventListener("pointermove", (e) => {
            if (!isDragging) {
                return;
            }
            // Mise à jour de la position de la pièce en tenant compte des limites
            position.x = clamp(position.x + e.movementX, 0, maxX);
            position.y = clamp(position.y + e.movementY, 0, maxY);
            piece.style.setProperty(
                "transform",
                `translate(${position.x}px, ${position.y}px)`
            );
            // Mise à jour de la valeur de l'entrée avec la nouvelle position
            input.value = `${position.x}-${position.y}`;
        });
    }
}

// Définition de l'élément personnalisé puzzle-captcha
customElements.define("puzzle-captcha", PuzzleCaptcha);

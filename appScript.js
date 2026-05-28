// On attend que le DOM soit complètement chargé
document.addEventListener("DOMContentLoaded", () => {

  /* 
     RÉCUPÉRATION DES ÉLÉMENTS DU DOM
  */

  const btnAdd = document.getElementById("btn-add-trip"); // bouton header "+ Nouveau voyage"
  const btnOpenFirst = document.querySelector(".btn-open-form"); // bouton état vide
  const modal = document.getElementById("trip-modal"); // modal create/update
  const form = document.getElementById("form-new-trip"); // modal create/update
  const msg = document.getElementById("newtrip-msg"); // message d'erreur/succès modal
  const grid = document.getElementById("grid-voyages"); // grille des voyages

  const confirmModal = document.getElementById("confirm-modal"); // modal confirmation suppression
  const confirmText = document.getElementById("confirm-text"); // texte confirmation suppression
  const btnCancel = document.getElementById("btn-cancel"); // bouton annuler 
  const btnConfirm = document.getElementById("btn-confirm"); // bouton confirmer

  let editingId = null; // contient l'id si on est en mode UPDATE
  let deleteTargetBtn = null; // bouton cible pour la suppression

  /*========================
     HELPERS MODAL
  ==========================*/

 // Ouvre la modal avec le titre et texte du bouton spécifiés
  function openModal(title, btnText) {
    modal.classList.remove("hidden");
    modal.setAttribute("aria-hidden", "false");
    modal.querySelector("#modal-title").textContent = title;
    document.getElementById("submit-btn").textContent = btnText;
    msg.textContent = "";
    msg.classList.remove("error", "success");
  }
  // Ferme la modal et réinitialise le formulaire
  function closeModal() {
    modal.classList.add("hidden");
    modal.setAttribute("aria-hidden", "true");
    editingId = null; 
    form.reset(); 
    modal.querySelector("#modal-title").textContent = "Nouveau voyage";
    document.getElementById("submit-btn").textContent = "Créer";
  }

  /* =======================
     OUVERTURE CREATE
  ======================= */

  // Clic sur "+ Nouveau voyage"
  btnAdd?.addEventListener("click", () => openModal("Nouveau voyage", "Créer"));
  btnOpenFirst?.addEventListener("click", () => openModal("Nouveau voyage", "Créer"));

  // Fermer si clic sur overlay ou bouton X
  modal?.addEventListener("click", e => {
    if (e.target?.dataset?.close === "1") closeModal();
  });

  document.addEventListener("keydown", e => {
    if (e.key === "Escape" && !modal.classList.contains("hidden")) {
      closeModal();
    }
  });

  /* =======================
     SUBMIT CREATE / UPDATE
  ======================= */
  form?.addEventListener("submit", async e => {
    e.preventDefault();

    msg.textContent = "";
    msg.classList.remove("error", "success");

    const fd = new FormData(form);
    if (editingId) fd.append("id", editingId);

    const url = editingId
      ? "api/voyage_update.php"
      : "api/voyage_create.php";

    try {
      const res = await fetch(url, {
        method: "POST",
        body: fd,
        credentials: "same-origin"
      });

      const data = await res.json();

      if (!res.ok || !data.ok) {
        msg.textContent = data.error || "Erreur.";
        msg.classList.add("error");
        return;
      }

      if (editingId) {
        
        closeModal();
        location.reload();
        return;
      }

      // CREATE → simple reload
      closeModal();
      location.reload();

    } catch {
      msg.textContent = "Erreur réseau.";
      msg.classList.add("error");
    }
  });

  /* =======================
     DELETE + UPDATE CLIC
  ======================= */
  grid?.addEventListener("click", e => {

    /* DELETE */
    const delBtn = e.target.closest(".btn.danger");
    if (delBtn) {
      deleteTargetBtn = delBtn;
      const title = delBtn.closest(".card")?.querySelector(".card-title")?.textContent || "";
      confirmText.textContent =
        `Êtes-vous sûr de vouloir supprimer le voyage "${title}" ? Cette action est irréversible.`;
      confirmModal.classList.remove("hidden");
      return;
    }

    /* UPDATE */
    const editBtn = e.target.closest(".btn.ghost");
    if (editBtn) {
      const card = editBtn.closest(".card");
      if (!card) return;

      editingId = editBtn.dataset.id;

      form.titre.value = card.querySelector(".card-title")?.textContent || "";
      form.categorie.value =
        card.querySelector(".badge")?.textContent === "Sans catégorie"
          ? ""
          : card.querySelector(".badge")?.textContent || "";
      form.description.value =
        card.querySelector(".card-desc")?.textContent || "";
      form.date_depart.value = card.dataset.dateDepart || "";
      form.date_retour.value = card.dataset.dateRetour || "";

      openModal("Modifier le voyage", "Enregistrer les modifications");
    }
  });

  /* =======================
     CONFIRM DELETE
  ======================= */
  btnCancel?.addEventListener("click", () => {
    confirmModal.classList.add("hidden");
    deleteTargetBtn = null;
  });

  btnConfirm?.addEventListener("click", async () => {
    if (!deleteTargetBtn) return;

    const id = deleteTargetBtn.dataset.id;

    try {
      const fd = new FormData();
      fd.append("id", id);

      const res = await fetch("api/voyage_delete.php", {
        method: "POST",
        body: fd,
        credentials: "same-origin"
      });

      const data = await res.json();

      if (!res.ok || !data.ok) {
        alert(data.error || "Erreur suppression");
        return;
      }

      deleteTargetBtn.closest(".card")?.remove();
      confirmModal.classList.add("hidden");
      deleteTargetBtn = null;
      location.reload();



      confirmModal.classList.add("hidden");
      deleteTargetBtn = null;

    } catch {
      alert("Erreur réseau");
      confirmModal.classList.add("hidden");
      deleteTargetBtn = null;
    }
  });

  /* =======================
     HELPERS
  ======================= */
  function escapeHtml(str) {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }
  // =========================
  // AUTO-SUBMIT FILTRE
  // =========================
  document.getElementById("select-categorie")
    ?.addEventListener("change", function () {
      this.form.submit();
  });

  // =========================
  // AUTO-SEARCH (debounce)
  // =========================
  const inputQ = document.getElementById("search-q");
  const searchForm = inputQ?.form;

  let timer = null;

  inputQ?.addEventListener("input", () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      // Reset pagination
      const pageInput = searchForm?.querySelector('input[name="page"]');
      if (pageInput) pageInput.value = "1";

      searchForm?.submit();
    }, 750); // délai pour éviter trop de reloads
  });

});

document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("confirmation-modal");
  const message = document.getElementById("confirmation-message");
  const cancelButton = document.getElementById("confirmation-cancel");
  const confirmButton = document.getElementById("confirmation-confirm");

  if (!modal || !message || !cancelButton || !confirmButton) {
    return;
  }

  let pendingForm = null;

  function openModal(form) {
    pendingForm = form;

    message.textContent =
      form.dataset.confirm || "Deseja realmente continuar com esta operação?";

    modal.hidden = false;
    document.body.classList.add("modal-open");
    cancelButton.focus();
  }

  function closeModal() {
    modal.hidden = true;
    document.body.classList.remove("modal-open");
    pendingForm = null;
  }

  document.querySelectorAll("form[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      if (form.dataset.confirmed === "true") {
        delete form.dataset.confirmed;
        return;
      }

      event.preventDefault();
      openModal(form);
    });
  });

  cancelButton.addEventListener("click", closeModal);

  confirmButton.addEventListener("click", () => {
    if (!pendingForm) {
      closeModal();
      return;
    }

    const form = pendingForm;

    form.dataset.confirmed = "true";
    closeModal();
    form.requestSubmit();
  });

  modal.addEventListener("click", (event) => {
    if (event.target === modal) {
      closeModal();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !modal.hidden) {
      closeModal();
    }
  });
});

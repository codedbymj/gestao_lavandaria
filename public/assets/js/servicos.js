document.addEventListener("DOMContentLoaded", () => {
  const body = document.getElementById("items-body");

  const template = document.getElementById("item-template");

  const addButton = document.getElementById("add-item");

  const discountInput = document.getElementById("discount");

  if (!body || !template || !addButton || !discountInput) {
    return;
  }

  const money = (value) => {
    return (
      new Intl.NumberFormat("pt-AO", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value) + " Kz"
    );
  };

  function calculate() {
    let subtotal = 0;

    body.querySelectorAll(".service-item").forEach((row) => {
      const selected = row.querySelector(".price-select").selectedOptions[0];

      const price = Number(selected?.dataset.price || 0);

      const quantity = Math.max(
        1,
        Number(row.querySelector(".quantity-input").value || 1),
      );

      const lineTotal = price * quantity;

      row.querySelector(".line-total").textContent = money(lineTotal);

      subtotal += lineTotal;
    });

    const discountPercentage = Math.min(
      100,
      Math.max(0, Number(discountInput.value || 0)),
    );

    const discountAmount = (subtotal * discountPercentage) / 100;

    const total = subtotal - discountAmount;

    document.getElementById("subtotal-display").textContent = money(subtotal);

    document.getElementById("discount-display").textContent =
      discountPercentage.toLocaleString("pt-AO", {
        maximumFractionDigits: 2,
      }) +
      "% (- " +
      money(discountAmount) +
      ")";

    document.getElementById("total-display").textContent = money(
      Math.max(0, total),
    );
  }

  function addItem() {
    const row = template.content.firstElementChild.cloneNode(true);

    row.querySelectorAll("select, input").forEach((element) => {
      element.addEventListener("input", calculate);
    });

    row.querySelector(".remove-item").addEventListener("click", () => {
      row.remove();
      calculate();
    });

    body.appendChild(row);
    calculate();
  }

  addButton.addEventListener("click", addItem);

  discountInput.addEventListener("input", calculate);

  if (!addButton.disabled) {
    addItem();
  }
});

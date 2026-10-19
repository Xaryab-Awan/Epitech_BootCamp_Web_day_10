const searchType = document.querySelector("#search-type");
const searchBrand = document.querySelector("#search-brand");
const searchPrice = document.querySelector("#search-price");
const searchNumber = document.querySelector("#search-number");

const searchButton = document.querySelector("#search-button");
const searchMessage = document.querySelector("#search-message");
const results = document.querySelector("#results");

const insertButton = document.querySelector("#insert-button");
const insertSection = document.querySelector("#insert-section");

const insertType = document.querySelector("#insert-type");
const insertBrand = document.querySelector("#insert-brand");
const insertPrice = document.querySelector("#insert-price");
const insertNumber = document.querySelector("#insert-number");

const saveButton = document.querySelector("#save-button");
const insertMessage = document.querySelector("#insert-message");

/* SEARCH PRODUCTS */

searchButton.addEventListener("click", async function () {
  results.replaceChildren();
  searchMessage.textContent = "";
  searchMessage.className = "";

  const params = new URLSearchParams({
    action: "search",
    type: searchType.value,
    brand: searchBrand.value,
    price: searchPrice.value,
    number: searchNumber.value,
  });

  try {
    const response = await fetch("task06.php?" + params.toString());
    const data = await response.json();

    if (!response.ok || !data.success) {
      searchMessage.textContent = data.error || "Search failed.";
      searchMessage.className = "error";
      return;
    }

    for (const product of data.products) {
      const row = document.createElement("tr");

      for (const value of [
        product.type,
        product.brand,
        product.price,
        product.number,
        product.stock,
      ]) {
        const cell = document.createElement("td");
        cell.textContent = value;
        row.appendChild(cell);
      }

      results.appendChild(row);
    }
  } catch (error) {
    searchMessage.textContent = "Request failed.";
    searchMessage.className = "error";
    console.error(error);
  }
});

/* OPEN INSERT SECTION */

insertButton.addEventListener("click", function () {
  insertSection.style.display = "block";
  insertMessage.textContent = "";
});

/* INSERT OR UPDATE PRODUCT */

saveButton.addEventListener("click", async function () {
  insertMessage.textContent = "";
  insertMessage.className = "";

  const params = new URLSearchParams({
    action: "save",
    type: insertType.value,
    brand: insertBrand.value,
    price: insertPrice.value,
    number: insertNumber.value,
  });

  try {
    const response = await fetch("task06.php?" + params.toString());
    const data = await response.json();

    if (!response.ok || !data.success) {
      insertMessage.textContent = data.error || "Request failed.";
      insertMessage.className = "error";
      return;
    }

    insertMessage.textContent = data.message;
    insertMessage.className = "valid";
  } catch (error) {
    insertMessage.textContent = "Request failed.";
    insertMessage.className = "error";
    console.error(error);
  }
});

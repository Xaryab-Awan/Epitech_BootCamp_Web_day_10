const type = document.querySelector("#type");
const brand = document.querySelector("#brand");
const price = document.querySelector("#price");
const number = document.querySelector("#number");

const typeMessage = document.querySelector("#type-message");
const brandMessage = document.querySelector("#brand-message");
const priceMessage = document.querySelector("#price-message");
const numberMessage = document.querySelector("#number-message");

const searchButton = document.querySelector("#search-button");
const requestMessage = document.querySelector("#request-message");
const results = document.querySelector("#results");

/* TYPE VALIDATION */

function validateType() {
  if (type.value.length < 3) {
    typeMessage.textContent = "Type must have at least 3 characters.";
    typeMessage.className = "error";
    return false;
  }

  if (type.value.length > 10) {
    typeMessage.textContent = "Type must have at most 10 characters.";
    typeMessage.className = "error";
    return false;
  }

  if (!/^[a-z-]+$/i.test(type.value)) {
    typeMessage.textContent = "Type contains invalid characters.";
    typeMessage.className = "error";
    return false;
  }

  typeMessage.textContent = "Valid type format.";
  typeMessage.className = "valid";
  return true;
}

/* BRAND VALIDATION */

function validateBrand() {
  if (brand.value.length < 2) {
    brandMessage.textContent = "Brand must have at least 2 characters.";
    brandMessage.className = "error";
    return false;
  }

  if (brand.value.length > 20) {
    brandMessage.textContent = "Brand must have at most 20 characters.";
    brandMessage.className = "error";
    return false;
  }

  if (!/^[a-z0-9&-]+$/i.test(brand.value)) {
    brandMessage.textContent = "Brand contains invalid characters.";
    brandMessage.className = "error";
    return false;
  }

  brandMessage.textContent = "Valid brand format.";
  brandMessage.className = "valid";
  return true;
}

/* PRICE VALIDATION */

function validatePrice() {
  if (price.value.length < 2) {
    priceMessage.textContent = "Price does not have enough characters.";
    priceMessage.className = "error";
    return false;
  }

  if (price.value.length > 5) {
    priceMessage.textContent = "Price has too many characters.";
    priceMessage.className = "error";
    return false;
  }

  if (!/^[><=][0-9]+$/.test(price.value)) {
    priceMessage.textContent =
      "Invalid price string. Use >, < or = followed by digits.";
    priceMessage.className = "error";
    return false;
  }

  priceMessage.textContent = "Valid price format.";
  priceMessage.className = "valid";
  return true;
}

/* NUMBER VALIDATION */

function validateNumber() {
  if (
    number.value.trim() === "" ||
    !/^[0-9]+$/.test(number.value) ||
    Number(number.value) <= 0
  ) {
    numberMessage.textContent = "Number must be a positive whole number.";
    numberMessage.className = "error";
    return false;
  }

  numberMessage.textContent = "Valid number.";
  numberMessage.className = "valid";
  return true;
}

/* REAL-TIME VALIDATION */

type.addEventListener("input", validateType);
brand.addEventListener("input", validateBrand);
price.addEventListener("input", validatePrice);
number.addEventListener("input", validateNumber);

/* SEARCH PRODUCTS */

searchButton.addEventListener("click", async function () {
  results.replaceChildren();
  requestMessage.textContent = "";
  requestMessage.className = "";

  const typeValid = validateType();
  const brandValid = validateBrand();
  const priceValid = validatePrice();
  const numberValid = validateNumber();

  if (!typeValid || !brandValid || !priceValid || !numberValid) {
    requestMessage.textContent = "Please correct the invalid fields.";
    requestMessage.className = "error";
    return;
  }

  const params = new URLSearchParams({
    type: type.value,
    brand: brand.value,
    price: price.value,
    number: number.value,
  });

  try {
    const response = await fetch("task05.php?" + params.toString());
    const data = await response.json();

    if (!response.ok || !data.success) {
      requestMessage.textContent = data.error || "Request failed.";
      requestMessage.className = "error";
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

    requestMessage.textContent = "Search completed successfully.";
    requestMessage.className = "valid";
  } catch (error) {
    requestMessage.textContent = "Request failed. Please try again.";
    requestMessage.className = "error";
    console.error(error);
  }
});

const typeInput = document.querySelector("#type");
const brandInput = document.querySelector("#brand");

const typeMessage = document.querySelector("#type-message");
const brandMessage = document.querySelector("#brand-message");

let requestNumber = 0;

async function validateFields() {
  const type = typeInput.value;
  const brand = brandInput.value;

  const params = new URLSearchParams({
    type: type,
    brand: brand,
  });

  const currentRequest = requestNumber++;

  try {
    const response = await fetch("task04.php?" + params);
    const result = await response.json();

    if (currentRequest !== requestNumber) {
      return;
    }

    typeMessage.textContent = result.typeMessage;
    typeMessage.className = result.typeValid ? "valid" : "error";

    brandMessage.textContent = result.brandMessage;
    brandMessage.className = result.brandValid ? "valid" : "error";
  } catch (error) {
    console.error("Validation error:", error);
  }
}

typeInput.addEventListener("input", validateFields);
brandInput.addEventListener("input", validateFields);

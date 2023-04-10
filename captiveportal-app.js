"use strict";

const form = document.getElementById("enregistrement");

const raInput = document.querySelector(".ra__input");
const raError = document.querySelector(".ra__error");
raError.style.display = "none";
const raErrorContribuitor = document.querySelector(".ra__error-contribuitor");
raErrorContribuitor.style.display = "none";

const fullNameInput = document.querySelector(".full-name__input");
const fullNameError = document.querySelector(".full-name__error");
fullNameError.style.display = "none";

const checkbox = document.getElementById("termsOfUse");
const checkboxError = document.querySelector(".checkbox__error");
checkboxError.style.display = "none";

const checkboxTerms = document.querySelector(".checkbox__terms");
checkboxTerms.style.display = "none";

const termsLink = document.querySelector(".terms__link");

const termsButton = document.querySelector(".terms__button");

const selectContainer = document.querySelector(".course-container");
const courseDefault = document.querySelector(".course__default");
const courseError = document.querySelector(".course__error");
courseError.style.display = "none";

const registerButton = document.querySelector(".register__button");

const voucherInput = document.querySelector(".voucher__input");
const voucherError = document.querySelector(".voucher__error");
voucherError.style.display = "none";

const voucherButton = document.querySelector(".voucher__button");

const validateRaInput = function () {
  let status = true;

  if (raInput.value == "") {
    raInput.classList.add("error");
    raError.classList.add("error-text");
    raError.style.display = "block";
    raInput.focus();
    status = false;
  }
  return status;
};

const validateIsContributor = async function () {
  let status = true;

  const response = await fetch("./captiveportal-contribuintes.txt");
  const data = await response.text();

  let contribuitors = data;
  const lineBreak = /\r/g;
  const newLine = /\n/g;

  contribuitors = contribuitors
    .replace(lineBreak, "")
    .replace(newLine, "")
    .split(",");

  if (!contribuitors.includes(raInput.value)) {
    raInput.classList.add("error");
    raErrorContribuitor.classList.add("error-text");
    raErrorContribuitor.style.display = "block";
    raInput.focus();
    status = false;
  }

  return status;
};

const removeRaErrors = function () {
  raInput.classList.remove("error");
  raError.classList.remove("error-text");
  raError.style.display = "none";
};

const removeRaErrorsContribuitor = function () {
  raInput.classList.remove("error");
  raErrorContribuitor.classList.remove("error-text");
  raErrorContribuitor.style.display = "none";
};

const validateFullNameInput = function () {
  let status = true;

  if (fullNameInput.value == "") {
    fullNameInput.classList.add("error");
    fullNameError.classList.add("error-text");
    fullNameError.style.display = "block";
    fullNameInput.focus();
    status = false;
  }
  return status;
};

const removeFullNameErrors = function () {
  fullNameInput.classList.remove("error");
  fullNameError.classList.remove("error-text");
  fullNameError.style.display = "none";
};

const validateSelect = function (event) {
  let status = true;

  if (selectContainer.value == "") {
    selectContainer.classList.add("error");
    courseError.classList.add("error-text");
    courseError.style.display = "block";
    status = false;
  }

  return status;
};

const removeCourseErrors = function () {
  selectContainer.classList.remove("error");
  selectContainer.classList.remove("error-text");
  courseError.style.display = "none";
};

const removeCourseDefault = function () {
  courseDefault.style.display = "none";
};

const validateCheckbox = function () {
  let status = true;

  if (!checkbox.checked) {
    checkbox.classList.add("error");
    checkboxError.classList.add("error-text");
    checkboxError.style.display = "block";
    status = false;
  } else {
    removeCheckboxErrors();
  }
  return status;
};

const removeCheckboxErrors = function () {
  checkbox.classList.remove("error");
  checkboxError.classList.remove("error-text");
  checkboxError.style.display = "none";
};

const handleTermsLinkClick = function () {
  checkboxTerms.style.display = "flex";
};

const handleTermsButtonClick = function (event) {
  event.preventDefault();
  checkboxTerms.style.display = "none";
};

const validateVoucherInput = function (event) {
  let status = true;

  if (voucherInput.value == "") {
    voucherInput.classList.add("error");
    voucherError.classList.add("error-text");
    voucherError.style.display = "block";
    voucherInput.focus();
    event.preventDefault();
    status = false;
  }
  return status;
};

const removeVoucherErrors = function () {
  voucherInput.classList.remove("error");
  voucherError.classList.remove("error-text");
  voucherError.style.display = "none";
};

const validateAll = async function () {
  if (
    validateRaInput() &&
    validateFullNameInput() &&
    validateSelect() &&
    validateCheckbox() &&
    (await validateIsContributor())
  ) {
    form.submit();
  }
};

raInput.addEventListener("keydown", removeRaErrors);
raInput.addEventListener("keydown", removeRaErrorsContribuitor);

fullNameInput.addEventListener("keydown", removeFullNameErrors);

checkbox.addEventListener("click", removeCheckboxErrors);

termsLink.addEventListener("click", handleTermsLinkClick);

termsButton.addEventListener("click", handleTermsButtonClick);

registerButton.addEventListener("click", validateAll);

voucherInput.addEventListener("keydown", removeVoucherErrors);

voucherButton.addEventListener("click", validateVoucherInput);

selectContainer.addEventListener("change", removeCourseErrors);

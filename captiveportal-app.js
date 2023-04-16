"use strict";

const loginForm = document.querySelector(".login");

const loginTitle = document.querySelector(".login__title");

const signUpform = document.getElementById("enregistrement");

const signUpTitle = document.querySelector(".signUp__title");

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

const selectContainer = document.querySelector(".courses-container");
const courseDefault = document.querySelector(".course__default");
const courseError = document.querySelector(".course__error");
courseError.style.display = "none";

const registerButton = document.querySelector(".register__button");

const registerAlreadyRegistred = document.querySelector(
  ".register__already-registred"
);

const anotherOption = document.querySelector(".another-option");

const voucherForm = document.querySelector(".voucher");

const voucherInput = document.querySelector(".voucher__input");
const voucherError = document.querySelector(".voucher__error");
voucherError.style.display = "none";

const voucherButton = document.querySelector(".voucher__button");

const userInput = document.querySelector(".user__input");
const userError = document.querySelector(".user__error");
userError.style.display = "none";

const passwordInput = document.querySelector(".password__input");
const passwordError = document.querySelector(".password__error");
passwordError.style.display = "none";

const loginButton = document.querySelector(".login__button");

const loginNotRegistred = document.querySelector(".login__not-registred");

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

const validateSelect = function () {
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

const validateSignUp = async function () {
  if (
    validateRaInput() &&
    validateFullNameInput() &&
    validateSelect() &&
    validateCheckbox() &&
    (await validateIsContributor())
  ) {
    signUpform.submit();
  }
};

const validateUserInput = function () {
  let status = true;

  if (userInput.value == "") {
    userInput.classList.add("error");
    userError.classList.add("error-text");
    userError.style.display = "block";
    userInput.focus();
    status = false;
  }
  return status;
};

const validatePasswordInput = function () {
  let status = true;

  if (passwordInput.value == "") {
    passwordInput.classList.add("error");
    passwordError.classList.add("error-text");
    passwordError.style.display = "block";
    passwordInput.focus();
    status = false;
  }
  return status;
};

const removeUserErrors = function () {
  userInput.classList.remove("error");
  userError.classList.remove("error-text");
  userError.style.display = "none";
};

const removePasswordErrors = function () {
  passwordInput.classList.remove("error");
  passwordError.classList.remove("error-text");
  passwordError.style.display = "none";
};

const validateLogin = function () {
  if (validateUserInput() && validatePasswordInput()) {
    loginForm.submit();
  }
};

const changeAuthenticationMethodLogin = function () {
  signUpTitle.style.display = "none";
  signUpform.style.display = "none";
  anotherOption.style.display = "none";
  voucherForm.style.display = "none";

  loginTitle.style.display = "block";
  loginForm.style.display = "block";
};

const changeAuthenticationMethodSignUp = function () {
  signUpTitle.style.display = "block";
  signUpform.style.display = "flex";
  anotherOption.style.display = "block";
  voucherForm.style.display = "flex";

  loginTitle.style.display = "none";
  loginForm.style.display = "none";
};

raInput.addEventListener("keydown", removeRaErrors);
raInput.addEventListener("keydown", removeRaErrorsContribuitor);

fullNameInput.addEventListener("keydown", removeFullNameErrors);

checkbox.addEventListener("click", removeCheckboxErrors);

termsLink.addEventListener("click", handleTermsLinkClick);

termsButton.addEventListener("click", handleTermsButtonClick);

registerButton.addEventListener("click", validateSignUp);

registerAlreadyRegistred.addEventListener(
  "click",
  changeAuthenticationMethodLogin
);

voucherInput.addEventListener("keydown", removeVoucherErrors);

voucherButton.addEventListener("click", validateVoucherInput);

selectContainer.addEventListener("change", removeCourseErrors);

userInput.addEventListener("keydown", removeUserErrors);

passwordInput.addEventListener("keydown", removePasswordErrors);

loginButton.addEventListener("click", validateLogin);

loginNotRegistred.addEventListener("click", changeAuthenticationMethodSignUp);

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

// TODO Fazer isso funcionar
// const removeErrors = function (input, error) {
//   input.classList.remove("error");
//   error.classList.remove("error-text");
//   error.style.display = "none";
// };

const validate = function (input, error) {
  let status = true;

  if (input.value == "") {
    input.classList.add("error");
    error.classList.add("error-text");
    error.style.display = "block";
    input.focus();
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

const validateSignUp = async function () {
  if (
    validate(raInput, raError) &&
    validate(fullNameInput, fullNameError) &&
    validate(selectContainer, courseError) &&
    validateCheckbox() &&
    (await validateIsContributor())
  ) {
    signUpform.submit();
  }
};

const validateLogin = function (event) {
  if (
    validate(userInput, userError) &&
    validate(passwordInput, passwordError)
  ) {
    loginForm.submit();
  } else {
    event.preventDefault();
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

termsLink.addEventListener("click", handleTermsLinkClick);

termsButton.addEventListener("click", handleTermsButtonClick);

registerButton.addEventListener("click", validateSignUp);

registerAlreadyRegistred.addEventListener(
  "click",
  changeAuthenticationMethodLogin
);

voucherButton.addEventListener("click", validateVoucherInput);

loginButton.addEventListener("click", validateLogin);

loginNotRegistred.addEventListener("click", changeAuthenticationMethodSignUp);

//Remover input errors
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

const removeFullNameErrors = function () {
  fullNameInput.classList.remove("error");
  fullNameError.classList.remove("error-text");
  fullNameError.style.display = "none";
};

const removeCourseErrors = function () {
  selectContainer.classList.remove("error");
  selectContainer.classList.remove("error-text");
  courseError.style.display = "none";
};

const removeCheckboxErrors = function () {
  checkbox.classList.remove("error");
  checkboxError.classList.remove("error-text");
  checkboxError.style.display = "none";
};

const removeVoucherErrors = function () {
  voucherInput.classList.remove("error");
  voucherError.classList.remove("error-text");
  voucherError.style.display = "none";
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

raInput.addEventListener("keydown", removeRaErrors);
raInput.addEventListener("keydown", removeRaErrorsContribuitor);

fullNameInput.addEventListener("keydown", removeFullNameErrors);

checkbox.addEventListener("click", removeCheckboxErrors);

voucherInput.addEventListener("keydown", removeVoucherErrors);

selectContainer.addEventListener("change", removeCourseErrors);

userInput.addEventListener("keydown", removeUserErrors);

passwordInput.addEventListener("keydown", removePasswordErrors);

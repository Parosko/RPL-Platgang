document.addEventListener("DOMContentLoaded", function () {

    const passwordInput = document.querySelector("input[name='password']");

    if (passwordInput) {
        passwordInput.addEventListener("focus", function () {
            this.style.borderColor = "#333";
        });

        passwordInput.addEventListener("blur", function () {
            this.style.borderColor = "#ddd";
        });
    }

});
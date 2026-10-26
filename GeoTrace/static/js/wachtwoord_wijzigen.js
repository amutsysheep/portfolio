const currentPasswordInput = document.getElementById("current_password");
const newPasswordInput = document.getElementById("new_password");

const rules = {
    length: document.getElementById("rule-length"),
    uppercase: document.getElementById("rule-uppercase"),
    lowercase: document.getElementById("rule-lowercase"),
    number: document.getElementById("rule-number"),
    special: document.getElementById("rule-special"),
    different: document.getElementById("rule-different")
};

function setRuleStatus(ruleElement, isValid) {
    if (!ruleElement) {
        return;
    }

    if (isValid) {
        ruleElement.classList.add("valid");
    } else {
        ruleElement.classList.remove("valid");
    }
}

function checkPasswordRules() {
    const currentPassword = currentPasswordInput.value;
    const newPassword = newPasswordInput.value;

    setRuleStatus(rules.length, newPassword.length >= 16);
    setRuleStatus(rules.uppercase, /[A-Z]/.test(newPassword));
    setRuleStatus(rules.lowercase, /[a-z]/.test(newPassword));
    setRuleStatus(rules.number, /[0-9]/.test(newPassword));
    setRuleStatus(rules.special, /[^A-Za-z0-9]/.test(newPassword));
    setRuleStatus(
        rules.different,
        currentPassword.length > 0 && newPassword.length > 0 && currentPassword !== newPassword
    );
}

if (currentPasswordInput && newPasswordInput) {
    currentPasswordInput.addEventListener("input", checkPasswordRules);
    newPasswordInput.addEventListener("input", checkPasswordRules);
}
$(document).ready(function() {
    // 1. Настройка и подключение маски к полю телефона
    $('input[name="phone"]').inputmask("+38(999)999-99-99", {
        clearIncomplete: false, // Не стирать введенное, если номер введен не до конца
        showMaskOnHover: false  // Показывать маску только при фокусе
    });

    // 2. Валидация при отправке формы
    $('form').on('submit', function(e) {
        let phoneInput = $(this).find('input[name="phone"]');
        let phoneNumber = phoneInput.val();
        
        // Регулярка ищет символ "_" или проверяет, заполнена ли маска полностью
        let hasUnderscores = /_+/.test(phoneNumber);

        if (hasUnderscores || !phoneInput.inputmask("isComplete")) {
            e.preventDefault(); // Блокируем отправку
            
            // Подсветка ошибки для пользователя
            phoneInput.css('border', '2px solid red');
            alert('Будь ласка, введіть номер телефону повністю!');
        } else {
            phoneInput.css('border', ''); // Снимаем подсветку, если всё ок
        }
    });
});
